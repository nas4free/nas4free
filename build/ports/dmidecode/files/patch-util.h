--- util.h.orig	2015-09-03 08:03:19.000000000 +0200
+++ util.h	2015-12-11 12:13:07.000000000 +0200
@@ -25,7 +25,7 @@
 #define ARRAY_SIZE(x) (sizeof(x)/sizeof((x)[0]))
 
 int checksum(const u8 *buf, size_t len);
-void *read_file(size_t len, const char *filename);
+void *read_file(size_t *len, const char *filename);
 void *mem_chunk(off_t base, size_t len, const char *devmem);
 int write_dump(size_t base, size_t len, const void *data, const char *dumpfile, int add);
 u64 u64_range(u64 start, u64 end);
