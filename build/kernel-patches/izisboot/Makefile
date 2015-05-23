# iSCSI boot driver version by Daisuke Aoyama (aoyama@peach.ne.jp)
# Latest can be found at http://shell.peach.ne.jp/aoyama/
#
PATCHSITE=http://www.peach.ne.jp/archives/isboot

FETCH_ARGS=-4ApRr
OSVERSION!=/sbin/sysctl -n kern.osreldate

#.if ${OSVERSION} < 800000
#.else
#.endif
ARCHIVE=isboot-0.2.6.tar.gz

all: fetch

fetch:
	@if [ ! -f ${.CURDIR}/files/${ARCHIVE} ]; then \
		fetch ${FETCH_ARGS} -o ${.CURDIR}/files ${PATCHSITE}/${ARCHIVE}; \
	fi

clean:
	-@rm -f ${.CURDIR}/files/${ARCHIVE}

install:
	${MAKE} fetch
	@tar -zxvf ${.CURDIR}/files/${ARCHIVE} -C /usr/src
