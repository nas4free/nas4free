PORTNAME=		inadyn-mt
PORTVERSION=	02.24.36
CATEGORIES=		dns
COMMENT=		A simple DYNAMIC DNS client
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	SF/${PORTNAME}/${PORTNAME}/${PORTNAME}.v.${PORTVERSION}
DISTNAME=			${PORTNAME}.v.${PORTVERSION}

WRKSRC=				${WRKDIR}/${PORTNAME}.v.${PORTVERSION}
USE_DOS2UNIX=		yes
GNU_CONFIGURE=	yes

CONFIGURE_ARGS+=	--disable-sound

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/inadyn-mt ${NAS4FREE_ROOTFS}/usr/local/bin/inadyn
	@${INSTALL_SCRIPT} -v ${FILESDIR}/inadyn.in ${NAS4FREE_ROOTFS}/etc/rc.d/inadyn

.include <bsd.port.mk>
