--- src/sys/cddl/contrib/opensolaris/uts/common/fs/zfs/arc.c.orig	2016-03-13 20:04:18.966628000 +0100
+++ src/sys/cddl/contrib/opensolaris/uts/common/fs/zfs/arc.c	2016-03-13 22:40:33.000000000 +0100
@@ -5303,12 +5303,37 @@
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
 
 	/*
 	 * In userland, there's only the memory pressure that we artificially
@@ -5365,6 +5390,19 @@
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
 	if (zfs_arc_num_sublists_per_state < 1)
 		zfs_arc_num_sublists_per_state = MAX(max_ncpus, 1);
 
