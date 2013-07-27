--- ./m4/nut_check_libfreeipmi.m4.orig	2012-07-31 19:38:56.000000000 +0200
+++ ./m4/nut_check_libfreeipmi.m4	2012-09-19 20:35:44.000000000 +0200
@@ -66,7 +66,6 @@
 	dnl when version cannot be tested (prior to 1.0.5, with no pkg-config)
 	dnl we have to check for some specific functions
 	AC_SEARCH_LIBS([ipmi_ctx_find_inband], [freeipmi], [], [nut_have_freeipmi=no])
-	AC_SEARCH_LIBS([ipmi_fru_parse_ctx_create], [freeipmi], [], [nut_have_freeipmi=no])
 
 	AC_SEARCH_LIBS([ipmi_monitoring_init], [ipmimonitoring], [nut_have_freeipmi_monitoring=yes], [nut_have_freeipmi_monitoring=no])
 	AC_SEARCH_LIBS([ipmi_monitoring_sensor_read_record_id], [ipmimonitoring], [], [nut_have_freeipmi_monitoring=no])
