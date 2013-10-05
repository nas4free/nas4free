PORTNAME=		iscontrol
PORTVERSION=	1.0
CATEGORIES=		sysutils
COMMENT=		login/negotiator/control for an iSCSI initiator session
MAINTAINER=		aoyama@peach.ne.jp
DISTFILES=
SRCDIR=/usr/src/sbin/iscontrol
NO_STAGE=       yes

do-build:
	@(cd ${SRCDIR}; make clean obj; make)

do-install:
	@${INSTALL_PROGRAM} -v /usr/obj${SRCDIR}/iscontrol ${NAS4FREE_ROOTFS}/sbin

.include <bsd.port.mk>
