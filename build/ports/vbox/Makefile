PORTNAME=		nas4free-virtualbox
PORTVERSION=		1.0
PORTREVISION=		1
CATEGORIES=		emulators

COMMENT=		VirtualBox
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=	${NONEXISTENT}:emulators/virtualbox-ose
BUILD_DEPENDS+=	${NONEXISTENT}:emulators/virtualbox-ose-kmod
DEP_STAGEDIR=	${PORTSDIR}/emulators/virtualbox-ose/work/stage
KMOD_STAGEDIR=	${PORTSDIR}/emulators/virtualbox-ose-kmod/work/stage

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
# kmod
.for file in vboxdrv.ko vboxnetadp.ko vboxnetflt.ko
	@${INSTALL_KLD} -v ${KMOD_STAGEDIR}/boot/modules/${file} ${NAS4FREE_ROOTFS}/boot/modules
.endfor
# rc
	@${INSTALL_SCRIPT} -v ${KMOD_STAGEDIR}${PREFIX}/etc/rc.d/vboxnet ${NAS4FREE_ROOTFS}/usr/local/etc/rc.d
.for file in vboxheadless vboxwatchdog vboxwebsrv
	@${INSTALL_SCRIPT} -v ${DEP_STAGEDIR}${PREFIX}/etc/rc.d/${file} ${NAS4FREE_ROOTFS}/usr/local/etc/rc.d
.endfor
	@${INSTALL_SCRIPT} -v ${FILESDIR}/vbox.in ${NAS4FREE_ROOTFS}/etc/rc.d/vbox
# bin
.for file in VBoxAutostart VBoxBalloonCtrl VBoxHeadless VBoxManage VBoxVRDP vboxautostart vboxballoonctrl vboxheadless vboxmanage vboxwebsrv
	@${LN} -fsv /usr/local/lib/virtualbox/VBox.sh ${NAS4FREE_ROOTFS}/usr/local/bin/${file}
.endfor
# lib
	@${RM} -rf ${NAS4FREE_ROOTFS}/usr/local/lib/virtualbox
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/virtualbox ${NAS4FREE_ROOTFS}/usr/local/lib
.for file in VBoxHeadless VBoxNetAdpCtl VBoxNetDHCP
	chown 0:920 ${NAS4FREE_ROOTFS}/usr/local/lib/virtualbox/${file}
	chmod 4510 ${NAS4FREE_ROOTFS}/usr/local/lib/virtualbox/${file}
.endfor
# share
	@${RM} -rf ${NAS4FREE_ROOTFS}/usr/local/share/virtualbox-ose
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/share/virtualbox-ose ${NAS4FREE_ROOTFS}/usr/local/share
# misc
	@${MKDIR} -p ${NAS4FREE_ROOTFS}/usr/local/lib/python2.7/site-packages
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python2.7/site-packages/vboxapi ${NAS4FREE_ROOTFS}/usr/local/lib/python2.7/site-packages
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python2.7/site-packages/xpcom ${NAS4FREE_ROOTFS}/usr/local/lib/python2.7/site-packages

.include <bsd.port.mk>
