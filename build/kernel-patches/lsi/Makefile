install:
	# mps mpr_sas.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mpr__mpr_sas.c.diff
	# mps mps_sas.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-mps__mps_sas.c.diff
