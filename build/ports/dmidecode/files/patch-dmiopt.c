--- dmiopt.c.orig	2015-09-03 08:03:19.000000000 +0200
+++ dmiopt.c	2015-12-06 18:49:25.767930000 +0200
@@ -314,6 +314,7 @@
 		" -u, --dump             Do not decode the entries\n"
 		"     --dump-bin FILE    Dump the DMI data to a binary file\n"
 		"     --from-dump FILE   Read the DMI data from a binary file\n"
+		"     --no-sysfs         Do not attempt to read DMI data from sysfs files\n"
 		" -V, --version          Display the version and exit\n";
 
 	printf("%s", help);
