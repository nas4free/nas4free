PORTNAME=		unison
PORTVERSION=	2.40.63
CATEGORIES=		net
COMMENT=		A user-level file synchronization tool
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://www.cis.upenn.edu/~bcpierce/unison/download/releases/stable/
DISTFILES=		${EXTRACT_ONLY}
DIST_SUBDIR=	${PORTNAME}-${PORTVERSION}
EXTRACT_ONLY=	${PORTNAME}-${PORTVERSION}.tar.gz

BUILD_DEPENDS=	ocamlc:${PORTSDIR}/lang/ocaml-nox11
PATCH_DEPENDS=	${BUILD_DEPENDS}

USE_GMAKE=	yes
MAKE_ARGS=	CFLAGS=""
MAKE_ARGS+=	UISTYLE=text

# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly).
FORCE_PKG_REGISTER=	yes

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/unison ${NAS4FREE_ROOTFS}/usr/local/bin
# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly). 
	@${INSTALL_PROGRAM} -v ${WRKSRC}/unison ${PREFIX}/bin

.include <bsd.port.mk>
