PORTNAME=		nano
PORTVERSION=	2.7.4
PORTREVISION?=  0
CATEGORIES=		editors
COMMENT=		Nano's ANOther editor, an enhanced free Pico clone
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=	http://www.nano-editor.org/dist/v${PORTVERSION:R}/ GNU

LICENSE=	GPLv3
CONFLICTS=	nano-devel-2*

USES=		ncurses
GNU_CONFIGURE=	yes

CONFIGURE_ARGS=	--docdir=${DOCSDIR} --enable-utf8
MAKE_ARGS+=	install_htmlmanDATA=install-htmlmanDATA
CPPFLAGS+=	-I${LOCALBASE}/include
LDFLAGS+=	-L${LOCALBASE}/lib

OPTIONS_DEFINE=	DOCS EXAMPLES NLS
OPTIONS_SUB=	yes

NLS_USES=		gettext
NLS_CONFIGURE_ENABLE=	nls

INFO=		nano

PORTSCOUT=	limitw:1,even

post-configure:
	${REINPLACE_CMD} -e 's/update_revision//g' ${WRKSRC}/src/Makefile

post-build:
	cd ${WRKSRC}/doc; ${REINPLACE_CMD} \
		-e 's:SYSCONFDIR:${PREFIX}/etc:g' \
		-e 's:EXAMPLESDIR:${EXAMPLESDIR}:g' \
		nano.1 rnano.1 nanorc.5 ${WRKSRC}/doc/${INFO:=.info}
	${REINPLACE_CMD} -e 's/\\</[[:<:]]/g' -e 's/\\>/[[:>:]]/g' \
		${WRKSRC}/doc/*.nanorc

post-install:
# NAS4Free install
	${INSTALL_PROGRAM} -v ${WRKSRC}/src/nano ${NAS4FREE_ROOTFS}/usr/local/bin;

.include <bsd.port.mk>