--- src/plugins/database_sqlite3.cpp.orig	2011-09-22 17:45:44.000000000 +0900
+++ src/plugins/database_sqlite3.cpp	2014-12-30 07:49:55.000000000 +0900
@@ -33,6 +33,7 @@
 #include <iostream>
 
 #include <sqlite3.h>
+#include <unistd.h>
 
 #ifdef WIN32
 #include <windows.h>
