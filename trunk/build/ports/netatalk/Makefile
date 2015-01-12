PORTNAME=		netatalk
PORTVERSION=	2.2.5
PORTREVISION=	0
CATEGORIES=		net print
MASTER_SITES=	SF
COMMENT=		File server for Mac OS X
MAINTAINER=		zoon01@nas4free.org

LIB_DEPENDS=	libgcrypt.so:${PORTSDIR}/security/libgcrypt

USES=		libtool
USE_BDB=	46+
USE_BZIP2=	yes
GNU_CONFIGURE=	yes
USES=           iconv gmake perl5
WANT_GNOME=	yes
DISABLE_VULNERABILITIES=yes

CONFIGURE_ARGS+=	--bindir=/usr/local/bin \
			--sbindir=/usr/local/sbin \
			--sysconfdir=/var/etc \
			--localstatedir=/var \
			--enable-largefile \
			--disable-tcp-wrappers \
			--disable-cups \
			--disable-sendfile \
			--with-pam=/etc/pam.d \
			--with-uams-path=/etc/uams/ \
			--with-libiconv=${LOCALBASE} \
			--with-libgcrypt-dir=${LOCALBASE} \
			--with-bdb=${LOCALBASE}

post-patch:
	@${REINPLACE_CMD} -e 's|%%DB_NAME%%|${BDB_INCLUDE_DIR:T}| ; \
	    	s|%%DB_LIB%%|-l${BDB_LIB_NAME}|g ; \
		s|%%LOCALBASE%%|${LOCALBASE}|g' \
		${WRKSRC}/configure

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/afpd/afpd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/cnid_dbd/cnid_metad ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/cnid_dbd/cnid_dbd ${NAS4FREE_ROOTFS}/usr/local/sbin

	@${MKDIR} -v ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_dhx_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_dhx_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_dhx2_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_dhx2_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_guest.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_pam.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_passwd.so ${NAS4FREE_ROOTFS}/etc/uams
	@${INSTALL_PROGRAM} -v ${WRKSRC}/etc/uams/.libs/uams_randnum.so ${NAS4FREE_ROOTFS}/etc/uams

	@${INSTALL_SCRIPT} -v ${FILESDIR}/afpd.in ${NAS4FREE_ROOTFS}/etc/rc.d/afpd

	@cd ${NAS4FREE_ROOTFS}/etc/uams && ${LN} -fsv uams_passwd.so uams_clrtxt.so && ${LN} -fsv uams_dhx_passwd.so uams_dhx.so && ${LN} -fsv uams_dhx2_passwd.so uams_dhx2.so

.include <bsd.port.mk>
