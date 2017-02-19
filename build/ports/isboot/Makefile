PORTNAME=		isboot
PORTVERSION=		0.2.13
CATEGORIES=		net
COMMENT=		iSCSI boot driver by Daisuke Aoyama (aoyama@peach.ne.jp)

MAINTAINER=		aoyama@nas4free.org
MASTER_SITES=		http://www.peach.ne.jp/archives/isboot/

WRKSRC=                 ${WRKDIR}/${DISTNAME}/src
USES=			kmod
ONLY_FOR_ARCHS=		i386 amd64

.include <bsd.port.pre.mk>
.if ${ARCH} == "amd64"
CFLAGS+=	-DVIMAGE
.endif

do-install:
	@${INSTALL_KLD} -v ${WRKSRC}/isboot.ko ${NAS4FREE_ROOTFS}/boot/kernel

.include <bsd.port.post.mk>
