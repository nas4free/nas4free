# ZFS patch by Daisuke Aoyama (aoyama@peach.ne.jp)
# Latest can be found at http://shell.peach.ne.jp/aoyama/
#
OSVERSION!=/sbin/sysctl -n kern.osreldate

#.if ${OSVERSION} < 800000
#.else
#.endif

PATCHFILE=patch-arc.c

all:

install:
	(cd /usr/src; patch -p1 < ${.CURDIR}/files/${PATCHFILE})
