--- src/sys/cddl/contrib/opensolaris/uts/common/fs/zfs/arc.c.orig	2015-07-12 15:29:17.258890000 +0900
+++ src/sys/cddl/contrib/opensolaris/uts/common/fs/zfs/arc.c	2015-07-12 15:41:25.622642000 +0900
@@ -4163,12 +4163,37 @@
 #endif	/* sun */
 	/* set min cache to 1/32 of all memory, or 16MB, whichever is more */
 	arc_c_min = MAX(arc_c / 4, 16 << 20);
+#if 0
 	/* set max to 1/2 of all memory, or all but 1GB, whichever is more */
 	if (arc_c * 8 >= 1 << 30)
 		arc_c_max = (arc_c * 8) - (1 << 30);
 	else
 		arc_c_max = arc_c_min;
 	arc_c_max = MAX(arc_c * 5, arc_c_max);
+#endif
+#if 1
+	/* for NAS4Free tuning */
+	/* max = pmem - 4GB if pmem >= 8GB */
+	/* max = 2GB if pmem >= 4GB */
+	/* max = 1GB if pmem >= 2GB */
+	/* max = 512MB if pmem >= 1.5GB */
+	/* max = 256MB if pmem >= 1GB */
+	/* otherwise adjust to small */
+	if (((uint64_t)physmem * PAGESIZE) >= (8192UL * (1<<20)))
+		arc_c_max = ((uint64_t)physmem * PAGESIZE) - (4096UL * (1<<20));
+	else if (((uint64_t)physmem * PAGESIZE) >= (4096UL * (1<<20)))
+		arc_c_max = (2048UL * (1<<20));
+	else if (((uint64_t)physmem * PAGESIZE) >= (2048UL * (1<<20)))
+		arc_c_max = (1024UL * (1<<20));
+	else if (((uint64_t)physmem * PAGESIZE) >= (1536UL * (1<<20)))
+		arc_c_max = (512UL * (1<<20));
+	else if (((uint64_t)physmem * PAGESIZE) >= (1024UL * (1<<20)))
+		arc_c_max = (256UL * (1<<20));
+	else if (((uint64_t)physmem * PAGESIZE) >= (512UL * (1<<20)))
+		arc_c_max = (128UL * (1<<20));
+	else
+		arc_c_max = MIN(arc_c_min, (32UL * (1<<20)));
+#endif
 
 #ifdef _KERNEL
 	/*
@@ -4203,6 +4228,19 @@
 	if (zfs_arc_p_min_shift > 0)
 		arc_p_min_shift = zfs_arc_p_min_shift;
 
+#if 1
+	/* for NAS4Free tuning */
+	if (arc_c_max >= (2048UL * (1<<20)) && arc_c_min < ((arc_c_max * 1) / 2)
+	    && zfs_arc_min == 0)
+		arc_c_min = (arc_c_max * 1) / 2;
+	else if (arc_c_max >= (512UL * (1<<20)) && arc_c_min < ((arc_c_max * 1) / 3)
+	    && zfs_arc_min == 0)
+		arc_c_min = (arc_c_max * 1) / 3;
+	else if (arc_c_max >= (256UL * (1<<20)) && arc_c_min < ((arc_c_max * 1) / 4)
+	    && zfs_arc_min == 0)
+		arc_c_min = (arc_c_max * 1) / 4;
+#endif
+
 	/* if kmem_flags are set, lets try to use less memory */
 	if (kmem_debugging())
 		arc_c = arc_c / 2;
