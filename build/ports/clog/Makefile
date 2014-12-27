PORTNAME=		clog
PORTVERSION=	1.0
CATEGORIES=		sysutils
COMMENT=		Circular log file tool
MAINTAINER=		aoyama@peach.ne.jp

DISTFILES?=

NO_PKG_REGISTER=yes

.include <bsd.port.pre.mk>
do-build:
.if ${OSVERSION} >= 1100000
	@${TAR} -zxvf ${FILESDIR}/syslogd-clog-11.tar.gz -C /usr/src/usr.bin
	@${TAR} -zxvf ${FILESDIR}/clog-1.0.1-11.tar.gz -C /usr/src/usr.bin
.else
	@${TAR} -zxvf ${FILESDIR}/syslogd-clog.tar.gz -C /usr/src/usr.bin
	@${TAR} -zxvf ${FILESDIR}/clog-1.0.1.tar.gz -C /usr/src/usr.bin
.endif

	@${MAKE} -C /usr/src/usr.bin/syslogd
	@${MAKE} -C /usr/src/usr.bin/clog

do-install:
	@${INSTALL_PROGRAM} -v /usr/src/usr.bin/syslogd/syslogd ${NAS4FREE_ROOTFS}/usr/sbin
	@${INSTALL_PROGRAM} -v /usr/src/usr.bin/clog/clog ${NAS4FREE_ROOTFS}/usr/sbin

.include <bsd.port.post.mk>
