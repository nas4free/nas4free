PORTNAME=	php70-intl
PORTREVISION=	1
CATEGORIES=	devel

MAINTAINER=	zoon1@nas4free.org
COMMENT=	PHP extension to support Internationalization(ICU)

DISTFILES?=
NO_BUILD?=	           yes
NO_PKG_REGISTER?=	   yes
DEPENDS_TARGET=	clean build stage
DISABLE_VULNERABILITIES=   yes

DEP_STAGEDIR=	${PORTSDIR}/devel/php70-intl/work/stage

BUILD_DEPENDS=	${NONEXISTENT}:devel/php70-intl

do-install:
# NAS4Free install
	@${CP} -pv ${FILESDIR}/ext-20-intl.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc/php
	@${MKDIR} ${NAS4FREE_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20151012
	${INSTALL_DATA} ${DEP_STAGEDIR}/usr/local/lib/php/20151012/intl.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/php/extensions/no-debug-non-zts-20151012

.include <bsd.port.mk>