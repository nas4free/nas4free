# https://bugs.freebsd.org/bugzilla/show_bug.cgi?id=164696

PATCHFILE=patch-sys__netinet__ip_carp.c

all:

install:
	(cd /usr/src; patch -p < ${.CURDIR}/files/${PATCHFILE})
