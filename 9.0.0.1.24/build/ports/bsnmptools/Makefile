PORTNAME=			bsnmptools
PORTVERSION=	0.0.20060818
DISTVERSION=	20060818-01
PORTREVISION=	2
CATEGORIES=		net-mgmt
COMMENT=			SNMP client tools
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://www.ringlet.net/~shteryana/snmp/ CENKES

EXTRACT_SUFX=	.tar
USE_LDCONFIG=	yes
WRKSRC=				${WRKDIR}/${PORTNAME}
MAKE_ARGS=		SHLIB_MAJOR=0 INCLUDEDIR=${PREFIX}/include LIBDIR=${PREFIX}/lib \
							BINDIR=${PREFIX}/bin NO_PROFILE=yes
CFLAGS=				-I${WRKSRC}/lib -L${WRKSRC}/lib

post-extract:
	@${CHMOD} -R u+w ${WRKSRC}
	@${MV} ${WRKSRC}/lib/libbsnmptools/libbsnmptools ${WRKSRC}/tlib
	@${MV} ${WRKSRC}/usr.sbin/bsnmpd/tools ${WRKSRC}/
	@${RM} -rf ${WRKSRC}/lib ${WRKSRC}/usr.sbin ${WRKSRC}/tools/Makefile.inc
	@${MV} ${WRKSRC}/tlib ${WRKSRC}/lib

post-patch:
	@${REINPLACE_CMD} -e 's/usr.sbin/tools/' ${WRKSRC}/Makefile

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/lib/libbsnmptools.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib
	${LN} -sfv /usr/lib/libbsnmptools.so.0 ${NAS4FREE_ROOTFS}/${PREFIX}/lib/libbsnmptools.so

	@${INSTALL_PROGRAM} -v ${WRKSRC}/tools/bsnmpget/bsnmpget ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tools/bsnmpset/bsnmpset ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tools/bsnmpwalk/bsnmpwalk ${NAS4FREE_ROOTFS}/${PREFIX}/bin

.include <bsd.port.mk>
