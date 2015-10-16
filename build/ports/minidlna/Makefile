PORTNAME=	minidlna
PORTVERSION=	1.1.5
PORTREVISION=	0
PORTEPOCH=	0
CATEGORIES=	net multimedia www
MASTER_SITES=	SF

MAINTAINER=	aoyama@nas4free.org
COMMENT=	Media-server compatible with "Digital Life Network Alliance"

LIB_DEPENDS=	libsqlite3.so:${PORTSDIR}/databases/sqlite3	\
		libexif.so:${PORTSDIR}/graphics/libexif	\
		libid3tag.so:${PORTSDIR}/audio/libid3tag	\
		libogg.so:${PORTSDIR}/audio/libogg		\
		libvorbis.so:${PORTSDIR}/audio/libvorbis	\
		libFLAC.so:${PORTSDIR}/audio/flac		\
		libavformat.so:${PORTSDIR}/multimedia/ffmpeg

GNU_CONFIGURE=	yes
CONFIGURE_ARGS=	--with-db-path=/var/db/minidlna \
		--with-os-url="http://www.FreeBSD.org"
CPPFLAGS+=	-I${LOCALBASE}/include
LIBS+=		-L${LOCALBASE}/lib
USES=		autoreconf gettext-tools iconv jpeg

PKGMESSAGE=	${WRKSRC}/pkg-message
SUB_FILES+=	pkg-message
USE_RC_SUBR=	minidlna
SUB_LIST+=	USER=${USERS}

USERS=	dlna
GROUPS=	dlna

OPTIONS_DEFINE=	DEBUG KQUEUE NLS
OPTIONS_DEFAULT=KQUEUE
OPTIONS_SUB=	yes

DEBUG_CFLAGS=	-DDEBUG
KQUEUE_DESC=	Experimental patch for automatic rescan using kqueue(2)
NLS_USES=	gettext-runtime

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MKQUEUE}
EXTRA_PATCHES+=	${PATCHDIR}/extra-patch-kqueue
.endif

# NAS4Free
NO_PKG_REGISTER=	yes

post-install:
	${INSTALL_MAN} ${WRKSRC}/*.5 ${STAGEDIR}${MANPREFIX}/man/man5/
	${INSTALL_MAN} ${WRKSRC}/*.8 ${STAGEDIR}${MANPREFIX}/man/man8/
	${INSTALL_DATA} ${WRKSRC}/minidlna.conf ${STAGEDIR}${PREFIX}/etc/minidlna.conf.sample

# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/minidlnad ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_SCRIPT} -v ${WRKDIR}/minidlna ${NAS4FREE_ROOTFS}/usr/local/etc/rc.d
	${INSTALL_DATA} ${STAGEDIR}${PREFIX}/etc/minidlna.conf.sample ${NAS4FREE_ROOTFS}/usr/local/etc
	for lang in da de es fr it ja nb nl pl ru sl sv; do \
		${CP} -pv ${STAGEDIR}${PREFIX}/share/locale/$${lang}/LC_MESSAGES/minidlna.mo ${NAS4FREE_ROOTFS}/usr/local/share/locale/$${lang}/LC_MESSAGES; \
	done

.include <bsd.port.mk>
