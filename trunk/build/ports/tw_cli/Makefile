PORTNAME=		tw_cli
PORTVERSION=	 9.5.5
PORTREVISION?= 0
CATEGORIES=		sysutils
COMMENT=		3ware storage controllers management CLI
MAINTAINER=		redantz@nas4free.org

MASTER_SITES=		http://www.lsi.com/downloads/Public/SATA/SATA%20Common%20Files/${DISTNAME}${EXTRACT_SUFX}?source=/
DISTNAME=		CLI_freebsd-from_the_10.2.2.1_9.5.5.1_codesets

EXTRACT_SUFX:=	.zip


RESTRICTED=	Redistribution forbidden by license
# i386 binary should work on ia64, but not tested
ONLY_FOR_ARCHS=	i386 amd64 ia64

USE_ZIP=	yes
WRKSRC=	${WRKDIR}
NO_BUILD=	yes
BINSUFFIX=	${ARCH:S,i386,x86,:S,amd64,x86_64,:S,ia64,x86,}
NO_PKG_REGISTER=yes

.include <bsd.port.options.mk>

do-install:
	${INSTALL_PROGRAM} -v ${WRKSRC}/${BINSUFFIX}/tw_cli ${NAS4FREE_ROOTFS}/${PREFIX}/sbin/

.include <bsd.port.mk>