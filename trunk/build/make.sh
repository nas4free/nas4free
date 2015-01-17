#!/usr/bin/env bash
#
# This script is designed to automate the assembly of NAS4Free builds.
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#
# Debug script
# set -x
#

################################################################################
# Settings
################################################################################

# Global variables
NAS4FREE_ROOTDIR="/usr/local/nas4free"
NAS4FREE_WORKINGDIR="$NAS4FREE_ROOTDIR/work"
NAS4FREE_ROOTFS="$NAS4FREE_ROOTDIR/rootfs"
NAS4FREE_SVNDIR="$NAS4FREE_ROOTDIR/svn"
NAS4FREE_WORLD=""
NAS4FREE_PRODUCTNAME=$(cat $NAS4FREE_SVNDIR/etc/prd.name)
NAS4FREE_VERSION=$(cat $NAS4FREE_SVNDIR/etc/prd.version)
NAS4FREE_REVISION=$(svn info ${NAS4FREE_SVNDIR} | grep "Revision:" | awk '{print $2}')
if [ -f "${NAS4FREE_SVNDIR}/local.revision" ]; then
	NAS4FREE_REVISION=$(printf $(cat ${NAS4FREE_SVNDIR}/local.revision) ${NAS4FREE_REVISION})
fi
NAS4FREE_ARCH=$(uname -p)
NAS4FREE_KERNCONF="$(echo ${NAS4FREE_PRODUCTNAME} | tr '[:lower:]' '[:upper:]')-${NAS4FREE_ARCH}"
if [ "amd64" = ${NAS4FREE_ARCH} ]; then
    NAS4FREE_XARCH="x64"
elif [ "i386" = ${NAS4FREE_ARCH} ]; then
    NAS4FREE_XARCH="x86"
elif [ "armv6" = ${NAS4FREE_ARCH} ]; then
    NAS4FREE_ARCH="arm"
    NAS4FREE_XARCH="rpi"
    NAS4FREE_KERNCONF="$(echo ${NAS4FREE_PRODUCTNAME} | tr '[:lower:]' '[:upper:]')-${NAS4FREE_XARCH}"
else
    NAS4FREE_XARCH=$NAS4FREE_ARCH
fi
NAS4FREE_OBJDIRPREFIX="/usr/obj/$(echo ${NAS4FREE_PRODUCTNAME} | tr '[:upper:]' '[:lower:]')"
NAS4FREE_BOOTDIR="$NAS4FREE_ROOTDIR/bootloader"
NAS4FREE_TMPDIR="/tmp/nas4freetmp"

export NAS4FREE_ROOTDIR
export NAS4FREE_WORKINGDIR
export NAS4FREE_ROOTFS
export NAS4FREE_SVNDIR
export NAS4FREE_WORLD
export NAS4FREE_PRODUCTNAME
export NAS4FREE_VERSION
export NAS4FREE_ARCH
export NAS4FREE_KERNCONF
export NAS4FREE_OBJDIRPREFIX
export NAS4FREE_BOOTDIR
export NAS4FREE_REVISION
export NAS4FREE_TMPDIR

NAS4FREE_MK=${NAS4FREE_SVNDIR}/build/ports/nas4free.mk
rm -rf ${NAS4FREE_MK}
echo "NAS4FREE_ROOTDIR=${NAS4FREE_ROOTDIR}" >> ${NAS4FREE_MK}
echo "NAS4FREE_WORKINGDIR=${NAS4FREE_WORKINGDIR}" >> ${NAS4FREE_MK}
echo "NAS4FREE_ROOTFS=${NAS4FREE_ROOTFS}" >> ${NAS4FREE_MK}
echo "NAS4FREE_SVNDIR=${NAS4FREE_SVNDIR}" >> ${NAS4FREE_MK}
echo "NAS4FREE_WORLD=${NAS4FREE_WORLD}" >> ${NAS4FREE_MK}
echo "NAS4FREE_PRODUCTNAME=${NAS4FREE_PRODUCTNAME}" >> ${NAS4FREE_MK}
echo "NAS4FREE_VERSION=${NAS4FREE_VERSION}" >> ${NAS4FREE_MK}
echo "NAS4FREE_ARCH=${NAS4FREE_ARCH}" >> ${NAS4FREE_MK}
echo "NAS4FREE_KERNCONF=${NAS4FREE_KERNCONF}" >> ${NAS4FREE_MK}
echo "NAS4FREE_OBJDIRPREFIX=${NAS4FREE_OBJDIRPREFIX}" >> ${NAS4FREE_MK}
echo "NAS4FREE_BOOTDIR=${NAS4FREE_BOOTDIR}" >> ${NAS4FREE_MK}
echo "NAS4FREE_REVISION=${NAS4FREE_REVISION}" >> ${NAS4FREE_MK}
echo "NAS4FREE_TMPDIR=${NAS4FREE_TMPDIR}" >> ${NAS4FREE_MK}

# Local variables
NAS4FREE_URL=$(cat $NAS4FREE_SVNDIR/etc/prd.url)
NAS4FREE_SVNURL="https://svn.code.sf.net/p/nas4free/code/trunk"
NAS4FREE_SVN_SRCTREE="svn://svn.FreeBSD.org/base/releng/9.3"

# Size in MB of the MFS Root filesystem that will include all FreeBSD binary
# and NAS4FREE WEbGUI/Scripts. Keep this file very small! This file is unzipped
# to a RAM disk at NAS4FREE startup.
# The image must fit on 2GB CF/USB.
# Actual size of MDLOCAL is defined in /etc/rc.
NAS4FREE_MFSROOT_SIZE=128
NAS4FREE_MDLOCAL_SIZE=768
NAS4FREE_MDLOCAL_MINI_SIZE=32
# Now image size is less than 500MB (up to 476MiB - alignment)
NAS4FREE_IMG_SIZE=460
if [ "amd64" = ${NAS4FREE_ARCH} ]; then
	NAS4FREE_MFSROOT_SIZE=128
	NAS4FREE_MDLOCAL_SIZE=768
	NAS4FREE_MDLOCAL_MINI_SIZE=32
	NAS4FREE_IMG_SIZE=460
fi
# xz9->673MB/64MB, 8->369MB/32MB, 7->185MB/16MB, 6->93MB/8MB, 5->47MB/4MB
# 4->24MB/2.1MB, 3->12.6MB/1.1MB, 2->4.8MB/576KB, 1->1.4MB/128KB
if [ "arm" = ${NAS4FREE_ARCH} ]; then
	NAS4FREE_COMPLEVEL=3
else
	NAS4FREE_COMPLEVEL=8
fi
NAS4FREE_XMD_SEGLEN=32768
#NAS4FREE_XMD_SEGLEN=65536

# Media geometry, only relevant if bios doesn't understand LBA.
NAS4FREE_IMG_SIZE_SEC=`expr ${NAS4FREE_IMG_SIZE} \* 2048`
NAS4FREE_IMG_SECTS=63
#NAS4FREE_IMG_HEADS=16
NAS4FREE_IMG_HEADS=255
# cylinder alignment
NAS4FREE_IMG_SIZE_SEC=`expr \( $NAS4FREE_IMG_SIZE_SEC / \( $NAS4FREE_IMG_SECTS \* $NAS4FREE_IMG_HEADS \) \) \* \( $NAS4FREE_IMG_SECTS \* $NAS4FREE_IMG_HEADS \)`

# aligned BSD partition on MBR slice
NAS4FREE_IMG_SSTART=$NAS4FREE_IMG_SECTS
NAS4FREE_IMG_SSIZE=`expr $NAS4FREE_IMG_SIZE_SEC - $NAS4FREE_IMG_SSTART`
# aligned by BLKSEC: 8=4KB, 64=32KB, 128=64KB, 2048=1MB
NAS4FREE_IMG_BLKSEC=8
#NAS4FREE_IMG_BLKSEC=64
NAS4FREE_IMG_BLKSIZE=`expr $NAS4FREE_IMG_BLKSEC \* 512`
# PSTART must BLKSEC aligned in the slice.
NAS4FREE_IMG_POFFSET=16
NAS4FREE_IMG_PSTART=`expr \( \( \( $NAS4FREE_IMG_SSTART + $NAS4FREE_IMG_POFFSET + $NAS4FREE_IMG_BLKSEC - 1 \) / $NAS4FREE_IMG_BLKSEC \) \* $NAS4FREE_IMG_BLKSEC \) - $NAS4FREE_IMG_SSTART`
NAS4FREE_IMG_PSIZE0=`expr $NAS4FREE_IMG_SSIZE - $NAS4FREE_IMG_PSTART`
if [ `expr $NAS4FREE_IMG_PSIZE0 % $NAS4FREE_IMG_BLKSEC` -ne 0 ]; then
    NAS4FREE_IMG_PSIZE=`expr $NAS4FREE_IMG_PSIZE0 - \( $NAS4FREE_IMG_PSIZE0 % $NAS4FREE_IMG_BLKSEC \)`
else
    NAS4FREE_IMG_PSIZE=$NAS4FREE_IMG_PSIZE0
fi

# BSD partition only
NAS4FREE_IMG_SSTART=0
NAS4FREE_IMG_SSIZE=$NAS4FREE_IMG_SIZE_SEC
NAS4FREE_IMG_BLKSEC=1
NAS4FREE_IMG_BLKSIZE=512
NAS4FREE_IMG_POFFSET=16
NAS4FREE_IMG_PSTART=$NAS4FREE_IMG_POFFSET
NAS4FREE_IMG_PSIZE=`expr $NAS4FREE_IMG_SSIZE - $NAS4FREE_IMG_PSTART`

# newfs parameters
NAS4FREE_IMGFMT_SECTOR=512
NAS4FREE_IMGFMT_FSIZE=2048
#NAS4FREE_IMGFMT_SECTOR=4096
#NAS4FREE_IMGFMT_FSIZE=4096
NAS4FREE_IMGFMT_BSIZE=`expr $NAS4FREE_IMGFMT_FSIZE \* 8`

#echo "IMAGE=$NAS4FREE_IMG_SIZE_SEC"
#echo "SSTART=$NAS4FREE_IMG_SSTART"
#echo "SSIZE=$NAS4FREE_IMG_SSIZE"
#echo "ALIGN=$NAS4FREE_IMG_BLKSEC"
#echo "PSTART=$NAS4FREE_IMG_PSTART"
#echo "PSIZE0=$NAS4FREE_IMG_PSIZE0"
#echo "PSIZE=$NAS4FREE_IMG_PSIZE"

# Options:
# Support bootmenu
OPT_BOOTMENU=1
# Support bootsplash
OPT_BOOTSPLASH=0
# Support serial console
OPT_SERIALCONSOLE=0

# Dialog command
DIALOG="dialog"

################################################################################
# Functions
################################################################################

# Update source tree and ports collection.
update_sources() {
	tempfile=$NAS4FREE_WORKINGDIR/tmp$$

	# Choose what to do.
	$DIALOG --title "$NAS4FREE_PRODUCTNAME - Update Sources" --checklist "Please select what to update." 12 60 5 \
		"svnco" "Fetch source tree" OFF \
		"svnup" "Update source tree" OFF \
		"freebsd-update" "Fetch and install binary updates" OFF \
		"portsnap" "Update ports collection" OFF \
		"portupgrade" "Upgrade ports on host" OFF 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choices=`cat $tempfile`
	rm $tempfile

	for choice in $(echo $choices | tr -d '"'); do
		case $choice in
			freebsd-update)
				freebsd-update fetch install;;
			portsnap)
				portsnap fetch update;;
			svnco)
				rm -rf /usr/src; svn co ${NAS4FREE_SVN_SRCTREE} /usr/src;;
			svnup)
				svn up /usr/src;;
			portupgrade)
				portupgrade -aFP;;
  	esac
  done

	return $?
}

# Build world. Copying required files defined in 'build/nas4free.files'.
build_world() {
	# Make a pseudo 'chroot' to NAS4FREE root.
  cd $NAS4FREE_ROOTFS

	echo
	echo "Building World:"

	[ -f $NAS4FREE_WORKINGDIR/nas4free.files ] && rm -f $NAS4FREE_WORKINGDIR/nas4free.files
	cp $NAS4FREE_SVNDIR/build/nas4free.files $NAS4FREE_WORKINGDIR

	# Add custom binaries
	if [ -f $NAS4FREE_WORKINGDIR/nas4free.custfiles ]; then
		cat $NAS4FREE_WORKINGDIR/nas4free.custfiles >> $NAS4FREE_WORKINGDIR/nas4free.files
	fi

	for i in $(cat $NAS4FREE_WORKINGDIR/nas4free.files | grep -v "^#"); do
		file=$(echo "$i" | cut -d ":" -f 1)

		# Deal with directories
		dir=$(dirname $file)
		if [ ! -d ${NAS4FREE_WORLD}/$dir ]; then
			echo "skip: $file ($dir)"
			continue;
		fi
		if [ ! -d $dir ]; then
		  mkdir -pv $dir
		fi
		#if [ "$(echo $file | grep '*')" == "" -a ! -f ${NAS4FREE_WORLD}/$file ]; then
		#	echo "skip: $file ($dir)"
		#	continue;
		#fi

		# Copy files from world.
		cp -Rpv ${NAS4FREE_WORLD}/$file $(echo $file | rev | cut -d "/" -f 2- | rev)

		# Deal with links
		if [ $(echo "$i" | grep -c ":") -gt 0 ]; then
			for j in $(echo $i | cut -d ":" -f 2- | sed "s/:/ /g"); do
				ln -sv /$file $j
			done
		fi
	done

	# Cleanup
	chflags -R noschg $NAS4FREE_TMPDIR
	chflags -R noschg $NAS4FREE_ROOTFS
	[ -d $NAS4FREE_TMPDIR ] && rm -f $NAS4FREE_WORKINGDIR/nas4free.files
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz

	return 0
}

# Create rootfs
create_rootfs() {
	$NAS4FREE_SVNDIR/build/nas4free-create-rootfs.sh -f $NAS4FREE_ROOTFS

	# Configuring platform variable
	echo ${NAS4FREE_VERSION} > ${NAS4FREE_ROOTFS}/etc/prd.version

	# Config file: config.xml
	cd $NAS4FREE_ROOTFS/conf.default/
	cp -v $NAS4FREE_SVNDIR/conf/config.xml .

	# Compress zoneinfo data, exclude some useless files.
	mkdir $NAS4FREE_TMPDIR
	echo "Factory" > $NAS4FREE_TMPDIR/zoneinfo.exlude
	echo "posixrules" >> $NAS4FREE_TMPDIR/zoneinfo.exlude
	echo "zone.tab" >> $NAS4FREE_TMPDIR/zoneinfo.exlude
	tar -c -v -f - -X $NAS4FREE_TMPDIR/zoneinfo.exlude -C /usr/share/zoneinfo/ . | xz -cv > $NAS4FREE_ROOTFS/usr/share/zoneinfo.txz
	rm $NAS4FREE_TMPDIR/zoneinfo.exlude

	return 0
}

# Actions before building kernel (e.g. install special/additional kernel patches).
pre_build_kernel() {
	tempfile=$NAS4FREE_WORKINGDIR/tmp$$
	patches=$NAS4FREE_WORKINGDIR/patches$$

	# Create list of available packages.
	echo "#! /bin/sh
$DIALOG --title \"$NAS4FREE_PRODUCTNAME - Kernel Patches\" \\
--checklist \"Select the patches you want to add. Make sure you have clean/origin kernel sources (via suvbersion) to apply patches successful.\" 22 88 14 \\" > $tempfile

	for s in $NAS4FREE_SVNDIR/build/kernel-patches/*; do
		[ ! -d "$s" ] && continue
		package=`basename $s`
		desc=`cat $s/pkg-descr`
		state=`cat $s/pkg-state`
		echo "\"$package\" \"$desc\" $state \\" >> $tempfile
	done

	# Display list of available kernel patches.
	sh $tempfile 2> $patches
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi
	rm $tempfile

	echo "Remove old patched files..."
	for file in $(find /usr/src -name "*.orig"); do
		rm -rv ${file}
	done

	for patch in $(cat $patches | tr -d '"'); do
    echo
		echo "--------------------------------------------------------------"
		echo ">>> Adding kernel patch: ${patch}"
		echo "--------------------------------------------------------------"
		cd $NAS4FREE_SVNDIR/build/kernel-patches/$patch
		make install
		[ 0 != $? ] && return 1 # successful?
	done
	rm $patches
}

# Build/Install the kernel.
build_kernel() {
	tempfile=$NAS4FREE_WORKINGDIR/tmp$$

	# Make sure kernel directory exists.
	[ ! -d "${NAS4FREE_ROOTFS}/boot/kernel" ] && mkdir -p ${NAS4FREE_ROOTFS}/boot/kernel

	# Choose what to do.
	$DIALOG --title "$NAS4FREE_PRODUCTNAME - Build/Install Kernel" --checklist "Please select whether you want to build or install the kernel." 10 75 3 \
		"prebuild" "Apply kernel patches" OFF \
		"build" "Build kernel" OFF \
		"install" "Install kernel + modules" ON 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choices=`cat $tempfile`
	rm $tempfile

	for choice in $(echo $choices | tr -d '"'); do
		case $choice in
			prebuild)
				# Apply kernel patches.
				pre_build_kernel;
				[ 0 != $? ] && return 1;; # successful?
			build)
				# Copy kernel configuration.
				cd /sys/${NAS4FREE_ARCH}/conf;
				cp -f $NAS4FREE_SVNDIR/build/kernel-config/${NAS4FREE_KERNCONF} .;
				# Clean object directory.
				rm -f -r ${NAS4FREE_OBJDIRPREFIX};
				# Compiling and compressing the kernel.
				cd /usr/src;
				env MAKEOBJDIRPREFIX=${NAS4FREE_OBJDIRPREFIX} make buildkernel KERNCONF=${NAS4FREE_KERNCONF};
				gzip -9cnv ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/kernel > ${NAS4FREE_WORKINGDIR}/kernel.gz;;
			install)
				# Installing the modules.
				echo "--------------------------------------------------------------";
				echo ">>> Install Kernel Modules";
				echo "--------------------------------------------------------------";

				[ -f ${NAS4FREE_WORKINGDIR}/modules.files ] && rm -f ${NAS4FREE_WORKINGDIR}/modules.files;
				cp ${NAS4FREE_SVNDIR}/build/kernel-config/modules.files ${NAS4FREE_WORKINGDIR};

				modulesdir=${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules;
				for module in $(cat ${NAS4FREE_WORKINGDIR}/modules.files | grep -v "^#"); do
					install -v -o root -g wheel -m 555 ${modulesdir}/${module} ${NAS4FREE_ROOTFS}/boot/kernel
				done
				;;
  	esac
  done

	return 0
}

# Adding the libraries
add_libs() {
	echo
	echo "Adding required libs:"

	# Identify required libs.
	[ -f /tmp/lib.list ] && rm -f /tmp/lib.list
	dirs=(${NAS4FREE_ROOTFS}/bin ${NAS4FREE_ROOTFS}/sbin ${NAS4FREE_ROOTFS}/usr/bin ${NAS4FREE_ROOTFS}/usr/sbin ${NAS4FREE_ROOTFS}/usr/local/bin ${NAS4FREE_ROOTFS}/usr/local/sbin ${NAS4FREE_ROOTFS}/usr/lib ${NAS4FREE_ROOTFS}/usr/local/lib ${NAS4FREE_ROOTFS}/usr/libexec ${NAS4FREE_ROOTFS}/usr/local/libexec)
	for i in ${dirs[@]}; do
		for file in $(find -L ${i} -type f -print); do
			ldd -f "%p\n" ${file} 2> /dev/null >> /tmp/lib.list
		done
	done

	# Copy identified libs.
	for i in $(sort -u /tmp/lib.list); do
		if [ -e "${NAS4FREE_WORLD}${i}" ]; then
			DESTDIR=${NAS4FREE_ROOTFS}$(echo $i | rev | cut -d '/' -f 2- | rev)
			if [ ! -d ${DESTDIR} ]; then
			    DESTDIR=${NAS4FREE_ROOTFS}/usr/local/lib
			fi
			install -c -s -v ${NAS4FREE_WORLD}${i} ${DESTDIR}
		fi
	done

	# for compatibility
	install -c -s -v ${NAS4FREE_WORLD}/lib/libreadline.* ${NAS4FREE_ROOTFS}/lib

	# Cleanup.
	rm -f /tmp/lib.list

  return 0
}

# Creating mdlocal-mini
create_mdlocal_mini() {
	echo "--------------------------------------------------------------"
	echo ">>> Generating MDLOCAL mini"
	echo "--------------------------------------------------------------"

	cd $NAS4FREE_WORKINGDIR

	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.files ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.files
	cp $NAS4FREE_SVNDIR/build/nas4free-mdlocal-mini.files $NAS4FREE_WORKINGDIR/mdlocal-mini.files

	# Make mfsroot to have the size of the NAS4FREE_MFSROOT_SIZE variable
	#dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mdlocal-mini bs=1k count=$(expr ${NAS4FREE_MDLOCAL_MINI_SIZE} \* 1024)
	dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mdlocal-mini bs=1k seek=$(expr ${NAS4FREE_MDLOCAL_MINI_SIZE} \* 1024) count=0
	# Configure this file as a memory disk
	md=`mdconfig -a -t vnode -f $NAS4FREE_WORKINGDIR/mdlocal-mini`
	# Format memory disk using UFS
	newfs -S $NAS4FREE_IMGFMT_SECTOR -b $NAS4FREE_IMGFMT_BSIZE -f $NAS4FREE_IMGFMT_FSIZE -O2 -o space -m 0 -U -t /dev/${md}
	# Umount memory disk (if already used)
	umount $NAS4FREE_TMPDIR >/dev/null 2>&1
	# Mount memory disk
	mkdir -p ${NAS4FREE_TMPDIR}/usr/local
	mount /dev/${md} ${NAS4FREE_TMPDIR}/usr/local

	# Create tree
	cd $NAS4FREE_ROOTFS/usr/local
	find . -type d | cpio -pmd ${NAS4FREE_TMPDIR}/usr/local

	# Copy selected files
	cd $NAS4FREE_TMPDIR
	for i in $(cat $NAS4FREE_WORKINGDIR/mdlocal-mini.files | grep -v "^#"); do
		d=`dirname $i`
		b=`basename $i`
		echo "cp $NAS4FREE_ROOTFS/$d/$b  ->  $NAS4FREE_TMPDIR/$d/$b"
		cp $NAS4FREE_ROOTFS/$d/$b $NAS4FREE_TMPDIR/$d/$b
		# Copy required libraries
		for j in $(ldd $NAS4FREE_ROOTFS/$d/$b | cut -w -f 4 | grep /usr/local | sed -e '/:/d' -e 's/^\///'); do
			d=`dirname $j`
			b=`basename $j`
			if [ ! -e $NAS4FREE_TMPDIR/$d/$b ]; then
				echo "cp $NAS4FREE_ROOTFS/$d/$b  ->  $NAS4FREE_TMPDIR/$d/$b"
				cp $NAS4FREE_ROOTFS/$d/$b $NAS4FREE_TMPDIR/$d/$b
			fi
		done
	done

	# Identify required libs.
	[ -f /tmp/lib.list ] && rm -f /tmp/lib.list
	dirs=(${NAS4FREE_TMPDIR}/usr/local/bin ${NAS4FREE_TMPDIR}/usr/local/sbin ${NAS4FREE_TMPDIR}/usr/local/lib ${NAS4FREE_TMPDIR}/usr/local/libexec)
	for i in ${dirs[@]}; do
		for file in $(find -L ${i} -type f -print); do
			ldd -f "%p\n" ${file} 2> /dev/null >> /tmp/lib.list
		done
	done

	# Copy identified libs.
	for i in $(sort -u /tmp/lib.list); do
		if [ -e "${NAS4FREE_WORLD}${i}" ]; then
			d=`dirname $i`
			b=`basename $i`
			if [ "$d" = "/lib" -o "$d" = "/usr/lib" ]; then
				# skip lib in mfsroot
				[ -e ${NAS4FREE_ROOTFS}${i} ] && continue
			fi
			DESTDIR=${NAS4FREE_TMPDIR}$(echo $i | rev | cut -d '/' -f 2- | rev)
			if [ ! -d ${DESTDIR} ]; then
			    DESTDIR=${NAS4FREE_TMPDIR}/usr/local/lib
			fi
			install -c -s -v ${NAS4FREE_WORLD}${i} ${DESTDIR}
		fi
	done

	# Cleanup.
	rm -f /tmp/lib.list

	# Umount memory disk
	umount $NAS4FREE_TMPDIR/usr/local
	# Detach memory disk
	mdconfig -d -u ${md}

	xz -${NAS4FREE_COMPLEVEL}v $NAS4FREE_WORKINGDIR/mdlocal-mini

	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.files ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.files

	return 0
}

# Creating msfroot
create_mfsroot() {
	echo "--------------------------------------------------------------"
	echo ">>> Generating MFSROOT Filesystem"
	echo "--------------------------------------------------------------"

	cd $NAS4FREE_WORKINGDIR

	[ -f $NAS4FREE_WORKINGDIR/mfsroot ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip
	[ -d $NAS4FREE_SVNDIR ] && use_svn ;

	# Make mfsroot to have the size of the NAS4FREE_MFSROOT_SIZE variable
	#dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mfsroot bs=1k count=$(expr ${NAS4FREE_MFSROOT_SIZE} \* 1024)
	#dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mdlocal bs=1k count=$(expr ${NAS4FREE_MDLOCAL_SIZE} \* 1024)
	dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mfsroot bs=1k seek=$(expr ${NAS4FREE_MFSROOT_SIZE} \* 1024) count=0
	dd if=/dev/zero of=$NAS4FREE_WORKINGDIR/mdlocal bs=1k seek=$(expr ${NAS4FREE_MDLOCAL_SIZE} \* 1024) count=0
	# Configure this file as a memory disk
	md=`mdconfig -a -t vnode -f $NAS4FREE_WORKINGDIR/mfsroot`
	md2=`mdconfig -a -t vnode -f $NAS4FREE_WORKINGDIR/mdlocal`
	# Format memory disk using UFS
	newfs -S $NAS4FREE_IMGFMT_SECTOR -b $NAS4FREE_IMGFMT_BSIZE -f $NAS4FREE_IMGFMT_FSIZE -O2 -o space -m 0 /dev/${md}
	newfs -S $NAS4FREE_IMGFMT_SECTOR -b $NAS4FREE_IMGFMT_BSIZE -f $NAS4FREE_IMGFMT_FSIZE -O2 -o space -m 0 -U -t /dev/${md2}
	# Umount memory disk (if already used)
	umount $NAS4FREE_TMPDIR >/dev/null 2>&1
	# Mount memory disk
	mount /dev/${md} ${NAS4FREE_TMPDIR}
	mkdir -p ${NAS4FREE_TMPDIR}/usr/local
	mount /dev/${md2} ${NAS4FREE_TMPDIR}/usr/local
	cd $NAS4FREE_TMPDIR
	tar -cf - -C $NAS4FREE_ROOTFS ./ | tar -xvpf -

	cd $NAS4FREE_WORKINGDIR
	# Umount memory disk
	umount $NAS4FREE_TMPDIR/usr/local
	umount $NAS4FREE_TMPDIR
	# Detach memory disk
	mdconfig -d -u ${md2}
	mdconfig -d -u ${md}

	mkuzip -s ${NAS4FREE_XMD_SEGLEN} $NAS4FREE_WORKINGDIR/mfsroot
	chmod 644 $NAS4FREE_WORKINGDIR/mfsroot.uzip
	gzip -9kfnv $NAS4FREE_WORKINGDIR/mfsroot
	if [ "arm" = ${NAS4FREE_ARCH} ]; then
		mkuzip -s ${NAS4FREE_XMD_SEGLEN} $NAS4FREE_WORKINGDIR/mdlocal
	fi
	xz -${NAS4FREE_COMPLEVEL}kv $NAS4FREE_WORKINGDIR/mdlocal

	create_mdlocal_mini;

	return 0
}

update_mfsroot() {
	echo "--------------------------------------------------------------"
	echo ">>> Generating MFSROOT Filesystem (use existing image)"
	echo "--------------------------------------------------------------"

	# Check if mfsroot exists.
	if [ ! -f $NAS4FREE_WORKINGDIR/mfsroot ]; then
		echo "==> Error: $NAS4FREE_WORKINGDIR/mfsroot does not exist."
		return 1
	fi

	# Cleanup.
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	#[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	#[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip

	cd $NAS4FREE_WORKINGDIR
	mkuzip -s ${NAS4FREE_XMD_SEGLEN} $NAS4FREE_WORKINGDIR/mfsroot
	chmod 644 $NAS4FREE_WORKINGDIR/mfsroot.uzip
	gzip -9kfnv $NAS4FREE_WORKINGDIR/mfsroot
	#xz -8kv $NAS4FREE_WORKINGDIR/mdlocal

	return 0
}

copy_kmod() {
	local kmodlist
	echo "Copy kmod to $NAS4FREE_TMPDIR/boot/kernel"
	kmodlist=`(cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules; find . -name '*.ko' | sed -e 's/\.\///')`
	for f in $kmodlist; do
		if grep -q "^${f}" $NAS4FREE_SVNDIR/build/nas4free.kmod.exclude > /dev/null; then
			echo "skip: $f"
			continue;
		fi
		b=`basename ${f}`
		#(cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules; install -v -o root -g wheel -m 555 ${f} $NAS4FREE_TMPDIR/boot/kernel/${b}; gzip -9 $NAS4FREE_TMPDIR/boot/kernel/${b})
		(cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules; install -v -o root -g wheel -m 555 ${f} $NAS4FREE_TMPDIR/boot/kernel/${b})
	done
	return 0;
}

create_image() {
	echo "--------------------------------------------------------------"
	echo ">>> Generating ${NAS4FREE_PRODUCTNAME} IMG File (to be rawrite on CF/USB/HD/SSD)"
	echo "--------------------------------------------------------------"

	# Check if rootfs (contining OS image) exists.
	if [ ! -d "$NAS4FREE_ROOTFS" ]; then
		echo "==> Error: ${NAS4FREE_ROOTFS} does not exist."
		return 1
	fi

	# Cleanup.
	[ -f ${NAS4FREE_WORKINGDIR}/image.bin ] && rm -f ${NAS4FREE_WORKINGDIR}/image.bin
	[ -f ${NAS4FREE_WORKINGDIR}/image.bin.xz ] && rm -f ${NAS4FREE_WORKINGDIR}/image.bin.xz

	# Set platform information.
	PLATFORM="${NAS4FREE_XARCH}-embedded"
	echo $PLATFORM > ${NAS4FREE_ROOTFS}/etc/platform

	# Set build time.
	date > ${NAS4FREE_ROOTFS}/etc/prd.version.buildtime

	# Set revision.
	echo ${NAS4FREE_REVISION} > ${NAS4FREE_ROOTFS}/etc/prd.revision

	IMGFILENAME="${NAS4FREE_PRODUCTNAME}-${PLATFORM}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}.img"

	echo "===> Generating tempory $NAS4FREE_TMPDIR folder"
	mkdir $NAS4FREE_TMPDIR
	create_mfsroot;

	echo "===> Creating Empty IMG File"
	#dd if=/dev/zero of=${NAS4FREE_WORKINGDIR}/image.bin bs=${NAS4FREE_IMG_SECTS}b count=`expr ${NAS4FREE_IMG_SIZE_SEC} / ${NAS4FREE_IMG_SECTS} + 64`
	dd if=/dev/zero of=${NAS4FREE_WORKINGDIR}/image.bin bs=${NAS4FREE_IMG_SECTS}b seek=`expr ${NAS4FREE_IMG_SIZE_SEC} / ${NAS4FREE_IMG_SECTS} + 64` count=0
	echo "===> Use IMG as a memory disk"
	md=`mdconfig -a -t vnode -f ${NAS4FREE_WORKINGDIR}/image.bin -x ${NAS4FREE_IMG_SECTS} -y ${NAS4FREE_IMG_HEADS}`
	diskinfo -v ${md}

	# create 1MB aligned MBR image
	echo "===> Creating MBR partition on this memory disk"
	gpart create -s mbr ${md}
	gpart add -t freebsd ${md}
	gpart set -a active -i 1 ${md}
	gpart bootcode -b ${NAS4FREE_BOOTDIR}/mbr ${md}

	echo "===> Creating BSD partition on this memory disk"
	gpart create -s bsd ${md}s1
	gpart bootcode -b ${NAS4FREE_BOOTDIR}/boot ${md}s1
	gpart add -a 1m -t freebsd-ufs ${md}s1
	mdp=${md}s1a

	echo "===> Formatting this memory disk using UFS"
	newfs -S $NAS4FREE_IMGFMT_SECTOR -b $NAS4FREE_IMGFMT_BSIZE -f $NAS4FREE_IMGFMT_FSIZE -O2 -U -o space -m 0 -L "embboot" /dev/${mdp}
	echo "===> Mount this virtual disk on $NAS4FREE_TMPDIR"
	mount /dev/${mdp} $NAS4FREE_TMPDIR
	echo "===> Copying previously generated MFSROOT file to memory disk"
	cp $NAS4FREE_WORKINGDIR/mfsroot.gz $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mfsroot.uzip $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mdlocal.xz $NAS4FREE_TMPDIR
	#cp $NAS4FREE_WORKINGDIR/mdlocal.uzip $NAS4FREE_TMPDIR
	echo "${NAS4FREE_PRODUCTNAME}-${PLATFORM}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}" > $NAS4FREE_TMPDIR/version

	echo "===> Copying Bootloader File(s) to memory disk"
	mkdir -p $NAS4FREE_TMPDIR/boot
	mkdir -p $NAS4FREE_TMPDIR/boot/kernel $NAS4FREE_TMPDIR/boot/defaults $NAS4FREE_TMPDIR/boot/zfs
	mkdir -p $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_ROOTFS/conf.default/config.xml $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_BOOTDIR/kernel/kernel.gz $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/kernel/*.ko $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/boot $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.conf $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.rc $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/support.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/defaults/loader.conf $NAS4FREE_TMPDIR/boot/defaults/
	cp $NAS4FREE_BOOTDIR/device.hints $NAS4FREE_TMPDIR/boot
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $NAS4FREE_SVNDIR/boot/menu.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/brand.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/check-password.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/color.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/delay.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/menu-commands.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/shortcuts.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/version.4th $NAS4FREE_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $NAS4FREE_SVNDIR/boot/splash.bmp $NAS4FREE_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules/splash/bmp/splash_bmp.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${NAS4FREE_ARCH} ]; then
		cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 apm/apm.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${NAS4FREE_ROOTFS}/boot/kernel/isboot.ko $NAS4FREE_TMPDIR/boot/kernel
	# preload kernel drivers
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 opensolaris/opensolaris.ko $NAS4FREE_TMPDIR/boot/kernel
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 zfs/zfs.ko $NAS4FREE_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	echo "===> Unmount memory disk"
	umount $NAS4FREE_TMPDIR
	echo "===> Detach memory disk"
	mdconfig -d -u ${md}
	echo "===> Compress the IMG file"
	xz -${NAS4FREE_COMPLEVEL}v $NAS4FREE_WORKINGDIR/image.bin
	cp $NAS4FREE_WORKINGDIR/image.bin.xz $NAS4FREE_ROOTDIR/${IMGFILENAME}.xz

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	#[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/image.bin ] && rm -f $NAS4FREE_WORKINGDIR/image.bin

	return 0
}

create_iso () {
	# Check if rootfs (contining OS image) exists.
	if [ ! -d "$NAS4FREE_ROOTFS" ]; then
		echo "==> Error: ${NAS4FREE_ROOTFS} does not exist!."
		return 1
	fi

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz

	if [ ! $TINY_ISO ]; then
		LABEL="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveCD-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}"
		VOLUMEID="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveCD-${NAS4FREE_VERSION}"
		echo "ISO: Generating the $NAS4FREE_PRODUCTNAME Image file:"
		create_image;
	else
		LABEL="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveCD-Tiny-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}"
		VOLUMEID="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveCD-Tiny-${NAS4FREE_VERSION}"
	fi

	# Set Platform Informations.
	PLATFORM="${NAS4FREE_XARCH}-liveCD"
	echo $PLATFORM > ${NAS4FREE_ROOTFS}/etc/platform

	# Set Revision.
	echo ${NAS4FREE_REVISION} > ${NAS4FREE_ROOTFS}/etc/prd.revision

	echo "ISO: Generating temporary folder '$NAS4FREE_TMPDIR'"
	mkdir $NAS4FREE_TMPDIR
	if [ $TINY_ISO ]; then
		# Not call create_image if TINY_ISO
		create_mfsroot;
	elif [ -z "$FORCE_MFSROOT" -o "$FORCE_MFSROOT" != "0" ]; then
		# Mount mfsroot/mdlocal created by create_image
		md=`mdconfig -a -t vnode -f $NAS4FREE_WORKINGDIR/mfsroot`
		mount /dev/${md} ${NAS4FREE_TMPDIR}
		# Update mfsroot/mdlocal
		echo $PLATFORM > ${NAS4FREE_TMPDIR}/etc/platform
		# Umount and update mfsroot/mdlocal
		umount $NAS4FREE_TMPDIR
		mdconfig -d -u ${md}
		update_mfsroot;
	else
		create_mfsroot;
	fi

	echo "ISO: Copying previously generated MFSROOT file to $NAS4FREE_TMPDIR"
	cp $NAS4FREE_WORKINGDIR/mfsroot.gz $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mfsroot.uzip $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mdlocal.xz $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mdlocal-mini.xz $NAS4FREE_TMPDIR
	echo "${LABEL}" > $NAS4FREE_TMPDIR/version

	echo "ISO: Copying Bootloader file(s) to $NAS4FREE_TMPDIR"
	mkdir -p $NAS4FREE_TMPDIR/boot
	mkdir -p $NAS4FREE_TMPDIR/boot/kernel $NAS4FREE_TMPDIR/boot/defaults $NAS4FREE_TMPDIR/boot/zfs
	cp $NAS4FREE_BOOTDIR/kernel/kernel.gz $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/kernel/*.ko $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/cdboot $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.conf $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.rc $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/support.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/defaults/loader.conf $NAS4FREE_TMPDIR/boot/defaults/
	cp $NAS4FREE_BOOTDIR/device.hints $NAS4FREE_TMPDIR/boot
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $NAS4FREE_SVNDIR/boot/menu.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/brand.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/check-password.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/color.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/delay.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/menu-commands.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/shortcuts.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/version.4th $NAS4FREE_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $NAS4FREE_SVNDIR/boot/splash.bmp $NAS4FREE_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules/splash/bmp/splash_bmp.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${NAS4FREE_ARCH} ]; then
		cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 apm/apm.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${NAS4FREE_ROOTFS}/boot/kernel/isboot.ko $NAS4FREE_TMPDIR/boot/kernel
	# preload kernel drivers
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 opensolaris/opensolaris.ko $NAS4FREE_TMPDIR/boot/kernel
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 zfs/zfs.ko $NAS4FREE_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	if [ ! $TINY_ISO ]; then
		echo "ISO: Copying IMG file to $NAS4FREE_TMPDIR"
		cp ${NAS4FREE_WORKINGDIR}/image.bin.xz ${NAS4FREE_TMPDIR}/${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-embedded.xz
	fi

	echo "ISO: Generating ISO File"
	mkisofs -b "boot/cdboot" -no-emul-boot -r -J -A "${NAS4FREE_PRODUCTNAME} CD-ROM image" -publisher "${NAS4FREE_URL}" -V "${VOLUMEID}" -o "${NAS4FREE_ROOTDIR}/${LABEL}.iso" ${NAS4FREE_TMPDIR}
	[ 0 != $? ] && return 1 # successful?

	echo "Generating SHA256 CHECKSUM File"
	NAS4FREE_CHECKSUMFILENAME="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}.checksum"
	cd ${NAS4FREE_ROOTDIR} && sha256 *.img *.xz *.iso > ${NAS4FREE_ROOTDIR}/${NAS4FREE_CHECKSUMFILENAME}

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f $NAS4FREE_WORKINGDIR/mfsroot ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz
	[ -f $NAS4FREE_WORKINGDIR/image.bin.xz ] && rm -f $NAS4FREE_WORKINGDIR/image.bin.xz

	return 0
}

create_iso_tiny() {
	TINY_ISO=1
	create_iso;
	unset TINY_ISO
	return 0
}

create_embedded() {
	create_image;

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f $NAS4FREE_WORKINGDIR/mfsroot ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz
	[ -f $NAS4FREE_WORKINGDIR/image.bin.xz ] && rm -f $NAS4FREE_WORKINGDIR/image.bin.xz
}

create_usb () {
	# Check if rootfs (contining OS image) exists.
	if [ ! -d "$NAS4FREE_ROOTFS" ]; then
		echo "==> Error: ${NAS4FREE_ROOTFS} does not exist!."
		return 1
	fi

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f ${NAS4FREE_WORKINGDIR}/image.bin ] && rm -f ${NAS4FREE_WORKINGDIR}/image.bin
	[ -f ${NAS4FREE_WORKINGDIR}/image.bin.xz ] && rm -f ${NAS4FREE_WORKINGDIR}/image.bin.xz
	[ -f ${NAS4FREE_WORKINGDIR}/mfsroot.gz ] && rm -f ${NAS4FREE_WORKINGDIR}/mfsroot.gz
	[ -f ${NAS4FREE_WORKINGDIR}/mfsroot.uzip ] && rm -f ${NAS4FREE_WORKINGDIR}/mfsroot.uzip
	[ -f ${NAS4FREE_WORKINGDIR}/mdlocal.xz ] && rm -f ${NAS4FREE_WORKINGDIR}/mdlocal.xz
	[ -f ${NAS4FREE_WORKINGDIR}/mdlocal.uzip ] && rm -f ${NAS4FREE_WORKINGDIR}/mdlocal.uzip
	[ -f ${NAS4FREE_WORKINGDIR}/mdlocal-mini.xz ] && rm -f ${NAS4FREE_WORKINGDIR}/mdlocal-mini.xz
	[ -f ${NAS4FREE_WORKINGDIR}/usb-image.bin ] && rm -f ${NAS4FREE_WORKINGDIR}/usb-image.bin
	[ -f ${NAS4FREE_WORKINGDIR}/usb-image.bin.gz ] && rm -f ${NAS4FREE_WORKINGDIR}/usb-image.bin.gz

	echo "USB: Generating the $NAS4FREE_PRODUCTNAME Image file:"
	create_image;

	# Set Platform Informations.
	PLATFORM="${NAS4FREE_XARCH}-liveUSB"
	echo $PLATFORM > ${NAS4FREE_ROOTFS}/etc/platform

	# Set Revision.
	echo ${NAS4FREE_REVISION} > ${NAS4FREE_ROOTFS}/etc/prd.revision

	IMGFILENAME="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveUSB-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}.img"

	echo "USB: Generating temporary folder '$NAS4FREE_TMPDIR'"
	mkdir $NAS4FREE_TMPDIR
	if [ -z "$FORCE_MFSROOT" -o "$FORCE_MFSROOT" != "0" ]; then
		# Mount mfsroot/mdlocal created by create_image
		md=`mdconfig -a -t vnode -f $NAS4FREE_WORKINGDIR/mfsroot`
		mount /dev/${md} ${NAS4FREE_TMPDIR}
		# Update mfsroot/mdlocal
		echo $PLATFORM > ${NAS4FREE_TMPDIR}/etc/platform
		# Umount and update mfsroot/mdlocal
		umount $NAS4FREE_TMPDIR
		mdconfig -d -u ${md}
		update_mfsroot;
	else
		create_mfsroot;
	fi

	# for 2GB USB stick
	IMGSIZE=$(stat -f "%z" ${NAS4FREE_WORKINGDIR}/image.bin.xz)
	MFSSIZE=$(stat -f "%z" ${NAS4FREE_WORKINGDIR}/mfsroot.gz)
	MFS2SIZE=$(stat -f "%z" ${NAS4FREE_WORKINGDIR}/mfsroot.uzip)
	MDLSIZE=$(stat -f "%z" ${NAS4FREE_WORKINGDIR}/mdlocal.xz)
	MDLSIZE2=$(stat -f "%z" ${NAS4FREE_WORKINGDIR}/mdlocal-mini.xz)
	IMGSIZEM=$(expr \( $IMGSIZE + $MFSSIZE + $MFS2SIZE + $MDLSIZE + $MDLSIZE2 - 1 + 1024 \* 1024 \) / 1024 / 1024)
	USBROOTM=416
	USBSWAPM=512
	USBDATAM=12
	#USB_SECTS=64
	#USB_HEADS=32
	USB_SECTS=63
	USB_HEADS=255

	# 1MB alignment
	#USBSYSSIZEM=$(expr $USBROOTM + $IMGSIZEM + 1)
	USBSYSSIZEM=$(expr $USBROOTM + 1)
	USBSWPSIZEM=$(expr $USBSWAPM + 2)
	USBDATSIZEM=$(expr $USBDATAM + 2)
	USBIMGSIZEM=$(expr $USBSYSSIZEM + $USBSWAPM + $USBDATSIZEM + 3)

	# 1MB aligned USB stick
	echo "USB: Creating Empty IMG File"
	#dd if=/dev/zero of=${NAS4FREE_WORKINGDIR}/usb-image.bin bs=1m count=${USBIMGSIZEM}
	dd if=/dev/zero of=${NAS4FREE_WORKINGDIR}/usb-image.bin bs=1m seek=${USBIMGSIZEM} count=0
	echo "USB: Use IMG as a memory disk"
	md=`mdconfig -a -t vnode -f ${NAS4FREE_WORKINGDIR}/usb-image.bin -x ${USB_SECTS} -y ${USB_HEADS}`
	diskinfo -v ${md}

	echo "USB: Creating BSD partition on this memory disk"
	#gpart create -s bsd ${md}
	#gpart bootcode -b ${NAS4FREE_BOOTDIR}/boot ${md}
	#gpart add -s ${USBSYSSIZEM}m -t freebsd-ufs ${md}
	#gpart add -s ${USBSWAPM}m -t freebsd-swap ${md}
	#gpart add -s ${USBDATSIZEM}m -t freebsd-ufs ${md}
	#mdp=${md}a

	#gpart create -s mbr ${md}
	#gpart add -i 4 -t freebsd ${md}
	#gpart set -a active -i 4 ${md}
	#gpart bootcode -b ${NAS4FREE_BOOTDIR}/mbr ${md}
	#mdp=${md}s4
	#gpart create -s bsd ${mdp}
	#gpart bootcode -b ${NAS4FREE_BOOTDIR}/boot ${mdp}
	#gpart add -a 1m -s ${USBSYSSIZEM}m -t freebsd-ufs ${mdp}
	#gpart add -a 1m -s ${USBSWAPM}m -t freebsd-swap ${mdp}
	#gpart add -a 1m -s ${USBDATSIZEM}m -t freebsd-ufs ${mdp}
	#mdp=${mdp}a

	gpart create -s mbr ${md}
	gpart add -s ${USBSYSSIZEM}m -t freebsd ${md}
	gpart add -s ${USBSWPSIZEM}m -t freebsd ${md}
	gpart add -s ${USBDATSIZEM}m -t freebsd ${md}
	gpart set -a active -i 1 ${md}
	gpart bootcode -b ${NAS4FREE_BOOTDIR}/mbr ${md}

	# s1 (UFS/SYSTEM)
	gpart create -s bsd ${md}s1
	gpart bootcode -b ${NAS4FREE_BOOTDIR}/boot ${md}s1
	gpart add -a 1m -s ${USBROOTM}m -t freebsd-ufs ${md}s1
	# s2 (SWAP)
	gpart create -s bsd ${md}s2
	gpart add -i2 -a 1m -s ${USBSWAPM}m -t freebsd-swap ${md}s2
	# s3 (UFS/DATA) dummy
	gpart create -s bsd ${md}s3
	gpart add -a 1m -s ${USBDATAM}m -t freebsd-ufs ${md}s3
	# SYSTEM partition
	mdp=${md}s1a

	echo "USB: Formatting this memory disk using UFS"
	#newfs -S 512 -b 32768 -f 4096 -O2 -U -j -o time -m 8 -L "liveboot" /dev/${mdp}
	#newfs -S $NAS4FREE_IMGFMT_SECTOR -b $NAS4FREE_IMGFMT_BSIZE -f $NAS4FREE_IMGFMT_FSIZE -O2 -U -o space -m 0 -L "liveboot" /dev/${mdp}
	newfs -S 4096 -b 32768 -f 4096 -O2 -U -j -o space -m 0 -L "liveboot" /dev/${mdp}

	echo "USB: Mount this virtual disk on $NAS4FREE_TMPDIR"
	mount /dev/${mdp} $NAS4FREE_TMPDIR

	#echo "USB: Creating swap file on the memory disk"
	#dd if=/dev/zero of=$NAS4FREE_TMPDIR/swap.dat bs=1m seek=${USBSWAPM} count=0

	echo "USB: Copying previously generated MFSROOT file to memory disk"
	cp $NAS4FREE_WORKINGDIR/mfsroot.gz $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mfsroot.uzip $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mdlocal.xz $NAS4FREE_TMPDIR
	cp $NAS4FREE_WORKINGDIR/mdlocal-mini.xz $NAS4FREE_TMPDIR
	echo "${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-LiveUSB-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}" > $NAS4FREE_TMPDIR/version

	echo "USB: Copying Bootloader File(s) to memory disk"
	mkdir -p $NAS4FREE_TMPDIR/boot
	mkdir -p $NAS4FREE_TMPDIR/boot/kernel $NAS4FREE_TMPDIR/boot/defaults $NAS4FREE_TMPDIR/boot/zfs
	mkdir -p $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_ROOTFS/conf.default/config.xml $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_BOOTDIR/kernel/kernel.gz $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/kernel/*.ko $NAS4FREE_TMPDIR/boot/kernel
	cp $NAS4FREE_BOOTDIR/boot $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.conf $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.rc $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/support.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/defaults/loader.conf $NAS4FREE_TMPDIR/boot/defaults/
	cp $NAS4FREE_BOOTDIR/device.hints $NAS4FREE_TMPDIR/boot
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $NAS4FREE_SVNDIR/boot/menu.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/brand.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/check-password.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/color.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/delay.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/menu-commands.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/shortcuts.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/version.4th $NAS4FREE_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $NAS4FREE_SVNDIR/boot/splash.bmp $NAS4FREE_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules/splash/bmp/splash_bmp.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${NAS4FREE_ARCH} ]; then
		cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 apm/apm.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${NAS4FREE_ROOTFS}/boot/kernel/isboot.ko $NAS4FREE_TMPDIR/boot/kernel
	# preload kernel drivers
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 opensolaris/opensolaris.ko $NAS4FREE_TMPDIR/boot/kernel
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 zfs/zfs.ko $NAS4FREE_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	echo "USB: Copying IMG file to $NAS4FREE_TMPDIR"
	cp ${NAS4FREE_WORKINGDIR}/image.bin.xz ${NAS4FREE_TMPDIR}/${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-embedded.xz

	echo "USB: Unmount memory disk"
	umount $NAS4FREE_TMPDIR
	echo "USB: Detach memory disk"
	mdconfig -d -u ${md}
	#echo "USB: Compress the IMG file"
	#gzip -9n $NAS4FREE_WORKINGDIR/usb-image.bin
	#cp $NAS4FREE_WORKINGDIR/usb-image.bin.gz $NAS4FREE_ROOTDIR/$IMGFILENAME
	cp $NAS4FREE_WORKINGDIR/usb-image.bin $NAS4FREE_ROOTDIR/$IMGFILENAME

	echo "Generating SHA256 CHECKSUM File"
	NAS4FREE_CHECKSUMFILENAME="${NAS4FREE_PRODUCTNAME}-${NAS4FREE_XARCH}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}.checksum"
	cd ${NAS4FREE_ROOTDIR} && sha256 *.img *.xz *.iso > ${NAS4FREE_ROOTDIR}/${NAS4FREE_CHECKSUMFILENAME}

	# Cleanup.
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	[ -f $NAS4FREE_WORKINGDIR/mfsroot ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.gz ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.gz
	[ -f $NAS4FREE_WORKINGDIR/mfsroot.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mfsroot.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.xz
	[ -f $NAS4FREE_WORKINGDIR/mdlocal.uzip ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal.uzip
	[ -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz ] && rm -f $NAS4FREE_WORKINGDIR/mdlocal-mini.xz
	[ -f $NAS4FREE_WORKINGDIR/image.bin.xz ] && rm -f $NAS4FREE_WORKINGDIR/image.bin.xz
	[ -f $NAS4FREE_WORKINGDIR/usb-image.bin ] && rm -f $NAS4FREE_WORKINGDIR/usb-image.bin

	return 0
}

create_full() {
	[ -d $NAS4FREE_SVNDIR ] && use_svn ;

	echo "FULL: Generating $NAS4FREE_PRODUCTNAME tgz update file"

	# Set platform information.
	PLATFORM="${NAS4FREE_XARCH}-full"
	echo $PLATFORM > ${NAS4FREE_ROOTFS}/etc/platform

	# Set Revision.
	echo ${NAS4FREE_REVISION} > ${NAS4FREE_ROOTFS}/etc/prd.revision

	FULLFILENAME="${NAS4FREE_PRODUCTNAME}-${PLATFORM}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}.tgz"

	echo "FULL: Generating tempory $NAS4FREE_TMPDIR folder"
	#Clean TMP dir:
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR
	mkdir $NAS4FREE_TMPDIR

	#Copying all NAS4FREE rootfilesystem (including symlink) on this folder
	cd $NAS4FREE_TMPDIR
	tar -cf - -C $NAS4FREE_ROOTFS ./ | tar -xvpf -
	#tar -cf - -C $NAS4FREE_ROOTFS ./ | tar -xvpf - -C $NAS4FREE_TMPDIR
	echo "${NAS4FREE_PRODUCTNAME}-${PLATFORM}-${NAS4FREE_VERSION}.${NAS4FREE_REVISION}" > $NAS4FREE_TMPDIR/version

	echo "Copying bootloader file(s) to root filesystem"
	mkdir -p $NAS4FREE_TMPDIR/boot/kernel $NAS4FREE_TMPDIR/boot/defaults $NAS4FREE_TMPDIR/boot/zfs
	#mkdir $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_ROOTFS/conf.default/config.xml $NAS4FREE_TMPDIR/conf
	cp $NAS4FREE_BOOTDIR/kernel/kernel.gz $NAS4FREE_TMPDIR/boot/kernel
	gunzip $NAS4FREE_TMPDIR/boot/kernel/kernel.gz
	cp $NAS4FREE_BOOTDIR/boot $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.rc $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/loader.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/support.4th $NAS4FREE_TMPDIR/boot
	cp $NAS4FREE_BOOTDIR/defaults/loader.conf $NAS4FREE_TMPDIR/boot/defaults/
	cp $NAS4FREE_BOOTDIR/device.hints $NAS4FREE_TMPDIR/boot
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $NAS4FREE_SVNDIR/boot/menu.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		#cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/brand.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/check-password.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/color.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/delay.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/frames.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/menu-commands.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/screen.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/shortcuts.4th $NAS4FREE_TMPDIR/boot
		cp $NAS4FREE_BOOTDIR/version.4th $NAS4FREE_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $NAS4FREE_SVNDIR/boot/splash.bmp $NAS4FREE_TMPDIR/boot
		cp ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules/splash/bmp/splash_bmp.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${NAS4FREE_ARCH} ]; then
		cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && cp apm/apm.ko $NAS4FREE_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${NAS4FREE_ROOTFS}/boot/kernel/isboot.ko $NAS4FREE_TMPDIR/boot/kernel
	# preload kernel drivers
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 opensolaris/opensolaris.ko $NAS4FREE_TMPDIR/boot/kernel
	cd ${NAS4FREE_OBJDIRPREFIX}/usr/src/sys/${NAS4FREE_KERNCONF}/modules/usr/src/sys/modules && install -v -o root -g wheel -m 555 zfs/zfs.ko $NAS4FREE_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	#Generate a loader.conf for full mode:
	echo 'kernel="kernel"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'bootfile="kernel"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'kernel_options=""' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'hw.est.msr_info="0"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'hw.hptrr.attach_generic="0"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'kern.maxfiles="65536"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'kern.maxfilesperproc="60000"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'kern.cam.boot_delay="8000"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'splash_bmp_load="YES"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'bitmap_load="YES"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'bitmap_name="/boot/splash.bmp"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'autoboot_delay="5"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'isboot_load="YES"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'zfs_load="YES"' >> $NAS4FREE_TMPDIR/boot/loader.conf
	echo 'geom_xmd_load="YES"' >> $NAS4FREE_TMPDIR/boot/loader.conf

	#Check that there is no /etc/fstab file! This file can be generated only during install, and must be kept
	[ -f $NAS4FREE_TMPDIR/etc/fstab ] && rm -f $NAS4FREE_TMPDIR/etc/fstab

	#Check that there is no /etc/cfdevice file! This file can be generated only during install, and must be kept
	[ -f $NAS4FREE_TMPDIR/etc/cfdevice ] && rm -f $NAS4FREE_TMPDIR/etc/cfdevice

	echo "FULL: tgz the directory"
	cd $NAS4FREE_ROOTDIR
	tar cvfz $FULLFILENAME -C $NAS4FREE_TMPDIR ./

	# Cleanup.
	echo "Cleaning tempo file"
	[ -d $NAS4FREE_TMPDIR ] && rm -rf $NAS4FREE_TMPDIR

	return 0
}

# Update Subversion Sources.
update_svn() {
	# Update sources from repository.
	cd $NAS4FREE_ROOTDIR
	svn co $NAS4FREE_SVNURL svn

	# Update Revision Number.
	NAS4FREE_REVISION=$(svn info ${NAS4FREE_SVNDIR} | grep Revision | awk '{print $2}')

	return 0
}

use_svn() {
	echo "===> Replacing old code with SVN code"

	cd ${NAS4FREE_SVNDIR}/build && cp -pv CHANGES ${NAS4FREE_ROOTFS}/usr/local/www
	cd ${NAS4FREE_SVNDIR}/build/scripts && cp -pv carp-hast-switch ${NAS4FREE_ROOTFS}/usr/local/sbin
	cd ${NAS4FREE_SVNDIR}/build/scripts && cp -pv hastswitch ${NAS4FREE_ROOTFS}/usr/local/sbin
	cd ${NAS4FREE_SVNDIR}/root && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${NAS4FREE_ROOTFS}/root
	cd ${NAS4FREE_SVNDIR}/etc && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${NAS4FREE_ROOTFS}/etc
	cd ${NAS4FREE_SVNDIR}/www && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${NAS4FREE_ROOTFS}/usr/local/www
	cd ${NAS4FREE_SVNDIR}/conf && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${NAS4FREE_ROOTFS}/conf.default

	return 0
}

build_system() {
  while true; do
echo -n '
-----------------------------
Compile NAS4FREE from Scratch
-----------------------------
Menu Options:

1 - Update FreeBSD Source Tree and Ports Collections.
2 - Create Filesystem Structure.
3 - Build/Install the Kernel.
4 - Build World.
5 - Build Ports.
6 - Build Bootloader.
7 - Add Necessary Libraries.
8 - Modify File Permissions.
* - Exit.
Press # '
		read choice
		case $choice in
			1)	update_sources;;
			2)	create_rootfs;;
			3)	build_kernel;;
			4)	build_world;;
			5)	build_ports;;
			6)	opt="-f";
					if [ 0 != $OPT_BOOTMENU ]; then
						opt="$opt -m"
					fi;
					if [ 0 != $OPT_BOOTSPLASH ]; then
						opt="$opt -b"
					fi;
					if [ 0 != $OPT_SERIALCONSOLE ]; then
						opt="$opt -s"
					fi;
					$NAS4FREE_SVNDIR/build/nas4free-create-bootdir.sh $opt $NAS4FREE_BOOTDIR;;
			7)	add_libs;;
			8)	$NAS4FREE_SVNDIR/build/nas4free-modify-permissions.sh $NAS4FREE_ROOTFS;;
			*)	main; return $?;;
		esac
		[ 0 == $? ] && echo "=> Successfully done <=" || echo "=> Failed!"
		sleep 1
  done
}

build_ports() {
	tempfile=$NAS4FREE_WORKINGDIR/tmp$$
	ports=$NAS4FREE_WORKINGDIR/ports$$

	# Choose what to do.
	$DIALOG --title "$NAS4FREE_PRODUCTNAME - Build/Install Ports" --menu "Please select whether you want to build or install ports." 10 45 2 \
		"build" "Build ports" \
		"install" "Install ports" 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choice=`cat $tempfile`
	rm $tempfile

	# Create list of available ports.
	echo "#! /bin/sh
$DIALOG --title \"$NAS4FREE_PRODUCTNAME - Ports\" \\
--checklist \"Select the ports you want to process.\" 21 75 14 \\" > $tempfile

	for s in $NAS4FREE_SVNDIR/build/ports/*; do
		[ ! -d "$s" ] && continue
		port=`basename $s`
		state=`cat $s/pkg-state`
		if [ "arm" = ${NAS4FREE_ARCH} ]; then
			for forceoff in arcconf isboot open-vm-tools tw_cli vbox-additions vmxnet3; do
				if [ "$port" = "$forceoff" ]; then
					state="OFF"; break;
				fi
			done
		fi
		case ${state} in
			[hH][iI][dD][eE])
				;;
			*)
				desc=`cat $s/pkg-descr`;
				echo "\"$port\" \"$desc\" $state \\" >> $tempfile;
				;;
		esac
	done

	# Display list of available ports.
	sh $tempfile 2> $ports
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		rm $ports
		return 1
	fi
	rm $tempfile

	case ${choice} in
		build)
			# Set ports options
			echo;
			echo "--------------------------------------------------------------";
			echo ">>> Set Ports Options.";
			echo "--------------------------------------------------------------";
			cd ${NAS4FREE_SVNDIR}/build/ports/options && make
			# Clean ports.
			echo;
			echo "--------------------------------------------------------------";
			echo ">>> Cleaning Ports.";
			echo "--------------------------------------------------------------";
			for port in $(cat ${ports} | tr -d '"'); do
				cd ${NAS4FREE_SVNDIR}/build/ports/${port};
				make clean;
			done;
			# Build ports.
			for port in $(cat $ports | tr -d '"'); do
				echo;
				echo "--------------------------------------------------------------";
				echo ">>> Building Port: ${port}";
				echo "--------------------------------------------------------------";
				cd ${NAS4FREE_SVNDIR}/build/ports/${port};
				make build;
				[ 0 != $? ] && return 1; # successful?
			done;
			;;
		install)
			for port in $(cat ${ports} | tr -d '"'); do
				echo;
				echo "--------------------------------------------------------------";
				echo ">>> Installing Port: ${port}";
				echo "--------------------------------------------------------------";
				cd ${NAS4FREE_SVNDIR}/build/ports/${port};
				# Delete cookie first, otherwise Makefile will skip this step.
				rm -f ./work/.install_done.* ./work/.stage_done.*;
				env PKG_DBDIR=$NAS4FREE_WORKINGDIR/pkg FORCE_PKG_REGISTER=1 make install;
				[ 0 != $? ] && return 1; # successful?
			done;
			;;
	esac
	rm ${ports}

  return 0
}

main() {
	# Ensure we are in $NAS4FREE_WORKINGDIR
	[ ! -d "$NAS4FREE_WORKINGDIR" ] && mkdir $NAS4FREE_WORKINGDIR
	[ ! -d "$NAS4FREE_WORKINGDIR/pkg" ] && mkdir $NAS4FREE_WORKINGDIR/pkg
	cd $NAS4FREE_WORKINGDIR

	echo -n "
--------------------------
${NAS4FREE_PRODUCTNAME} Build Environment
--------------------------
Menu Options:

1  - Update NAS4FREE Source Files to CURRENT.
2  - Compile NAS4FREE from Scratch.
10 - Create 'Embedded' (IMG) File (rawrite to CF/USB/DD).
11 - Create 'LiveUSB' (IMG) File.
12 - Create 'LiveCD' (ISO) File.
13 - Create 'LiveCD-Tiny' (ISO) File without 'Embedded' File.
14 - Create 'Full' (TGZ) Update File.
*  - Exit.
Press # "
	read choice
	case $choice in
		1)	update_svn;;
		2)	build_system;;
		10)	create_embedded;;
		11)	create_usb;;
		12)	create_iso;;
		13)	create_iso_tiny;;
		14)	create_full;;
		*)	exit 0;;
	esac

	[ 0 == $? ] && echo "=> Successfully done <=" || echo "=> Failed! <="
	sleep 1

	return 0
}

while true; do
	main
done
exit 0
