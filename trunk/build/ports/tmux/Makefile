PORTNAME=		tmux
PORTVERSION=	1.9a
PORTREVISION?=  0
CATEGORIES=		sysutils
COMMENT=		Terminal Multiplexer
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		SF/${PORTNAME}/${PORTNAME}/${PORTNAME}-${PORTVERSION:R}

GNU_CONFIGURE=		yes
USE_PKGCONFIG=		build

PLIST_FILES=		bin/tmux \
			etc/bash_completion.d/tmux

PLIST_DIRSTRY=	etc/bash_completion.d

OPTIONS_DEFINE= 	DOCS LIBEVENT2 LIBEVENT_STATIC BACKSPACE
NO_STAGE=       	yes

LIBEVENT2_DESC=		Use libevent version 2
LIBEVENT_STATIC_DESC=	Build with static libevent
BACKSPACE_DESC=		Build with tty/keys patch

BACKSPACE_EXTRA_PATCHES=        ${PATCHDIR}/extra-patch-tty-keys.c
LIBEVENT2_CONFIGURE_ENV=        LIBEVENT_CFLAGS="-I${LOCALBASE}/include/event2/compat" \
				LIBEVENT_LIBS="-L${LOCALBASE}/lib/event2 -levent"
LIBEVENT2_CONFIGURE_ENV_OFF=    LIBEVENT_CFLAGS="-I${LOCALBASE}/include" \
				LIBEVENT_LIBS="-L${LOCALBASE}/lib -levent"
LIBEVENT2_LIB_DEPENDS=          libevent-2.0.so:${PORTSDIR}/devel/libevent2
LIBEVENT2_LIB_DEPENDS_OFF=      libevent-1.4.so:${PORTSDIR}/devel/libevent

.include <bsd.port.options.mk>

post-patch:
	@${REINPLACE_CMD} -e 's|/etc/tmux.conf|${PREFIX}/etc/tmux.conf|g' \
		${WRKSRC}/tmux.1 ${WRKSRC}/tmux.h ${WRKSRC}/CHANGES
	${REINPLACE_CMD} -e '/LIBS=/s/-lrt//' ${WRKSRC}/${CONFIGURE_SCRIPT}

.if ${PORT_OPTIONS:MLIBEVENT_STATIC}
.if ${PORT_OPTIONS:MLIBEVENT2}
LESTATIC=       ${LOCALBASE}/lib/event2/libevent.a
.else
LESTATIC=       ${LOCALBASE}/lib/libevent.a
.endif

post-configure:
	${REINPLACE_CMD} -e '/LIBS/s|-levent[^[:blank:]]*|${LESTATIC}|' \
		${WRKSRC}/${MAKEFILE}
.endif

do-install:
	@${INSTALL_PROGRAM} ${WRKSRC}/tmux ${NAS4FREE_ROOTFS}/bin

post-install:
	${MKDIR} ${NAS4FREE_ROOTFS}/etc/bash_completion.d
	@${INSTALL_DATA} ${WRKSRC}/examples/bash_completion_tmux.sh ${NAS4FREE_ROOTFS}/etc/bash_completion.d/tmux

.include <bsd.port.mk>
