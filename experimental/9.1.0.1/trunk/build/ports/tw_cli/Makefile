PORTNAME=	tw_cli
PORTVERSION=	9.5.4
CATEGORIES=	sysutils
MASTER_SITES=	ftp://tsupport:tsupport@ftp0.lsil.com/private/3Ware/downloads/
DISTNAME=	cli_freebsd_10.2.1_9.5.4

MAINTAINER=	redantz@nas4free.org
COMMENT=	3ware storage controllers management CLI

RESTRICTED=	Redistribution is not allowed
# i386 binary should work on ia64, but not tested
ONLY_FOR_ARCHS=	i386 amd64 ia64

USE_ZIP=	yes
WRKSRC=		${WRKDIR}
NO_BUILD=	yes
BINSUFFIX=	${ARCH:S,i386,x86,:S,amd64,x86_64,:S,ia64,x86,}

.include <bsd.port.options.mk>

do-install:
	${INSTALL_PROGRAM} -v ${WRKSRC}/${BINSUFFIX}/tw_cli ${NAS4FREE_ROOTFS}/${PREFIX}/sbin/

.include <bsd.port.mk>
