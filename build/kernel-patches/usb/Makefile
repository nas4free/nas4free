install:
	# ehci_pci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__ehci_pci.c.diff
	# ohci_pci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__ohci_pci.c.diff
	# uhci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__uhci.c.diff
	# uhci_pci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__uhci_pci.c.diff
	# xhci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__xhci.c.diff
	# xhci.h patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__xhci.h.diff
	# xhci_pci.c patch.
	@patch --forward --remove-empty-files --unified --directory=/usr/src/sys/dev < ${.CURDIR}/files/patch-sys__dev__usb__controller__xhci_pci.c.diff
