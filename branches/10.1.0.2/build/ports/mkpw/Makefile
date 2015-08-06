PORTNAME=		mkpw
PORTVERSION=		1.1
CATEGORIES=		sysutils
COMMENT=		Password generation tool
MAINTAINER=		zoon01@nas4free.org

DISTFILES?=
NO_PKG_REGISTER=	yes

do-build:
	@${MKDIR} -p ${WRKSRC}
	@${TAR} -xvf ${FILESDIR}/${PORTNAME}.tar -C ${WRKSRC}
	@${MAKE} -C ${WRKSRC}

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME} ${NAS4FREE_ROOTFS}/usr/sbin

.include <bsd.port.mk>
