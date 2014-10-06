PORTNAME=			fusefs-ntfs
PORTVERSION=	1.0
PORTREVISION=	0
PORTEPOCH=		8
CATEGORIES=		sysutils
COMMENT=			Mount NTFS partitions and disk images
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
NO_STAGE=       yes


BUILD_DEPENDS=	fusefs-ntfs:${PORTSDIR}/sysutils/fusefs-ntfs \
								fusefs-kmod:${PORTSDIR}/sysutils/fusefs-kmod \
								fusefs-libs:${PORTSDIR}/sysutils/fusefs-libs

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/fusefs.in ${NAS4FREE_ROOTFS}/etc/rc.d/fusefs

	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/fuse4bsd*/mount_fusefs/mount_fusefs ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/src/.libs/ntfs-3g ${NAS4FREE_ROOTFS}/usr/local/bin

	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/fuse4bsd*/fuse_module/fuse.ko ${NAS4FREE_ROOTFS}/boot/kernel
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/libntfs-3g/.libs/libntfs-3g.so.* ${NAS4FREE_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-libs/work/fuse-*/lib/.libs/libfuse.so.* ${NAS4FREE_ROOTFS}/usr/local/lib

	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${NAS4FREE_ROOTFS}/sbin/mount_ntfs
	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${NAS4FREE_ROOTFS}/sbin/mount_ntfs-3g

.include <bsd.port.mk>
