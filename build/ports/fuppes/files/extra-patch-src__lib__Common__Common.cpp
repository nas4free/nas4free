--- src/lib/Common/Common.cpp.orig	2011-09-22 17:45:44.000000000 +0900
+++ src/lib/Common/Common.cpp	2014-12-30 06:59:01.000000000 +0900
@@ -46,6 +46,7 @@
 #include <algorithm>
 #include <cctype>
 #include <time.h>
+#include <unistd.h>
 
 #ifndef WIN32
 #include <dlfcn.h>
