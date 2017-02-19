install:
	# mrsas mrsas__mrsas.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mrsas__mrsas.c.diff
	# mrsas mrsas.h patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mrsas__mrsas.h.diff
	# mrsas mrsas_cam.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mrsas__mrsas_cam.c.diff
	# mrsas mrsas_fp.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mrsas__mrsas_fp.c.diff
	# mrsas mrsas_ioctl.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mrsas__mrsas_ioctl.c.diff
