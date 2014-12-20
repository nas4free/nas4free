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
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=	${NONEXISTENT}:${PORTSDIR}/sysutils/fusefs-ntfs

.include <bsd.port.pre.mk>
.if ${OSVERSION} < 1000000
BUILD_DEPENDS+=	${NONEXISTENT}:${PORTSDIR}/sysutils/fusefs-kmod
.endif

BUILD_DEPENDS+=	${NONEXISTENT}:${PORTSDIR}/sysutils/fusefs-libs \
		${NONEXISTENT}:${PORTSDIR}/devel/libublio

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/fusefs.in ${NAS4FREE_ROOTFS}/etc/rc.d/fusefs

#	mount_fusefs is in basesystem, copy from there
#	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/fuse4bsd*/mount_fusefs/mount_fusefs ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/src/.libs/ntfs-3g ${NAS4FREE_ROOTFS}/usr/local/bin

.if ${OSVERSION} < 1000000
#	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/fuse4bsd*/fuse_module/fuse.ko ${NAS4FREE_ROOTFS}/boot/kernel
	@${INSTALL_KLD} -v ${PORTSDIR}/sysutils/fusefs-kmod/work/modules/fuse/fuse.ko ${NAS4FREE_ROOTFS}/boot/kernel
.else
	@${CP} -pv /boot/kernel/fuse.ko ${NAS4FREE_ROOTFS}/boot/kernel
.endif
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-ntfs/work/ntfs-3g*/libntfs-3g/.libs/libntfs-3g.so.85 ${NAS4FREE_ROOTFS}/usr/local/lib
#	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-libs/work/fuse-*/lib/.libs/lib*.so* ${NAS4FREE_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/sysutils/fusefs-libs/work/fuse-*/lib/.libs/lib*.so ${NAS4FREE_ROOTFS}/usr/local/lib
#	@${INSTALL_PROGRAM} -v ${PORTSDIR}/devel/libublio/work/libublio-*/libubl*.so* ${NAS4FREE_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/devel/libublio/work/libublio-*/libubl*.so ${NAS4FREE_ROOTFS}/usr/local/lib

	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${NAS4FREE_ROOTFS}/sbin/mount_ntfs
	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${NAS4FREE_ROOTFS}/sbin/mount_ntfs-3g

.include <bsd.port.post.mk>
