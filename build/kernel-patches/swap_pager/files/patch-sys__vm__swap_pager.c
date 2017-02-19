--- sys/vm/swap_pager.c.orig	2016-03-13 20:04:42.372222000 +0100
+++ sys/vm/swap_pager.c	2016-03-13 22:13:34.000000000 +0100
@@ -862,7 +862,7 @@
 	VM_OBJECT_WLOCK(object);
 	while (size) {
 		if (n == 0) {
-			n = BLIST_MAX_ALLOC;
+			n = min(BLIST_MAX_ALLOC, size); /* happy with small size */
 			while ((blk = swp_pager_getswapspace(n)) == SWAPBLK_NONE) {
 				n >>= 1;
 				if (n == 0) {
