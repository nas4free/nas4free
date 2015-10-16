COMMENT=	Ports options (located in /var/db/ports) used \
					by ports build system

MAINTAINER=		zoon01@nas4free.org

ARCH!=		uname -p

install:
	@for i in `cd files && ls *-options | sed -e 's/-options//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options /var/db/ports/$$i/options; \
	done;
.if ${ARCH} == "i386"
	@for i in `cd files && ls *-options-i386 | sed -e 's/-options-i386//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options-i386 /var/db/ports/$$i/options; \
	done;
.endif
