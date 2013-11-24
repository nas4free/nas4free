PORTNAME=		fdisk
PORTVERSION=	1.0
CATEGORIES=		sysutils
COMMENT=		PC slice table maintenance utility
MAINTAINER=		aoyama@peach.ne.jp
DISTFILES=

do-extract:
	@${RM} -rf ${WRKDIR}
	@${MKDIR} ${WRKDIR}
	@${TAR} -xf ${FILESDIR}/${DISTNAME}.tar.gz -C ${WRKDIR}

do-build:
	@${MAKE} -C ${WRKSRC}

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/fdisk ${NAS4FREE_ROOTFS}/sbin

.include <bsd.port.mk>
