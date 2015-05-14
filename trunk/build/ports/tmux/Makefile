PORTNAME=		tmux
PORTVERSION=	2.0
PORTREVISION?=  0
CATEGORIES=		sysutils
COMMENT=		Terminal Multiplexer
MAINTAINER=		zoon01@nas4free.org

COMMENT=	Terminal Multiplexer

USES=		gmake libtool
SHEBANG_FILES=	examples/tmux_backup.sh

GNU_CONFIGURE=	yes
CONFIGURE_ARGS+=--sysconfdir=${PREFIX}/etc

PLIST_FILES=		bin/tmux \
			etc/bash_completion.d/tmux

PLIST_DIRSTRY=	etc/bash_completion.d

PORTDOCS=	CHANGES FAQ README TODO
PORTEXAMPLES=	*

OPTIONS_DEFINE=	DOCS EXAMPLES LIBEVENT_STATIC BACKSPACE

LIBEVENT_STATIC_DESC=	Build with static libevent
BACKSPACE_DESC=	Build with tty/keys patch

CFLAGS+=	-Wno-pointer-sign

BACKSPACE_EXTRA_PATCHES=	${PATCHDIR}/extra-patch-tty-keys.c

LIBEVENT_STATIC_LIB_DEPENDS_OFF=	libevent.so:${PORTSDIR}/devel/libevent2
LIBEVENT_STATIC_BUILD_DEPENDS=	${LOCALBASE}/lib/libevent.a:${PORTSDIR}/devel/libevent2

.include <bsd.port.options.mk>

.if ${OSVERSION} < 900004
LIB_DEPENDS+=   libutempter.so:${PORTSDIR}/sysutils/libutempter
CFLAGS+=        -I${LOCALBASE}/include
LDFLAGS+=       -L${LOCALBASE}/lib
.endif

post-patch:
	@${REINPLACE_CMD} -e 's|/etc/tmux.conf|${PREFIX}/etc/tmux.conf|g' \
		${WRKSRC}/tmux.1 ${WRKSRC}/tmux.h ${WRKSRC}/CHANGES
	${REINPLACE_CMD} -e '/LIBS=/s/-lrt//' ${WRKSRC}/${CONFIGURE_SCRIPT}

.if ${PORT_OPTIONS:MLIBEVENT_STATIC}
LESTATIC=	${LOCALBASE}/lib/libevent.a
post-configure:
	${REINPLACE_CMD} -e '/LIBS/s|-levent[^[:blank:]]*|${LESTATIC}|' \
		${WRKSRC}/${MAKEFILE}
.endif

.include <bsd.port.pre.mk>

do-install:
#	for now also build on system
	${INSTALL_PROGRAM} ${WRKSRC}/tmux ${STAGEDIR}${PREFIX}/bin
	${INSTALL_MAN} ${WRKSRC}/tmux.1 ${STAGEDIR}${MANPREFIX}/man/man1
	@${MKDIR} ${STAGEDIR}${PREFIX}/etc/bash_completion.d
	${INSTALL_DATA} ${WRKSRC}/examples/bash_completion_tmux.sh ${STAGEDIR}${PREFIX}/etc/bash_completion.d/tmux
	@${MKDIR} ${STAGEDIR}${DOCSDIR}
	(cd ${WRKSRC} && ${INSTALL_DATA} ${PORTDOCS} ${STAGEDIR}${DOCSDIR})
	@${MKDIR} ${STAGEDIR}${EXAMPLESDIR}
	${INSTALL_DATA} ${WRKSRC}/examples/* ${STAGEDIR}${EXAMPLESDIR}

#	NAS4Free Install
	@${INSTALL_PROGRAM} ${WRKSRC}/tmux ${NAS4FREE_ROOTFS}/bin

post-install:
	${MKDIR} ${NAS4FREE_ROOTFS}/etc/bash_completion.d
	@${INSTALL_DATA} ${WRKSRC}/examples/bash_completion_tmux.sh ${NAS4FREE_ROOTFS}/etc/bash_completion.d/tmux
.include <bsd.port.mk>