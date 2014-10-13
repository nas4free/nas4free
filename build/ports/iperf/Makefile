PORTNAME=		iperf
PORTVERSION=	2.0.5
CATEGORIES=		benchmarks ipv6
COMMENT=		A tool to measure maximum TCP and UDP bandwidth
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=			SF/${PORTNAME}/${PORTNAME}/${PORTVERSION}%20source

GNU_CONFIGURE=	yes
CONFIGURE_ARGS+=	--enable-ipv6 \
									--enable-threads \
									--enable-multicast
CONFIGURE_ENV+=		CFLAGS=${PTHREAD_LIBS}

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/${PORTNAME} ${NAS4FREE_ROOTFS}/${PREFIX}/bin

.include <bsd.port.mk>