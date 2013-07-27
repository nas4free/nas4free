ARCHIVE=	bsd4a100.zip
SRCDIR=		${NAS4FREE_WORKINGDIR}/a100

install:	clean
	@mkdir -pv ${SRCDIR}
	@tar -zxvf ${.CURDIR}/files/${ARCHIVE} -C ${SRCDIR}

	@cp -pv ${SRCDIR}/a100.* /usr/src/sys/pci

	@echo "" >> /usr/src/sys/conf/files
	@echo "pci/a100.c optional ihb device-driver" >> /usr/src/sys/conf/files

clean:
	@rm -f -r ${SRCDIR}
