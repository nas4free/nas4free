PORTNAME=		transmission
PORTVERSION=	2.92
PORTREVISION=	1
CATEGORIES?=		net-p2p
COMMENT=		Fast and lightweight CLI BitTorrent client
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=   	http://download-origin.transmissionbt.com/files/ \
			http://transmission.cachefly.net/

LIB_DEPENDS=		libb64.so:converters/libb64 \
					libcurl.so:ftp/curl \
					libdht.so:devel/jech-dht \
					libminiupnpc.so:net/miniupnpc \
					libnatpmp.so:net/libnatpmp \
					libutp.so:net/libutp \
					libevent.so:devel/libevent2

GEN_RUN_DEPENDS=${LOCALBASE}/share/transmission/web/index.html:www/transmission-web

USES+=		gmake iconv libtool pkgconfig
USE_OPENSSL=	yes
EXTRACT_SUFX=	.tar.xz
GNU_CONFIGURE=	yes
CPPFLAGS=	-I${LOCALBASE}/include
LDFLAGS=	-L${LOCALBASE}/lib
CONFIGURE_ENV=	CPPFLAGS="${CPPFLAGS}"
LDFLAGS+=	-pthread

CONFIGURE_ENV=  ZLIB_CFLAGS=" " ZLIB_LIBS="-lz" \
		${ICONV_LIB:C@.+@ac_cv_func_iconv=yes@}

CONFIGURE_ARGS+=	--without-inotify \
					--enable-largefile \
					--enable-daemon \
					--without-gtk \
					--disable-mac \
					--enable-external-b64 \
					--enable-external-dht \
					--enable-external-natpmp \
		 			--disable-nls
OPTIONS_DEFINE+=	DOCS LITE
OPTIONS_DEFAULT+=	OPENSSL
OPTIONS_SINGLE+=	SSL
OPTIONS_SINGLE_SSL+=	OPENSSL POLARSSL WOLFSSL

LITE_CONFIGURE_ENABLE=	lightweight

OPENSSL_USE=		OPENSSL=yes
OPENSSL_CONFIGURE_ENV=	OPENSSL_CFLAGS="-I${OPENSSLINC}" \
			OPENSSL_LIBS="-L${OPENSSLLIB} -lcrypto"
OPENSSL_CONFIGURE_ON=	--with-crypto=openssl

POLARSSL_LIB_DEPENDS=	libpolarssl.so:${PORTSDIR}/security/polarssl13
POLARSSL_CONFIGURE_ON=	--with-crypto=polarssl

WOLFSSL_LIB_DEPENDS=	libwolfssl.so:${PORTSDIR}/security/wolfssl
WOLFSSL_CONFIGURE_ON=	--with-crypto=cyassl
WOLFSSL_BROKEN=		Requires security/wolfssl built with --enable-arc4

general-patch:
	@${FIND} ${WRKSRC} -name Makefile.in | ${XARGS} ${REINPLACE_CMD} -e \
		's|noinst_PROGRAMS = |noinst_PROGRAMS = #|g'
	@${REINPLACE_CMD} -e '/#define DISABLE_GETTEXT/s,/\* \(.*\) \*/,\1,' \
		${WRKSRC}/libtransmission/utils.h
	@${REINPLACE_CMD} -e \
		's|_pthread_flags=".*"|_pthread_flags="${PTHREAD_LIBS}"|g ; \
		s|rt_clock_gettime=yes|rt_clock_gettime=no|g ; \
		s|" == "|" = "|g' \
		${WRKSRC}/configure
	@${REINPLACE_CMD} -e 's|doc \\| \\|g' \
		${WRKSRC}/Makefile.in
	@${REINPLACE_CMD} -e 's|third-party \\|\\|g' \
		${WRKSRC}/Makefile.in
	@${REINPLACE_CMD} \
		-e 's|third-party/[^ ]*Makefile||g' \
		${WRKSRC}/configure

post-patch: general-patch

do-install:

	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${NAS4FREE_ROOTFS}/etc/rc.d/${PORTNAME}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-daemon ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-remote ${NAS4FREE_ROOTFS}/usr/local/bin

	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/images/
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/jqueryui
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images/buttons

	@${INSTALL_DATA} -v ${WRKSRC}/web/index.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/favicon.ico ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/favicon.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/webclip-icon.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/images

	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/common.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/dialog.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/file-row.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/formatter.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/inspector.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/notifications.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/prefs-dialog.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/remote.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/torrent.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/torrent-row.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/transmission.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript

	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.transmenu.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.ui-contextmenu.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery-migrate.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery-ui.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/json2.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery

	@${INSTALL_DATA} -v ${WRKSRC}/web/style/jqueryui/jquery-ui.min.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/jqueryui

	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/common.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/common.scss ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/mobile.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/mobile.scss ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission

	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/arrow-down.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/arrow-up.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/blue-turtle.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/compact.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/file-priority-high.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/file-priority-low.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/file-priority-normal.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/filter_bar.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/filter_icon.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/inspector-files.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/inspector-info.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/inspector-peers.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/inspector-trackers.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/lock_icon.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/logo.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/progress.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/settings.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-close.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-folder.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-info.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-pause.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-pause-all.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-start.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/toolbar-start-all.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/turtle.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/wrench.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/images/buttons/torrent_buttons.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images/buttons

.include <bsd.port.mk>
