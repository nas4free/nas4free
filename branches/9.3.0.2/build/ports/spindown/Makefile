PORTNAME=		spindown
PORTVERSION=		0.4
PORTREVISION=		0
CATEGORIES=		sysutils

COMMENT=		SCSI/firewire harddrive spindown daemon
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/sysutils/spindown
DEP_STAGEDIR=		${PORTSDIR}/sysutils/spindown/work/stage

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/spindown ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>
