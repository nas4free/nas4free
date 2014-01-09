# bge driver patch for freebsd 9.2
# see http://svnweb.freebsd.org/base?view=revision&revision=258967
#
install:
	# bge driver patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-if_bge.c.diff
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-if_bgereg.h.diff
