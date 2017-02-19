PORTNAME=		tmux
PORTVERSION=	2.3
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Terminal Multiplexer
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=	https://github.com/tmux/tmux/releases/download/${PORTVERSION}/ \
		SF/${PORTNAME}/${PORTNAME}/${PORTNAME}-${PORTVERSION:S/a$//}

USES=		gmake libtool

GNU_CONFIGURE=	yes
CONFIGURE_ARGS+=--sysconfdir=${PREFIX}/etc

PLIST_FILES=	bin/tmux

PORTDOCS=	CHANGES FAQ README TODO

OPTIONS_DEFINE=	DOCS LIBEVENT_STATIC BACKSPACE

LIBEVENT_STATIC_DESC=	 Build with static libevent
BACKSPACE_DESC=		 Build with tty/keys patch
BACKSPACE_EXTRA_PATCHES= ${PATCHDIR}/extra-patch-tty-keys.c

LIBEVENT_STATIC_LIB_DEPENDS_OFF= libevent.so:devel/libevent2
LIBEVENT_STATIC_BUILD_DEPENDS=	 ${LOCALBASE}/lib/libevent.a:devel/libevent2
LIBEVENT_STATIC_VARS=		 LESTATIC=${LOCALBASE}/lib/libevent.a

post-patch:
	@${REINPLACE_CMD} -e 's|/etc/tmux.conf|${PREFIX}/etc/tmux.conf|g' \
		${WRKSRC}/tmux.1 ${WRKSRC}/tmux.h ${WRKSRC}/CHANGES
	${REINPLACE_CMD} -e '/LIBS=/s/-lrt//' ${WRKSRC}/${CONFIGURE_SCRIPT}

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/tmux ${STAGEDIR}${PREFIX}/bin
	${INSTALL_MAN} ${WRKSRC}/tmux.1 ${STAGEDIR}${MANPREFIX}/man/man1
	@${MKDIR} ${STAGEDIR}${DOCSDIR}
	(cd ${WRKSRC} && ${INSTALL_DATA} ${PORTDOCS} ${STAGEDIR}${DOCSDIR})
# NAS4Free install
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tmux ${NAS4FREE_ROOTFS}/bin

.include <bsd.port.mk>