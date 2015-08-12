PORTNAME=		openssh-portable
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		security
COMMENT=		The portable version of OpenBSD's OpenSSH
MAINTAINER=		zoon01@nas4free.org

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build
DISABLE_VULNERABILITIES=yes

BUILD_DEPENDS=	${NONEXISTENT}:${PORTSDIR}/security/openssh-portable

do-install:
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work/openssh-*/sshd ${NAS4FREE_ROOTFS}/usr/sbin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work/openssh-*/ssh ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work/openssh-*/sftp-server ${NAS4FREE_ROOTFS}/usr/libexec
	@${CP} -pv ${PORTSDIR}/security/openssh-portable/work/openssh-*/moduli ${NAS4FREE_ROOTFS}/etc/ssh

# Create link to moduli file to prevent log entry:
# WARNING: /usr/local/etc/ssh/moduli does not exist, using fixed modulus
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/etc/ssh
	@${LN} -fsv /etc/ssh/moduli ${NAS4FREE_ROOTFS}/usr/local/etc/ssh/moduli

.include <bsd.port.mk>
