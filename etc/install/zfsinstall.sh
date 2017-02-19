#!/bin/sh
#
# /etc/install/zfsinstall.sh
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#
# Debug script
# set -x

# Set environment.
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin
export PATH

# Global variables.
PLATFORM=`uname -m`
CDPATH="/mnt/cdrom"
SYSBACKUP="/tmp/sysbackup"
PRDNAME=`cat /etc/prd.name`
APPNAME="RootOnZFS"
ZROOT="zroot"

tmpfile=`tmpfile 2>/dev/null` || tmpfile=/tmp/tui$$
trap "rm -f $tmpfile" 0 1 2 5 15

# Mount CD/USB drive.
mount_cdrom()
{
	LIVECD=`glabel list | grep -q iso9660/${PRDNAME}; echo $?`
	LIVEUSB=`glabel list | grep -q ufs/liveboot; echo $?`
	if [ "${LIVECD}" == 0 ]; then
		# Check if cd-rom is mounted else auto mount cd-rom.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount cd-rom.
			mkdir -p ${CDPATH}
			echo "Mounting CD-ROM Drive"
			mount_cd9660 /dev/cd0 ${CDPATH} > /dev/null 2>&1 || mount_cd9660 /dev/cd1 ${CDPATH} > /dev/null 2>&1
		fi
	elif [ "${LIVEUSB}" == 0 ]; then
		# Check if liveusb is mounted else auto mount liveusb.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount liveusb.
			mkdir -p ${CDPATH}
			echo "Mounting LiveUSB Drive"
			mount /dev/ufs/liveboot ${CDPATH} > /dev/null 2>&1
		fi
	fi
	# If no cd/usb is mounted ask for manual mount.
	if [ ! -f "${CDPATH}/version" ]; then
		manual_cdmount
	fi
}

umount_cdrom()
{
	echo "Unmount CD/USB Drive"
	umount -f ${CDPATH} > /dev/null 2>&1
	rm -R ${CDPATH}
}

manual_cdmount()
{
	DRIVES=`camcontrol devlist`

	cdialog --backtitle "$PRDNAME $APPNAME Installer" --title "Select the Install Media Source" \
	--form "${DRIVES}" 0 0 0 \
	"Select CD/USB Drive e.g: cd0:" 1 1 "" 1 30 30 30 \
	2>/tmp/_zmcd
	if [ 0 -ne $? ]; then
		exit 0
	fi

	# Try to mount from specified device.
	echo "Mounting CD/USB Drive"
	DEVICE=`awk '{ print $1; }' /tmp/_zmcd | tr -d '"'`
	mount /dev/${DEVICE}s1a ${CDPATH} > /dev/null 2>&1 || mount_cd9660 /dev/${DEVICE} ${CDPATH} > /dev/null 2>&1
	# Check if mounted cd/usb is accessible.
	if [ ! -f "${CDPATH}/version" ]; then
		# Re-try
		mount_cdrom
	fi
}

# Clean any existing metadata on selected disks.
cleandisk_init()
{
	sysctl kern.geom.debugflags=0x10
	sleep 1

	# Load geom_mirror kernel module.
	if ! kldstat | grep -q geom_mirror; then
		kldload /boot/kernel/geom_mirror.ko
	fi

	# Destroy any existing swap gmirror.
	gmirror destroy -f gswap > /dev/null 2>&1

	# Check if disk has been specified.
	if [ ! -z "${DISK1}" ]; then
		echo "Cleaning disk ${DISK1}"
		gmirror clear ${DISK1} > /dev/null 2>&1
		zpool labelclear -f /dev/gpt/sysdisk0 > /dev/null 2>&1
		zpool labelclear -f /dev/${DISK1} > /dev/null 2>&1
		gpart destroy -F ${DISK1} > /dev/null 2>&1

		diskinfo ${DISK1} | while read DISK1 sectorsize size sectors other
			do
				# Delete MBR, GPT Primary, ZFS(L0L1)/other partition table.
				/bin/dd if=/dev/zero of=/dev/${DISK1} bs=${sectorsize} count=8192 > /dev/null 2>&1
				# Delete GEOM metadata, GPT Secondary(L2L3).
				/bin/dd if=/dev/zero of=/dev/${DISK1} bs=${sectorsize} oseek=`expr ${sectors} - 8192` count=8192 > /dev/null 2>&1
			done
	fi

	# Check if disk has been specified.
	if [ ! -z "${DISK2}" ]; then
		echo "Cleaning disk ${DISK2}"
		gmirror clear ${DISK2} > /dev/null 2>&1
		zpool labelclear -f /dev/gpt/sysdisk1 > /dev/null 2>&1
		zpool labelclear -f /dev/${DISK2} > /dev/null 2>&1
		gpart destroy -F ${DISK2} > /dev/null 2>&1

		diskinfo ${DISK2} | while read DISK2 sectorsize size sectors other
			do
				# Delete MBR, GPT Primary, ZFS(L0L1)/other partition table.
				/bin/dd if=/dev/zero of=/dev/${DISK2} bs=${sectorsize} count=8192 > /dev/null 2>&1
				# Delete GEOM metadata, GPT Secondary(L2L3).
				/bin/dd if=/dev/zero of=/dev/${DISK2} bs=${sectorsize} oseek=`expr ${sectors} - 8192` count=8192 > /dev/null 2>&1
			done
	fi
}

# Create GPT/Partition on disks.
gptpart_init()
{
	# Check if disk has been specified.
	if [ ! -z "${DISK1}" ]; then
		echo "Creating GPT/Partition on ${DISK1}"
		gpart create -s gpt ${DISK1} > /dev/null

		# Create boot partition.
		gpart add -a 4k -s 512K -t freebsd-boot -l sysboot ${DISK1} > /dev/null
		#gpart add -a 4k -s 800K -t efi -l efiboot ${DISK1} > /dev/null

		#gpart add -s 512K -t freebsd-boot ${DISK1} > /dev/null
		if [ ! -z "${SWAP}" ]; then
			gpart add -a 4m -s ${SWAP} -t freebsd-swap -l swap0 ${DISK1} > /dev/null
		fi
		gpart add -a 4m ${ZROOTSIZE} -t freebsd-zfs -l sysdisk0 ${DISK1} > /dev/null
	fi

	# Check if disk has been specified.
	if [ ! -z "${DISK2}" ]; then
		echo "Creating GPT/Partition on ${DISK2}"
		gpart create -s gpt ${DISK2} > /dev/null

		# Create boot partition.
		gpart add -a 4k -s 512K -t freebsd-boot -l sysboot ${DISK2} > /dev/null
		#gpart add -a 4k -s 800K -t efi -l efiboot ${DISK2} > /dev/null

		#gpart add -s 512K -t freebsd-boot ${DISK2} > /dev/null
		if [ ! -z "${SWAP}" ]; then
			gpart add -a 4m -s ${SWAP} -t freebsd-swap -l swap1 ${DISK2} > /dev/null
		fi
		gpart add -a 4m ${ZROOTSIZE} -t freebsd-zfs -l sysdisk1 ${DISK2} > /dev/null
	fi
}

# Install NAS4Free on single zfs disk.
zdisk_init()
{
	# If more than one drive is specified then exit.
	if [ ! -z "${DISK2}" ]; then
		cdialog --msgbox "You should select a maximum of one drive for ZFS Disk Install!" 6 50 && exit 1
	fi

	# Check if variables has been specified.
	if [ -z "${DISK1}" ]; then
		cdialog --msgbox "You should select at least one drive for ZFS Disk Install!" 6 50 && exit 1
	fi

	# Install confirmation.
	install_yesno

	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	mount_cdrom

	# Check for existing zroot pool.
	zpool_check

	# Get rid of any metadata on selected disk.
	cleandisk_init

	# Create GPT/Partition on disk.
	gptpart_init

	# Create bootable zfs disk with boot environments support.
	echo "Creating bootable ${ZROOT} Disk"
	export ALTROOT="/mnt/sys_install"
	export DATASET="/ROOT"
	export BOOTENV="/default-install"

	zpool create -f -R ${ALTROOT}/${ZROOT} ${ZROOT} /dev/gpt/sysdisk0
	zfs set canmount=off ${ZROOT}
	zfs create -o canmount=off ${ZROOT}${DATASET}
	zfs create -o mountpoint=/ ${ZROOT}${DATASET}${BOOTENV}
	zfs set freebsd:boot-environment=1 ${ZROOT}${DATASET}${BOOTENV}
	zpool set bootfs=${ZROOT}${DATASET}${BOOTENV} ${ZROOT}

	if [ $? -eq 1 ]; then
		echo "An error has occurred while creating ${ZROOT} pool."
		exit 1
	fi

	# Install system files.
	install_sys_files

	# Write bootcode.
	echo "Writing bootcode..."
	gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 1 ${DISK1}
	#/bin/dd if=/boot/boot1.efifat of=/dev/${DISK1}"p2" > /dev/null 2>&1

	sysctl kern.geom.debugflags=0
	sleep 1

	# Add swap device to fstab.
	if [ ! -z "${SWAP}" ]; then
		echo "/dev/gpt/swap0 none swap sw 0 0" >> ${ALTROOT}/${ZROOT}/etc/fstab
	fi

	# Unmount cd-rom.
	umount_cdrom

	# Creates system default snapshot after install.
	create_default_snapshot

	# Flush disk cache and wait 1 second.
	sync && sleep 1
	zpool export ${ZROOT}
	rm -Rf ${ALTROOT}

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "$PRDNAME $APPNAME Disk Successfully Installed!" 6 60
	else
		echo "An error has occurred during the installation."
		exit 1
	fi
	exit 0
}

# Install NAS4Free RootOnZFS on zfs mirror.
zmirror_init()
{
	# If more than two drives are specified then exit.
	if [ ! -z "${DISKX}" ]; then
		cdialog --msgbox "You should select a maximum of two drive for ZFS Mirror Install!" 6 50 && exit 1
	fi

	# Check if variables has been specified.
	if [ -z "${DISK2}" ]; then
		cdialog --msgbox "You should select a minimum of two drives for ZFS Mirror Install!" 6 50 && exit 1
	fi

	# Install confirmation.
	install_yesno

	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	mount_cdrom

	# Check for existing zroot pool.
	zpool_check

	# Get rid of any metadata on selected disk.
	cleandisk_init

	# Create GPT/Partition on disk.
	gptpart_init

	# Create bootable zfs mirror with boot environments support.
	echo "Creating bootable ${ZROOT} Mirror"
	export ALTROOT="/mnt/sys_install"
	export DATASET="/ROOT"
	export BOOTENV="/default-install"

	zpool create -f -R ${ALTROOT}/${ZROOT} ${ZROOT} /dev/gpt/sysdisk0 /dev/gpt/sysdisk1
	zfs set canmount=off ${ZROOT}
	zfs create -o canmount=off ${ZROOT}${DATASET}
	zfs create -o mountpoint=/ ${ZROOT}${DATASET}${BOOTENV}
	zfs set freebsd:boot-environment=1 ${ZROOT}${DATASET}${BOOTENV}
	zpool set bootfs=${ZROOT}${DATASET}${BOOTENV} ${ZROOT}

	if [ $? -eq 1 ]; then
		echo "An error has occurred while creating ${ZROOT} pool."
		exit 1
	fi

	# Install system files.
	install_sys_files

	# Write bootcode.
	echo "Writing bootcode..."
	gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 1 ${DISK1}
	gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 1 ${DISK2}
	#/bin/dd if=/boot/boot1.efifat of=/dev/${DISK1}"p2" > /dev/null 2>&1
	#/bin/dd if=/boot/boot1.efifat of=/dev/${DISK2}"p2" > /dev/null 2>&1

	sysctl kern.geom.debugflags=0
	sleep 1

	# Creating the mirrored swap with gmirror.
	if [ ! -z "${SWAP}" ]; then
		echo "Creating swap Mirror..."
		if ! kldstat | grep -q geom_mirror; then
			kldload /boot/kernel/geom_mirror.ko
		fi
		gmirror label -b prefer gswap /dev/gpt/swap0 /dev/gpt/swap1
		# Add swap device to fstab.
		echo "/dev/mirror/gswap none swap sw 0 0" >> ${ALTROOT}/${ZROOT}/etc/fstab
	fi

	# Unmount cd-rom.
	umount_cdrom

	# Creates system default snapshot after install.
	create_default_snapshot

	# Flush disk cache and wait 1 second.
	sync && sleep 1
	zpool export ${ZROOT}
	rm -Rf ${ALTROOT}

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "$PRDNAME $APPNAME Mirror Successfully Installed!" 6 60
	else
		echo "An error has occurred during the installation."
		exit 1
	fi
	exit 0
}

install_sys_files()
{
	echo "Installing system files on ${ZROOT}..."

	# Install system files and discard unwanted folders.
	EXCLUDEDIRS="--exclude .snap/ --exclude resources/ --exclude zinstall.sh/ --exclude ${ZROOT}/ --exclude mnt/ --exclude dev/ --exclude var/ --exclude tmp/ --exclude cf/"
	/usr/bin/tar ${EXCLUDEDIRS} -c -f - -C / . | tar -xpf - -C ${ALTROOT}/${ZROOT}

	# Copy files from live media source.
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/var
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/dev
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/mnt
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/tmp
	/bin/chmod 1777 ${ALTROOT}/${ZROOT}/tmp
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/boot/defaults
	/bin/cp -r /mnt/cdrom/boot/* ${ALTROOT}/${ZROOT}/boot
	/bin/cp -r /mnt/cdrom/boot/defaults/* ${ALTROOT}/${ZROOT}/boot/defaults
	/bin/cp -r /mnt/cdrom/boot/kernel/* ${ALTROOT}/${ZROOT}/boot/kernel

	# Decompress kernel.
	/usr/bin/gzip -d -f ${ALTROOT}/${ZROOT}/boot/kernel/kernel.gz

	# Decompress modules (legacy versions).
	cd ${ALTROOT}/${ZROOT}/boot/kernel
	for FILE in *.gz
	do
		if [ -f ${FILE} ]; then
			/usr/bin/gzip -d -f ${FILE}
		fi
	done
	cd

	# Install configuration file.
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/cf/conf
	/bin/cp /conf.default/config.xml ${ALTROOT}/${ZROOT}/cf/conf

	# Generate new loader.conf file.
	cat << EOF > ${ALTROOT}/${ZROOT}/boot/loader.conf
kernel="kernel"
bootfile="kernel"
kernel_options=""
hw.est.msr_info="0"
hw.hptrr.attach_generic="0"
hw.msk.msi_disable="1"
kern.maxfiles="6289573"
kern.cam.boot_delay="12000"
kern.cam.ada.legacy_aliases="0"
kern.geom.label.disk_ident.enable="0"
kern.geom.label.gptid.enable="0"
kern.vty="sc"
hint.acpi_throttle.0.disabled="0"
hint.p4tcc.0.disabled="0"
autoboot_delay="3"
isboot_load="YES"
vfs.root.mountfrom="zfs:${ZROOT}${DATASET}${BOOTENV}"
zfs_load="YES"
EOF

	if [ "${PLATFORM}" == "amd64" ]; then
		echo 'mlxen_load="YES"' >> ${ALTROOT}/${ZROOT}/boot/loader.conf
	fi

	if [ ! -z "${DISK2}" ]; then
		if [ ! -z "${SWAP}" ]; then
			echo 'geom_mirror_load="YES"' >> ${ALTROOT}/${ZROOT}/boot/loader.conf
		fi
	fi

	# Generate new rc.conf file.
	#cat << EOF > ${ALTROOT}/${ZROOT}/etc/rc.conf

#EOF

	# Clear default router and netwait ip on new installs.
	sysrc -f ${ALTROOT}/${ZROOT}/etc/rc.conf defaultrouter="" > /dev/null 2>&1
	sysrc -f ${ALTROOT}/${ZROOT}/etc/rc.conf netwait_ip="" > /dev/null 2>&1

	# Set the release type.
	if [ "${PLATFORM}" == "amd64" ]; then
		echo "x64-full" > ${ALTROOT}/${ZROOT}/etc/platform
	elif [ "${PLATFORM}" == "i386" ]; then
		echo "x86-full" > ${ALTROOT}/${ZROOT}/etc/platform
	fi

	# Generate /etc/fstab.
	cat << EOF > ${ALTROOT}/${ZROOT}/etc/fstab
# Device    Mountpoint    FStype    Options    Dump    Pass#
#
EOF

	# Generate /etc/swapdevice.
	if [ ! -z "${SWAP}" ]; then
		cat << EOF > ${ALTROOT}/${ZROOT}/etc/swapdevice
swapinfo
EOF
	fi

	# Generating the /etc/cfdevice (this file is linked in /var/etc at bootup)
	# This file is used by the firmware and mount check and is normally
	# generated with 'liveCD' and 'embedded' during startup, but need to be
	# created during install of 'full'.
	if [ ! -z "${DISK1}" ]; then
		cat << EOF > ${ALTROOT}/${ZROOT}/etc/cfdevice
${DISK1}
EOF
	fi

	if [ ! -z "${DISK2}" ]; then
		cat << EOF > ${ALTROOT}/${ZROOT}/etc/cfdevice
${DISK1}
${DISK2}
EOF
	fi

	echo "Done!"
}

create_default_snapshot()
{
	echo "Creating system default snapshot..."
	zfs snapshot ${ZROOT}${DATASET}${BOOTENV}@factory-defaults
	echo "Done!"
	sleep 1
}

create_upgrade_snapshot()
{
	echo "Creating system upgrade snapshot..."
	zfs snapshot ${ZROOT}${DATASET}${BOOTENV}@upgrade-`date +%Y-%m-%d-%H%M%S`
	echo "Done!"
	sleep 1
}

upgrade_sys_files()
{
	echo "Upgrading system files on ${ZROOT}..."

	# Remove chflags for protected files before upgrade to prevent errors
	# chflags will be restored after upgrade completion by default.
	if [ -f ${ALTROOT}/${ZROOT}/usr/lib/librt.so.1 ]; then
		chflags -R noschg ${ALTROOT}/${ZROOT}/usr/lib/librt.so.1
	fi

	# Install system files and discard unwanted folders.
	EXCLUDEDIRS="--exclude .snap/ --exclude resources/ --exclude zinstall.sh/ --exclude ${ZROOT}/ --exclude mnt/ --exclude dev/ --exclude var/ --exclude tmp/ --exclude cf/"
	/usr/bin/tar ${EXCLUDEDIRS} -c -f - -C / . | tar -xpf - -C ${ALTROOT}/${ZROOT}

	# Copy files from live media source.
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/var
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/dev
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/mnt
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/tmp
	/bin/chmod 1777 ${ALTROOT}/${ZROOT}/tmp
	/bin/mkdir -p ${ALTROOT}/${ZROOT}/boot/defaults
	/bin/cp -r /mnt/cdrom/boot/* ${ALTROOT}/${ZROOT}/boot
	/bin/cp -r /mnt/cdrom/boot/defaults/* ${ALTROOT}/${ZROOT}/boot/defaults
	/bin/cp -r /mnt/cdrom/boot/kernel/* ${ALTROOT}/${ZROOT}/boot/kernel

	# Decompress kernel.
	/usr/bin/gzip -d -f ${ALTROOT}/${ZROOT}/boot/kernel/kernel.gz

	# Decompress modules (legacy versions).
	cd ${ALTROOT}/${ZROOT}/boot/kernel
	for FILE in *.gz
	do
		if [ -f ${FILE} ]; then
			/usr/bin/gzip -d -f ${FILE}
		fi
	done
	cd

	echo "Done!"
}

backup_sys_files()
{
	# Backup system configuration.
	echo "Backup system configuration..."
	cp -p ${ALTROOT}/${ZROOT}/boot/loader.conf ${SYSBACKUP}

	if [ -f "/${ZROOT}/boot.config" ]; then
		cp -p ${ALTROOT}/${ZROOT}/boot.config ${SYSBACKUP}
	fi
	if [ -f "/${ZROOT}/boot/loader.conf.local" ]; then
		cp -p ${ALTROOT}/${ZROOT}/boot/loader.conf.local ${SYSBACKUP}
	fi
	if [ -f "/${ZROOT}/boot/zfs/zpool.cache" ]; then
		cp -p ${ALTROOT}/${ZROOT}/boot/zfs/zpool.cache ${SYSBACKUP}
	fi

	#cp -p /${ZROOT}/etc/platform ${SYSBACKUP}
	cp -p ${ALTROOT}/${ZROOT}/etc/fstab ${SYSBACKUP}
	cp -p ${ALTROOT}/${ZROOT}/etc/cfdevice ${SYSBACKUP}
}

restore_sys_files()
{
	# Restore previous backup files to upgraded system.
	echo "Restore system configuration..."
	cp -pf ${SYSBACKUP}/loader.conf ${ALTROOT}/${ZROOT}/boot

	if [ -f "${SYSBACKUP}/boot.config" ]; then
		cp -pf ${SYSBACKUP}/boot.config ${ALTROOT}/${ZROOT}
	else
		rm -f ${ALTROOT}/${ZROOT}/boot.config
	fi
	if [ -f "${SYSBACKUP}/loader.conf.local" ]; then
		cp -pf ${SYSBACKUP}/loader.conf.local ${ALTROOT}/${ZROOT}/boot
	fi
	if [ -f "${SYSBACKUP}/zpool.cache" ]; then
		cp -pf ${SYSBACKUP}/zpool.cache ${ALTROOT}/${ZROOT}/boot/zfs
	fi

	#cp -pf ${SYSBACKUP}/platform /${ZROOT}/etc
	cp -pf ${SYSBACKUP}/fstab ${ALTROOT}/${ZROOT}/etc
	cp -pf ${SYSBACKUP}/cfdevice ${ALTROOT}/${ZROOT}/etc
}

# Legacy upgrade on default install.
upgrade_system()
{
	# Import current zroot pool to be upgrade, otherwise exit.
	echo "Trying to import ${ZROOT} pool..."

	export ALTROOT="/mnt/sys_install"
	export DATASET="/ROOT"
	export BOOTENV="/default-install"

	if [ ! -f "${ALTROOT}/${ZROOT}/etc/prd.name" ]; then
		zpool import -R ${ALTROOT}/${ZROOT} ${ZROOT} > /dev/null 2>&1 || zpool import -f -R ${ALTROOT}/${ZROOT} ${ZROOT} > /dev/null 2>&1
		if [ $? -eq 1 ]; then
			echo "Unable to detect/import ${ZROOT} pool."
			exit 1
		fi
	fi

	# Check if NAS4Free exist on specified zroot pool, otherwise exit.
	if [ -f "${ALTROOT}/${ZROOT}/etc/prd.name" ]; then
		PRD=`cat ${ALTROOT}/${ZROOT}/etc/prd.name`
		if [ "${PRD}" != "NAS4Free" ]; then
			echo "NAS4Free product not detected."
			zpool export ${ZROOT}
			exit 1
		fi
	else
		echo "NAS4Free product not detected."
		zpool export ${ZROOT}
		exit 1
	fi

	# System upgrade confirmation.
	upgrade_yesno

	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	mount_cdrom

	# Create config backup directory.
	mkdir -p ${SYSBACKUP}

	# Backup system configuration.
	backup_sys_files

	# Start upgrade script to remove obsolete files. This should be done
	# before system is updated because it may happen that some files
	# may be reintroduced in the system.
	echo "Remove obsolete files..."
	/etc/install/upgrade.sh clean ${ALTROOT}/${ZROOT}

	# Upgrade system files.
	upgrade_sys_files
  
	# Restore previous backup files to upgraded system.
	restore_sys_files

	# Set the release type.
	if [ "${PLATFORM}" == "amd64" ]; then
		echo "x64-full" > ${ALTROOT}/${ZROOT}/etc/platform
	elif [ "${PLATFORM}" == "i386" ]; then
		echo "x86-full" > ${ALTROOT}/${ZROOT}/etc/platform
	fi

	# Cleanup system backup files.
	rm -Rf ${SYSBACKUP}

	# Unmount cd-rom.
	umount_cdrom

	# Create system upgrade snapshot after install.
	create_upgrade_snapshot

	# Flush disk cache and wait 1 second..
	sync && sleep 1
	zpool export ${ZROOT}

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "$PRDNAME $APPNAME System Successfully Upgraded!" 6 62
	else
		echo "An error has occurred during installation."
	fi
	exit 0
}

zpool_check()
{
	# Check if a zroot pool already exist and/or mounted.
	echo "Check for existing ${ZROOT} pool..."
	if zpool import | grep -q ${ZROOT} || zpool status | grep -q ${ZROOT}; then
		printf '\033[1;30;43m WARNING \033[0m\033[1;37m A pool called '${ZROOT}' already exist.\033[0m\n'
		while true
			do
				read -p "Do you wish to proceed with the install anyway? [y/N]:" yn
				case ${yn} in
				[Yy]) break;;
				[Nn]) exit 0;;
				esac
			done
		echo "Proceeding..."
		# Export existing zroot pool.
		zpool export -f ${ZROOT} > /dev/null 2>&1
	fi
}

upgrade_yesno()
{
	cdialog --title "Proceed with $PRDNAME $APPNAME Upgrade" \
	--backtitle "$PRDNAME $APPNAME Installer" \
	--yesno "NAS4Free has been detected and will be upgraded on <${ZROOT}> pool, do you really want to continue?" 6 70
	if [ 0 -ne $? ]; then
		zpool export ${ZROOT}
		exit 0
	fi
}

global_calls()
{
	menu_install
	menu_swap
	menu_zrootsize
}

menu_zdisk()
{
	global_calls
	zdisk_init
}

menu_zmirror()
{
	global_calls
	zmirror_init
}

menu_reboot()
{
	echo "System Rebooting..."
	shutdown -r now
	while [ 1 ]; do exit; done
}

install_yesno()
{
	DISKS=`echo ${DISK1} ${DISK2}`
	cdialog --title "Proceed with $PRDNAME $APPNAME Install" \
	--backtitle "$PRDNAME $APPNAME Installer" \
	--yesno "Continuing with the installation will destroy all data on <${DISKS}> device(s), do you really want to continue?" 6 62
	if [ 0 -ne $? ]; then
		exit 0
	fi
}

menu_swap()
{
	cdialog --backtitle "$PRDNAME $APPNAME Installer" --title "Enter a desired Swap size" \
	--form "\nPlease enter a valid swap size, default 2G, leave empty for none." 0 0 0 \
	"Enter swap size:" 1 1 "2G" 1 25 25 25 \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	export SWAP=`awk '{ print $1; }' ${tmpfile} | tr -d '"'`
}

menu_zrootsize()
{
	cdialog --title "Customize zroot pool partition size?" \
	--backtitle "$PRDNAME $APPNAME Installer" \
	--yesno "Would you like to customize the ${ZROOT} partition size?" 5 60
	choise=$?
	case "${choise}" in
		0) true;;
		1) return 0;;
		255) exit 0;;
	esac

	cdialog --backtitle "$PRDNAME $APPNAME Installer" --title "Enter a desired zroot pool size" \
	--form "\nPlease enter a valid zroot pool size in GB, for example 10G, or leave empty to use all remaining disk space (default empty)." 0 0 0 \
	"Enter zroot pool size:" 1 1 "" 1 25 25 25 \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	ZRSIZE=`awk '{ print $1; }' ${tmpfile} | tr -d '"'`

	if [ ! -z "${ZRSIZE}" ]; then
		export ZROOTSIZE="-s ${ZRSIZE}"
	fi
}

sort_disklist()
{
	sed 's/\([^0-9]*\)/\1 /' | sort +0 -1 +1n | tr -d ' '
}

get_disklist()
{
	local disklist

	for disklist in $(sysctl -n kern.disks)
	do
		VAL="${VAL} ${disklist}"
	done

	VAL=`echo ${VAL} | tr ' ' '\n'| grep -v '^cd' | sort_disklist`
	export VAL
}

get_media_desc()
{
	local media
	local description
	local cap

	media=$1
	VAL=""
	if [ -n "${media}" ]; then
		# Try to get model information for each detected device.
		description=`camcontrol identify ${media} | grep 'model' | awk '{print $3, $4, $5}'`
	if [ -z "${description}" ] ; then
		# Re-try with "camcontrol inquiry" instead.
		description=`camcontrol inquiry ${media} | grep -E '<*>' | cut -d '<' -f2 | cut -d '>' -f1`
		if [ -z "${description}" ] ; then
			description="Disk Drive"
		fi
	fi
		cap=`diskinfo ${media} | awk '{
			capacity = $3;
			if (capacity >= 1099511627776) {
				printf("%.1f TiB", capacity / 1099511627776.0);
			} else if (capacity >= 1073741824) {
				printf("%.1f GiB", capacity / 1073741824.0);
			} else if (capacity >= 1048576) {
				printf("%.1f MiB", capacity / 1048576.0);
			} else {
				printf("%d Bytes", capacity);
		}}'`
		VAL="${description} -- ${cap}"
	fi
	export VAL
}

menu_install()
{
	get_disklist
	disklist="${VAL}"
	list=""
	items=0

	for disklist in ${disklist}
		do
			get_media_desc "${disklist}"
			desc="${VAL}"
			list="${list} ${disklist} '${desc}' off"
			items=$((${items} + 1))
		done

	if [ "${items}" -ge 10 ]; then
		items=10
		menuheight=20
	else
		menuheight=10
		menuheight=$((${menuheight} + ${items}))
	fi

	if [ "${items}" -eq 0 ]; then
		eval "cdialog --title 'Choose destination drive(s)' --msgbox 'No drives available' 5 60" 2>${tmpfile}
		exit 1
	fi

	eval "cdialog --backtitle '$PRDNAME $APPNAME Installer'   --title 'Choose destination drive' \
		--checklist 'Select (one) or (two) drives where $PRDNAME should be installed, use arrow keys to navigate to the drive(s) for installation then select a drive with the spacebar.' \
		${menuheight} 60 ${items} ${list}" 2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	if [ -f "${tmpfile}" ]; then
		disklist=$(eval "echo `cat "${tmpfile}"`")
	fi

	if [ -z "${disklist}" ]; then
		cdialog --msgbox "You need to select at least one disk!" 6 50 && exit 1
	fi

	export DISK1=`awk '{ print $1; }' ${tmpfile} | tr -d '"'`
	export DISK2=`awk '{ print $2; }' ${tmpfile} | tr -d '"'`
	export DISKX=`awk '{ print $3; }' ${tmpfile} | tr -d '"'`
}

menu_main()
{
	while :
		do
			cdialog --backtitle "$PRDNAME $APPNAME Installer" --clear --title "$PRDNAME Installer Options" --cancel-label "Exit" --menu "" 9 50 10 \
			"1" "Install $PRDNAME $APPNAME Disk" \
			"2" "Install $PRDNAME $APPNAME Mirror" \
			"3" "Upgrade $PRDNAME $APPNAME System" \
			2>${tmpfile}
			if [ 0 -ne $? ]; then
				exit 0
			fi
			choise=`cat "${tmpfile}"`
			case "${choise}" in
				1) menu_zdisk ;;
				2) menu_zmirror ;;
				3) upgrade_system ;;
			esac
		done
}
menu_main
