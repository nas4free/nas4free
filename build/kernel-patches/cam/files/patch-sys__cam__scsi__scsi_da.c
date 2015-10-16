Index: sys/cam/scsi/scsi_da.c
===================================================================
--- sys/cam/scsi/scsi_da.c	(revision 287768)
+++ sys/cam/scsi/scsi_da.c	(working copy)
@@ -1189,6 +1189,13 @@
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
 };
 
 static	disk_strategy_t	dastrategy;
