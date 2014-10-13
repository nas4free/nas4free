PORTNAME=		tmux
PORTVERSION=	1.9a
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

# 1.9a was released as an errata to 1.9
# this port was never updated to 1.9
PORTSCOUT=	ignore:1.9

.include <bsd.port.options.mk>

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

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/tmux ${STAGEDIR}${PREFIX}/bin
	${INSTALL_MAN} ${WRKSRC}/tmux.1 ${STAGEDIR}${MANPREFIX}/man/man1
	@${MKDIR} ${STAGEDIR}${PREFIX}/etc/bash_completion.d
	${INSTALL_DATA} ${WRKSRC}/examples/bash_completion_tmux.sh ${STAGEDIR}${PREFIX}/etc/bash_completion.d/tmux
	@${MKDIR} ${STAGEDIR}${DOCSDIR}
	(cd ${WRKSRC} && ${INSTALL_DATA} ${PORTDOCS} ${STAGEDIR}${DOCSDIR})
	@${MKDIR} ${STAGEDIR}${EXAMPLESDIR}
	${INSTALL_DATA} ${WRKSRC}/examples/* ${STAGEDIR}${EXAMPLESDIR}

.include <bsd.port.mk>