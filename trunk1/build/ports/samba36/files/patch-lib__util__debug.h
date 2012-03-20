--- ./lib/util/debug.h.orig	2011-10-19 03:48:48.000000000 +0900
+++ ./lib/util/debug.h	2011-11-10 07:17:59.000000000 +0900
@@ -201,7 +201,7 @@
 
 struct debug_settings {
 	size_t max_log_size;
-	bool syslog;
+	int syslog;
 	bool syslog_only;
 	bool timestamp_logs;
 	bool debug_prefix_timestamp;
