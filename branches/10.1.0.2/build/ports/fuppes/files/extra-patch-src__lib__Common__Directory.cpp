--- src/lib/Common/Directory.cpp.orig	2011-08-15 17:25:54.000000000 +0900
+++ src/lib/Common/Directory.cpp	2014-12-30 07:08:29.000000000 +0900
@@ -30,6 +30,7 @@
 
 #include <sys/types.h>
 #include <sys/stat.h>
+#include <unistd.h>
 
 #ifdef WIN32
 #include <string.h>
