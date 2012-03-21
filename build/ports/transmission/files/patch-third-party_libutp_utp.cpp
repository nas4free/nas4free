--- third-party/libutp/utp.cpp.orig	2012-02-15 03:00:56.511996000 +0100
+++ third-party/libutp/utp.cpp	2012-02-21 17:58:19.000000000 +0100
@@ -10,6 +10,9 @@
 #include <stdlib.h>
 #include <errno.h>
 #include <limits.h> // for UINT_MAX
+#include <sys/types.h>
+#include <sys/socket.h>
+#include <netinet/in.h>
 
 #ifdef WIN32
 #include "win32_inet_ntop.h"
