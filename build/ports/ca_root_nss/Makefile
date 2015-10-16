PORTNAME=		ca_root_nss
PORTVERSION=		${VERSION_NSS}
PORTREVISION=		1
CATEGORIES=		security

COMMENT=		The root certificate bundle from the Mozilla Project
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/security/ca_root_nss
DEP_STAGEDIR=		${PORTSDIR}/security/ca_root_nss/work/stage

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${MKDIR} -p ${NAS4FREE_ROOTFS}/usr/local/etc/ssl
	@${INSTALL_DATA} -v ${DEP_STAGEDIR}${PREFIX}/etc/ssl/cert.pem.sample ${NAS4FREE_ROOTFS}/usr/local/etc/ssl/cert.pem
	@${LN} -fsv ${PREFIX}/etc/ssl/cert.pem ${NAS4FREE_ROOTFS}/etc/ssl/cert.pem

.include <bsd.port.mk>
