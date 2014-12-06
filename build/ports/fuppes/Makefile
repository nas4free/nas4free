PORTNAME=		fuppes
PORTVERSION=	0.692
PORTREVISION= 2
CATEGORIES=		net
COMMENT=		Free UPnP Entertainment Service
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		SF/${PORTNAME}/${PORTNAME}/SVN-660
DISTNAME=		${PORTNAME}-${PORTVERSION}

LIB_DEPENDS+=		libsqlite3.so:${PORTSDIR}/databases/sqlite3 \
			libtag.so:${PORTSDIR}/audio/taglib \
			libcurl.so:${PORTSDIR}/ftp/curl \
			libpcre.so:${PORTSDIR}/devel/pcre \
			libuuid.so:${PORTSDIR}/misc/e2fsprogs-libuuid \
			libmp3lame.so:${PORTSDIR}/audio/lame \
			libtwolame.so:${PORTSDIR}/audio/twolame \
			libvorbis.so:${PORTSDIR}/audio/libvorbis \
			libavformat.so:${PORTSDIR}/multimedia/ffmpeg

USE_AUTOTOOLS=	autoconf
USE_GNOME=		libxml2
USE_ICONV=		yes
USE_GMAKE=		yes
MAKE_JOBS_UNSAFE=yes
GNU_CONFIGURE=		yes 
CONFIGURE_ARGS+=	--enable-largefile \
			--enable-transcoder-ffmpeg \
			--enable-faad \
			--enable-twolame \
			--enable-mp4v2 \
			--enable-taglib \
			--enable-lame \
			--enable-vorbis \
			--enable-musepack \
			--enable-flac \
			--enable-dlna \
			--disable-exiv2 \
			--disable-inotify
CONFIGURE_ARGS+=	--localstatedir=/var/db

CONFIGURE_ENV=		CPPFLAGS="-I${LOCALBASE}/include -I${LOCALBASE}/include/ffmpeg ${PTHREAD_CFLAGS}" \
			LIBS="-L${LOCALBASE}/lib ${PTHREAD_LIBS}"

pre-configure:
	@if [ -f "${WRKSRC}/m4/version.sh" ]; then \
		${REINPLACE_CMD} -e 's,/bin/bash,${LOCALBASE}/bin/bash,' ${WRKSRC}/m4/version.sh; \
	fi
	@(cd ${WRKDIR}/${DISTNAME}; autoreconf -vfi)

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/${PORTNAME}d ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/libfuppes.so.* ${NAS4FREE_ROOTFS}/usr/local/lib
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${NAS4FREE_ROOTFS}/etc/rc.d/${PORTNAME}

	# Presentation
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.png ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/*.jpg ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_DATA} -v ${WRKSRC}/resources/style.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}

	# Configs
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/devices
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/vfolders
	@${INSTALL_DATA} -v ${WRKSRC}/config/devices/*.cfg ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/devices
	@${INSTALL_DATA} -v ${WRKSRC}/config/vfolders/*.cfg ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/vfolders
	@${INSTALL_DATA} -v ${FILESDIR}/devices/*.cfg ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/devices

	# Install plugins
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdatabase_sqlite3.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdatabase_sqlite3.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdecoder_flac.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdecoder_flac.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libdecoder_vorbis.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libdecoder_vorbis.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libencoder_pcm.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libencoder_pcm.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libencoder_wav.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libencoder_wav.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_libavformat.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_libavformat.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_libmp4v2.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_libmp4v2.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libmetadata_taglib.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libmetadata_taglib.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libsource_bliptv.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libsource_bliptv.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${CP} -Rpv ${WRKSRC}/src/plugins/.libs/libsource_itunes.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libsource_itunes.so.* ${NAS4FREE_ROOTFS}/${PREFIX}/lib/${PORTNAME}

.include <bsd.port.mk>
