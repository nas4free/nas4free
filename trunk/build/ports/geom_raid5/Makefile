# http://lev.serebryakov.spb.ru/download/graid5/
PORTNAME=		geom_raid5
PORTVERSION=	1.3
PORTREVISION?=  1
CATEGORIES=		sysutils
COMMENT=		GEOM RAID5 filesystem
MAINTAINER=		zoon01@nas4free.org

DISTFILES?=
NO_BUILD?=	yes
NO_PKG_REGISTER?=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build

BUILD_DEPENDS=	${NONEXISTENT}:${PORTSDIR}/sysutils/graid5


do-install:
	# Install kernel module.
	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/graid5/work/graid5-1.3.20140711.62/sys/geom_raid5.ko ${NAS4FREE_ROOTFS}/boot/kernel

	# Install application.
	@${LN} -f -v -s /sbin/geom ${NAS4FREE_ROOTFS}/sbin/graid5
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/graid5/work/graid5-1.3.20140711.62/lib/geom_raid5.so ${NAS4FREE_ROOTFS}/lib/geom


.include <bsd.port.mk>
