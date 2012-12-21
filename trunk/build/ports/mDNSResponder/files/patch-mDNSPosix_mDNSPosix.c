--- mDNSPosix/mDNSPosix.c.orig	2012-04-18 01:01:01.000000000 +0200
+++ mDNSPosix/mDNSPosix.c	2012-12-20 04:18:53.000000000 +0100
@@ -503,6 +503,7 @@
             numOfServers++;
         }
     }
+    fclose(fp);
     return (numOfServers > 0) ? 0 : -1;
 }
 
