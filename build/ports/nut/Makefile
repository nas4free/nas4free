PORTNAME=		nut
PORTVERSION=	2.7.2
PORTREVISION=	2
CATEGORIES=		sysutils
COMMENT=		Network UPS tools
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	http://www.networkupstools.org/source/${PORTVERSION:R}/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX} ${DISTNAME}${EXTRACT_SUFX}.sig
EXTRACT_ONLY=	${DISTNAME}${EXTRACT_SUFX}

USE_AUTOTOOLS=  autoconf
GNU_CONFIGURE=	yes
USE_LDCONFIG=		yes
USES=		gmake pkgconfig libtool

NUT_USER=		root
NUT_GROUP=		root
NUT_PIDDIR=		/var/run
NUT_STATEDIR=		/var/db/nut
NUT_SYSCONFDIR=	/var/etc
NUT_DATADIR=		/usr/local/etc/${PORTNAME}
NUT_DRVDIR=			/usr/local/libexec/${PORTNAME}

CONFIGURE_ARGS+=	--sysconfdir=${NUT_SYSCONFDIR} \
		--program-transform-name="" \
		--localstatedir=${NUT_STATEDIR} \
		--datadir=${NUT_DATADIR} \
		--with-devd-dir=${PREFIX}/etc/devd \
		--with-drvpath=${NUT_DRVDIR} \
		--with-statepath=${NUT_STATEDIR} \
		--with-altpidpath=${NUT_PIDDIR} \
		--with-pidpath=${NUT_PIDDIR} \
		--without-pkgconfig-dir \
		--without-cgi \
		--with-serial \
		--with-usb=auto \
		--with-snmp \
		--with-neon \
		--with-powerman \
		--without-dev \
		--with-user=${NUT_USER} \
		--with-group=${NUT_GROUP} \
		--with-wrap

.include <bsd.port.pre.mk>

LIB_DEPENDS+=	libnetsnmp.so:${PORTSDIR}/net-mgmt/net-snmp
LIB_DEPENDS+=   libneon.so:${PORTSDIR}/www/neon
LIB_DEPENDS+=	libpowerman.so:${PORTSDIR}/sysutils/powerman
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
LIB_DEPENDS+=	libfreeipmi.so:${PORTSDIR}/sysutils/freeipmi
CONFIGURE_ARGS+=--with-ipmi \
		--with-freeipmi
.endif

pre-install:
	@${REINPLACE_CMD} -e 's/device-name\*/cdev/g' ${WRKSRC}/scripts/devd/nut-usb.conf

do-install:
	@${MKDIR} -pv ${NAS4FREE_ROOTFS}${PREFIX}/etc/devd
	@${INSTALL_SCRIPT} -v ${WRKSRC}/scripts/devd/nut-usb.conf ${NAS4FREE_ROOTFS}${PREFIX}/etc/devd/nut-usb.conf

	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/cmdvartab ${NAS4FREE_ROOTFS}/${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/driver.list ${NAS4FREE_ROOTFS}/${NUT_DATADIR}

	@${MKDIR} -pv ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/al175 ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart-old ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcupsd-ups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkin ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkinunv ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfcom ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfortress ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestuferrups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_ser ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone-outlet ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/.libs/dummy-ups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/etapro ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/everups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/gamatronic ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/genericups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/isbmex ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/ivtscd ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert-esp2 ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/masterguard ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/metasys ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-shut ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-utalk ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/microdowell ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/netxml-ups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_atcl_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_qx ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nut-ipmipsu ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
.endif
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/oldmge-shut ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/oneac ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/optiups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powercom ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerman-pdu ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerpanel ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/rhino ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/richcomm_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_ser ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/safenet ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/skel ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/snmp-ups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/solis ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite_usb ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplitesu ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upscode2 ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upsdrvctl ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/usbhid-ups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/victronups ${NAS4FREE_ROOTFS}/${NUT_DRVDIR}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/libupsclient.so.4 ${NAS4FREE_ROOTFS}/usr/local/lib
	@${LN} -f -v -s /usr/local/lib/libupsclient.so.4 ${NAS4FREE_ROOTFS}/usr/local/lib/libupsclient.so

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsc ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upscmd ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upslog ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsmon ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsrw ${NAS4FREE_ROOTFS}/usr/local/bin

	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/upsd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/upssched ${NAS4FREE_ROOTFS}/usr/local/sbin

	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut.sh.in ${NAS4FREE_ROOTFS}/etc/rc.d/nut
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upslog.sh.in ${NAS4FREE_ROOTFS}/etc/rc.d/nut_upslog
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upsmon.sh.in ${NAS4FREE_ROOTFS}/etc/rc.d/nut_upsmon
	@${INSTALL_SCRIPT} -v ${FILESDIR}/upssched-cmd.in ${NAS4FREE_ROOTFS}/usr/local/bin/upssched-cmd

.include <bsd.port.post.mk>
