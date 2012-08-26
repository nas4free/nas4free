PORTNAME=		samba36
PORTVERSION=	  3.6.7
#PORTREVISION?= 0
CATEGORIES?=		net
MASTER_SITES=		${MASTER_SITE_SAMBA}
MASTER_SITE_SUBDIR=	. old-versions rc pre
DISTNAME=		${PORTNAME:S|36$||}-${PORTVERSION:S|.p|pre|:S|.r|rc|:S|.t|tp|:S|.a|alpha|}

MAINTAINER?=		zoon01@nas4free.org
COMMENT?=		A free SMB and CIFS client and server for UNIX

# Additional patches from Sernet.de
#PATCH_STRIP=		-p1
#EXTRA_PATCHES=		${PATCHDIR}/sernet.patch

LATEST_LINK=		${PKGNAMEPREFIX}${PORTNAME}${PKGNAMESUFFIX}
EXAMPLESDIR=		${PREFIX}/share/examples/${PKGNAMEPREFIX}${PORTNAME}${PKGNAMESUFFIX}
PATCH_WRKSRC=		${WRKDIR}/${DISTNAME}
WRKSRC=			${WRKDIR}/${DISTNAME}/source3
IPATHS=			-I${WRKDIR}/${DISTNAME}/source3/m4 -I${WRKDIR}/${DISTNAME}/m4 -I${WRKDIR}/${DISTNAME}/lib/replace -I${WRKDIR}/${DISTNAME}/source4
AUTOHEADER_ARGS=	${IPATHS}
AUTOCONF_ARGS=		${IPATHS}

USE_GMAKE=		yes
USE_ICONV=		yes
GNU_CONFIGURE=	yes
USE_AUTOTOOLS=	autoconf autoheader
# directories
VARDIR?=		/var
SAMBA_SPOOL?=		${VARDIR}/spool/samba
SAMBA_LOGDIR=		${VARDIR}/log/samba
SAMBA_RUNDIR=		${VARDIR}/run
SAMBA_LOCKDIR?=	${VARDIR}/db/samba
SAMBA_CONFIG?=	smb.conf
SAMBA_CONFDIR?=	${VARDIR}/etc
SAMBA_LIBDIR=		/usr/local/lib
SAMBA_MODULEDIR=	${SAMBA_LIBDIR}/samba
SAMBA_INCLUDEDIR=	/usr/local/include/samba
SAMBA_PRIVATEDIR?=	${VARDIR}/etc/private

CONFIGURE_ARGS+=	--exec-prefix="${PREFIX}" \
			--sysconfdir="${SAMBA_CONFDIR}" \
			--with-configdir="${SAMBA_CONFDIR}" \
			--includedir="${SAMBA_INCLUDEDIR}" \
			--datadir="${DATADIR}" \
			--libdir="${SAMBA_LIBDIR}" \
			--with-modulesdir="${SAMBA_MODULEDIR}" \
			--with-pammodulesdir="${SAMBA_LIBDIR}" \
			--localstatedir="${VARDIR}" \
			--with-piddir="${SAMBA_RUNDIR}" \
			--with-ncalrpcdir="${SAMBA_RUNDIR}/ncalrpc" \
			--with-lockdir="${SAMBA_LOCKDIR}" \
			--with-statedir="${SAMBA_LOCKDIR}" \
			--with-cachedir="${SAMBA_LOCKDIR}" \
			--with-privatedir="${SAMBA_PRIVATEDIR}" \
			--with-logfilebase="${SAMBA_LOGDIR}"

CPPFLAGS+=		-I${LOCALBASE}/include
LDFLAGS+=		-L${LOCALBASE}/lib
CONFIGURE_ENV+=		CPPFLAGS="${CPPFLAGS}" LDFLAGS="${LDFLAGS}"
# pkg-config is used to find talloc
PKGCONFIGDIR?=		${PREFIX}/libdata/pkgconfig
PKGCONFIGDIR_REL?=	${PKGCONFIGDIR:S|^${PREFIX}/||}

USE_PKGCONFIG=          yes
LIB_DEPENDS+=		execinfo.1:${PORTSDIR}/devel/libexecinfo
BUILD_DEPENDS+=		talloc>=2.0.7:${PORTSDIR}/devel/talloc
RUN_DEPENDS+=		talloc>=2.0.7:${PORTSDIR}/devel/talloc
CONFIGURE_ARGS+=	--enable-external-libtalloc
BUILD_DEPENDS+=		tdb>=1.2.9:${PORTSDIR}/databases/tdb
RUN_DEPENDS+=		tdb>=1.2.9:${PORTSDIR}/databases/tdb
CONFIGURE_ARGS+=	--enable-external-libtdb

CONFIGURE_ARGS+=	--without-libtalloc \
			--without-libtdb \
			--with-libiconv="${LOCALBASE}" \
			--enable-as-needed \
			--disable-cups \
			--disable-fam \
			--with-winbind \
			--with-syslog \
			--with-quotas \
			--without-utmp \
			--with-pam \
			--with-pam_smbpass \
			--with-aio-support \
			--with-readline=/usr \
			--with-included-iniparser \
			--with-sendfile-support \
			--enable-largefile \
			--without-cluster-support \
			--without-libsmbclient \
			--without-libaddns \
			--without-libnetapi \
			--without-libsmbsharemodes \
			--disable-iprint \
			--disable-debug \
			--disable-socket-wrapper \
			--disable-nss-wrapper \
			--disable-developer \
			--disable-krb5developer \
			--disable-dmalloc \
			--without-profiling-data \
			--disable-swat \
			--with-acl-support \
			--without-dnsupdate \
			--disable-avahi \
			--with-included-popt

CONFIGURE_ARGS+=	--with-ads
.if defined(KRB5_HOME) && exists(${KRB5_HOME}/lib/libgssapi_krb5.so)
CONFIGURE_ARGS+=	--with-krb5="${KRB5_HOME}"
.elif defined(HEIMDAL_HOME) && exists(${HEIMDAL_HOME}/lib/libgssapi.so)
CONFIGURE_ARGS+=	--with-krb5="${HEIMDAL_HOME}"
.elif exists(/usr/lib/libkrb5.so) && exists(/usr/bin/krb5-config)
CONFIGURE_ARGS+=	--with-krb5="/usr"
.else
LIB_DEPENDS+=		krb5:${PORTSDIR}/security/heimdal
CONFIGURE_ARGS+=	--with-krb5="${LOCALBASE}"
.endif

USE_OPENLDAP=		yes
CONFIGURE_ARGS+=	--with-ldap

.include <bsd.port.pre.mk>

#CONFIGURE_ARGS+=	--with-shared-modules="idmap_tdb2,idmap_ad,idmap_adex,idmap_hash,idmap_rid,charset_weird"
.if ${OSVERSION} > 800000
LIB_DEPENDS+=		sunacl.1:${PORTSDIR}/sysutils/libsunacl
CONFIGURE_ARGS+=	--with-shared-modules="idmap_tdb2,idmap_ad,idmap_adex,idmap_hash,idmap_rid,charset_weird,vfs_cacheprime,vfs_commit,vfs_dirsort,vfs_syncops,vfs_zfsacl"
.else
CONFIGURE_ARGS+=	--with-shared-modules="idmap_tdb2,idmap_ad,idmap_adex,idmap_hash,idmap_rid,charset_weird,vfs_cacheprime,vfs_commit,vfs_dirsort,vfs_syncops"
.endif

post-patch:
		@${REINPLACE_CMD} -e 's|%%SAMBA_CONFIG%%|${SAMBA_CONFIG}|g' \
		    ${WRKSRC}/Makefile.in

pre-configure:
	@${FIND} ${WRKDIR}/${DISTNAME} -type d | ${XARGS} ${CHMOD} u+w,a+rx
	@${FIND} ${WRKDIR}/${DISTNAME} -type f | ${XARGS} ${CHMOD} u+w,a+r

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/samba.in ${NAS4FREE_ROOTFS}/etc/rc.d/samba
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/libwbclient.so.0 ${NAS4FREE_ROOTFS}/${SAMBA_LIBDIR}
	@${LN} -f -v -s libwbclient.so.0 ${NAS4FREE_ROOTFS}/${SAMBA_LIBDIR}/libwbclient.so

	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/nmbd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/winbindd ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/wbinfo ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/net ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/pdbedit ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbpasswd ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbstatus ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbcontrol ${NAS4FREE_ROOTFS}/usr/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbtree ${NAS4FREE_ROOTFS}/usr/bin

	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/eventlogadm ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbadd ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbdel ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbedit ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbmodify ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbrename ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/ldbsearch ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/nmblookup ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/profiles ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/sharesec ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbcacls ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbcquotas ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbget ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/smbspool ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/testparm ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/tdbbackup ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/tdbdump ${NAS4FREE_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/tdbtool ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v /usr/local/bin/tdbbackup ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v /usr/local/bin/tdbdump ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v /usr/local/bin/tdbrestore ${NAS4FREE_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v /usr/local/bin/tdbtool ${NAS4FREE_ROOTFS}/usr/local/bin

	@${MKDIR} -v ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/charset
	@${MKDIR} -v ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/rpc
	@${MKDIR} -v ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/pdb
	@${MKDIR} -v ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${MKDIR} -v ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs

	@${CP} -pv ${WRKSRC}/bin/CP*.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/charset
	@${CP} -pv ${WRKDIR}/${DISTNAME}/codepages/*.dat ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}
	@${CP} -pv ${WRKSRC}/bin/recycle.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/netatalk.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs

	@${CP} -pv $(WRKSRC)/bin/acl_tdb.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/acl_xattr.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/aio_fork.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/audit.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/cacheprime.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/cap.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/catia.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/commit.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/crossrename.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/default_quota.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/dirsort.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/expand_msdfs.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/extd_audit.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/fake_perms.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/full_audit.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/linux_xfs_sgid.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/preopen.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/readahead.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/readonly.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/scannedonly.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/shadow_copy.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/shadow_copy2.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
#	@${CP} -pv $(WRKSRC)/bin/shadow_copy3.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/smb_traffic_analyzer.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/streams_depot.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/streams_xattr.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/syncops.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/time_audit.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/weird.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
	@${CP} -pv $(WRKSRC)/bin/xattr_tdb.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
.if ${OSVERSION} > 800000
	@${CP} -pv $(WRKSRC)/bin/zfsacl.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/vfs
.endif

	@${CP} -pv ${WRKSRC}/bin/ad.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${CP} -pv ${WRKSRC}/bin/adex.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${CP} -pv ${WRKSRC}/bin/autorid.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${CP} -pv ${WRKSRC}/bin/hash.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${CP} -pv ${WRKSRC}/bin/tdb2.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap
	@${CP} -pv ${WRKSRC}/bin/rid.so ${NAS4FREE_ROOTFS}${SAMBA_MODULEDIR}/idmap

	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/pam_winbind.so ${NAS4FREE_ROOTFS}/${SAMBA_LIBDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bin/pam_smbpass.so ${NAS4FREE_ROOTFS}/${SAMBA_LIBDIR}
	@${INSTALL_PROGRAM} -v ${WRKDIR}/${DISTNAME}/nsswitch/nss_winbind.so ${NAS4FREE_ROOTFS}${SAMBA_LIBDIR}/nss_winbind.so.1
	@${INSTALL_PROGRAM} -v ${WRKDIR}/${DISTNAME}/nsswitch/nss_wins.so ${NAS4FREE_ROOTFS}${SAMBA_LIBDIR}/nss_wins.so.1
	@${LN} -f -v -s nss_winbind.so.1 ${NAS4FREE_ROOTFS}${SAMBA_LIBDIR}/nss_winbind.so
	@${LN} -f -v -s nss_wins.so.1 ${NAS4FREE_ROOTFS}${SAMBA_LIBDIR}/nss_wins.so

.include <bsd.port.post.mk>
