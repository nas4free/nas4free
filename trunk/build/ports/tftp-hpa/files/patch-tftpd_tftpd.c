
$FreeBSD: ports/ftp/tftp-hpa/files/patch-tftpd_tftpd.c,v 1.4 2009/02/13 17:28:49 brooks Exp $

--- tftpd/tftpd.c.orig
+++ tftpd/tftpd.c
@@ -355,7 +355,10 @@
     p = strrchr(argv[0], '/');
     __progname = (p && p[1]) ? p + 1 : argv[0];
 
-    openlog(__progname, LOG_PID | LOG_NDELAY, LOG_DAEMON);
+    /* syslog in localtime */
+    tzset();
+
+    openlog(__progname, LOG_PID | LOG_NDELAY, LOG_FTP);
 
     srand(time(NULL) ^ getpid());
 
