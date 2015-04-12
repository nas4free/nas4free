PORTNAME=	dmidecode
PORTVERSION=	2.12
CATEGORIES=	sysutils
MASTER_SITES=	SAVANNAH

MAINTAINER=	zoon01@nas4free.org
COMMENT=	A tool for dumping DMI (SMBIOS) contents in human-readable format

USES=		tar:bzip2
LICENSE=	GPLv2


.include <bsd.port.pre.mk>

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/dmidecode ${NAS4FREE_ROOTFS}/usr/local/sbin
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
	@${INSTALL_PROGRAM} -v ${WRKSRC}/biosdecode ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ownership ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/vpddecode ${NAS4FREE_ROOTFS}/usr/local/sbin
.endif

.include <bsd.port.post.mk>
