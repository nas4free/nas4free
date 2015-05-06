--- xmd.c.orig	2015-03-31 15:02:57.000000000 +0900
+++ xmd.c	2015-05-06 14:23:05.497583000 +0900
@@ -61,7 +61,7 @@
 #include <vm/swap_pager.h>
 
 #include <geom/geom.h>
-#include <net/zlib.h>
+#include <sys/zlib.h>
 
 #include "lz4.h"
 #include "lz4hc.h"
