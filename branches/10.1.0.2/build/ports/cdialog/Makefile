PORTNAME=			cdialog
PORTVERSION=	1.2.20140219
PORTEPOCH=	1
CATEGORIES=			devel
COMMENT=			An enhanced version of 'dialog' to work with ncurses
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	ftp://invisible-island.net/${PORTNAME:S|^c||}/
DISTNAME=			${PORTNAME:S|^c||}-${PORTVERSION:R}-${PORTVERSION:E}

EXTRACT_SUFX=	.tgz
NO_PKG_REGISTER=yes
USES=           libtool:build
GNU_CONFIGURE=		yes
USE_LDCONFIG=			yes
CONFIGURE_ARGS=		--enable-widec \
					--includedir=${PREFIX}/include/${PORTNAME} \
					--with-libtool \
					--with-ncursesw \
					--with-package=${PORTNAME}
MAKEFILE=			makefile

post-patch:
	@${FIND} ${WRKSRC}/samples/ -type f -exec ${REINPLACE_CMD} -i "" "s|dialog|cdialog|g" {} \;

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/cdialog ${NAS4FREE_ROOTFS}/${PREFIX}/bin/cdialog
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/libcdialog.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib

.include <bsd.port.mk>