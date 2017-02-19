--- util.c.orig	2015-09-03 08:03:19.000000000 +0200
+++ util.c	2015-12-11 12:11:38.000000000 +0200
@@ -94,10 +94,11 @@
  * needs to be freed by the caller.
  * This provides a similar usage model to mem_chunk()
  *
- * Returns pointer to buffer of max_len bytes, or NULL on error
+ * Returns pointer to buffer of max_len bytes, or NULL on error, and
+ * sets max_len to the length actually read.
  *
  */
-void *read_file(size_t max_len, const char *filename)
+void *read_file(size_t *max_len, const char *filename)
 {
 	int fd;
 	size_t r2 = 0;
@@ -115,7 +116,7 @@
 		return(NULL);
 	}
 
-	if ((p = malloc(max_len)) == NULL)
+	if ((p = malloc(*max_len)) == NULL)
 	{
 		perror("malloc");
 		return NULL;
@@ -123,7 +124,7 @@
 
 	do
 	{
-		r = read(fd, p + r2, max_len - r2);
+		r = read(fd, p + r2, *max_len - r2);
 		if (r == -1)
 		{
 			if (errno != EINTR)
@@ -140,6 +141,8 @@
 	while (r != 0);
 
 	close(fd);
+	*max_len = r2;
+
 	return p;
 }
 
@@ -152,6 +155,7 @@
 	void *p;
 	int fd;
 #ifdef USE_MMAP
+	struct stat statbuf;
 	off_t mmoffset;
 	void *mmp;
 #endif
@@ -165,10 +169,28 @@
 	if ((p = malloc(len)) == NULL)
 	{
 		perror("malloc");
-		return NULL;
+		goto out;
 	}
 
 #ifdef USE_MMAP
+	if (fstat(fd, &statbuf) == -1)
+	{
+		fprintf(stderr, "%s: ", devmem);
+		perror("stat");
+		goto err_free;
+	}
+
+	/*
+	 * mmap() will fail with SIGBUS if trying to map beyond the end of
+	 * the file.
+	 */
+	if (S_ISREG(statbuf.st_mode) && base + (off_t)len > statbuf.st_size)
+	{
+		fprintf(stderr, "mmap: Can't map beyond end of file %s\n",
+			devmem);
+		goto err_free;
+	}
+
 #ifdef _SC_PAGESIZE
 	mmoffset = base % sysconf(_SC_PAGESIZE);
 #else
@@ -199,19 +221,17 @@
 	{
 		fprintf(stderr, "%s: ", devmem);
 		perror("lseek");
-		free(p);
-		return NULL;
+		goto err_free;
 	}
 
-	if (myread(fd, p, len, devmem) == -1)
-	{
-		free(p);
-		return NULL;
-	}
+	if (myread(fd, p, len, devmem) == 0)
+		goto out;
+
+err_free:
+	free(p);
+	p = NULL;
 
-#ifdef USE_MMAP
 out:
-#endif
 	if (close(fd) == -1)
 		perror(devmem);
 
