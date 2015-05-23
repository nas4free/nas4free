# Small replacement for FreeBSD 'host' command.
PORTNAME=		host
PORTVERSION=	991529
CATEGORIES=		dns
COMMENT=		An utility to query DNS servers
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://www.dtek.chalmers.se/~d3august/xt/
DISTNAME=			${PORTNAME}

EXTRACT_SUFX=	.tar.Z
NO_WRKSUBDIR=	yes
NO_STAGE=       yes

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME} ${NAS4FREE_ROOTFS}/usr/bin

.include <bsd.port.mk>
