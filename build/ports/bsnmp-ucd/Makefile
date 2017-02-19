PORTNAME=		bsnmp-ucd
PORTVERSION=	0.4.2
CATEGORIES=		net-mgmt
COMMENT=			A bsnmpd module that implements parts of UCD-SNMP-MIB
MAINTAINER=		zoon1@nas4free.org

USE_GITHUB=	yes
GH_ACCOUNT=	trociny

USE_MAKE=		yes
USES=           uidfix
MAKE_ENV+=      NO_OBJ=yes

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/snmp_ucd.so.* ${NAS4FREE_ROOTFS}/usr/lib
	${LN} -sfv /usr/lib/snmp_ucd.so.1 ${NAS4FREE_ROOTFS}/usr/lib/snmp_ucd.so

	@${INSTALL_DATA} ${WRKSRC}/UCD-SNMP-MIB.txt ${NAS4FREE_ROOTFS}/usr/share/snmp/mibs

.include <bsd.port.mk>