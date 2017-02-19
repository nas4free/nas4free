--- sys/cam/scsi/scsi_xpt.c.orig	2016-11-24 22:36:01.118651000 +0100
+++ sys/cam/scsi/scsi_xpt.c	2016-11-29 10:18:17.000000000 +0100
@@ -1600,8 +1600,8 @@
 				  sizeof(struct scsi_inquiry_data));
 
 			if (have_serialnum)
-				MD5Update(&context, serial_buf->serial_num,
-					  serial_buf->length);
+				MD5Update(&context, path->device->serial_num,
+					  path->device->serial_num_len);
 
 			MD5Final(digest, &context);
 			if (bcmp(softc->digest, digest, 16) == 0)
