--- src/lib/DeviceSettings/DeviceIdentificationMgr.cpp.orig	2011-11-13 04:25:46.000000000 +0900
+++ src/lib/DeviceSettings/DeviceIdentificationMgr.cpp	2011-11-14 04:02:49.000000000 +0900
@@ -26,6 +26,7 @@
 #include "DeviceIdentificationMgr.h"
 #include "../Log.h"
 #include "../SharedConfig.h"
+#include "../Configuration/DeviceConfigFile.h"
 #include "../Configuration/DeviceMapping.h"
 #include "../Common/RegEx.h"
 #include "MacAddressTable.h"
@@ -138,6 +139,43 @@
     }
   }
 
+  // for NAS4Free
+  if(!foundMatch) {
+    string devName = CSharedConfig::Shared()->globalSettings->DefaultDevice();
+    string vFolder = CSharedConfig::Shared()->globalSettings->DefaultVirtualFolder();
+    if (!devName.empty()) {
+      CDeviceSettings* pSettings = NULL;
+      if (devName.compare("default") == 0) {
+        pSettings = m_pDefaultSettings;
+      }
+      for (list<CDeviceSettings*>::const_iterator it = m_Settings.begin(); pSettings == NULL && it != m_Settings.end(); ++it) {
+        if ((*it)->m_sDeviceName.compare(devName) == 0) {
+          pSettings = *it;
+          break;
+        }
+      }
+      if (pSettings == NULL) {
+        // new device
+        CDeviceConfigFile* devConf = new CDeviceConfigFile();
+        int error;
+
+        pSettings = new CDeviceSettings(devName, m_pDefaultSettings);
+        m_Settings.push_back(pSettings);
+        error = devConf->SetupDevice(pSettings, devName);
+        if (error) {
+#if 0
+          Log::error_(Log::config, Log::normal, __FILE__, __LINE__, "An error ocurred while trying to load '%s'. It had the error code: %d",
+            devName.c_str(),
+            error);
+#endif
+        }
+        delete devConf;
+      }
+      pDeviceMessage->DeviceSettings(pSettings);
+      pDeviceMessage->setVirtualFolderLayout(vFolder);
+      foundMatch = true;
+    }
+  }
   
   // ... just use the default device
 	if(!foundMatch) {
