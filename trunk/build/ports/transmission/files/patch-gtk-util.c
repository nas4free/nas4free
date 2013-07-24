--- ./gtk/util.c.orig	2013-07-18 05:24:41.020228000 +0200
+++ ./gtk/util.c	2013-07-24 19:21:27.000000000 +0200
@@ -361,21 +361,11 @@
 void
 gtr_open_file (const char * path)
 {
-  char * uri;
-
-  if (g_path_is_absolute (path))
-    {
-      uri = g_strdup_printf ("file://%s", path);
-    }
-  else
-    {
-      char * cwd = g_get_current_dir ();
-      uri = g_strdup_printf ("file://%s/%s", cwd, path);
-      g_free (cwd);
-    }
-
+  GFile * file = g_file_new_for_path (path);
+  gchar * uri = g_file_get_uri (file);
   gtr_open_uri (uri);
   g_free (uri);
+  g_object_unref (file);
 }
 
 void
