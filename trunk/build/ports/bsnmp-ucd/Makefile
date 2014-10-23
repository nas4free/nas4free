PORTNAME=		bsnmp-ucd
PORTVERSION=	0.4.1
CATEGORIES=		net-mgmt
COMMENT=			A bsnmpd module that implements parts of UCD-SNMP-MIB
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	${MASTER_SITE_GOOGLE_CODE}

USE_MAKE=		yes
USES=           uidfix
MAKE_ENV+=      NO_OBJ=yes

post-patch:
	@${REINPLACE_CMD} -e '32,32 s/^.*/#include <sys\/queue.h>/' ${WRKSRC}/snmp_ucd.h

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/snmp_ucd.so.* ${NAS4FREE_ROOTFS}/usr/lib
	${LN} -sfv /usr/lib/snmp_ucd.so.1 ${NAS4FREE_ROOTFS}/usr/lib/snmp_ucd.so

	@${INSTALL_DATA} ${WRKSRC}/UCD-SNMP-MIB.txt ${NAS4FREE_ROOTFS}/usr/share/snmp/mibs

.include <bsd.port.mk>