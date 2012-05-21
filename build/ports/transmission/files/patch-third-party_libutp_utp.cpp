--- third-party/libutp/utp.cpp.orig	2012-05-19 15:51:14.000000000 +0200
+++ third-party/libutp/utp.cpp	2012-05-21 23:56:59.000000000 +0200
@@ -10,6 +10,9 @@
 #include <stdlib.h>
 #include <errno.h>
 #include <limits.h> // for UINT_MAX
+#include <sys/types.h>
+#include <sys/socket.h>
+#include <netinet/in.h>
 
 #ifdef WIN32
 #include "win32_inet_ntop.h"
