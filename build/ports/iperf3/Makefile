PORTNAME=		iperf
PORTVERSION=	3.1.6
PORTREVISION?=  0
CATEGORIES=		benchmarks ipv6
COMMENT=		Improved tool to measure TCP and UDP bandwidth
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=		http://downloads.es.net/pub/iperf/
PKGNAMESUFFIX=	3

GNU_CONFIGURE=	yes
USES=			libtool
USE_LDCONFIG=		yes

.include <bsd.port.options.mk>

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/iperf3 ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/libiperf.so.0 ${NAS4FREE_ROOTFS}/${PREFIX}/lib

.include <bsd.port.mk>
