PORTNAME=			mt-daapd
PORTVERSION=	1586
CATEGORIES=			audio
COMMENT=			Multithread daapd yet another server for Apple iTunes
MAINTAINER=			zoon01@nas4free.org

MASTER_SITES=	http://nightlies.fireflymediaserver.org/nightlies/svn-1586/
DISTNAME=				${PORTNAME}-svn-${PORTVERSION}

LIB_DEPENDS=				gdbm.3:${PORTSDIR}/databases/gdbm \
										sqlite3:${PORTSDIR}/databases/sqlite3 \
										id3tag.0:${PORTSDIR}/audio/libid3tag \
										vorbis.4:${PORTSDIR}/audio/libvorbis \
										FLAC.10:${PORTSDIR}/audio/flac \
										avcodec:${PORTSDIR}/multimedia/ffmpeg

USE_AUTOTOOLS=			autoheader:262 autoconf:262:env
USE_ICONV=			yes
GNU_CONFIGURE=			yes
NO_STAGE=       yes
CONFIGURE_ENV=			CFLAGS="${CFLAGS} ${PTHREAD_CFLAGS}" \
										CPPFLAGS="${CPPFLAGS} -I${LOCALBASE}/include" \
										LDFLAGS="-L${LOCALBASE}/lib" \
										LIBS="-L${LOCALBASE}/lib ${PTHREAD_LIBS}"
CONFIGURE_ARGS=			--disable-gdbm \
										--enable-sqlite3 \
										--with-id3tag=${PREFIX} \
										--enable-oggvorbis \
										--enable-flac \
										--enable-ffmpeg \
										--with-ffmpeg-includes=${LOCALBASE}/include/ffmpeg

do-install:
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/plugins

	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/${PORTNAME} ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libout-daap.so* ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/plugins/out-daap.so
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/librsp.so* ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/plugins/rsp.so
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libssc-ffmpeg.so* ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/plugins/ssc-ffmpeg.so
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.playlist ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${NAS4FREE_ROOTFS}/etc/rc.d/${PORTNAME}

	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us

	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/about.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.css $NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.xml ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config-update.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/CREDITS ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/feedback.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ff_logo_sm.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ftr.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/gpl-license.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/hdr.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.css ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkOpaque.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkTransparent.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/no_access.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/pngfix.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/required.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smartpopup.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner_stopped.gif ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/status.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/thanks.html ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/util.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/prototype.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/rico.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/builder.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/controls.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/dragdrop.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/effects.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/scriptaculous.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/slider.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/unittest.js ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root/lib-js/script.aculo.us

	@${INSTALL_DATA} -v ${FILESDIR}/favicon.ico ${NAS4FREE_ROOTFS}/usr/local/share/${PORTNAME}/admin-root

.include <bsd.port.mk>
