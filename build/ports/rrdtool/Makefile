PORTNAME=		rrdtool
PORTVERSION=		1.6.0
PORTREVISION=		0
CATEGORIES=		databases graphics

COMMENT=		Round Robin Database Tools
MAINTAINER=		zoon1@nas4free.org

USES=			perl5 python

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:databases/rrdtool
DEP_STAGEDIR=		${PORTSDIR}/databases/rrdtool/work/stage
PANGO_MODULES=		${PREFIX}/lib/pango/1.8.0/modules

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
# share
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs
	@${MKDIR} -v ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-graph.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/rrd-graph.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-start.php ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/rrd-start.php
	@${INSTALL_SCRIPT} -v ${FILESDIR}/rrd-update.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/rrd-update.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu_frequency.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu_frequency.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/cpu_temperature.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/cpu_temperature.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/disk_usage.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/disk_usage.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/latency.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/latency.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/load_averages.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/load_averages.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/memory.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/memory.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/memory-detailed.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/memory-detailed.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/network_traffic.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/network_traffic.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/processes.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/processes.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/ups.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/ups.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/uptime.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/uptime.sh
	@${INSTALL_SCRIPT} -v ${FILESDIR}/templates/zfs_arc.sh ${NAS4FREE_ROOTFS}/usr/local/share/rrdgraphs/templates/zfs_arc.sh

	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcached ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcgi ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdcreate ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdinfo ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdtool ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/rrdupdate ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/lib/librrd.so.8.0.0 ${NAS4FREE_ROOTFS}/usr/local/lib
	@${LN} -fsv /usr/local/lib/librrd.so.8.0.0 ${NAS4FREE_ROOTFS}/usr/local/lib/librrd.so
	@${LN} -fsv /usr/local/lib/librrd.so.8.0.0 ${NAS4FREE_ROOTFS}/usr/local/lib/librrd.so.8
# bindings
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs/RRDs.so ${NAS4FREE_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/auto/RRDs/
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/RRDs.pm ${NAS4FREE_ROOTFS}${SITE_PERL}/${PERL_ARCH}/${PERL_VER}/
	@${CP} -pv ${DEP_STAGEDIR}${SITE_PERL}/RRDp.pm ${NAS4FREE_ROOTFS}${SITE_PERL}/
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PYTHON_SITELIBDIR}
	@${CP} -pv ${DEP_STAGEDIR}${PYTHON_SITELIBDIR}/rrdtool.so ${NAS4FREE_ROOTFS}${PYTHON_SITELIBDIR}/
# pango
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PANGO_MODULES}
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PREFIX}/etc/pango
	@${CP} -pv ${PANGO_MODULES}/pango-arabic-lang.so ${NAS4FREE_ROOTFS}${PANGO_MODULES}
	@${CP} -pv ${PANGO_MODULES}/pango-basic-fc.so ${NAS4FREE_ROOTFS}${PANGO_MODULES}
	@${CP} -pv ${PANGO_MODULES}/pango-indic-lang.so ${NAS4FREE_ROOTFS}${PANGO_MODULES}
	@${CP} -pv ${PANGO_MODULES}/../modules.cache ${NAS4FREE_ROOTFS}${PANGO_MODULES}/../
	@${CP} -pv ${PANGO_MODULES}/../modules.cache ${NAS4FREE_ROOTFS}${PREFIX}/etc/pango/pango.modules
# fonts
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PREFIX}/share/fonts
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${MKDIR} -p ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts/conf.d
	@${RM} -rf ${NAS4FREE_ROOTFS}${PREFIX}/share/fonts/dejavu
	@${CP} -Rpv ${PREFIX}/share/fonts/dejavu ${NAS4FREE_ROOTFS}${PREFIX}/share/fonts
	@${CP} -pv ${PREFIX}/etc/fonts/fonts.conf ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts
	@${CP} -pv ${PREFIX}/etc/fonts/fonts.dtd ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts

	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans-mono.conf ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans.conf ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${CP} -pv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-serif.conf ${NAS4FREE_ROOTFS}${PREFIX}/etc/fonts/conf.avail
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans-mono.conf ${NAS4FREE_ROOTFS}/usr/local/etc/fonts/conf.d/
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-sans.conf ${NAS4FREE_ROOTFS}/usr/local/etc/fonts/conf.d/
	@${LN} -fsv ${PREFIX}/etc/fonts/conf.avail/57-dejavu-serif.conf ${NAS4FREE_ROOTFS}/usr/local/etc/fonts/conf.d/

.include <bsd.port.mk>
