PORTNAME=		ca_root_nss
PORTVERSION=		1.0
PORTREVISION=		0
CATEGORIES=		security

COMMENT=		The root certificate bundle from the Mozilla Project
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		ca_root_nss:${PORTSDIR}/security/ca_root_nss
DEP_STAGEDIR=		${PORTSDIR}/security/ca_root_nss/work/stage

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${MKDIR} -p ${NAS4FREE_ROOTFS}/usr/local/etc/ssl
	@${INSTALL_DATA} -v ${DEP_STAGEDIR}${PREFIX}/etc/ssl/cert.pem ${NAS4FREE_ROOTFS}/usr/local/etc/ssl

.include <bsd.port.mk>
