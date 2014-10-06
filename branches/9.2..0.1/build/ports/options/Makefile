COMMENT=	Ports options (located in /var/db/ports) used \
					by ports build system

MAINTAINER=		zoon01@nas4free.org

install:
	@for i in `cd files && ls *-options | sed -e 's/-options//'`; do \
		mkdir -pv /var/db/ports/$$i; \
		cp -pv files/$$i-options /var/db/ports/$$i/options; \
	done;
