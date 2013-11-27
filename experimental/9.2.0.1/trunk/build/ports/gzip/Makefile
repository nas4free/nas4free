PORTNAME=		gzip
PORTVERSION=		1.6
CATEGORIES=		archivers
COMMENT=		A compression utility designed to be a replacement for compress
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		${MASTER_SITE_GNU}
MASTER_SITE_SUBDIR=	${PORTNAME}

NO_STAGE=       	yes
GNU_CONFIGURE=		yes
OPTIONS_DEFINE= 	RSYNCABLE
OPTIONS_DEFAULT=        RSYNCABLE
RSYNCABLE_DESC= Include --rsyncable patch

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MRSYNCABLE}
EXTRA_PATCHES=  ${FILESDIR}/extra-patch-gzip-rsyncable.diff
.endif

post-configure:
	@${REINPLACE_CMD} -e 's|^SHELL =.*|SHELL=/bin/sh|' ${WRKSRC}/Makefile

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/gzip ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/gunzip ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/zcat ${NAS4FREE_ROOTFS}/usr/bin

.include <bsd.port.mk>
