--- ./drivers/nut-libfreeipmi.c.orig	2012-07-31 19:38:59.000000000 +0200
+++ ./drivers/nut-libfreeipmi.c	2012-09-19 20:35:44.000000000 +0200
@@ -42,10 +42,12 @@
 #include <stdlib.h>
 #include <string.h>
 #include "timehead.h"
+#include "common.h"
 #include <freeipmi/freeipmi.h>
 #include <ipmi_monitoring.h>
+#if HAVE_FREEIPMI_MONITORING
 #include <ipmi_monitoring_bitmasks.h>
-#include "common.h"
+#endif
 #include "nut-ipmi.h"
 #include "dstate.h"
 
@@ -57,18 +59,46 @@
 
 /* FreeIPMI contexts and configuration*/
 ipmi_ctx_t ipmi_ctx = NULL;
-ipmi_fru_parse_ctx_t fru_parse_ctx = NULL;
 ipmi_monitoring_ctx_t mon_ctx = NULL;
 struct ipmi_monitoring_ipmi_config ipmi_config;
+
 /* SDR management API has changed with 1.1.X and later */
 #ifdef HAVE_FREEIPMI_11X_12X
   ipmi_sdr_ctx_t sdr_ctx = NULL;
+  ipmi_fru_ctx_t fru_ctx = NULL;
+  #define SDR_PARSE_CTX sdr_ctx
 #else
-  ipmi_sdr_cache_ctx_t sdr_cache_ctx = NULL;
+  ipmi_sdr_cache_ctx_t sdr_ctx = NULL;
   ipmi_sdr_parse_ctx_t sdr_parse_ctx = NULL;
-#ifndef IPMI_SDR_MAX_RECORD_LENGTH
-  #define IPMI_SDR_MAX_RECORD_LENGTH IPMI_SDR_CACHE_MAX_SDR_RECORD_LENGTH
-#endif
+  #define SDR_PARSE_CTX sdr_parse_ctx
+  ipmi_fru_parse_ctx_t fru_ctx = NULL;
+  /* Functions remapping */
+  #define ipmi_sdr_ctx_create                           ipmi_sdr_cache_ctx_create
+  #define ipmi_sdr_ctx_destroy                          ipmi_sdr_cache_ctx_destroy
+  #define ipmi_sdr_ctx_errnum                           ipmi_sdr_cache_ctx_errnum
+  #define ipmi_sdr_ctx_errormsg                         ipmi_sdr_cache_ctx_errormsg
+  #define ipmi_fru_ctx_create                           ipmi_fru_parse_ctx_create
+  #define ipmi_fru_ctx_destroy                          ipmi_fru_parse_ctx_destroy
+  #define ipmi_fru_ctx_set_flags                        ipmi_fru_parse_ctx_set_flags
+  #define ipmi_fru_ctx_strerror                         ipmi_fru_parse_ctx_strerror
+  #define ipmi_fru_ctx_errnum                           ipmi_fru_parse_ctx_errnum
+  #define ipmi_fru_open_device_id                       ipmi_fru_parse_open_device_id
+  #define ipmi_fru_close_device_id                      ipmi_fru_parse_close_device_id
+  #define ipmi_fru_ctx_errormsg                         ipmi_fru_parse_ctx_errormsg
+  #define ipmi_fru_read_data_area                       ipmi_fru_parse_read_data_area
+  #define ipmi_fru_next                                 ipmi_fru_parse_next
+  #define ipmi_fru_type_length_field_to_string          ipmi_fru_parse_type_length_field_to_string
+  #define ipmi_fru_multirecord_power_supply_information ipmi_fru_parse_multirecord_power_supply_information
+  #define ipmi_fru_board_info_area                      ipmi_fru_parse_board_info_area
+  #define ipmi_fru_field_t                              ipmi_fru_parse_field_t
+  /* Constants */
+  #define IPMI_SDR_MAX_RECORD_LENGTH                               IPMI_SDR_CACHE_MAX_SDR_RECORD_LENGTH
+  #define IPMI_SDR_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST             IPMI_SDR_CACHE_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST
+  #define IPMI_FRU_AREA_SIZE_MAX                                   IPMI_FRU_PARSE_AREA_SIZE_MAX
+  #define IPMI_FRU_FLAGS_SKIP_CHECKSUM_CHECKS                      IPMI_FRU_PARSE_FLAGS_SKIP_CHECKSUM_CHECKS
+  #define IPMI_FRU_AREA_TYPE_BOARD_INFO_AREA                       IPMI_FRU_PARSE_AREA_TYPE_BOARD_INFO_AREA
+  #define IPMI_FRU_AREA_TYPE_MULTIRECORD_POWER_SUPPLY_INFORMATION  IPMI_FRU_PARSE_AREA_TYPE_MULTIRECORD_POWER_SUPPLY_INFORMATION
+  #define IPMI_FRU_AREA_STRING_MAX                                 IPMI_FRU_PARSE_AREA_STRING_MAX
 #endif /* HAVE_FREEIPMI_11X_12X */
 
 /* FIXME: freeipmi auto selects a cache based on the hostname you are
@@ -78,7 +108,7 @@
 
 /* Support functions */
 static const char* libfreeipmi_getfield (uint8_t language_code,
-	ipmi_fru_parse_field_t *field);
+	ipmi_fru_field_t *field);
 
 static void libfreeipmi_cleanup();
 
@@ -97,7 +127,7 @@
 int nut_ipmi_open(int ipmi_id, IPMIDevice_t *ipmi_dev)
 {
 	int ret = -1;
-	uint8_t areabuf[IPMI_FRU_PARSE_AREA_SIZE_MAX+1];
+	uint8_t areabuf[IPMI_FRU_AREA_SIZE_MAX+1];
 	unsigned int area_type = 0;
 	unsigned int area_length = 0;
 
@@ -134,26 +164,26 @@
 	upsdebugx(1, "FreeIPMI initialized...");
 
 	/* Parse FRU information */
-	if (!(fru_parse_ctx = ipmi_fru_parse_ctx_create (ipmi_ctx)))
+	if (!(fru_ctx = ipmi_fru_ctx_create (ipmi_ctx)))
 	{
 		libfreeipmi_cleanup();
-		fatal_with_errno(EXIT_FAILURE, "ipmi_fru_parse_ctx_create()");
+		fatal_with_errno(EXIT_FAILURE, "ipmi_fru_ctx_create()");
 	}
       
 	/* lots of motherboards calculate checksums incorrectly */
-	if (ipmi_fru_parse_ctx_set_flags (fru_parse_ctx, IPMI_FRU_PARSE_FLAGS_SKIP_CHECKSUM_CHECKS) < 0)
+	if (ipmi_fru_ctx_set_flags (fru_ctx, IPMI_FRU_FLAGS_SKIP_CHECKSUM_CHECKS) < 0)
 	{
 		libfreeipmi_cleanup();
-		fatalx(EXIT_FAILURE, "ipmi_fru_parse_ctx_set_flags: %s\n",
-			ipmi_fru_parse_ctx_strerror (ipmi_fru_parse_ctx_errnum (fru_parse_ctx)));
+		fatalx(EXIT_FAILURE, "ipmi_fru_ctx_set_flags: %s\n",
+			ipmi_fru_ctx_strerror (ipmi_fru_ctx_errnum (fru_ctx)));
 	}
 
 	/* Now open the requested (local) PSU */
-	if (ipmi_fru_parse_open_device_id (fru_parse_ctx, ipmi_id) < 0)
+	if (ipmi_fru_open_device_id (fru_ctx, ipmi_id) < 0)
 	{
 		libfreeipmi_cleanup();
-		fatalx(EXIT_FAILURE, "ipmi_fru_parse_open_device_id: %s\n",
-			ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+		fatalx(EXIT_FAILURE, "ipmi_fru_open_device_id: %s\n",
+			ipmi_fru_ctx_errormsg (fru_ctx));
 	}
 
 	/* Set IPMI identifier */
@@ -164,19 +194,19 @@
 		/* clear fields */
 		area_type = 0;
 		area_length = 0;
-		memset (areabuf, '\0', IPMI_FRU_PARSE_AREA_SIZE_MAX + 1);
+		memset (areabuf, '\0', IPMI_FRU_AREA_SIZE_MAX + 1);
 
 		/* parse FRU buffer */
-		if (ipmi_fru_parse_read_data_area (fru_parse_ctx,
+		if (ipmi_fru_read_data_area (fru_ctx,
 											&area_type,
 											&area_length,
 											areabuf,
-											IPMI_FRU_PARSE_AREA_SIZE_MAX) < 0)
+											IPMI_FRU_AREA_SIZE_MAX) < 0)
 		{
 			libfreeipmi_cleanup();
 			fatal_with_errno(EXIT_FAILURE, 
-				"ipmi_fru_parse_open_device_id: %s\n",
-				ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+				"ipmi_fru_read_data_area: %s\n",
+				ipmi_fru_ctx_errormsg (fru_ctx));
 		}
 
 		if (area_length)
@@ -184,7 +214,7 @@
 			switch (area_type)
 			{
 				/* get generic board information */
-				case IPMI_FRU_PARSE_AREA_TYPE_BOARD_INFO_AREA:
+				case IPMI_FRU_AREA_TYPE_BOARD_INFO_AREA:
 
 					if(libfreeipmi_get_board_info (areabuf, area_length,
 						ipmi_dev) < 0)
@@ -193,7 +223,7 @@
 					}
 					break;
 				/* get specific PSU information */
-				case IPMI_FRU_PARSE_AREA_TYPE_MULTIRECORD_POWER_SUPPLY_INFORMATION:
+				case IPMI_FRU_AREA_TYPE_MULTIRECORD_POWER_SUPPLY_INFORMATION:
 
 					if(libfreeipmi_get_psu_info (areabuf, area_length, ipmi_dev) < 0)
 					{
@@ -205,13 +235,13 @@
 					break;
 			}
 		}
-	} while ((ret = ipmi_fru_parse_next (fru_parse_ctx)) == 1);
+	} while ((ret = ipmi_fru_next (fru_ctx)) == 1);
 
 	/* check for errors */
 	if (ret < 0) {
 		libfreeipmi_cleanup();
-		fatal_with_errno(EXIT_FAILURE, "ipmi_fru_parse_next: %s",
-			ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+		fatal_with_errno(EXIT_FAILURE, "ipmi_fru_next: %s",
+			ipmi_fru_ctx_errormsg (fru_ctx));
 	}
 	else {
 		/* Get all related sensors information */
@@ -232,25 +262,25 @@
 }
 
 static const char* libfreeipmi_getfield (uint8_t language_code,
-									ipmi_fru_parse_field_t *field)
+									ipmi_fru_field_t *field)
 {
-	static char strbuf[IPMI_FRU_PARSE_AREA_STRING_MAX + 1];
-	unsigned int strbuflen = IPMI_FRU_PARSE_AREA_STRING_MAX;
+	static char strbuf[IPMI_FRU_AREA_STRING_MAX + 1];
+	unsigned int strbuflen = IPMI_FRU_AREA_STRING_MAX;
 
 	if (!field->type_length_field_length)
 		return NULL;
 
-	memset (strbuf, '\0', IPMI_FRU_PARSE_AREA_STRING_MAX + 1);
+	memset (strbuf, '\0', IPMI_FRU_AREA_STRING_MAX + 1);
 
-	if (ipmi_fru_parse_type_length_field_to_string (fru_parse_ctx,
+	if (ipmi_fru_type_length_field_to_string (fru_ctx,
 													field->type_length_field,
 													field->type_length_field_length,
 													language_code,
 													strbuf,
 													&strbuflen) < 0)
 		{
-			upsdebugx (2, "ipmi_fru_parse_type_length_field_to_string: %s",
-				ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+			upsdebugx (2, "ipmi_fru_type_length_field_to_string: %s",
+				ipmi_fru_ctx_errormsg (fru_ctx));
 			return NULL;
 		}
 
@@ -279,24 +309,20 @@
 static void libfreeipmi_cleanup()
 {
 	/* cleanup */
-	if (fru_parse_ctx) {
-		ipmi_fru_parse_close_device_id (fru_parse_ctx);
-		ipmi_fru_parse_ctx_destroy (fru_parse_ctx);
+	if (fru_ctx) {
+		ipmi_fru_close_device_id (fru_ctx);
+		ipmi_fru_ctx_destroy (fru_ctx);
 	}
 
-#ifdef HAVE_FREEIPMI_11X_12X
 	if (sdr_ctx) {
 		ipmi_sdr_ctx_destroy (sdr_ctx);
 	}
-#else /* HAVE_FREEIPMI_11X_12X */
-	if (sdr_cache_ctx) {
-		ipmi_sdr_cache_ctx_destroy (sdr_cache_ctx);
-	}
 
+#ifndef HAVE_FREEIPMI_11X_12X
 	if (sdr_parse_ctx) {
 		ipmi_sdr_parse_ctx_destroy (sdr_parse_ctx);
 	}
-#endif /* HAVE_FREEIPMI_11X_12X */
+#endif
 
 	if (ipmi_ctx) {
 		ipmi_ctx_close (ipmi_ctx);
@@ -342,7 +368,7 @@
 
 	upsdebugx(1, "entering libfreeipmi_get_psu_info()");
 
-	if (ipmi_fru_parse_multirecord_power_supply_information (fru_parse_ctx,
+	if (ipmi_fru_multirecord_power_supply_information (fru_ctx,
 			areabuf,
 			area_length,
 			&overall_capacity,
@@ -368,8 +394,8 @@
 			&total_combined_wattage,
 			&predictive_fail_tachometer_lower_threshold) < 0)
 	{
-		fatalx(EXIT_FAILURE, "ipmi_fru_parse_multirecord_power_supply_information: %s",
-			ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+		fatalx(EXIT_FAILURE, "ipmi_fru_multirecord_power_supply_information: %s",
+			ipmi_fru_ctx_errormsg (fru_ctx));
 	}
 
 	ipmi_dev->overall_capacity = overall_capacity;
@@ -383,6 +409,8 @@
 
 	ipmi_dev->voltage = libfreeipmi_get_voltage(voltage_1);
 
+	upsdebugx(1, "libfreeipmi_get_psu_info() retrieved successfully");
+
 	return (0);
 }
 
@@ -392,12 +420,12 @@
 {
 	uint8_t language_code;
 	uint32_t mfg_date_time;
-	ipmi_fru_parse_field_t board_manufacturer;
-	ipmi_fru_parse_field_t board_product_name;
-	ipmi_fru_parse_field_t board_serial_number;
-	ipmi_fru_parse_field_t board_part_number;
-	ipmi_fru_parse_field_t board_fru_file_id;
-	ipmi_fru_parse_field_t board_custom_fields[IPMI_FRU_CUSTOM_FIELDS];
+	ipmi_fru_field_t board_manufacturer;
+	ipmi_fru_field_t board_product_name;
+	ipmi_fru_field_t board_serial_number;
+	ipmi_fru_field_t board_part_number;
+	ipmi_fru_field_t board_fru_file_id;
+	ipmi_fru_field_t board_custom_fields[IPMI_FRU_CUSTOM_FIELDS];
 	const char *string = NULL;
 	time_t timetmp;
 	struct tm mfg_date_time_tm;
@@ -406,15 +434,15 @@
 	upsdebugx(1, "entering libfreeipmi_get_board_info()");
 
 	/* clear fields */
-	memset (&board_manufacturer, '\0', sizeof (ipmi_fru_parse_field_t));
-	memset (&board_product_name, '\0', sizeof (ipmi_fru_parse_field_t));
-	memset (&board_serial_number, '\0', sizeof (ipmi_fru_parse_field_t));
-	memset (&board_fru_file_id, '\0', sizeof (ipmi_fru_parse_field_t));
+	memset (&board_manufacturer, '\0', sizeof (ipmi_fru_field_t));
+	memset (&board_product_name, '\0', sizeof (ipmi_fru_field_t));
+	memset (&board_serial_number, '\0', sizeof (ipmi_fru_field_t));
+	memset (&board_fru_file_id, '\0', sizeof (ipmi_fru_field_t));
 	memset (&board_custom_fields[0], '\0',
-			sizeof (ipmi_fru_parse_field_t) * IPMI_FRU_CUSTOM_FIELDS);
+			sizeof (ipmi_fru_field_t) * IPMI_FRU_CUSTOM_FIELDS);
 
 	/* parse FRU buffer */
-	if (ipmi_fru_parse_board_info_area (fru_parse_ctx,
+	if (ipmi_fru_board_info_area (fru_ctx,
 			areabuf,
 			area_length,
 			&language_code,
@@ -428,8 +456,8 @@
 			IPMI_FRU_CUSTOM_FIELDS) < 0)
 	{
 		libfreeipmi_cleanup();
-		fatalx(EXIT_FAILURE, "ipmi_fru_parse_board_info_area: %s",
-			ipmi_fru_parse_ctx_errormsg (fru_parse_ctx));
+		fatalx(EXIT_FAILURE, "ipmi_fru_board_info_area: %s",
+			ipmi_fru_ctx_errormsg (fru_ctx));
 	}
 
 
@@ -498,113 +526,64 @@
 	ipmi_dev->sensors_count = 0;
 	memset(ipmi_dev->sensors_id_list, 0, sizeof(ipmi_dev->sensors_id_list));
 
-#ifdef HAVE_FREEIPMI_11X_12X
 	if (!(sdr_ctx = ipmi_sdr_ctx_create ()))
 	{
 		libfreeipmi_cleanup();
 		fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_ctx_create()");
 	}
 
-	if (ipmi_sdr_cache_open (sdr_ctx, ipmi_ctx, CACHE_LOCATION) < 0)
-	{
-		if (ipmi_sdr_ctx_errnum (sdr_ctx) != IPMI_SDR_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
-		{
-			libfreeipmi_cleanup();
-			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_open: %s",
-				ipmi_sdr_ctx_errormsg (sdr_ctx));
-		}
-	}
-#else /* HAVE_FREEIPMI_11X_12X */
-	if (!(sdr_cache_ctx = ipmi_sdr_cache_ctx_create ()))
-	{
-		libfreeipmi_cleanup();
-		fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_ctx_create()");
-	}
-
+#ifndef HAVE_FREEIPMI_11X_12X
 	if (!(sdr_parse_ctx = ipmi_sdr_parse_ctx_create ()))
 	{
 		libfreeipmi_cleanup();
 		fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_parse_ctx_create()");
 	}
+#endif
 
-	if (ipmi_sdr_cache_open (sdr_cache_ctx, ipmi_ctx, CACHE_LOCATION) < 0)
+	if (ipmi_sdr_cache_open (sdr_ctx, ipmi_ctx, CACHE_LOCATION) < 0)
 	{
-		if (ipmi_sdr_cache_ctx_errnum (sdr_cache_ctx) != IPMI_SDR_CACHE_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
+		if (ipmi_sdr_ctx_errnum (sdr_ctx) != IPMI_SDR_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
 		{
 			libfreeipmi_cleanup();
 			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_open: %s",
-				ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
+				ipmi_sdr_ctx_errormsg (sdr_ctx));
 		}
 	}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
-#ifdef HAVE_FREEIPMI_11X_12X
 	if (ipmi_sdr_ctx_errnum (sdr_ctx) == IPMI_SDR_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
 	{
 		if (ipmi_sdr_cache_create (sdr_ctx,
 				 ipmi_ctx, CACHE_LOCATION,
 				 IPMI_SDR_CACHE_CREATE_FLAGS_DEFAULT,
+#ifndef HAVE_FREEIPMI_11X_12X
+				 IPMI_SDR_CACHE_VALIDATION_FLAGS_DEFAULT,
+#endif
 				 NULL, NULL) < 0)
 		{
 			libfreeipmi_cleanup();
 			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_create: %s",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 		}
-		if (ipmi_sdr_cache_open (sdr_ctx,
-				ipmi_ctx, CACHE_LOCATION) < 0)
+		if (ipmi_sdr_cache_open (sdr_ctx, ipmi_ctx, CACHE_LOCATION) < 0)
 		{
 			if (ipmi_sdr_ctx_errnum (sdr_ctx) != IPMI_SDR_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
 			{
-			libfreeipmi_cleanup();
-			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_open: %s",
-				ipmi_sdr_ctx_errormsg (sdr_ctx));
-			}
-		}
-	}
-#else /* HAVE_FREEIPMI_11X_12X */
-	if (ipmi_sdr_cache_ctx_errnum (sdr_cache_ctx) == IPMI_SDR_CACHE_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
-	{
-		if (ipmi_sdr_cache_create (sdr_cache_ctx,
-				 ipmi_ctx, CACHE_LOCATION,
-				 IPMI_SDR_CACHE_CREATE_FLAGS_DEFAULT,
-				 IPMI_SDR_CACHE_VALIDATION_FLAGS_DEFAULT,
-				 NULL, NULL) < 0)
-		{
-			libfreeipmi_cleanup();
-			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_create: %s",
-				ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
-		}
-		if (ipmi_sdr_cache_open (sdr_cache_ctx,
-				ipmi_ctx, CACHE_LOCATION) < 0)
-		{
-			if (ipmi_sdr_cache_ctx_errnum (sdr_cache_ctx) != IPMI_SDR_CACHE_ERR_CACHE_READ_CACHE_DOES_NOT_EXIST)
-			{
-			libfreeipmi_cleanup();
-			fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_open: %s",
-				ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
+				libfreeipmi_cleanup();
+				fatal_with_errno(EXIT_FAILURE, "ipmi_sdr_cache_open: %s",
+					ipmi_sdr_ctx_errormsg (sdr_ctx));
 			}
 		}
 	}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
-#ifdef HAVE_FREEIPMI_11X_12X
-	if (ipmi_sdr_cache_record_count (sdr_ctx, &record_count) < 0)	{
+	if (ipmi_sdr_cache_record_count (sdr_ctx, &record_count) < 0) {
 		fprintf (stderr,
-			"ipmi_sdr_cache_record_count: %s",
+			"ipmi_sdr_cache_record_count: %s\n",
 			ipmi_sdr_ctx_errormsg (sdr_ctx));
 		goto cleanup;
 	}
-#else
-	if (ipmi_sdr_cache_record_count (sdr_cache_ctx, &record_count) < 0)
-	{
-		fprintf (stderr,
-			"ipmi_sdr_cache_record_count: %s",
-			ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
-		goto cleanup;
-	}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
-#ifdef HAVE_FREEIPMI_11X_12X
+	upsdebugx(3, "Found %i records in SDR cache", record_count);
+
 	for (i = 0; i < record_count; i++, ipmi_sdr_cache_next (sdr_ctx))
 	{
 		memset (sdr_record, '\0', IPMI_SDR_MAX_RECORD_LENGTH);
@@ -613,50 +592,29 @@
 				sdr_record,
 				IPMI_SDR_MAX_RECORD_LENGTH)) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_cache_record_read: %s",
+			fprintf (stderr, "ipmi_sdr_cache_record_read: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
-		if (ipmi_sdr_parse_record_id_and_type (sdr_ctx,
+		if (ipmi_sdr_parse_record_id_and_type (SDR_PARSE_CTX,
 				sdr_record,
 				sdr_record_len,
 				NULL,
 				&record_type) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s",
+			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
-#else
-	for (i = 0; i < record_count; i++, ipmi_sdr_cache_next (sdr_cache_ctx))
-	{
-		memset (sdr_record, '\0', IPMI_SDR_MAX_RECORD_LENGTH);
 
-		if ((sdr_record_len = ipmi_sdr_cache_record_read (sdr_cache_ctx,
-				sdr_record,
-				IPMI_SDR_MAX_RECORD_LENGTH)) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_cache_record_read: %s",
-				ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
-			goto cleanup;
-		}
-		if (ipmi_sdr_parse_record_id_and_type (sdr_parse_ctx,
-				sdr_record,
-				sdr_record_len,
-				NULL,
-				&record_type) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s",
-				ipmi_sdr_parse_ctx_errormsg (sdr_parse_ctx));
-			goto cleanup;
-		}
-#endif /* HAVE_FREEIPMI_11X_12X */
+		upsdebugx (5, "Checking record %i (/%i)", i, record_count);
 
-		if (record_type != IPMI_SDR_FORMAT_FRU_DEVICE_LOCATOR_RECORD)
+		if (record_type != IPMI_SDR_FORMAT_FRU_DEVICE_LOCATOR_RECORD) {
+			upsdebugx(1, "=======> not device locator (%i)!!", record_type);
 			continue;
+		}
 
-#ifdef HAVE_FREEIPMI_11X_12X
-		if (ipmi_sdr_parse_fru_device_locator_parameters (sdr_ctx,
+		if (ipmi_sdr_parse_fru_device_locator_parameters (SDR_PARSE_CTX,
 				sdr_record,
 				sdr_record_len,
 				NULL,
@@ -666,86 +624,49 @@
 				&logical_physical_fru_device,
 				NULL) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_parse_fru_device_locator_parameters: %s",
+			fprintf (stderr, "ipmi_sdr_parse_fru_device_locator_parameters: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
-#else /* HAVE_FREEIPMI_11X_12X */
-		if (ipmi_sdr_parse_fru_device_locator_parameters (sdr_parse_ctx,
-				sdr_record,
-				sdr_record_len,
-				NULL,
-				&logical_fru_device_device_slave_address,
-				NULL,
-				NULL,
-				&logical_physical_fru_device,
-				NULL) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_parse_fru_device_locator_parameters: %s",
-				ipmi_sdr_parse_ctx_errormsg (sdr_parse_ctx));
-			goto cleanup;
-		}
-#endif /* HAVE_FREEIPMI_11X_12X */
+
+		upsdebugx(2, "Checking device %i/%i", logical_physical_fru_device,
+					logical_fru_device_device_slave_address);
 
 		if (logical_physical_fru_device
 			&& logical_fru_device_device_slave_address == ipmi_dev->ipmi_id)
 		{
 			found_device_id++;
 
-#ifdef HAVE_FREEIPMI_11X_12X
-			if (ipmi_sdr_parse_fru_entity_id_and_instance (sdr_ctx,
+			if (ipmi_sdr_parse_fru_entity_id_and_instance (SDR_PARSE_CTX,
 					sdr_record,
 					sdr_record_len,
 					&entity_id,
 					&entity_instance) < 0)
 			{
 				fprintf (stderr,
-					"ipmi_sdr_parse_fru_entity_id_and_instance: %s",
+					"ipmi_sdr_parse_fru_entity_id_and_instance: %s\n",
 					ipmi_sdr_ctx_errormsg (sdr_ctx));
 				goto cleanup;
 			}
-#else /* HAVE_FREEIPMI_11X_12X */
-			if (ipmi_sdr_parse_fru_entity_id_and_instance (sdr_parse_ctx,
-					sdr_record,
-					sdr_record_len,
-					&entity_id,
-					&entity_instance) < 0)
-			{
-				fprintf (stderr,
-					"ipmi_sdr_parse_fru_entity_id_and_instance: %s",
-					ipmi_sdr_parse_ctx_errormsg (sdr_parse_ctx));
-				goto cleanup;
-			}
-#endif /* HAVE_FREEIPMI_11X_12X */
 			break;
 		}
 	}
 
 	if (!found_device_id)
 	{
-		fprintf (stderr, "Couldn't find device id %d", ipmi_dev->ipmi_id);
+		fprintf (stderr, "Couldn't find device id %d\n", ipmi_dev->ipmi_id);
 		goto cleanup;
 	}
 	else
 		upsdebugx(1, "Found device id %d", ipmi_dev->ipmi_id);
 
-#ifdef HAVE_FREEIPMI_11X_12X
 	if (ipmi_sdr_cache_first (sdr_ctx) < 0)
 	{
-		fprintf (stderr, "ipmi_sdr_cache_first: %s", 
+		fprintf (stderr, "ipmi_sdr_cache_first: %s\n", 
 			ipmi_sdr_ctx_errormsg (sdr_ctx));
 		goto cleanup;
 	}
-#else /* HAVE_FREEIPMI_11X_12X */
-	if (ipmi_sdr_cache_first (sdr_cache_ctx) < 0)
-	{
-		fprintf (stderr, "ipmi_sdr_cache_first: %s", 
-			ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
-		goto cleanup;
-	}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
-#ifdef HAVE_FREEIPMI_11X_12X
 	for (i = 0; i < record_count; i++, ipmi_sdr_cache_next (sdr_ctx))
 	{
 		/* uint8_t sdr_record[IPMI_SDR_CACHE_MAX_SDR_RECORD_LENGTH];
@@ -757,49 +678,21 @@
 				sdr_record,
 				IPMI_SDR_MAX_RECORD_LENGTH)) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_cache_record_read: %s",
+			fprintf (stderr, "ipmi_sdr_cache_record_read: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
 
-		if (ipmi_sdr_parse_record_id_and_type (sdr_ctx,
+		if (ipmi_sdr_parse_record_id_and_type (SDR_PARSE_CTX,
 				sdr_record,
 				sdr_record_len,
 				&record_id,
 				&record_type) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s",
+			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
-#else /* HAVE_FREEIPMI_11X_12X */
-	for (i = 0; i < record_count; i++, ipmi_sdr_cache_next (sdr_cache_ctx))
-	{
-		/* uint8_t sdr_record[IPMI_SDR_CACHE_MAX_SDR_RECORD_LENGTH];
-		uint8_t record_type, tmp_entity_id, tmp_entity_instance;
-		int sdr_record_len; */
-
-		memset (sdr_record, '\0', IPMI_SDR_MAX_RECORD_LENGTH);
-		if ((sdr_record_len = ipmi_sdr_cache_record_read (sdr_cache_ctx,
-				sdr_record,
-				IPMI_SDR_MAX_RECORD_LENGTH)) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_cache_record_read: %s",
-				ipmi_sdr_cache_ctx_errormsg (sdr_cache_ctx));
-			goto cleanup;
-		}
-
-		if (ipmi_sdr_parse_record_id_and_type (sdr_parse_ctx,
-				sdr_record,
-				sdr_record_len,
-				&record_id,
-				&record_type) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_parse_record_id_and_type: %s",
-				ipmi_sdr_parse_ctx_errormsg (sdr_parse_ctx));
-			goto cleanup;
-		}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
 		upsdebugx (5, "Checking record %i (/%i)", record_id, record_count);
 
@@ -809,31 +702,17 @@
 			continue;
 		}
 
-#ifdef HAVE_FREEIPMI_11X_12X
-		if (ipmi_sdr_parse_entity_id_instance_type (sdr_ctx,
+		if (ipmi_sdr_parse_entity_id_instance_type (SDR_PARSE_CTX,
 				sdr_record,
 				sdr_record_len,
 				&tmp_entity_id,
 				&tmp_entity_instance,
 				NULL) < 0)
 		{
-			fprintf (stderr, "ipmi_sdr_parse_entity_instance_type: %s",
+			fprintf (stderr, "ipmi_sdr_parse_entity_instance_type: %s\n",
 				ipmi_sdr_ctx_errormsg (sdr_ctx));
 			goto cleanup;
 		}
-#else /* HAVE_FREEIPMI_11X_12X */
-		if (ipmi_sdr_parse_entity_id_instance_type (sdr_parse_ctx,
-				sdr_record,
-				sdr_record_len,
-				&tmp_entity_id,
-				&tmp_entity_instance,
-				NULL) < 0)
-		{
-			fprintf (stderr, "ipmi_sdr_parse_entity_instance_type: %s",
-				ipmi_sdr_parse_ctx_errormsg (sdr_parse_ctx));
-			goto cleanup;
-		}
-#endif /* HAVE_FREEIPMI_11X_12X */
 
 		if (tmp_entity_id == entity_id
 			&& tmp_entity_instance == entity_instance)
@@ -850,15 +729,11 @@
 
 cleanup:
 	/* Cleanup */
-#ifdef HAVE_FREEIPMI_11X_12X
 	if (sdr_ctx) {
 		ipmi_sdr_ctx_destroy (sdr_ctx);
 	}
-#else /* HAVE_FREEIPMI_11X_12X */
-	if (sdr_cache_ctx) {
-		ipmi_sdr_cache_ctx_destroy (sdr_cache_ctx);
-	}
 
+#ifndef HAVE_FREEIPMI_11X_12X
 	if (sdr_parse_ctx) {
 		ipmi_sdr_parse_ctx_destroy (sdr_parse_ctx);
 	}
