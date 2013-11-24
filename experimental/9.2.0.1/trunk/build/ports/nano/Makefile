PORTNAME=		nano
PORTVERSION=	2.2.6
CATEGORIES=		editors
COMMENT=		Nano's ANOther editor, an enhanced free Pico clone
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://www.nano-editor.org/dist/v2.2/ \
						${MASTER_SITE_GNU}
MASTER_SITE_SUBDIR=	${PORTNAME}

GNU_CONFIGURE=	yes
USE_GETTEXT=    	yes
CONFIGURE_ARGS=	--enable-all
CONFIGURE_ENV=	CPPFLAGS="-I${LOCALBASE}/include" \
							LDFLAGS="-L${LOCALBASE}/lib"

.include <bsd.port.pre.mk>

CONFIGURE_ARGS+=--enable-utf8

do-install:
	${INSTALL_PROGRAM} -v ${WRKSRC}/src/nano ${NAS4FREE_ROOTFS}/usr/local/bin;

.include <bsd.port.post.mk>
