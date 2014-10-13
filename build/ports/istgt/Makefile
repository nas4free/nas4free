PORTNAME=		istgt
PORTVERSION=		20121123
CATEGORIES=		net
COMMENT=		Implementation of userland ISCSI target
MAINTAINER=		aoyama@nas4free.org

MASTER_SITES=		http://www.peach.ne.jp/archives/istgt/

CC=clang
GNU_CONFIGURE=	yes
CONFIGURE_ARGS+= --with-logfacility="daemon" --with-logpriority="info"

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/istgt ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/istgtcontrol ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_SCRIPT} -v ${FILESDIR}/iscsi_target.in ${NAS4FREE_ROOTFS}/etc/rc.d/iscsi_target

.include <bsd.port.mk>