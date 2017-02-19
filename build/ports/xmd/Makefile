PORTNAME=		xmd
PORTVERSION=		0.5
CATEGORIES=		sysutils
COMMENT=		Memory Disk driver by Daisuke Aoyama (aoyama@peach.ne.jp)

MAINTAINER=		aoyama@nas4free.org
MASTER_SITES=		http://www.peach.ne.jp/archives/nas4free/xmd/

#NO_BUILD=		yes
NO_PKG_REGISTER=	yes
WRKSRC=                 ${WRKDIR}/${DISTNAME}/xmd
USES=			kmod

.include <bsd.port.pre.mk>
.if ${ARCH} == "amd64"
BASEDIR=x64
.elif ${ARCH} == "i386"
BASEDIR=x86
.elif ${ARCH} == "armv6"
BASEDIR=armv6
.else
IGNORE=unsupported on ${ARCH}
.endif

post-build:
	cd ${WRKSRC}/../xmdconfig && ${SETENV} ${MAKE_ENV} ${MAKE} ${MAKE_ARGS}
	cd ${WRKSRC}/../uncloop && ${SETENV} ${MAKE_ENV} ${MAKE} ${MAKE_ARGS}

do-install:
	@${MKDIR} -p ${NAS4FREE_BOOTDIR}/kernel
	@${INSTALL_KLD} -v ${WRKSRC}/geom_xmd.ko ${NAS4FREE_BOOTDIR}/kernel
	@${INSTALL_KLD} -v ${WRKSRC}/geom_xmd.ko ${NAS4FREE_ROOTFS}/boot/kernel
	@${INSTALL_PROGRAM} -v ${WRKSRC}/../xmdconfig/xmdconfig ${NAS4FREE_ROOTFS}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/../uncloop/uncloop ${NAS4FREE_ROOTFS}/usr/bin

.include <bsd.port.post.mk>
