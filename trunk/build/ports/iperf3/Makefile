PORTNAME=		iperf
PORTVERSION=	3.0.9
CATEGORIES=		benchmarks ipv6
COMMENT=		Improved tool to measure TCP and UDP bandwidth
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=			SF/${PORTNAME}

GNU_CONFIGURE=	yes
USES=		libtool
USE_LDCONFIG=	yes

CONFIGURE_ARGS+=	--enable-ipv6 \
									--enable-threads \
									--enable-multicast
CONFIGURE_ENV+=		CFLAGS=${PTHREAD_LIBS}

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/iperf3 ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/libiperf.so.0 ${NAS4FREE_ROOTFS}/${PREFIX}/lib

.include <bsd.port.mk>
