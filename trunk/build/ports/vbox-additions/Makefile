PORTNAME=		freenas-virtualbox-ose-additions
PORTVERSION=	1.0
#PORTREVISION= 0
CATEGORIES=		emulators
MAINTAINER=		aoyama@peach.ne.jp
COMMENT=		VirtualBox additions for FreeBSD guests

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build

BUILD_DEPENDS=	${NONEXISTENT}:${PORTSDIR}/emulators/virtualbox-ose-additions:build

BLDSRC=${PORTSDIR}/emulators/virtualbox-ose-additions/work/VirtualBox-*/
WRKSRC=${WRKDIR}/virtualbox-ose-additions

.include <../freenas.mk>
.if ${FREENAS_ARCH} == amd64
ARCH_DIR=	freebsd.amd64
.else
ARCH_DIR=	freebsd.x86
.endif

do-install:
	@${INSTALL_DATA} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/src/vboxguest.ko ${NAS4FREE_ROOTFS}/boot/modules
	@${INSTALL_PROGRAM} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/VBoxControl ${NAS4FREE_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${BLDSRC}/out/${ARCH_DIR}/release/bin/additions/VBoxService ${NAS4FREE_ROOTFS}/${PREFIX}/sbin

.include <bsd.port.mk>
