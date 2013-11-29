# wgboome.homepage.t-online.de/geom_raid5.tbz
PORTNAME=		geom_raid5
PORTVERSION=	1.0
PORTREVISION=	0
PORTEPOCH=	3
CATEGORIES=		sysutils
COMMENT=		GEOM RAID5 filesystem
MAINTAINER=		zoon01@nas4free.org

DISTFILES?=
NO_STAGE=       yes

do-build:
	@${TAR} -zxvf ${FILESDIR}/geom_raid5.tbz -C /usr/src

	# Patch geom raid 5 for FreeBSD 8.0.
	@${PATCH} -f /usr/src/sys/geom/raid5/g_raid5.c ${FILESDIR}/g_raid5.c.patch
	@${PATCH} -f /usr/src/sbin/geom/class/raid5/Makefile ${FILESDIR}/Makefile.patch
	@${PATCH} -f /usr/src/sbin/geom/class/raid5/geom_raid5.c ${FILESDIR}/geom_raid5.c.patch

	# Make kernel module.
	@${MAKE} -C /usr/src/sys/modules/geom/geom_raid5 depend
	@${MAKE} -C /usr/src/sys/modules/geom/geom_raid5

	# Make application.
	@${MKDIR} -v /usr/include/geom/raid5
	@${CP} -pv /usr/src/sys/geom/raid5/g_raid5.h /usr/include/geom/raid5
	@${MAKE} -C /usr/src/sbin/geom/class/raid5 depend
	@${MAKE} -C /usr/src/sbin/geom/class/raid5

do-install:
	# Install kernel module.
	@${INSTALL_KLD} -v /usr/obj/usr/src/sys/modules/geom/geom_raid5/geom_raid5.ko ${NAS4FREE_ROOTFS}/boot/kernel

	# Install application.
	@${LN} -f -v -s /sbin/geom ${NAS4FREE_ROOTFS}/sbin/graid5
	@${INSTALL_PROGRAM} -v /usr/obj/usr/src/sbin/geom/class/raid5/geom_raid5.so ${NAS4FREE_ROOTFS}/lib/geom

pre-clean:
.if exists(/usr/src/sys/modules/geom/geom_raid5)
	@${MAKE} -C /usr/src/sys/modules/geom/geom_raid5 clean obj
.endif
.if exists(/usr/src/sbin/geom/class/raid5)
	@${MAKE} -C /usr/src/sbin/geom/class/raid5 clean obj
.endif

.include <bsd.port.mk>
