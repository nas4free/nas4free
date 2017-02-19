Index: src/istgt_iscsi.c
===================================================================
--- src/istgt_iscsi.c	(revision 159)
+++ src/istgt_iscsi.c	(working copy)
@@ -3416,6 +3416,10 @@
 				|| lu_cmd->status == ISTGT_SCSI_STATUS_INTERMEDIATE_CONDITION_MET)) {
 				S_bit = 1;
 				sent_status = 1;
+			} else if (residual_len != 0) {
+				/* residual count exists */
+				S_bit = 1;
+				sent_status = 1;
 			}
 		} else {
 			F_bit = 0;
@@ -3863,7 +3867,8 @@
 	bidi_residual_len = residual_len = 0;
 	data_len = lu_cmd->data_len;
 	if (transfer_len != 0
-	    && lu_cmd->status == ISTGT_SCSI_STATUS_GOOD) {
+	    && (lu_cmd->status == ISTGT_SCSI_STATUS_GOOD
+		|| lu_cmd->sense_data_len != 0)) {
 		if (data_len < transfer_len) {
 			/* underflow */
 			ISTGT_TRACELOG(ISTGT_TRACE_DEBUG, "Underflow %zu/%u\n",
