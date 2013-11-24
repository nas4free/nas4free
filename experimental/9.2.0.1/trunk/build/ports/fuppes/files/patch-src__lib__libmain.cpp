--- src/lib/libmain.cpp.orig	2011-11-13 04:25:54.000000000 +0900
+++ src/lib/libmain.cpp	2011-11-13 11:01:50.000000000 +0900
@@ -176,18 +176,26 @@
         CSharedLog::Shared()->SetLogLevel(nLogLevel, false);
         break;
       case 'm':
+#if 0
         if(file.Search(optarg)) {
           CSharedLog::SetLogFileName(optarg);
         } else {
           fileFail = true;
         }
+#else
+        CSharedLog::Shared()->SetLogFileName(optarg);
+#endif
         break;
       case 'c':
+#if 0
         if(file.Search(optarg)) {
           CSharedConfig::Shared()->SetConfigFileName(optarg);
         } else {
           fileFail = true;
         }
+#else
+        CSharedConfig::Shared()->SetConfigFileName(optarg);
+#endif
         break;
       case 'a':
         if(directory.Search(optarg)) {
