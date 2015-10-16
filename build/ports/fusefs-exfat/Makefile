PORTNAME=	exfat
PORTVERSION=	20150924
PORTREVISION=	0
CATEGORIES=	sysutils

MAINTAINER=	aoyama@nas4free.org
COMMENT=	Full-featured exFAT FS implementation as a FUSE module

LICENSE=	GPLv2

LIB_DEPENDS=	libublio.so:${PORTSDIR}/devel/libublio

USE_GITHUB=     yes
GH_ACCOUNT=     relan
GH_TAGNAME=     b7953e2

USES=		autoreconf fuse
GNU_CONFIGURE=	yes
CPPFLAGS+=	-I${LOCALBASE}/include -DUSE_UBLIO
LDFLAGS+=	-L${LOCALBASE}/lib
LIBS+=		-lublio

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/fuse/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin
	${INSTALL_PROGRAM} ${WRKSRC}/fsck/exfatfsck ${STAGEDIR}${PREFIX}/bin
	${LN} -fs ${PREFIX}/bin/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin/mount.exfat

# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/mount.exfat-fuse ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/exfatfsck ${NAS4FREE_ROOTFS}/usr/local/bin
	@${LN} -fsv ${PREFIX}/bin/mount.exfat-fuse ${NAS4FREE_ROOTFS}/usr/local/bin/mount.exfat

.include <bsd.port.mk>
