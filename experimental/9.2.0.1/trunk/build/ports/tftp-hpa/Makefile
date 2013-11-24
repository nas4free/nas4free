PORTNAME=		tftp-hpa
PORTVERSION=	5.2
CATEGORIES=		ftp
COMMENT=		An advanced tftp server
MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=	ftp://ftp.kernel.org/pub/software/network/tftp/tftp-hpa/

USE_BZIP2=		yes
GNU_CONFIGURE=	yes
USE_GMAKE=		yes

CONFIGURE_ARGS+=	--enable-largefile

post-patch:
	@${REINPLACE_CMD} -e 's,SBINDIR,LIBEXECDIR,; s,sbindir,libexecdir,' \
	    ${WRKSRC}/MCONFIG.in
	@${REINPLACE_CMD} -e 's,SBINDIR,LIBEXECDIR,g' \
	    ${WRKSRC}/tftpd/Makefile
	@${REINPLACE_CMD} -e 's,LOG_DAEMON,LOG_FTP,g' \
	    ${WRKSRC}/tftpd/tftpd.c

do-install:
	@${INSTALL_SCRIPT} -v ${FILESDIR}/tftpd.in ${NAS4FREE_ROOTFS}/etc/rc.d/tftpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tftpd/tftpd ${NAS4FREE_ROOTFS}/usr/local/libexec/in.tftpd

.include <bsd.port.mk>
