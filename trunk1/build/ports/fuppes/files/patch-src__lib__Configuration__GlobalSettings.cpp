--- src/lib/Configuration/GlobalSettings.cpp.orig	2011-11-13 04:25:59.000000000 +0900
+++ src/lib/Configuration/GlobalSettings.cpp	2011-11-13 20:22:04.000000000 +0900
@@ -27,6 +27,9 @@
 
   m_sTempDir = Directory::appendTrailingSlash(m_sTempDir);	
   Directory::create(m_sTempDir);
+
+  m_sDefaultDevice = "";
+  m_sDefaultVirtualFolder = "";
 }
 
 std::string GlobalSettings::GetFriendlyName()
@@ -62,6 +65,16 @@
         //appendTrailingSlash(&m_sTrashDir);
       }
     }	
+    else if(pTmp->Name().compare("default_device") == 0) {
+      if(pTmp->Value().length() > 0) {
+        m_sDefaultDevice = pTmp->Value();
+      }
+    }
+    else if(pTmp->Name().compare("default_vfolder") == 0) {
+      if(pTmp->Value().length() > 0) {
+        m_sDefaultDevice = pTmp->Value();
+      }
+    }
   }  
 
   return true;
