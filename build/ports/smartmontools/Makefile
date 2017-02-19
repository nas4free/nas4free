PORTNAME=		smartmontools
PORTVERSION=	6.5
PORTREVISION?=	2
CATEGORIES=		sysutils
COMMENT=		S.M.A.R.T. disk monitoring tools
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=		SF

CONFLICTS=		smartmontools-devel-[0-9]*

USE_GMAKE=		yes
NO_PKG_REGISTER=yes

GNU_CONFIGURE=	yes
CONFIGURE_ARGS=	--docdir=${DOCSDIR} --disable-sample \
			--with-nvme-devicescan=yes \
			--disable-dependency-tracking


CFLAGS:=		${CFLAGS:S/-O2/-O/} -Wno-write-strings


do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartctl ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${CP} -pv ${WRKSRC}/smartd_warning.sh ${NAS4FREE_ROOTFS}/${PREFIX}/etc

.include <bsd.port.mk>