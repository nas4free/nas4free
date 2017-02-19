install:
	# ahci_pci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__ahci_ahci_pci.c.diff
