#!/bin/sh
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#

MINIBSD_DIR=${NAS4FREE_ROOTDIR}/bootloader;

# Initialize variables.
opt_a=0
opt_b=0
opt_d=0
opt_m=0
opt_s=0
opt_f=0

# Parse the command-line options.
while getopts 'abdfhms' option
do
	case "$option" in
    "a")  opt_a=1;;
    "b")  opt_b=1;;
    "d")  opt_d=1;;
    "m")  opt_m=1;;
    "f")  opt_f=1;;
    "s")  opt_s=1;;
    "h")  echo "$(basename $0): Build boot loader";
          echo "Common Options:";
          echo "  -a    Disable ACPI"
          echo "  -b    Enable bootsplash";
          echo "  -d    Enable debug"
          echo "  -m    Enable bootmenu";
          echo "  -s    Enable serial console";
          echo "  -f    Force executing this script";
          exit 1;;
    ?)    echo "$0: Unknown option. Exiting...";
          exit 1;;
  esac
done

shift `expr $OPTIND - 1`

echo "Building the boot loader..."

if [ -n "$1" ]; then
  MINIBSD_DIR=$1
  echo "Using directory $1."
fi

if [ 1 != $opt_f -a -d "$MINIBSD_DIR" ]; then
  echo
  echo "=> $MINIBSD_DIR directory does already exist. Remove it"
  echo "=> before running this script."
  echo
  echo "=> Exiting..."
  echo
  exit 1
fi

# Create the boot directory that will contain boot, and kernel
mkdir $MINIBSD_DIR
mkdir $MINIBSD_DIR/defaults
mkdir $MINIBSD_DIR/firmware
mkdir $MINIBSD_DIR/kernel
mkdir $MINIBSD_DIR/modules
mkdir $MINIBSD_DIR/zfs

# Copy required files
cp -v ${NAS4FREE_WORLD}/boot/defaults/loader.conf $MINIBSD_DIR/defaults
cp -v ${NAS4FREE_WORLD}/boot/loader $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/boot $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/mbr $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/gptboot $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/pmbr $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/cdboot $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/loader.4th $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/support.4th $MINIBSD_DIR
cp -v ${NAS4FREE_WORLD}/boot/device.hints $MINIBSD_DIR
# Copy files required by bootmenu
if [ 0 != $opt_m ]; then
#	cp -v ${NAS4FREE_WORLD}/boot/screen.4th $MINIBSD_DIR
#	cp -v ${NAS4FREE_WORLD}/boot/frames.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/brand.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/check-password.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/color.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/delay.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/frames.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/menu-commands.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/screen.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/shortcuts.4th $MINIBSD_DIR
	cp -v ${NAS4FREE_WORLD}/boot/version.4th $MINIBSD_DIR
fi

# Generate the loader.rc file used by bootloader
echo "Generate $MINIBSD_DIR/loader.rc"
echo 'include /boot/loader.4th
start
check-password' > $MINIBSD_DIR/loader.rc
# Enable bootmenu
if [ 0 != $opt_m ]; then
	echo 'include /boot/menu.4th' >> $MINIBSD_DIR/loader.rc
	echo 'menu-start' >> $MINIBSD_DIR/loader.rc
fi

# Generate the loader.conf file using by bootloader
echo "Generate $MINIBSD_DIR/loader.conf"
echo 'mfsroot_load="NO"' > $MINIBSD_DIR/loader.conf
echo 'mfsroot_type="mfs_root"' >> $MINIBSD_DIR/loader.conf
echo 'mfsroot_name="/mfsroot"' >> $MINIBSD_DIR/loader.conf
echo 'hw.est.msr_info="0"' >> $MINIBSD_DIR/loader.conf
echo 'hw.hptrr.attach_generic="0"' >> $MINIBSD_DIR/loader.conf
echo 'hw.msk.msi_disable="1"' >> $MINIBSD_DIR/loader.conf
echo 'kern.maxfiles="65536"' >> $MINIBSD_DIR/loader.conf
echo 'kern.maxfilesperproc="60000"' >> $MINIBSD_DIR/loader.conf
echo 'kern.cam.boot_delay="8000"' >> $MINIBSD_DIR/loader.conf
echo 'kern.geom.label.disk_ident.enable="0"' >> $MINIBSD_DIR/loader.conf
echo 'hint.acpi_throttle.0.disabled="0"' >> $MINIBSD_DIR/loader.conf
echo 'hint.p4tcc.0.disabled="0"' >> $MINIBSD_DIR/loader.conf

# Enable bootsplash?
if [ 0 != $opt_b ]; then
	echo 'splash_bmp_load="YES"' >> $MINIBSD_DIR/loader.conf
	echo 'bitmap_load="YES"' >> $MINIBSD_DIR/loader.conf
	echo 'bitmap_name="/boot/splash.bmp"' >> $MINIBSD_DIR/loader.conf
fi
# Enable bootmenu?
if [ 0 != $opt_m ]; then
	echo 'autoboot_delay="3"' >> $MINIBSD_DIR/loader.conf
else
	echo 'autoboot_delay="-1"' >> $MINIBSD_DIR/loader.conf
fi
# Enable debug?
if [ 0 != $opt_d ]; then
  echo 'verbose_loading="YES"' >> $MINIBSD_DIR/loader.conf
  echo 'boot_verbose=""' >> $MINIBSD_DIR/loader.conf
fi
# Enable serial console?
if [ 0 != $opt_s ]; then
  echo 'console="vidconsole,comconsole"' >> $MINIBSD_DIR/loader.conf
fi
# Disable ACPI?
if [ 0 != $opt_a ]; then
  echo 'hint.acpi.0.disabled="1"' >> $MINIBSD_DIR/device.hints
fi
# iSCSI driver
echo 'isboot_load="YES"' >> $MINIBSD_DIR/loader.conf
# preload kernel drivers
#echo 'opensolaris_load="YES"' >> $MINIBSD_DIR/loader.conf
echo 'zfs_load="YES"' >> $MINIBSD_DIR/loader.conf
#echo 'geom_mirror_load="YES"' >> $MINIBSD_DIR/loader.conf
#echo 'geom_stripe_load="YES"' >> $MINIBSD_DIR/loader.conf
# xmd
echo 'xmfsroot_load="YES"' >> $MINIBSD_DIR/loader.conf
echo 'xmfsroot_type="xmd_root"' >> $MINIBSD_DIR/loader.conf
echo 'xmfsroot_name="/mfsroot.uzip"' >> $MINIBSD_DIR/loader.conf
echo 'geom_xmd_load="YES"' >> $MINIBSD_DIR/loader.conf

# Copy kernel.
if [ -e "${NAS4FREE_WORKINGDIR}/kernel.gz" ] ; then
  cp ${NAS4FREE_WORKINGDIR}/kernel.gz $MINIBSD_DIR/kernel
else
  echo "=> ERROR: File kernel.gz does not exist!";
  exit 1;
fi
