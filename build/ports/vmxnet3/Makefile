PORTNAME=		vmxnet3
PORTVERSION=		1.0
CATEGORIES=		emulators kld
COMMENT=		vmxnet3 driver (ESXi version)
MAINTAINER=		aoyama@peach.ne.jp
DISTFILES=

NO_STAGE=       yes

WRKSRC=${WRKDIR}/vmxnet3-only

do-extract:
	@${RM} -rf ${WRKDIR}
	@${MKDIR} ${WRKDIR}
	@${TAR} -xf ${FILESDIR}/vmxnet3.tar -C ${WRKDIR}

do-build:
	@${MAKE} -C ${WRKSRC}

do-install:
	@${INSTALL_KLD} -v ${WRKDIR}/vmxnet3.ko ${NAS4FREE_ROOTFS}/boot/kernel/

.include <bsd.port.mk>
