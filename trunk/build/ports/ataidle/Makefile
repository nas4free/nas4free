PORTNAME=		ataidle
PORTVERSION=	2.7.2
CATEGORIES=		sysutils
COMMENT=		Utility to set spindown timeout for ATA drives
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		http://www.cran.org.uk/bruce/software/

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${NAS4FREE_ROOTFS}/etc/rc.d/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ataidle ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>
