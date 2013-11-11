#!/bin/sh
#
# This script does Modify the file permissions.
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#

ROOTDIR=

echo "Modify file permissions..."

if [ ! -z "$1" ] && [ -d "$1" ]; then
  ROOTDIR=$1;
  echo "Using directory $1.";
fi

if [ -z $ROOTDIR ]; then
	echo "=> No root directory defined.";
	echo "=> Exiting..."
	exit 1;
fi

# Change directory to given root.
cd $ROOTDIR

# usr/bin/su
echo "usr/bin/su"
chflags -RH noschg usr/bin/su
chmod 4755 usr/bin/su

# usr/bin/passwd
echo "usr/bin/passwd"
chflags -RH noschg usr/bin/passwd

# sbin/init
echo "sbin/init"
chflags -RH noschg sbin/init

# libexec/ld-elf.so.1
echo "libexec/ld-elf.so.1"
chflags -RH noschg libexec/ld-elf.so.1

# lib/libc.so.7
echo "lib/libc.so.7"
chflags -RH noschg lib/libc.so.7

# lib/libcrypt.so.5
echo "lib/libcrypt.so.5"
chflags -RH noschg lib/libcrypt.so.5

# lib/libthr.so.3
echo "lib/libthr.so.3"
chflags -RH noschg lib/libthr.so.3
