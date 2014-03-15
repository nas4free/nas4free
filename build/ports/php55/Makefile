PORTNAME=		php55
PORTVERSION=	5.5.10
PORTREVISION?=  0
CATEGORIES=		lang
COMMENT=		PHP5 Scripting Language
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		${MASTER_SITE_PHP:S,$,:release,}
MASTER_SITE_SUBDIR=	distributions/:release
DISTNAME=		php-${PORTVERSION:S/.r/RC/}
DISTFILES=		${DISTNAME}${EXTRACT_SUFX}:release
USE_BZIP2=		yes

BUILD_DEPENDS=	sqlite3>=3.0:${PORTSDIR}/databases/sqlite3
LIB_DEPENDS=	libpcre.so:${PORTSDIR}/devel/pcre
LIB_DEPENDS+=	curl.7:${PORTSDIR}/ftp/curl
LIB_DEPENDS+=	mcrypt.8:${PORTSDIR}/security/libmcrypt
LIB_DEPENDS+=	onig.1:${PORTSDIR}/devel/oniguruma4
LIB_DEPENDS+=	libfreetype.so:${PORTSDIR}/print/freetype2
LIB_DEPENDS+=	libpng15.so:${PORTSDIR}/graphics/png
LIB_DEPENDS+=	libjpeg.so:${PORTSDIR}/graphics/jpeg

USE_AUTOTOOLS=	 autoconf
AUTOTOOLSFILES=	 scripts/phpize.in
USE_BZIP2=	 yes
USE_GETOPT_LONG= yes
USE_GNOME=	 libxml2
USE_OPENLDAP=	 yes
USE_OPENSSL=	 yes
GNU_CONFIGURE=	 yes
NO_STAGE=       yes

CONFIGURE_ARGS+=	\
		--without-mysql \
		--without-pear \
		--with-openssl \
		--with-gettext \
		--enable-libxml \
		--disable-mysqlnd \
		--with-libxml-dir=${LOCALBASE} \
		--with-pcre-regex=${LOCALBASE} \
		--with-zlib-dir=/usr \
		--with-config-file-path=/usr/local/etc \
		--with-config-file-scan-dir=/usr/local/etc/php \
		--enable-pdo \
		--with-pdo-sqlite=${LOCALBASE} \
		--with-sqlite3 \
		--with-iconv-dir=${LOCALBASE} \
		--with-bz2=/usr \
		--with-curl=${LOCALBASE} \
		--with-gd \
		--with-freetype-dir=${LOCALBASE} \
		--with-jpeg-dir=${LOCALBASE} \
		--with-png-dir=${LOCALBASE} \
		--with-ldap=${LOCALBASE} \
		--enable-mbstring \
		--with-onig=${LOCALBASE} \
		--with-pcre-dir=${LOCALBASE} \
		--with-mcrypt=${LOCALBASE} \
		--enable-soap \
		--with-xmlrpc \
		--enable-zip \
		--with-zlib=/usr

OPTIONS=	CLI "Build CLI version" on \
		CGI "Build CGI version" on \
		FPM "Build FPM version" off \
		APACHE "Build Apache module" off \
		AP2FILTER " Use Apache 2.x filter interface (experimental)" off \
		EMBED "Build embedded library" off \
		DEBUG "Enable debug" off \
		DTRACE "Enable DTrace support" off \
		IPV6 "Enable ipv6 support" on \
		MAILHEAD "Enable mail header patch" off \
		LINKTHR "Link thread lib (for threaded extensions)" on \
		MZTS "Force Zend Thread Safety (ZTS) build" off
#		SUHOSIN "Enable Suhosin protection system" on \

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
	@${INSTALL_SCRIPT} -v ${FILESDIR}/php-cgi-websrv ${NAS4FREE_ROOTFS}/${PREFIX}/bin/
	@${CP} -pv ${FILESDIR}/php.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc

.include <bsd.port.mk>