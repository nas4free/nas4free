PORTNAME=		php5
PORTVERSION=	5.3.13
PORTREVISION?= 0
CATEGORIES=		lang
COMMENT=		PHP5 Scripting Language
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		${MASTER_SITE_PHP:S,$,:release,}
MASTER_SITE_SUBDIR=	distributions/:release
DISTNAME=		php-${PORTVERSION:S/.r/RC/}
DISTFILES=		${DISTNAME}${EXTRACT_SUFX}:release
USE_BZIP2=		yes

BUILD_DEPENDS=	sqlite3>=3.0:${PORTSDIR}/databases/sqlite3

USE_AUTOTOOLS=	autoconf
AUTOTOOLSFILES=	scripts/phpize.in
USE_GETOPT_LONG=	yes
USE_GNOME=			libxml2
USE_OPENSSL=		yes
GNU_CONFIGURE=	yes

CONFIGURE_ARGS+=	--without-mysql \
								--without-pear \
								--with-openssl \
								--without-sqlite \
								--with-pcre-regex=yes \
								--with-gettext \
								--enable-libxml \
								--disable-mysqlnd \
								--with-libxml-dir=${LOCALBASE} \
								--with-config-file-path=/usr/local/etc \
								--enable-pdo \
								--with-pdo-sqlite=${LOCALBASE} \
								--with-iconv-dir=${LOCALBASE}

OPTIONS=	CLI "Build CLI version" on \
		CGI "Build CGI version" on \
		APACHE "Build Apache module" off \
		DEBUG "Enable debug" off \
		SUHOSIN "Enable Suhosin protection system" on \
		MULTIBYTE "Enable zend multibyte support" off \
		IPV6 "Enable ipv6 support" on \
		MAILHEAD "Enable mail header patch" off

# Work around issues with newer (>=2.64) autoconf
pre-configure:
	@${REINPLACE_CMD} -E 's:^((m4_)?divert)[(]([0-9]*)[)]:\1(600\3):' \
		${WRKSRC}/configure.in `${FIND} ${WRKSRC} -name '*.m4'`
	@${RM} ${WRKSRC}/configure
	@${CAT} ${WRKSRC}/acinclude.m4 ${WRKSRC}/build/libtool.m4 > ${WRKSRC}/aclocal.m4

	@${TOUCH} ${WRKSRC}/ext/php_config.h

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cgi/php-cgi ${NAS4FREE_ROOTFS}/${PREFIX}/bin/php
	@${CP} -pv ${FILESDIR}/php.ini ${NAS4FREE_ROOTFS}/${PREFIX}/etc

.include <bsd.port.mk>
