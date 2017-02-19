PORTNAME=		ext4fuse
PORTVERSION=	0.1.3
DISTVERSIONPREFIX=	v
PORTEPOCH=	1
CATEGORIES=	sysutils
PKGNAMEPREFIX=	fusefs-

MAINTAINER=	zoon1@nas4free.org
COMMENT=	EXT4 implementation for FUSE

LICENSE=	GPLv2

USES=		execinfo fuse pkgconfig gmake
USE_GITHUB=	yes
GH_ACCOUNT=	gerard

# explicitly empty to fix a cross compiling related issue (PR: 165480)
MAKE_ENV+=	TARGET_ARCH=

ALL_TARGET=	${PORTNAME}

PLIST_FILES=	bin/ext4fuse

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/${PORTNAME} ${STAGEDIR}${PREFIX}/bin

# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/ext4fuse ${NAS4FREE_ROOTFS}/usr/local/bin

.include <bsd.port.mk>
