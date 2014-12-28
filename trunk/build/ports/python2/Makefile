PORTNAME=		python2
PORTVERSION=		${PYTHON_MAJOR_VER}
PORTREVISION=		3
CATEGORIES=		lang python

COMMENT=		Interpreted object-oriented programming language
MAINTAINER=		aoyama@nas4free.org

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/lang/python27
DEP_STAGEDIR=		${PORTSDIR}/lang/python27/work/stage

# To test python, run samba-tool in NAS4Free

do-install:
# NAS4Free install
	@echo "install for ${NAS4FREE_ROOTFS}"
# bin
.for file in python2.7
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/${file} ${NAS4FREE_ROOTFS}/usr/local/bin
.endfor

# lib
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/lib/libpython2.7.so.* ${NAS4FREE_ROOTFS}/usr/local/lib
#	find ${DEP_STAGEDIR}${PREFIX}/lib/python2.7 -name '*.pyc' -delete
#	find ${DEP_STAGEDIR}${PREFIX}/lib/python2.7 -name '*.pyo' -delete
	${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python2.7 ${NAS4FREE_ROOTFS}/usr/local/lib

.include <bsd.port.mk>
