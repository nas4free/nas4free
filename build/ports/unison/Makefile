PORTNAME=		unison
PORTVERSION=	2.48.4
PORTREVISION=	0
CATEGORIES=	net
COMMENT=	A user-level file synchronization tool
MAINTAINER=	zoon1@nas4free.org

MASTER_SITES=	http://www.cis.upenn.edu/~bcpierce/unison/download/releases/stable/
DISTFILES=	${EXTRACT_ONLY}
DIST_SUBDIR=	${DISTNAME}
EXTRACT_ONLY=	${DISTNAME}${EXTRACT_SUFX}

PLIST_SUB=	PORTVERSION=${PORTVERSION}
USES=		gmake
USE_OCAML=	yes
WRKSRC=		${WRKDIR}/src
NO_OCAML_RUNDEPENDS=yes
WANT_GNOME=	yes
MAKE_ARGS=	CFLAGS=""
CONFLICTS?=	unison-devel-[0-9]* unison-nox11-[0-9]*

MAKE_JOBS_UNSAFE=	yes
ALL_TARGET=	unison all

DOCS=		BUGS.txt NEWS README
EXTRA_DOCS=	${DISTNAME}-manual.html \
		${DISTNAME}-manual.pdf ${DISTNAME}-manual.ps

OPTIONS_DEFINE= DOCS X11
OPTIONS_DEFAULT?=	X11

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MX11}
MAKE_ARGS+=	UISTYLE=gtk2
PLIST_SUB+=	TEXT=""
BUILD_DEPENDS+=	lablgtk2:x11-toolkits/ocaml-lablgtk2 \
		icotool:graphics/icoutils
RUN_DEPENDS+=	lablgtk2:x11-toolkits/ocaml-lablgtk2
SUB_FILES+=	${PORTNAME}.desktop
.else
MAKE_ARGS+=	UISTYLE=text
PLIST_SUB+=	TEXT="@comment "
PKGMESSAGE=	${PKGDIR}/pkg-message.nox11
.endif

.if ${PORT_OPTIONS:MX11}
post-patch:
	@${REINPLACE_CMD} -Ee 's@(\+|/)(lablgtk2)@\1site-lib/\2@' \
		${WRKSRC}/Makefile.OCaml

post-build:
	@${ECHO} Building text-only version
	@${ECHO} ${WRKSRC}
	${SETENV} ${MAKE_ENV} ${MAKE_CMD} -C ${WRKSRC} CFLAGS="" UISTYLE=text NAME=unison-text
	@cd ${WRKSRC}/win32rc && ${LOCALBASE}/bin/icotool -x U.ico
.endif

# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly).
FORCE_PKG_REGISTER=	yes

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/unison ${NAS4FREE_ROOTFS}/usr/local/bin
# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly). 
	@${INSTALL_PROGRAM} -v ${WRKSRC}/unison ${PREFIX}/bin

.include <bsd.port.mk>