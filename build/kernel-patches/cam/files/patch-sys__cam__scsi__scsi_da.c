--- sys/cam/scsi/scsi_da.c.orig	2016-03-13 20:04:37.858036000 +0100
+++ sys/cam/scsi/scsi_da.c	2016-03-13 21:43:27.000000000 +0100
@@ -1190,6 +1190,14 @@
 		{ T_DIRECT, SIP_MEDIA_REMOVABLE, "MX", "MXUB3*", "*"},
 		/*quirks*/DA_Q_NO_RC16
 	},
+	{
+		/*
+		 * Silicon Power USB-Stick
+		 */
+		{ T_DIRECT, SIP_MEDIA_REMOVABLE, "UFD*", "Silicon-Power*", "*"},
+		/*quirks*/DA_Q_NO_RC16
+	},
+
 };
 
 static	disk_strategy_t	dastrategy;
