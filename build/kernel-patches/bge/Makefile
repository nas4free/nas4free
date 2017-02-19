install:
	# bge if_bge.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-if_bge.c.diff
	# bge if_bgereg.h patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-if_bgereg.h.diff
