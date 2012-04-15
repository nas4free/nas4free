--- ./clients/lcdproc/main.c.orig	2011-10-02 03:38:55.000000000 +0900
+++ ./clients/lcdproc/main.c	2011-11-27 09:31:18.000000000 +0900
@@ -139,6 +139,25 @@
 const char *
 get_sysname(void)
 {
+#if 1
+	/* NAS4Free */
+	static char buf[1024];
+	char buf1[1024];
+	FILE *fp;
+	char *p;
+
+	fp = fopen("/etc/prd.name", "r");
+	if (fp != NULL) {
+		fgets(buf1, sizeof buf1, fp);
+		fclose(fp);
+		p = strchr(buf1, '\n');
+		if (p != NULL) {
+			*p = '\0';
+		}
+		snprintf(buf, sizeof buf, "%s", buf1);
+		return (buf);
+	}
+#endif
 	return (unamebuf.sysname);
 }
 
@@ -146,6 +165,34 @@
 const char *
 get_sysrelease(void)
 {
+#if 1
+	/* NAS4Free */
+	static char buf[1024];
+	char buf1[1024], buf2[1024];
+	FILE *fp;
+	char *p;
+
+	fp = fopen("/etc/prd.version", "r");
+	if (fp != NULL) {
+		fgets(buf1, sizeof buf1, fp);
+		fclose(fp);
+		p = strchr(buf1, '\n');
+		if (p != NULL) {
+			*p = '\0';
+		}
+		fp = fopen("/etc/prd.revision", "r");
+		if (fp != NULL) {
+			fgets(buf2, sizeof buf2, fp);
+			fclose(fp);
+			p = strchr(buf2, '\n');
+			if (p != NULL) {
+				*p = '\0';
+			}
+			snprintf(buf, sizeof buf, "%s (%s)", buf1, buf2);
+			return (buf);
+		}
+	}
+#endif
 	return (unamebuf.release);
 }
 
