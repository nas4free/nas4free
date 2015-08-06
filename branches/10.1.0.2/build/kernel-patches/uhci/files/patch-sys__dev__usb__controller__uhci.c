--- sys/dev/usb/controller/uhci.c.orig	2015-04-20 10:28:19.000000000 +0900
+++ sys/dev/usb/controller/uhci.c	2015-05-11 05:52:58.000000000 +0900
@@ -1478,7 +1478,8 @@
 	    UHCI_STS_USBEI |
 	    UHCI_STS_RD |
 	    UHCI_STS_HSE |
-	    UHCI_STS_HCPE);
+	    UHCI_STS_HCPE |
+	    UHCI_STS_HCH);
 
 	if (status == 0) {
 		/* nothing to acknowledge */
