--- sys/dev/ahci/ahci.c.orig	2015-04-20 10:28:10.000000000 +0900
+++ sys/dev/ahci/ahci.c	2015-04-27 02:57:14.000000000 +0900
@@ -117,6 +117,7 @@
 #define AHCI_Q_NOCOUNT	1024
 #define AHCI_Q_ALTSIG	2048
 #define AHCI_Q_NOMSI	4096
+#define AHCI_Q_1MSI	8192
 
 #define AHCI_Q_BIT_STRING	\
 	"\020"			\
@@ -132,14 +133,15 @@
 	"\012NOAA"		\
 	"\013NOCOUNT"		\
 	"\014ALTSIG"		\
-	"\015NOMSI"
+	"\015NOMSI"		\
+	"\0161MSI"
 } ahci_ids[] = {
 	{0x43801002, 0x00, "AMD SB600",	AHCI_Q_NOMSI},
-	{0x43901002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	0},
-	{0x43911002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	0},
-	{0x43921002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	0},
-	{0x43931002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	0},
-	{0x43941002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	0},
+	{0x43901002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	AHCI_Q_1MSI},
+	{0x43911002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	AHCI_Q_1MSI},
+	{0x43921002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	AHCI_Q_1MSI},
+	{0x43931002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	AHCI_Q_1MSI},
+	{0x43941002, 0x00, "AMD SB7x0/SB8x0/SB9x0",	AHCI_Q_1MSI},
 	{0x43951002, 0x00, "AMD SB8x0/SB9x0",	0},
 	{0x78001022, 0x00, "AMD Hudson-2",	0},
 	{0x78011022, 0x00, "AMD Hudson-2",	0},
@@ -729,10 +731,13 @@
 	struct ahci_controller *ctlr = device_get_softc(dev);
 	int i;
 
-	ctlr->msi = 2;
 	/* Process hints. */
 	if (ctlr->quirks & AHCI_Q_NOMSI)
 		ctlr->msi = 0;
+	else if (ctlr->quirks & AHCI_Q_1MSI)
+		ctlr->msi = 1;
+	else
+		ctlr->msi = 2;
 	resource_int_value(device_get_name(dev),
 	    device_get_unit(dev), "msi", &ctlr->msi);
 	ctlr->numirqs = 1;
