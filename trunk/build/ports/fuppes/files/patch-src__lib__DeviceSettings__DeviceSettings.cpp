--- src/lib/DeviceSettings/DeviceSettings.cpp.orig	2011-11-13 04:25:46.000000000 +0900
+++ src/lib/DeviceSettings/DeviceSettings.cpp	2011-11-13 18:03:55.000000000 +0900
@@ -392,7 +392,11 @@
 
   nDefaultReleaseDelay = DEFAULT_RELEASE_DELAY;
 
+#if 0
 	m_MediaServerSettings.FriendlyName = "FUPPES %v (%h)";
+#else
+	m_MediaServerSettings.FriendlyName = "FreeNAS (%h)";
+#endif
 	m_MediaServerSettings.Manufacturer = "Ulrich Voelkel";
 	m_MediaServerSettings.ManufacturerURL = "http://www.ulrich-voelkel.de"; 	
 	m_MediaServerSettings.ModelName = "Free UPnP Entertainment Service %v";
