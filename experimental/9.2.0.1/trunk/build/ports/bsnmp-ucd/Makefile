PORTNAME=	bsnmp-ucd
PORTVERSION=	0.4.0
CATEGORIES=	net-mgmt
COMMENT=	A bsnmpd module that implements parts of UCD-SNMP-MIB
MAINTAINER=	zoon01@nas4free.org

MASTER_SITES=	${MASTER_SITE_GOOGLE_CODE}

NO_STAGE=       yes
USE_MAKE=	yes
USE_AUTOTOOLS=	libtool:env

post-patch:
	@${REINPLACE_CMD} -e '32,32 s/^.*/#include <sys\/queue.h>/' ${WRKSRC}/snmp_ucd.h
	@${REINPLACE_CMD} -e "s/LOCALBASE/PREFIX/g" ${WRKSRC}/Makefile

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/snmp_ucd.so.* ${NAS4FREE_ROOTFS}/usr/local/lib
	${LN} -sfv /usr/local/lib/snmp_ucd.so.1 ${NAS4FREE_ROOTFS}/usr/local/lib/snmp_ucd.so

	@${INSTALL_DATA} ${WRKSRC}/UCD-SNMP-MIB.txt ${NAS4FREE_ROOTFS}/usr/share/snmp/mibs

.include <bsd.port.mk>
