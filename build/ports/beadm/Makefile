PORTNAME=	beadm
PORTVERSION=	1.2.6
PORTREVISION=	0
CATEGORIES=	sysutils
MASTER_SITES=	GH \
		http://mirror.shatow.net/freebsd/${PORTNAME}/ \
		LOCAL/bdrewery/${PORTNAME}

MAINTAINER=	zoon1@nas4free.org
COMMENT=	Solaris-like utility to manage Boot Environments on ZFS

USE_GITHUB=	yes
GH_ACCOUNT=	vermaden

NO_BUILD=	YES

CONFLICTS_INSTALL=	beadm-devel-*

do-install:
# NAS4Free install
	${INSTALL_SCRIPT} -v ${WRKSRC}/beadm ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>