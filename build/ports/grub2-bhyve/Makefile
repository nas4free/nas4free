PORTNAME=		grub2-bhyve
PORTVERSION=		1.0
PORTREVISION=		1
CATEGORIES=		sysutils

COMMENT=		Grub-emu loader for bhyve
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:sysutils/grub2-bhyve
DEP_STAGEDIR=		${PORTSDIR}/sysutils/grub2-bhyve/work/stage

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/grub-bhyve ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>
