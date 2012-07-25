--- clients/upssched.c.orig	2012-05-11 10:29:33.000000000 +0200
+++ clients/upssched.c	2012-07-25 13:37:42.000000000 +0200
@@ -694,7 +694,7 @@
 		snprintfcat(buf, sizeof(buf), " \"%s\"",
 			pconf_encode(arg2, enc, sizeof(enc)));
 
-	snprintfcat(enc, sizeof(enc), "%s\n", buf);
+	snprintf(enc, sizeof(enc), "%s\n", buf);
 
 	/* see if the parent needs to be started (and maybe start it) */
 
