--- src/dyndns.h.orig	2015-05-26 19:25:30.000000000 +0200
+++ src/dyndns.h	2015-06-05 09:37:00.000000000 +0200
@@ -110,11 +110,11 @@
 
 /*test values*/
 #define DYNDNS_DEFAULT_DEBUG_LEVEL		LOG_WARNING
-#define DYNDNS_MT_DEFAULT_CONFIG_FILE_OLD	"/etc/inadyn-mt/inadyn-mt.conf"
-#define DYNDNS_MT_DEFAULT_CONFIG_FILE		"/etc/inadyn-mt.conf"
+#define DYNDNS_MT_DEFAULT_CONFIG_FILE_OLD	"%%PREFIX%%/etc/inadyn-mt/inadyn-mt.conf"
+#define DYNDNS_MT_DEFAULT_CONFIG_FILE		"%%PREFIX%%/etc/inadyn-mt.conf"
 
 /*inadyn backward compatibility*/
-#define DYNDNS_DEFAULT_CONFIG_FILE		"/etc/inadyn.conf"
+#define DYNDNS_DEFAULT_CONFIG_FILE		"%%PREFIX%%/etc/inadyn.conf"
 
 #define DYNDNS_DEFAULT_CACHE_PREFIX		"/tmp/"
 #define DYNDNS_DEFAULT_IP_FILE			"inadyn_ip.cache"
