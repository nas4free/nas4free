PORTNAME=       noVNC
PORTVERSION=    20150924
PORTREVISION=   0
CATEGORIES=     net

MAINTAINER=     aoyama@nas4free.org
COMMENT=        HTML5 VNC Client

LICENSE=        MPL

USE_GITHUB=	yes
GH_ACCOUNT=	kanaka
GH_TAGNAME=	a0e7ab4

NO_BUILD=		yes
NO_PKG_REGISTER=	yes

WWWDIR=${PREFIX}/www/novnc

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${WWWDIR}
	@${CP} -Rpv ${WRKSRC}/images ${NAS4FREE_ROOTFS}${WWWDIR}
	@${CP} -Rpv ${WRKSRC}/include ${NAS4FREE_ROOTFS}${WWWDIR}
	@${CP} -pv ${WRKSRC}/vnc.html ${NAS4FREE_ROOTFS}${WWWDIR}
	@${CP} -pv ${WRKSRC}/vnc_auto.html ${NAS4FREE_ROOTFS}${WWWDIR}

.include <bsd.port.mk>
