install:
	# ATA timeout patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-ata.diff
