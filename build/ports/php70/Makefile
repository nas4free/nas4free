PORTNAME=		php70
PORTVERSION=	7.0.16
PORTREVISION=	0
CATEGORIES=		lang
COMMENT=		PHP7 Scripting Language
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=		${MASTER_SITE_PHP:S,$,:release,}
MASTER_SITE_SUBDIR=	distributions/:release
DISTNAME=			php-${PORTVERSION:S/.r/RC/}
DISTFILES=			${DISTNAME}${EXTRACT_SUFX}:release
USES+=			tar:xz cpe iconv
CPE_PRODUCT=		php
NO_OPTIONS_SORT=	yes

BUILD_DEPENDS=		sqlite3>=3.0:databases/sqlite3
LIB_DEPENDS=		libpcre.so:devel/pcre
LIB_DEPENDS+=		libcurl.so:ftp/curl
LIB_DEPENDS+=		libmcrypt.so:security/libmcrypt
LIB_DEPENDS+=		libonig.so:devel/oniguruma5
LIB_DEPENDS+=		libfreetype.so:print/freetype2
LIB_DEPENDS+=		libpng16.so:graphics/png
LIB_DEPENDS+=		libjpeg.so:graphics/jpeg
LIB_DEPENDS+=		libvpx.so:multimedia/libvpx
LIB_DEPENDS+=		libgmp.so:math/gmp
LIB_DEPENDS+=		libc-client4.so.9:mail/cclient
LIB_DEPENDS+=		libmysqlclient.so.18:databases/mariadb101-client

USE_AUTOTOOLS=	 autoconf
AUTOTOOLSFILES=	 scripts/phpize.in
USE_BZIP2=	 yes
USE_GETOPT_LONG= yes
USE_GNOME=	 libxml2
USE_OPENLDAP=	 yes
USE_OPENSSL=	 yes
GNU_CONFIGURE=	 yes
NO_PKG_REGISTER= yes

CONFIGURE_ARGS+=	\
		--with-pear \
		--with-openssl \
		--with-gettext \
		--enable-libxml \
		--enable-mysqlnd \
		--with-mysql=mysqlnd \
		--with-mysqli=mysqlnd \
		--with-libxml-dir=${LOCALBASE} \
		--with-pcre-regex=${LOCALBASE} \
		--with-zlib-dir=/usr \
		--with-config-file-path=/usr/local/etc \
		--with-config-file-scan-dir=/usr/local/etc/php \
		--enable-pdo \
		--with-pdo-mysql=mysqlnd \
		--with-pdo-sqlite=${LOCALBASE} \
		--with-sqlite3 \
		--with-iconv-dir=${LOCALBASE} \
		--with-bz2=/usr \
		--with-curl=${LOCALBASE} \
		--with-gd \
		--with-freetype-dir=${LOCALBASE} \
		--with-jpeg-dir=${LOCALBASE} \
		--with-png-dir=${LOCALBASE} \
		--with-vpx-dir=${LOCALBASE} \
		--with-ldap=${LOCALBASE} \
		--enable-mbstring \
		--with-onig=${LOCALBASE} \
		--with-pcre-dir=${LOCALBASE} \
		--with-mcrypt=${LOCALBASE} \
		--with-gmp=${LOCALBASE} \
		--enable-bcmath \
		--enable-hash \
		--with-mhash \
		--enable-ftp \
		--with-imap=${LOCALBASE} \
		--with-imap-ssl=${OPENSSLBASE} \
		--enable-soap \
		--enable-sockets \
		--enable-sysvmsg \
		--enable-sysvsem \
		--enable-sysvshm \
		--enable-tokenizer \
		--with-xmlrpc \
		--enable-zip \
		--with-zlib=/usr \
		--enable-opcache \

OPTIONS=	CLI "Build CLI version" on \
		CGI "Build CGI version" on \
		FPM "Build FPM version" off \
		IPV6 "Enable ipv6 support" on \
		APACHE "Build Apache module" off \
		AP2FILTER " Use Apache 2.x filter interface (experimental)" off \
		EMBED "Build embedded library" off \
		DEBUG "Enable debug" off \
		PHPDBG "Enable debug" off \
		DTRACE "Enable DTrace support" off \
		LINKTHR "Link thread lib (for threaded extensions)" on \
		ZTS "Force Zend Thread Safety (ZTS) build" off

CONFLICTS=	php55-5* php56-*

.if defined(WITH_DTRACE)
.if ${OSVERSION} < 900000
IGNORE=		cannot be built with DTrace support on FreeBSD < 9.x
.endif
CONFIGURE_ARGS+=--enable-dtrace
.endif

post-patch:
	@${REINPLACE_CMD} -i -e 's|freetype/||g' ${WRKSRC}/configure
	@${REINPLACE_CMD} -i -e 's|freetype/||g' ${WRKSRC}/ext/gd/config.m4

# Work around issues with newer (>=2.64) autoconf
pre-configure:
	@${REINPLACE_CMD} -E 's:^((m4_)?divert)[(]([0-9]*)[)]:\1(600\3):' \
		${WRKSRC}/configure.in `${FIND} ${WRKSRC} -name '*.m4'`
	@${RM} ${WRKSRC}/configure
	@${CAT} ${WRKSRC}/acinclude.m4 ${WRKSRC}/build/libtool.m4 > ${WRKSRC}/aclocal.m4

	@${TOUCH} ${WRKSRC}/ext/php_config.h

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cgi/php-cgi ${NAS4FREE_ROOTFS}/${PREFIX}/bin/
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cli/php ${NAS4FREE_ROOTFS}/${PREFIX}/bin/
	@${INSTALL_SCRIPT} -v ${FILESDIR}/php-cgi-websrv ${NAS4FREE_ROOTFS}/${PREFIX}/bin/
	@${CP} -pv ${FILESDIR}/php.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc

	@${MKDIR} ${NAS4FREE_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20151012
	${INSTALL_DATA} ${WRKSRC}/ext/opcache/.libs/opcache.so ${NAS4FREE_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20151012
	@${CP} -pv ${FILESDIR}/opcache.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc/php
	@${CP} -pv /usr/local/lib/mysql/libmysqlclient.so.18 ${NAS4FREE_ROOTFS}/${PREFIX}/lib

.include <bsd.port.mk>
