--- src/plugins/metadata_libavformat.c.orig	2011-11-01 18:50:10.000000000 +0900
+++ src/plugins/metadata_libavformat.c	2015-08-30 09:27:34.826399000 +0900
@@ -42,6 +42,10 @@
 #include <string.h>
 #include <stdarg.h>
 
+#ifndef AV_METADATA_IGNORE_SUFFIX
+#define AV_METADATA_IGNORE_SUFFIX AV_DICT_IGNORE_SUFFIX
+#endif
+
 
 /*void av_log_callback(void* ptr, int* level, const char* fmt, va_list vl)
 {
@@ -76,8 +80,13 @@
     return -1;
   }
 
+#if LIBAVFORMAT_VERSION_MAJOR < 56
 	if(av_find_stream_info(*ctx) < 0) {
 		av_close_input_file(*ctx);
+#elif LIBAVFORMAT_VERSION_MAJOR >= 56
+  if(avformat_find_stream_info(*ctx, NULL) < 0) {
+		avformat_close_input(ctx);
+#endif
 	  return -1;
 	}
 	
@@ -289,7 +298,11 @@
 
 void fuppes_metadata_file_close(plugin_info* plugin)
 {
+#if LIBAVFORMAT_VERSION_MAJOR < 56
 	av_close_input_file(plugin->user_data);
+#elif LIBAVFORMAT_VERSION_MAJOR >= 56
+	avformat_close_input(&plugin->user_data);
+#endif
 	plugin->user_data = NULL;
 }
 
