PORTNAME=		xmd
PORTVERSION=		0.3.6
CATEGORIES=		sysutils
COMMENT=		Experimental memory disk by Daisuke Aoyama (aoyama@peach.ne.jp)

MAINTAINER=		aoyama@nas4free.org
MASTER_SITES=		http://www.peach.ne.jp/archives/nas4free/xmd/

NO_BUILD=		yes
NO_PKG_REGISTER=	yes
WRKSRC=                 ${WRKDIR}/${DISTNAME}
USES=			kmod

.include <bsd.port.pre.mk>
.if ${ARCH} == "amd64"
BASEDIR=x64
.elif ${ARCH} == "i386"
BASEDIR=x86
.else
IGNORE=unsupported on ${ARCH}
.endif

do-install:
	@${MKDIR} -p ${NAS4FREE_BOOTDIR}/kernel
	@${INSTALL_KLD} -v ${WRKSRC}/${BASEDIR}/geom_xmd.ko ${NAS4FREE_BOOTDIR}/kernel
	@${INSTALL_KLD} -v ${WRKSRC}/${BASEDIR}/geom_xmd.ko ${NAS4FREE_ROOTFS}/boot/kernel
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${BASEDIR}/xmdconfig ${NAS4FREE_ROOTFS}/sbin

.include <bsd.port.post.mk>
