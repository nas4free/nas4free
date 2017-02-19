--- source3/modules/vfs_shadow_copy2.c.orig	2016-02-22 10:36:15.000000000 +0100
+++ source3/modules/vfs_shadow_copy2.c	2016-04-18 00:18:11.000000000 +0200
@@ -1215,7 +1215,7 @@
 					&smb_fname,
 					false,
 					SEC_DIR_LIST);
-	if (!NT_STATUS_IS_OK(status)) {
+	if (NT_STATUS_EQUAL(status, NT_STATUS_ACCESS_DENIED)) {
 		DEBUG(0,("user does not have list permission "
 			"on snapdir %s\n",
 			smb_fname.base_name));
