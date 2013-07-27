PORTNAME=	dmidecode
PORTVERSION=	2.11
CATEGORIES=	sysutils
MASTER_SITES=	${MASTER_SITE_SAVANNAH} \
		ftp://ftp.nuug.no/pub/anders/distfiles/
MASTER_SITE_SUBDIR=	${PORTNAME}

MAINTAINER=	aoyama@nas4free.org
COMMENT=	A tool for dumping DMI (SMBIOS) contents in human-readable format

USE_BZIP2=	yes
LICENSE=	GPLv2

.include <bsd.port.pre.mk>

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/dmidecode ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/biosdecode ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ownership ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/vpddecode ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.post.mk>
