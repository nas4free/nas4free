PORTNAME=		wol
PORTVERSION=	0.7.1
CATEGORIES=		net
COMMENT=		Tool to wake up Wake-On-LAN compliant computers
MAINTAINER=		aoyama@peach.ne.jp

MASTER_SITES=		SF/ahh/OldFiles

GNU_CONFIGURE=		yes
USE_PERL5_BUILD=	yes
CPPFLAGS+=		-I${LOCALBASE}/include
LDFLAGS+=		-L${LOCALBASE}/lib

CONFIGURE_ARGS=		--disable-nls

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/wol ${NAS4FREE_ROOTFS}/${PREFIX}/bin

.include <bsd.port.mk>
