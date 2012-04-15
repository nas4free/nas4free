PORTNAME=		lcdproc
PORTVERSION=	0.5.5
CATEGORIES=		sysutils
MASTER_SITES=	SF
COMMENT=		A client/server suite for LCD devices
MAINTAINER=		zoon01@nas4free.org

USE_AUTOTOOLS=	autoconf automake aclocal
GNU_CONFIGURE=	yes
USE_GNOME=	pkgconfig

LIB_DEPENDS+=	ftdi.19:${PORTSDIR}/devel/libftdi
LIB_DEPENDS+=	hid:${PORTSDIR}/devel/libhid

CONFIGURE_ARGS=	--enable-drivers="${LCDPROC_DRIVERS}" \
							--with-libusb=/usr \
							--enable-libftdi \
							--enable-libhid \
							--disable-dependency-tracking \
							--disable-doxygen
ACLOCAL_ARGS=

LCDPROC_DRIVERS=	bayrad \
					CFontz \
					CFontz633 \
					CFontzPacket \
					CwLnx \
					curses \
					ea65 \
					EyeboxOne \
					glk \
					hd44780 \
					icp_a106 \
					lb216 \
					lcdm001 \
					lcterm \
					MD8800 \
					ms6931 \
					mtc_s16209x \
					MtxOrb \
					NoritakeVFD \
					pyramid \
					SureElec \
					sed1330 \
					sed1520 \
					serialPOS \
					serialVFD \
					sli \
					stv5730 \
					t6963 \
					text \
					tyan
LCDPROC_DRIVERS+=	IOWarrior \
					picolcd \
					shuttleVFD
LCDPROC_DRIVERS+=	i2500vfd \
					lis \
					ula200
LCDPROC_DRIVERS+=	mdm166a

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/LCDd ${NAS4FREE_ROOTFS}/${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdexec/lcdexec ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdproc/lcdproc ${NAS4FREE_ROOTFS}/${PREFIX}/bin
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/lib/lcdproc
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/drivers/*.so ${NAS4FREE_ROOTFS}/${PREFIX}/lib/lcdproc
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/LCDd.conf ${NAS4FREE_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdexec/lcdexec.conf ${NAS4FREE_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdproc/lcdproc.conf ${NAS4FREE_ROOTFS}/${PREFIX}/share/lcdproc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/LCDd.in ${NAS4FREE_ROOTFS}/etc/rc.d/LCDd
	@${INSTALL_SCRIPT} -v ${FILESDIR}/lcdproc.in ${NAS4FREE_ROOTFS}/etc/rc.d/lcdproc

.include <bsd.port.mk>
