PORTNAME=		isboot
PORTVERSION=		0.2.8
CATEGORIES=		net
COMMENT=		iSCSI boot driver by Daisuke Aoyama (aoyama@peach.ne.jp)

MAINTAINER=		aoyama@nas4free.org
MASTER_SITES=		http://www.peach.ne.jp/archives/isboot/

WRKSRC=                 ${WRKDIR}/${DISTNAME}/src

.include <bsd.port.pre.mk>
.if ${ARCH} == "amd64"
post-patch:
	echo "CFLAGS += -DVIMAGE" >> ${WRKSRC}/Makefile
.endif

do-build:
	(cd ${WRKSRC}; make all)

do-install:
	@${INSTALL_KLD} -v ${WRKSRC}/isboot.ko ${NAS4FREE_ROOTFS}/boot/kernel

.include <bsd.port.post.mk>
