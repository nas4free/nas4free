PORTNAME=		transmission
PORTVERSION=	2.74
PORTREVISION?= 0
CATEGORIES?=		net-p2p
COMMENT=		A fast and lightweight CLI bitTorrent client
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=   	http://download-origin.transmissionbt.com/files/ \
			http://transmission.cachefly.net/

LIB_DEPENDS=		curl.6:${PORTSDIR}/ftp/curl \
	    		event-2.0.6:${PORTSDIR}/devel/libevent2
GEN_RUN_DEPENDS=${LOCALBASE}/share/transmission/web/index.html:${PORTSDIR}/www/transmission-web
USE_GMAKE=	yes
USE_GNOME?=	pkgconfig
USE_OPENSSL=	yes
USE_BZIP2=	yes
GNU_CONFIGURE=	yes
CPPFLAGS=	-I${LOCALBASE}/include
LDFLAGS=	-L${LOCALBASE}/lib
CONFIGURE_ENV=	CPPFLAGS="${CPPFLAGS}"

CONFIGURE_ARGS+=	--with-ssl=${OPENSSLBASE} \
									--with-zlib=/usr \
									--disable-libappindicator \
									--enable-largefile \
									--enable-daemon \
									--disable-gtk \
									--disable-libnotify \
		 							--disable-mac \
		 							--disable-nls

general-patch:
	@${FIND} ${WRKSRC} -name Makefile.in | ${XARGS} ${REINPLACE_CMD} -e \
		's|noinst_PROGRAMS = |noinst_PROGRAMS = #|g'
	@${REINPLACE_CMD} -e '/#define DISABLE_GETTEXT/s,/\* \(.*\) \*/,\1,' \
		${WRKSRC}/libtransmission/utils.h
	@${REINPLACE_CMD} -e \
		's|_pthread_flags=".*"|_pthread_flags="${PTHREAD_LIBS}"|g ; \
		s|rt_clock_gettime=yes|rt_clock_gettime=no|g ; \
		s|" == "|" = "|g ; s|$$with_ssl.*/usr;|${OPENSSLBASE};|g' \
			${WRKSRC}/configure
	@${REINPLACE_CMD} -e 's|doc \\| \\|g' \
		${WRKSRC}/Makefile.in


post-patch: general-patch

do-install:

	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${NAS4FREE_ROOTFS}/etc/rc.d/${PORTNAME}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-daemon ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-remote ${NAS4FREE_ROOTFS}/usr/local/bin

	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/images/
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/jqueryui
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission/images/buttons

	@${INSTALL_DATA} -v ${WRKSRC}/web/index.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/LICENSE ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web
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
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.contextmenu.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.form.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.form.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.transmenu.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jqueryui-1.8.16.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/json2.min.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/common.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/common.scss ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/mobile.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/transmission/mobile.scss ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/transmission
	@${INSTALL_DATA} -v ${WRKSRC}/web/style/jqueryui/jqueryui-1.8.16.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/web/style/jqueryui

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
