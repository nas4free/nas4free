--- src/lib/Configuration/GlobalSettings.h.orig	2011-11-13 04:25:59.000000000 +0900
+++ src/lib/Configuration/GlobalSettings.h	2011-11-13 20:21:24.000000000 +0900
@@ -16,6 +16,8 @@
 		void SetTempDir(std::string p_sTempDir) { m_sTempDir = p_sTempDir; }
 		std::string GetTempDir() { return m_sTempDir; }
 		std::string TrashDir() { return m_sTrashDir; }
+		std::string DefaultDevice() { return m_sDefaultDevice; }
+		std::string DefaultVirtualFolder() { return m_sDefaultVirtualFolder; }
 
     std::string GetFriendlyName(void);
     void        SetFriendlyName(std::string p_sFriendlyName) { m_sFriendlyName = p_sFriendlyName; }
@@ -28,6 +30,8 @@
 		std::string		m_sTempDir;
 		bool					m_useFixedUUID;
 		std::string   m_sTrashDir;
+		std::string   m_sDefaultDevice;
+		std::string   m_sDefaultVirtualFolder;
 };
 
 #endif
