PORTNAME=		ntfsprogs
PORTVERSION=	1.0
CATEGORIES=		sysutils
COMMENT=		Utilities to manipulaite NTFS filesystems
MAINTAINER=		zoon01@nas4free.org

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build

BUILD_DEPENDS=	ntfsprogs:${PORTSDIR}/sysutils/ntfsprogs

do-install:
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/ntfsprogs/work/ntfsprogs-*/ntfsprogs/.libs/ntfsfix ${NAS4FREE_ROOTFS}/usr/local/bin
	@${CP} -pv ${PORTSDIR}/sysutils/ntfsprogs/work/ntfsprogs-*/libntfs/.libs/libntfs.so.9 ${NAS4FREE_ROOTFS}/usr/local/lib/libntfs.so.9

.include <bsd.port.mk>