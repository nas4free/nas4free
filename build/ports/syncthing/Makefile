PORTNAME=		syncthing
PORTVERSION=	0.14.23
PORTREVISION=	0
DISTVERSIONPREFIX= v
CATEGORIES=	net

MAINTAINER=	zoon1@nas4free.org
COMMENT=	Encrypted file sync tool

BUILD_DEPENDS=	${LOCALBASE}/bin/go:lang/go

USE_GITHUB=	yes
GH_SUBDIR=      src/github.com/${PORTNAME}/${PORTNAME}
USE_RC_SUBR=	syncthing
# NAS4Free
#PLIST_FILES=	bin/syncthing
PORTDOCS=	*

USERS=		syncthing
GROUPS=		syncthing

STRIP=		# stripping can break go binaries

# NAS4Free
NO_PKG_REGISTER= yes

.include <bsd.port.pre.mk>

do-build:
	# timestamp here refers to source, not build time
	@cd ${WRKSRC}/src/github.com/${PORTNAME}/${PORTNAME} ; \
		${SETENV} GOPATH=${WRKSRC} go run build.go -no-upgrade=true -version v${PORTVERSION}

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/github.com/${PORTNAME}/${PORTNAME}/bin/syncthing \
		${NAS4FREE_ROOTFS}${PREFIX}/bin/
	@${INSTALL_SCRIPT} -v ${WRKDIR}/syncthing ${NAS4FREE_ROOTFS}/etc/rc.d

.include <bsd.port.post.mk>
