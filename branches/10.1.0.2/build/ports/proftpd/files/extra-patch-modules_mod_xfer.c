--- modules/mod_xfer.c.orig	2015-05-28 02:25:54.000000000 +0200
+++ modules/mod_xfer.c	2015-06-10 08:47:38.000000000 +0200
@@ -43,6 +43,8 @@
 # define PRIO_MAX	20
 #endif
 
+#include "mod_clamav.h"
+
 extern module auth_module;
 extern pid_t mpid;
 
@@ -1838,6 +1840,11 @@
       return PR_ERROR(cmd);
     }
 
+	if (clamav_scan(cmd)) {
+		pr_data_close(FALSE);
+		return PR_ERROR(cmd);
+	}
+
     if (session.xfer.path &&
         session.xfer.path_hidden) {
       if (pr_fsio_rename(session.xfer.path_hidden, session.xfer.path) != 0) {
