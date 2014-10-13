PORTNAME=		gzip
PORTVERSION=	1.6
CATEGORIES=		archivers
COMMENT=		A compression utility designed to be a replacement for compress
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=				${MASTER_SITE_GNU}
MASTER_SITE_SUBDIR=	${PORTNAME}

GNU_CONFIGURE=	yes

EXTRA_PATCHES=	${FILESDIR}/extra-patch-gzip-rsyncable.diff

post-configure:
	@${REINPLACE_CMD} -e 's|^SHELL =.*|SHELL=/bin/sh|' ${WRKSRC}/Makefile

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/gzip ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/gunzip ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/zcat ${NAS4FREE_ROOTFS}/usr/bin

.include <bsd.port.mk>