# Patch number of kernel page tables to allow booting of larger compressed mfs_root images
# on systems with > 2GB RAM.
# http://kerneltrap.org/mailarchive/freebsd-current/2008/3/29/1290894

UNAME?=		/usr/bin/uname

# Get the architecture
.if !defined(ARCH)
ARCH!=	${UNAME} -p
.endif

install:
	@if [ "${ARCH}" != "amd64" ]; then \
		patch --forward --remove-empty-files --unified --directory=/sys/i386/include < ${.CURDIR}/files/patch-sys_i386_include_pmap.h.diff; \
	else \
		patch --forward --remove-empty-files --unified --directory=/sys/amd64/include < ${.CURDIR}/files/patch-sys_amd64_include_pmap.h.diff; \
	fi
