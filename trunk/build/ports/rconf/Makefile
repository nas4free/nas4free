PORTNAME=		rconf
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Command-line tool to manage content of rc.conf
MAINTAINER=		zoon01@nas4free.org

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes

NO_WRKSUBDIR=	yes

NO_STAGE=       yes

do-install:
	${INSTALL_SCRIPT} ${FILESDIR}/${PORTNAME} ${NAS4FREE_ROOTFS}/usr/local/sbin/

.include <bsd.port.mk>
