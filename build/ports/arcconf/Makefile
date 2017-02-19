PORTNAME=		arcconf
DISTVERSION=	v1_8_21375
CATEGORIES=		sysutils
COMMENT=		Adaptec SCSI/SAS RAID administration tool
MAINTAINER=		zoon1@nas4free.org

MASTER_SITES=	http://download.adaptec.com/raid/storage_manager/

DISTNAME=		arcconf_${DISTVERSION}
USES=			zip

ONLY_FOR_ARCHS=	i386 amd64
RESTRICTED=		May not be redistributed in binary form
NO_CDROM=		May not be redistributed in binary form
NO_BUILD=	yes
NO_WRKSUBDIR=	yes

.include <bsd.port.pre.mk>

.if ${OPSYS} != FreeBSD
IGNORE=		installs incompatible binaries for FreeBSD
.else
DIST_OSVER=	10
.endif

.if ${OPSYS} != FreeBSD
IGNORE=		installs incompatible binaries for FreeBSD
.endif

.if ${ARCH} == amd64
DIST_EXT=	_x64
.elif ${ARCH} == i386
.if ${OPSYS} == FreeBSD && ${OSVERSION} >= 1000000
# use the 9 i386 version for 10 i386
DIST_OSVER=	9
RUN_DEPENDS+=	${LOCALBASE}/lib/compat/libstdc++.so.6:misc/compat9x
.endif
.endif

do-install:
	${INSTALL_PROGRAM} -v ${WRKSRC}/freebsd${DIST_OSVER}${DIST_EXT}/cmdline/arcconf ${NAS4FREE_ROOTFS}/${PREFIX}/sbin

.include <bsd.port.post.mk>