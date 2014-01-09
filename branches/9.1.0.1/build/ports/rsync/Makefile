PORTNAME=		rsync
PORTVERSION=	3.1.0
CATEGORIES=		net
COMMENT=		A network file distribution/sync utility
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		http://rsync.samba.org/ftp/%SUBDIR%/ \
							ftp://ftp.samba.org/pub/%SUBDIR%/ \
							ftp://ftp.sunet.se/pub/unix/admin/%SUBDIR%/  \
							ftp://ftp.fu-berlin.de/pub/unix/network/%SUBDIR%/ \
							http://www.mirrorservice.org/sites/rsync.samba.org/ \
							CRITICAL
MASTER_SITE_SUBDIR=	rsync
DISTFILES=		${DISTNAME}${EXTRACT_SUFX}
DISTFILES+=		${PORTNAME}-patches-${PORTVERSION}${EXTRACT_SUFX}

GNU_CONFIGURE=	yes
CONFIGURE_ARGS+=		--disable-debug \
							--enable-ipv6 \
							--with-rsyncd-conf=/var/etc/rsyncd.conf \
							--with-included-popt \
							--enable-largefile

# Enable iconv support.
USE_ICONV=	yes
CFLAGS+=		-I${LOCALBASE}/include
LDFLAGS+=		-L${LOCALBASE}/lib
CONFIGURE_ENV=	CFLAGS="${CFLAGS}" \
								LDFLAGS="${LDFLAGS}"

# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly).
FORCE_PKG_REGISTER=	yes

.include <bsd.port.pre.mk>

# guard against CFLAGS damage by MAKE_ENV
post-patch:
	@${REINPLACE_CMD} -e 's|CFLAGS|CONFIGUREDCFLAGS|' \
		-e 's|perl.*|${DO_NADA}|' \
			${WRKSRC}/Makefile.in


pre-configure:
	@${REINPLACE_CMD} -e 's:/etc/:${PREFIX}/etc/:g'  \
		${WRKSRC}/rsync.h ${WRKSRC}/rsync.1 ${WRKSRC}/rsyncd.conf.5
	@${REINPLACE_CMD} -e 's|malloc.h|stdlib.h|g' \
		${WRKSRC}/rsync.h ${WRKSRC}/zlib/zutil.h

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/rsync ${NAS4FREE_ROOTFS}/usr/local/bin
# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly). 
	@${INSTALL_PROGRAM} -v ${WRKSRC}/rsync ${PREFIX}/bin

.include <bsd.port.post.mk>
