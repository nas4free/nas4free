--- ./source3/include/includes.h.orig	2010-04-01 22:26:22.000000000 +0900
+++ ./source3/include/includes.h	2010-05-04 16:09:34.000000000 +0900
@@ -226,6 +226,10 @@
 #include <sys/uio.h>
 #endif
 
+#ifdef HAVE_SYS_SYSCTL_H
+#include <sys/sysctl.h>
+#endif
+
 #if HAVE_LANGINFO_H
 #include <langinfo.h>
 #endif
