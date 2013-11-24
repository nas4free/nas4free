PORTNAME=		scponly
PORTVERSION=	4.8.20110526
PORTREVISION= 0
CATEGORIES=		shells security
COMMENT=		A tiny shell that only permits scp and sftp
MAINTAINER= 		zoon01@nas4free.org

MASTER_SITE_SUBDIR=	${PORTNAME}/${PORTNAME}-snapshots
DISTNAME=	${PORTNAME}-20110526

EXTRACT_SUFX=		.tgz
GNU_CONFIGURE=	yes

CONFIGURE_ARGS+=	--enable-passwd-compat

# define if you want to enable rsync compatibility.
BUILD_DEPENDS+=		rsync:${.CURDIR}/../rsync
RUN_DEPENDS+=			${BUILD_DEPENDS}
CONFIGURE_ARGS+=	--enable-rsync-compat

# define if you want to enable unison compatibility.
BUILD_DEPENDS+=		unison:${.CURDIR}/../unison
RUN_DEPENDS+=			${BUILD_DEPENDS}
CONFIGURE_ARGS+=	--enable-unison-compat

# define if you want to enable vanilla scp compatibility.
CONFIGURE_ARGS+=	--enable-scp-compat

# define if you want to enable WinSCP compatibility.
CONFIGURE_ARGS+=	--enable-winscp-compat

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/scponly ${NAS4FREE_ROOTFS}/usr/local/bin

.include <bsd.port.mk>
