--- src/mod_dirlisting.c.orig	2015-07-26 12:36:36.000000000 +0200
+++ src/mod_dirlisting.c	2015-08-22 07:42:44.000000000 +0200
@@ -614,7 +614,7 @@
 			"<div class=\"foot\">"
 		));
 
-		if (buffer_string_is_empty(p->conf.set_footer)) {
+		if (!buffer_string_is_empty(p->conf.set_footer)) {
 			buffer_append_string_buffer(out, p->conf.set_footer);
 		} else if (buffer_is_empty(con->conf.server_tag)) {
 			buffer_append_string_len(out, CONST_STR_LEN(PACKAGE_DESC));
