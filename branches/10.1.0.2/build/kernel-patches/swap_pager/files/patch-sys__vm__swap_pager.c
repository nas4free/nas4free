Index: sys/vm/swap_pager.c
===================================================================
--- sys/vm/swap_pager.c	(revision 274088)
+++ sys/vm/swap_pager.c	(working copy)
@@ -858,7 +858,7 @@
 	VM_OBJECT_WLOCK(object);
 	while (size) {
 		if (n == 0) {
-			n = BLIST_MAX_ALLOC;
+			n = min(BLIST_MAX_ALLOC, size); /* happy with small size */
 			while ((blk = swp_pager_getswapspace(n)) == SWAPBLK_NONE) {
 				n >>= 1;
 				if (n == 0) {
