PORTNAME=		geom_eli
PORTVERSION=	1.0
PORTREVISION=	0
PORTEPOCH=	5
CATEGORIES=		sysutils
COMMENT=		GEOM Eli filesystem encryption
MAINTAINER=		zoon01@nas4free.org

DISTFILES?=

do-build:
	-@if [ -e /usr/src/sbin/geom/class/eli/geom_eli.c.orig ]; then \
		${MV} /usr/src/sbin/geom/class/eli/geom_eli.c.orig /usr/src/sbin/geom/class/eli/geom_eli.c; \
	fi

	# Patch geom eli sources.
	${PATCH} -f /usr/src/sbin/geom/class/eli/geom_eli.c ${FILESDIR}/geom_eli.c.diff

	@cd /usr/src/sbin/geom/class/eli; make clean obj; make

do-install:
	@${INSTALL_PROGRAM} -v /usr/obj/usr/src/sbin/geom/class/eli/geom_eli.so ${NAS4FREE_ROOTFS}/lib/geom

pre-clean:
	@cd /usr/src/sbin/geom/class/eli; make clean obj

.include <bsd.port.mk>
