# Taken from: http://www.areca.com.tw/support/s_freebsd/nonraid_freebsd.htm
#
ARCHIVE=	arcsas-freebsd10-20140214.zip
SRCDIR=		${NAS4FREE_WORKINGDIR}/arcsas

install: 	clean
	@mkdir -pv ${SRCDIR}
	@tar -zxvf ${.CURDIR}/files/${ARCHIVE} -C ${SRCDIR}

	mkdir /usr/src/sys/dev/arcsas
	mkdir /usr/src/sys/modules/arcsas
	@cp -pv ${SRCDIR}/arcsas.c /usr/src/sys/dev/arcsas
	@cp -pv ${SRCDIR}/arcsas.h /usr/src/sys/dev/arcsas
	@cp -pv ${SRCDIR}/arcsas-amd64.uu /usr/src/sys/dev/arcsas
	@cp -pv ${SRCDIR}/arcsas-i386.uu /usr/src/sys/dev/arcsas

	@cp -pv ${SRCDIR}/Makefile /usr/src/sys/modules/arcsas

	patch -i files/arcsas.patch /usr/src/sys/modules/Makefile

clean:
	@rm -f -r ${SRCDIR}
