PORTNAME=		lighttpd
PORTVERSION=	1.4.31
PORTREVISION= 0
CATEGORIES=		www
COMMENT=		A secure, fast, compliant, and very flexible Web Server
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://download.lighttpd.net/lighttpd/releases-1.4.x/ \
							http://mirrors.cat.pdx.edu/lighttpd/

LIB_DEPENDS+=		uuid.1:${PORTSDIR}/misc/e2fsprogs-libuuid \
							sqlite3.8:${PORTSDIR}/databases/sqlite3 \
							pcre.1:${PORTSDIR}/devel/pcre

USE_BZIP2=	  yes
#USE_AUTOTOOLS= libtool
GNU_CONFIGURE=  yes
USE_OPENSSL=	  yes
USE_GNOME+=	  libxml2
MAKE_JOBS_SAFE= yes

USE_AUTOTOOLS=	autoconf autoheader aclocal automake libtool
ACLOCAL_ARGS=	-I m4
AUTOMAKE_ARGS=	--add-missing

CONFIGURE_ARGS+=	--libdir=${PREFIX}/lib/lighttpd --enable-static \
						                     --sysconfdir=/var/etc/ \
									--enable-lfs \
									--without-mysql \
									--without-ldap \
									--with-openssl \
									--without-lua \
									--with-bzip2 \
									--with-webdav-props \
									--with-webdav-locks

do-install:
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/lighttpd ${NAS4FREE_ROOTFS}/usr/local/sbin

		@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_access.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_accesslog.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_alias.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_auth.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_cgi.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_cml.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_compress.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_dirlisting.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_evasive.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_evhost.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_expire.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_extforward.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_fastcgi.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_flv_streaming.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_indexfile.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_magnet.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_mysql_vhost.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_proxy.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_redirect.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_rewrite.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_rrdtool.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_scgi.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_secdownload.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_setenv.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_simple_vhost.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_ssi.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_staticfile.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_status.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_trigger_b4_dl.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_userdir.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_usertrack.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd
		@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_webdav.so ${NAS4FREE_ROOTFS}/usr/local/lib/lighttpd

.include <bsd.port.mk>
