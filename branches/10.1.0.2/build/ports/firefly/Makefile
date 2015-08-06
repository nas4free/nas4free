PORTNAME=			firefly
PORTVERSION=	1696
CATEGORIES=			audio
COMMENT=			Multithread daapd yet another server for Apple iTunes
MAINTAINER=			zoon01@nas4free.org

MASTER_SITES=			http://nightlies.fireflymediaserver.org/nightlies/svn-${PORTVERSION}/ \
									http://www.magnesium.net/~mdf/distfiles/
DISTNAME=				mt-daapd-svn-${PORTVERSION}

FORCE_PKG_REGISTER=yes
LIB_DEPENDS+=				libsqlite3.so:${PORTSDIR}/databases/sqlite3 \
										libid3tag.so:${PORTSDIR}/audio/libid3tag \
										libvorbis.so:${PORTSDIR}/audio/libvorbis \
										libFLAC.so:${PORTSDIR}/audio/flac

USE_PKGCONFIG=	yes
MAKE_JOBS_UNSAFE=	yes
#USE_GCC=		any
USE_LDCONFIG=		yes
USE_ICONV=		yes
GNU_CONFIGURE=	yes


CONFIGURE_ENV=			CFLAGS="${CFLAGS} ${PTHREAD_CFLAGS}" \
										CPPFLAGS="${CPPFLAGS} -I${LOCALBASE}/include" \
										LDFLAGS="-L${LOCALBASE}/lib" \
										LIBS="-L${LOCALBASE}/lib ${PTHREAD_LIBS}"
CONFIGURE_ARGS=			--disable-gdbm \
										--enable-sqlite3 \
										--with-id3tag=${PREFIX} \
										--enable-oggvorbis \
										--enable-flac
										
.include <bsd.port.options.mk>
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
USE_GCC=		any
.endif
.if ${OSVERSION} >= 1100000
EXTRA_PATCHES+=		${FILESDIR}/extra-patch-configure
.endif

do-install:
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins

	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/mt-daapd ${NAS4FREE_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libout-daap.so* ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/librsp.so* ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	#@${INSTALL_PROGRAM} -v ${WRKSRC}/src/plugins/.libs/libssc-ffmpeg.so* ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/
	${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/libout-daap.so.0.0 ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/libout-daap.so
	${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/librsp.so.0.0 ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/librsp.so
	#${LN} -sfv ${PREFIX}/share/mt-daapd/plugins/libssc-ffmpeg.so.0.0 ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/plugins/libssc-ffmpeg.so
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mt-daapd.in ${NAS4FREE_ROOTFS}/etc/rc.d/mt-daapd

	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	-@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us

	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/about.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.css ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config.xml ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/config-update.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/CREDITS ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/feedback.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ff_logo_sm.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.css ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/firefly.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/ftr.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/gpl-license.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/hdr.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.css ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/index.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkOpaque.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/linkTransparent.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/no_access.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/playlist.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/pngfix.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/required.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smart.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/smartpopup.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/spinner_stopped.gif ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/status.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/thanks.html ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/util.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/prototype.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/rico.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/builder.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/controls.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/dragdrop.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/effects.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/scriptaculous.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/slider.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us
	@${INSTALL_DATA} -v ${WRKSRC}/admin-root/lib-js/script.aculo.us/unittest.js ${NAS4FREE_ROOTFS}/${PREFIX}/share/mt-daapd/admin-root/lib-js/script.aculo.us

.include <bsd.port.mk>
