PORTNAME=	php71-APCu
PORTREVISION=	0
CATEGORIES=	devel

MAINTAINER=	zoon1@nas4free.org
COMMENT=	APC User Caching

DISTFILES?=
NO_BUILD?=	         yes
NO_PKG_REGISTER?=	 yes
DEPENDS_TARGET=	clean build stage

DEP_STAGEDIR=	${PORTSDIR}/devel/php71-APCu/work/stage

BUILD_DEPENDS=	${NONEXISTENT}:devel/php71-APCu

do-install:
# NAS4Free install
	@${CP} -pv ${FILESDIR}/apcu.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc/php
	@${MKDIR} ${NAS4FREE_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20160303
	${INSTALL_DATA} ${DEP_STAGEDIR}/usr/local/lib/php/20160303/apcu.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/php/extensions/no-debug-non-zts-20160303

.include <bsd.port.mk>
