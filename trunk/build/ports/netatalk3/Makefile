PORTNAME=		netatalk
PORTVERSION=	 3.1.7
PORTREVISION=	 1
PORTEPOCH=	 1
CATEGORIES=	 net
MASTER_SITES=	 SF
PKGNAMESUFFIX= 3

MAINTAINER=	aoyama@nas4free.org
COMMENT=	File server for Mac OS X

LICENSE=	GPLv2

LIB_DEPENDS=	libgcrypt.so:${PORTSDIR}/security/libgcrypt \
		libevent.so:${PORTSDIR}/devel/libevent2

USE_BDB=	5+
USES=		iconv gmake pkgconfig perl5 libtool tar:bzip2
GNU_CONFIGURE=	yes
USE_LDCONFIG=	yes
#USE_RC_SUBR=	netatalk

CONFIGURE_ARGS+=	--with-pkgconfdir=${PREFIX}/etc \
			--with-libgcrypt-dir=${LOCALBASE} \
			--with-uams-path=/etc/uams \
			--with-bdb=${LOCALBASE} \
			${ICONV_CONFIGURE_BASE} \
			--localstatedir=/var/db \
			--sysconfdir=/var/etc \
			--disable-bundled-libevent \
			--with-libevent-header=${LOCALBASE}/include \
			--with-libevent-lib=${LOCALBASE}/lib

LIB_DEPENDS+=	libsunacl.so:${PORTSDIR}/sysutils/libsunacl
CONFIGURE_ARGS+= 	--without-dtrace \
			--without-kerberos \
			--with-pam \
			--disable-zeroconf \
			--without-ldap \
			--disable-sendfile \
			--with-mysql-config=/nonexistent \
			--with-acls \
			--disable-tcp-wrappers

.include <bsd.port.options.mk>

#CONFLICTS=	bigloo-2.* cap-6.0.* tct-1.* netatalk-[12]* yudit-[0-9]*

post-patch:
	@${REINPLACE_CMD} -e 's|%%DB_NAME%%|${BDB_INCLUDE_DIR:T}| ; \
	    	s|%%DB_LIB%%|-l${BDB_LIB_NAME}|g ; \
		s|%%LOCALBASE%%|${LOCALBASE}|g' \
		${WRKSRC}/configure

post-stage:
	@${RM} -f ${STAGEDIR}${PREFIX}/etc/afp.conf \
		${STAGEDIR}${PREFIX}/etc/extmap.conf

post-install:
	${INSTALL_SCRIPT} ${WRKSRC}/contrib/macusers/macusers \
		${STAGEDIR}${PREFIX}/bin/macusers
	[ -f ${STAGEDIR}${PREFIX}/etc/afp.conf ] || \
		${INSTALL_DATA} -c ${STAGEDIR}${PREFIX}/etc/afp.conf.dist ${STAGEDIR}${PREFIX}/etc/afp.conf
	[ -f ${STAGEDIR}${PREFIX}/etc/extmap.conf ] || \
		${INSTALL_DATA} -c ${STAGEDIR}${PREFIX}/etc/extmap.conf.dist ${STAGEDIR}${PREFIX}/etc/extmap.conf

# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"

	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/afpd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_metad ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_dbd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/netatalk ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_LIB} -v ${STAGEDIR}${PREFIX}/lib/libatalk.so.16 ${NAS4FREE_ROOTFS}/usr/local/lib
	@${INSTALL_DATA} -v ${STAGEDIR}${PREFIX}/etc/extmap.conf.dist ${NAS4FREE_ROOTFS}/usr/local/etc/extmap.conf

	@${MKDIR} -v ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx2_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_dhx2_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_guest.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}/etc/uams/uams_randnum.so ${NAS4FREE_ROOTFS}/etc/uams

	@${INSTALL_SCRIPT} -v ${FILESDIR}/netatalk.in ${NAS4FREE_ROOTFS}/etc/rc.d/netatalk

	@cd ${NAS4FREE_ROOTFS}/etc/uams && ${LN} -fsv uams_passwd.so uams_clrtxt.so && ${LN} -fsv uams_dhx_passwd.so uams_dhx.so && ${LN} -fsv uams_dhx2_passwd.so uams_dhx2.so

.include <bsd.port.mk>
