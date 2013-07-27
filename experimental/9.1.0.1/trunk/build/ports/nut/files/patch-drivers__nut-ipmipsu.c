--- ./drivers/nut-ipmipsu.c.orig	2012-07-31 19:38:59.000000000 +0200
+++ ./drivers/nut-ipmipsu.c	2012-10-04 23:50:52.000000000 +0200
@@ -27,7 +27,7 @@
 #include "nut-ipmi.h"
 
 #define DRIVER_NAME	"IPMI PSU driver"
-#define DRIVER_VERSION	"0.07"
+#define DRIVER_VERSION	"0.30"
 
 /* driver description structure */
 upsdrv_info_t upsdrv_info = {
@@ -183,17 +183,20 @@
 		"Type of the device to match ('psu' for \"Power Supply\")");
 	
 	addvar(VAR_VALUE, "serial", "Serial number to match a specific device");
-	addvar(VAR_VALUE, "fruid", "FRU identifier to match a specific device");
-	addvar(VAR_VALUE, "sensorid", "Sensor identifier to match a specific device"); */
+	addvar(VAR_VALUE, "fruid", "FRU identifier to match a specific device"); */
 }
 
 void upsdrv_initups(void)
 {
 	upsdebugx(1, "upsdrv_initups...");
 
-	/* port can be expressed using:
-	 * "id?" for device (FRU) ID 0x?
-	 * "psu?" for PSU number ?
+	/* port can be expressed in various forms:
+	 * - inband:
+	 *   "id?" for device (FRU) ID 0x?
+	 *   "psu?" for PSU number ?
+	 * - out of band
+	 *   "id?@host"
+	 *   "host" => requires serial or ...
 	 */ 
 	if (!strncmp( device_path, "id", 2))
 	{
