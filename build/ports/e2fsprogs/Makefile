PORTNAME=		e2fsprogs
PORTVERSION=	1.42.10
PORTREVISION?= 0
CATEGORIES=		sysutils
COMMENT=		Utilities & library to manipulate ext2/3/4 filesystems

MAINTAINER=		zoon01@nas4free.org

MASTER_SITES=		KERNEL_ORG/linux/kernel/people/tytso/${PORTNAME}/v${PORTVERSION}

PATCH_STRIP=	  -p1
NO_STAGE=       yes
USES=           gmake pkgconfig tar:xz
USE_CSTD=	  gnu99
USE_LDCONFIG=	  yes
GNU_CONFIGURE=  yes
MAKE_JOBS_SAFE= yes
CONFIGURE_ARGS+=	--disable-fsck --disable-e2initrd-helper \
					--enable-quota \
					--with-root-prefix='${PREFIX}'
#CONFIGURE_ENV+=        CPPFLAGS='${CPPFLAGS} -I${WRKSRC}/lib \ 
   #               -I${LOCALBASE}/include -D_EXT2_USE_C_VERSIONS_' 

CONFIGURE_ENV+=		CPPFLAGS='${CPPFLAGS} -I${WRKSRC}/lib \
									-I${LOCALBASE}/include'
MAKE_ARGS+=     LDFLAGS='${LDFLAGS} -L${LOCALBASE}/lib' pkgconfigdir='${PREFIX}/libdata/pkgconfig'

.include <bsd.port.pre.mk>

USE_GETTEXT=	yes

post-patch::
# don't build/install libext2fs.info
	@${REINPLACE_CMD} -e 's/ install-doc-libs$$//' ${WRKSRC}/Makefile.in
	@${REINPLACE_CMD} -E -e 's/md5sum ([^ ]*)/printf "%s  %s\\n" $$(md5 -q \1) \1/' \
	    -e "s/ == 0/ = 0/" ${WRKSRC}/tests/[a-t]_*/script
# disable f_mmp_garbage, fails on FreeBSD, and the resize*big_expand tests,
# which are too unwieldy to run automatically (need too much free space).
# f_extent_oobounds fails in Tinderbox and is fine outside, reason unclear.
.for i in f_mmp_garbage m_bigjournal r_64bit_big_expand r_bigalloc_big_expand r_ext4_big_expand
	@${MV} ${WRKSRC}/tests/${i} ${WRKSRC}/tests/disabled_test-${i}
.endfor

.if ${MASTERDIR} == ${.CURDIR}
pre-build:
	# fix up Makefile ordering for parallel builds
	cd ${WRKSRC}/lib/et && ${DO_MAKE_BUILD} compile_et
	cd ${WRKSRC}/e2fsck && ${DO_MAKE_BUILD} prof_err.h

post-build:
# Relink e2fsck statically - We need to make sure that tools for the root file
# system are statically linked against anything that is outside the root fs,
# else we're in trouble if e2fsck is needed for boot:
# (we don't use e2fsck.static, since we can link libc.so dynamically)
	cd ${WRKSRC}/e2fsck && ${RM} -f e2fsck \
		&& ${GMAKE} e2fsck \
		STATIC_LIBS="../lib/libext2fs.a ../lib/libcom_err.a \
		../lib/libblkid.a  ../lib/libuuid.a" \
		LIBINTL="${LOCALBASE}/lib/libintl.a ${LOCALBASE}/lib/libiconv.a"
# Regression check: avoid a port (not upstream!) regression from 1.40.5,
# check that e2fsck isn't dynalinked against anything but libc.so:
	@${ECHO_CMD} -n "===>  checking that e2fsck depends on no shared objects beyond libc.so: "
	@a="$$(ldd ${WRKSRC}/e2fsck/e2fsck 2>/dev/null \
	    | ${GREP} -v 'not a dynamic executable' \
	    | ${GREP} '=>' \
	    | ${AWK}  '{print $$3;}' \
	    | ${GREP} -v '^/lib/libc\.so\.' || :)"; \
	if test "x$$a" = "x" ; then echo 'PASS' ; else \
	    echo 'FAIL' ; echo '===>  e2fsck depends on:' ; echo "$$a" ; exit 1 ; fi
# Build fsck(8) wrapper
	${CC} ${CPPFLAGS} ${CFLAGS} ${LDFLAGS} -s ${LIBS} \
		-o ${WRKSRC}/fsck_ext2fs ${FILESDIR}/fsck_ext2fs.c
.endif

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/e2fsck/e2fsck ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/misc/mke2fs ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/misc/dumpe2fs ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/misc/tune2fs ${NAS4FREE_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/fsck_ext2fs ${NAS4FREE_ROOTFS}/usr/local/sbin

	@${LN} -s -f -v /usr/local/sbin/e2fsck ${NAS4FREE_ROOTFS}/sbin/e2fsck
	@${LN} -s -f -v /usr/local/sbin/fsck_ext2fs ${NAS4FREE_ROOTFS}/sbin/fsck_ext2fs

.include <bsd.port.post.mk>
