--- src/plugins/metadata_libavformat.c.orig	2011-11-01 10:50:10.000000000 +0100
+++ src/plugins/metadata_libavformat.c	2014-01-07 09:11:12.000000000 +0100
@@ -42,6 +42,10 @@
 #include <string.h>
 #include <stdarg.h>
 
+#ifndef AV_METADATA_IGNORE_SUFFIX
+#define AV_METADATA_IGNORE_SUFFIX AV_DICT_IGNORE_SUFFIX
+#endif
+
 
 /*void av_log_callback(void* ptr, int* level, const char* fmt, va_list vl)
 {
