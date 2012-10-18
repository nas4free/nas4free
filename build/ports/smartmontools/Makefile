PORTNAME=		smartmontools
PORTVERSION=	6.0
CATEGORIES=		sysutils
COMMENT=		S.M.A.R.T. disk monitoring tools
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	SF

CONFLICTS=		smartmontools-devel-[0-9]*

USE_GMAKE=		yes
# hack to prevent gmake failure on 6.xx
GMAKE=		make
GNU_CONFIGURE=	yes
CONFIGURE_ARGS=	--with-docdir=${DOCSDIR} --disable-sample \
		--disable-dependency-tracking --disable-drivedb


CFLAGS:=	${CFLAGS:S/-O2/-O/} -Wno-write-strings

post-patch:
	@${REINPLACE_CMD} -e 's| install-initdDATA | |' ${WRKSRC}/Makefile.in

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartctl ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/smartd ${NAS4FREE_ROOTFS}/usr/local/sbin

.include <bsd.port.mk>
