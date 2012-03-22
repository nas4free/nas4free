# Hyper-V reboot issue patch by Daisuke Aoyama (aoyama@peach.ne.jp)
# Latest can be found at http://shell.peach.ne.jp/aoyama/
#
PATCHSITE=http://www.peach.ne.jp/archives/freenas/patches

FETCH_ARGS=-4ApRr
OSVERSION!= /sbin/sysctl -n kern.osreldate

.if ${OSVERSION} < 800000
ARCHIVE=fbs73-hv-20100528.patch.gz
.else
ARCHIVE=fbs81-hv-20100528.patch.gz
.endif

all: fetch

fetch:
	@if [ ! -f ${.CURDIR}/files/${ARCHIVE} ]; then \
		fetch ${FETCH_ARGS} -o ${.CURDIR}/files ${PATCHSITE}/${ARCHIVE}; \
	fi

clean:
	-@rm -f ${.CURDIR}/files/${ARCHIVE}

install:
	${MAKE} fetch
	(cd /usr/src; gzcat ${.CURDIR}/files/${ARCHIVE} | patch -p1)
