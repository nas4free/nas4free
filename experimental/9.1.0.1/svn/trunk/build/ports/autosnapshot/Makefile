PORTNAME=		autosnapshot
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Auto Snapshot tool
MAINTAINER=		aoyama@peach.ne.jp

DISTFILES=
NO_BUILD=	yes
NO_WRKSUBDIR=	yes

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot ${NAS4FREE_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot.in ${NAS4FREE_ROOTFS}/etc/rc.d/autosnapshot

.include <bsd.port.mk>
