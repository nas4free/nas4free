--- src/lib/Configuration/PathFinder.cpp.orig	2011-11-13 04:25:59.000000000 +0900
+++ src/lib/Configuration/PathFinder.cpp	2011-11-14 02:52:26.000000000 +0900
@@ -91,6 +91,10 @@
   string lsData;
   string lsConfig;
   
+  // for NAS4Free
+  env = getenv("HOME");
+  if(env == NULL || strlen(env) == 0)
+    setenv("HOME", "/root", 0);
   
   // XDG_DATA_HOME :: if $XDG_DATA_HOME is either not set or empty, a default equal to $HOME/.local/share should be used.
   env = getenv("XDG_DATA_HOME");
