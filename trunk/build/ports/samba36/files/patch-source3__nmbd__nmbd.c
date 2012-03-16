--- ./source3/nmbd/nmbd.c.orig	2011-10-19 03:48:48.000000000 +0900
+++ ./source3/nmbd/nmbd.c	2011-11-18 07:58:19.000000000 +0900
@@ -865,6 +865,8 @@
 		exit(1);
 	}
 
+	reopen_logs();
+
 	if (nmbd_messaging_context() == NULL) {
 		return 1;
 	}
