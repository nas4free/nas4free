#!/bin/sh
#
#This script does create the rootfs.
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#


MINIBSD_DIR=${NAS4FREE_ROOTFS};

# Initialize variables.
opt_f=0

# Parse the command-line options.
while getopts 'fh' option
do
	case "$option" in
    "f")  opt_f=1;;
    "h")  echo "$(basename $0): Create directory structure";
          echo "Common Options:";
          echo "  -f    Force executing this script";
          exit 1;;
    ?)    echo "$0: Unknown option. Exiting...";
          exit 1;;
  esac
done

shift `expr $OPTIND - 1`

echo "Create directory structure..."

if [ ! -z "$1" ]; then
  MINIBSD_DIR=$1;
  echo "Using directory $1.";
fi

if [ 1 != $opt_f -a -d "$MINIBSD_DIR" ]; then
  echo ;
  echo "$MINIBSD_DIR directory does already exist. Remove it" ;
  echo "before running this script." ;
  echo ;
  echo "Exiting..." ;
  echo ;
  exit 1 ;
fi ;

mkdir $MINIBSD_DIR ;
cd $MINIBSD_DIR ;

# Create directories
mkdir boot ;
mkdir boot/defaults ;
mkdir boot/kernel ;
mkdir boot/modules ;
mkdir boot/zfs ;
mkdir bin ;
mkdir cf ;
mkdir cf/conf ;
mkdir ftmp ;
mkdir conf.default ;
mkdir dev ;
mkdir etc ;
mkdir etc/mail ;
mkdir etc/defaults ;
mkdir etc/devd ;
mkdir etc/inc ;
mkdir etc/install ;
mkdir etc/pam.d ;
mkdir etc/ssh ;
mkdir etc/rc.d ;
mkdir etc/rc.d.php ;
mkdir etc/zfs ;
mkdir lib ;
mkdir lib/geom ;
mkdir libexec ;
mkdir -m 0755 mnt ;
mkdir -m 0700 root ;
mkdir sbin ;
mkdir usr ;
mkdir usr/bin ;
mkdir usr/lib ;
mkdir usr/lib/aout ;
mkdir usr/libexec ;
mkdir usr/local ;
mkdir usr/local/bin;
mkdir usr/local/lib ;
mkdir usr/local/libexec ;
mkdir usr/local/sbin ;
mkdir usr/local/share ;
mkdir usr/local/share/locale ;
mkdir usr/local/etc ;
mkdir usr/local/etc/php ;
mkdir usr/local/www ;
mkdir usr/local/www/syntaxhighlighter ;
mkdir usr/sbin ;
mkdir usr/share ;
mkdir usr/share/misc ;
mkdir usr/share/locale ;
mkdir usr/share/snmp ;
mkdir usr/share/snmp/defs ;
mkdir usr/share/snmp/mibs ;
#mkdir -m 01777 tmp ;
# /var will be populated by /etc/rc and /etc/rc.d/var
mkdir var ;
mkdir -m 0555 proc ;

# Creating symbolic links. Most of the target files will be created at runtime.
# !!! For optional ports add the required links in the port Makefile. !!!
ln -s cf/conf conf
ln -s /var/run/.htpasswd usr/local/www/.htpasswd
ln -s /var/etc/resolv.conf etc/resolv.conf
ln -s /var/etc/exports etc/exports
ln -s /var/etc/hast.conf etc/hast.conf
ln -s /var/etc/hosts etc/hosts
ln -s /var/etc/crontab etc/crontab
ln -s /var/etc/syslog.conf etc/syslog.conf
ln -s /var/etc/ssh/sshd_config etc/ssh/sshd_config
ln -s /var/etc/ssh/ssh_host_dsa_key etc/ssh/ssh_host_dsa_key
ln -s /var/etc/pam.d/ftp etc/pam.d/ftp
ln -s /var/etc/pam.d/ftp etc/pam.d/proftpd
ln -s /var/etc/pam.d/sshd etc/pam.d/sshd
ln -s /var/etc/pam.d/netatalk etc/pam.d/netatalk
ln -s /var/etc/pam.d/login etc/pam.d/login
ln -s /var/etc/pam.d/system etc/pam.d/system
ln -s /var/etc/nsswitch.conf etc/nsswitch.conf
ln -s /var/tmp tmp
