--- dmidecode.c.orig	2013-04-17 14:25:34.000000000 +0200
+++ dmidecode.c	2014-10-24 02:28:22.000000000 +0200
@@ -2,7 +2,7 @@
  * DMI Decode
  *
  *   Copyright (C) 2000-2002 Alan Cox <alan@redhat.com>
- *   Copyright (C) 2002-2010 Jean Delvare <khali@linux-fr.org>
+ *   Copyright (C) 2002-2014 Jean Delvare <jdelvare@suse.de>
  *
  *   This program is free software; you can redistribute it and/or modify
  *   it under the terms of the GNU General Public License as published by
@@ -69,7 +69,7 @@
 #define out_of_spec "<OUT OF SPEC>"
 static const char *bad_index = "<BAD INDEX>";
 
-#define SUPPORTED_SMBIOS_VER 0x0207
+#define SUPPORTED_SMBIOS_VER 0x0208
 
 /*
  * Type-independant Stuff
@@ -712,7 +712,6 @@
 		{ 0x3D, "Opteron 6200" },
 		{ 0x3E, "Opteron 4200" },
 		{ 0x3F, "FX" },
-
 		{ 0x40, "MIPS" },
 		{ 0x41, "MIPS R4000" },
 		{ 0x42, "MIPS R4200" },
@@ -729,7 +728,6 @@
 		{ 0x4D, "Opteron 6300" },
 		{ 0x4E, "Opteron 3300" },
 		{ 0x4F, "FirePro" },
-
 		{ 0x50, "SPARC" },
 		{ 0x51, "SuperSPARC" },
 		{ 0x52, "MicroSPARC II" },
@@ -1014,11 +1012,11 @@
 		sig = 1;
 	else if ((type >= 0x18 && type <= 0x1D) /* AMD */
 	      || type == 0x1F /* AMD */
-	      || (type >= 0x38 && type <= 0x3E) /* AMD */
-	      || (type >= 0x46 && type <= 0x49) /* AMD */
+	      || (type >= 0x38 && type <= 0x3F) /* AMD */
+	      || (type >= 0x46 && type <= 0x4F) /* AMD */
 	      || (type >= 0x83 && type <= 0x8F) /* AMD */
 	      || (type >= 0xB6 && type <= 0xB7) /* AMD */
-	      || (type >= 0xE6 && type <= 0xEF)) /* AMD */
+	      || (type >= 0xE4 && type <= 0xEF)) /* AMD */
 		sig = 2;
 	else if (type == 0x01 || type == 0x02)
 	{
@@ -1176,7 +1174,7 @@
 		"Socket LGA1356-3" /* 0x2C */
 	};
 
-	if (code >= 0x01 && code <= 0x2A)
+	if (code >= 0x01 && code <= 0x2C)
 		return upgrade[code - 0x01];
 	return out_of_spec;
 }
@@ -1699,6 +1697,10 @@
 		"PCI Express 3 x8",
 		"PCI Express 3 x16" /* 0xB6 */
 	};
+	/*
+	 * Note to developers: when adding entries to these lists, check if
+	 * function dmi_slot_id below needs updating too.
+	 */
 
 	if (code >= 0x01 && code <= 0x13)
 		return type[code - 0x01];
@@ -1792,6 +1794,12 @@
 		case 0xAE: /* PCI Express 2 */
 		case 0xAF: /* PCI Express 2 */
 		case 0xB0: /* PCI Express 2 */
+		case 0xB1: /* PCI Express 3 */
+		case 0xB2: /* PCI Express 3 */
+		case 0xB3: /* PCI Express 3 */
+		case 0xB4: /* PCI Express 3 */
+		case 0xB5: /* PCI Express 3 */
+		case 0xB6: /* PCI Express 3 */
 			printf("%sID: %u\n", prefix, code1);
 			break;
 		case 0x07: /* PCMCIA */
@@ -2236,7 +2244,7 @@
 	if (code == 0)
 		printf(" Unknown");
 	else
-		printf(" %.3f V", (float)(i16)code / 1000);
+		printf(code % 100 ? " %g V" : " %.1f V", (float)code / 1000);
 }
 
 static const char *dmi_memory_device_form_factor(u8 code)
@@ -2303,10 +2311,10 @@
 		"Reserved",
 		"Reserved",
 		"DDR3",
-		"FBD2", /* 0x19 */
+		"DDR4" /* 0x1A */
 	};
 
-	if (code >= 0x01 && code <= 0x19)
+	if (code >= 0x01 && code <= 0x1A)
 		return type[code - 0x01];
 	return out_of_spec;
 }
@@ -2338,7 +2346,7 @@
 	{
 		int i;
 
-		for (i = 1; i <= 14; i++)
+		for (i = 1; i <= 15; i++)
 			if (code & (1 << i))
 				printf(" %s", detail[i - 1]);
 	}
@@ -3657,13 +3665,13 @@
 			dmi_memory_device_speed(WORD(data + 0x20));
 			printf("\n");
 			if (h->length < 0x28) break;
-			printf("\tMinimum voltage: ");
+			printf("\tMinimum Voltage: ");
 			dmi_memory_voltage_value(WORD(data + 0x22));
 			printf("\n");
-			printf("\tMaximum voltage: ");
+			printf("\tMaximum Voltage: ");
 			dmi_memory_voltage_value(WORD(data + 0x24));
 			printf("\n");
-			printf("\tConfigured voltage: ");
+			printf("\tConfigured Voltage: ");
 			dmi_memory_voltage_value(WORD(data + 0x26));
 			printf("\n");
 			break;
@@ -4328,7 +4336,7 @@
 	u8 *data;
 	int i = 0;
 
-	if (ver > SUPPORTED_SMBIOS_VER)
+	if (ver > SUPPORTED_SMBIOS_VER && !(opt.flags & FLAG_QUIET))
 	{
 		printf("# SMBIOS implementations newer than version %u.%u are not\n"
 		       "# fully supported by this version of dmidecode.\n",
@@ -4528,7 +4536,9 @@
 		memcpy(crafted, buf, 16);
 		overwrite_dmi_address(crafted);
 
-		printf("# Writing %d bytes to %s.\n", 0x0F, opt.dumpfile);
+		if (!(opt.flags & FLAG_QUIET))
+			printf("# Writing %d bytes to %s.\n", 0x0F,
+				opt.dumpfile);
 		write_dump(0, 0x0F, crafted, opt.dumpfile, 1);
 	}
 
