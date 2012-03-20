--- ./os_freebsd.cpp.orig	2011-10-07 01:43:44.000000000 +0900
+++ ./os_freebsd.cpp	2011-11-17 05:27:28.000000000 +0900
@@ -1044,8 +1044,8 @@
   }
 
   if (iop->sensep) {
-    memcpy(iop->sensep,&(ccb->csio.sense_data),sizeof(struct scsi_sense_data));
-    iop->resp_sense_len = sizeof(struct scsi_sense_data);
+    iop->resp_sense_len = ccb->csio.sense_len - ccb->csio.sense_resid;
+    memcpy(iop->sensep,&(ccb->csio.sense_data),iop->resp_sense_len);
   }
 
   iop->scsi_status = ccb->csio.scsi_status;
