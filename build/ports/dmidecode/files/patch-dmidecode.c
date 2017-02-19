--- dmidecode.c.orig	2015-09-03 08:03:19.000000000 +0200
+++ dmidecode.c	2016-11-24 13:08:47.000000000 +0100
@@ -2274,10 +2274,13 @@
 {
 	code &= 0x7FFFFFFFUL;
 
-	/* Use the most suitable unit depending on size */
+	/*
+	 * Use the greatest unit for which the exact value can be displayed
+	 * as an integer without rounding
+	 */
 	if (code & 0x3FFUL)
 		printf(" %lu MB", (unsigned long)code);
-	else if (code & 0xFFFFFUL)
+	else if (code & 0xFFC00UL)
 		printf(" %lu GB", (unsigned long)code >> 10);
 	else
 		printf(" %lu TB", (unsigned long)code >> 20);
@@ -2389,7 +2392,7 @@
 		"LRDIMM"  /* 15 */
 	};
 
-	if ((code & 0x7FFE) == 0)
+	if ((code & 0xFFFE) == 0)
 		printf(" None");
 	else
 	{
@@ -2946,7 +2949,7 @@
  * first 5 characters of the device name to be trimmed. It's easy to
  * check and fix, so do it, but warn.
  */
-static void dmi_fixup_type_34(struct dmi_header *h)
+static void dmi_fixup_type_34(struct dmi_header *h, int display)
 {
 	u8 *p = h->data;
 
@@ -2954,7 +2957,10 @@
 	if (h->length == 0x10
 	 && is_printable(p + 0x0B, 0x10 - 0x0B))
 	{
-		printf("Invalid entry length (%u). Fixed up to %u.\n", 0x10, 0x0B);
+		if (!(opt.flags & FLAG_QUIET) && display)
+			fprintf(stderr,
+				"Invalid entry length (%u). Fixed up to %u.\n",
+				0x10, 0x0B);
 		h->length = 0x0B;
 	}
 }
@@ -4422,9 +4428,14 @@
 		 */
 		if (h.length < 4)
 		{
-			printf("Invalid entry length (%u). DMI table is "
-			       "broken! Stop.\n\n", (unsigned int)h.length);
-			opt.flags |= FLAG_QUIET;
+			if (!(opt.flags & FLAG_QUIET))
+			{
+				fprintf(stderr,
+					"Invalid entry length (%u). DMI table "
+					"is broken! Stop.\n\n",
+					(unsigned int)h.length);
+				opt.flags |= FLAG_QUIET;
+			}
 			break;
 		}
 
@@ -4443,7 +4454,7 @@
 
 		/* Fixup a common mistake */
 		if (h.type == 34)
-			dmi_fixup_type_34(&h);
+			dmi_fixup_type_34(&h, display);
 
 		/* look for the next handle */
 		next = data + h.length;
@@ -4485,11 +4496,11 @@
 	if (!(opt.flags & FLAG_QUIET))
 	{
 		if (num && i != num)
-			printf("Wrong DMI structures count: %d announced, "
+			fprintf(stderr, "Wrong DMI structures count: %d announced, "
 				"only %d decoded.\n", num, i);
 		if ((unsigned long)(data - buf) > len
 		 || (num && (unsigned long)(data - buf) < len))
-			printf("Wrong DMI structures length: %u bytes "
+			fprintf(stderr, "Wrong DMI structures length: %u bytes "
 				"announced, structures occupy %lu bytes.\n",
 				len, (unsigned long)(data - buf));
 	}
@@ -4521,22 +4532,37 @@
 		printf("\n");
 	}
 
-	/*
-	 * When we are reading the DMI table from sysfs, we want to print
-	 * the address of the table (done above), but the offset of the
-	 * data in the file is 0.  When reading from /dev/mem, the offset
-	 * in the file is the address.
-	 */
 	if (flags & FLAG_NO_FILE_OFFSET)
-		base = 0;
 
-	if ((buf = mem_chunk(base, len, devmem)) == NULL)
 	{
-		fprintf(stderr, "Table is unreachable, sorry."
+		/*
+		 * When reading from sysfs, the file may be shorter than
+		 * announced. For SMBIOS v3 this is expcted, as we only know
+		 * the maximum table size, not the actual table size. For older
+		 * implementations (and for SMBIOS v3 too), this would be the
+		 * result of the kernel truncating the table on parse error.
+		 */
+		size_t size = len;
+		buf = read_file(&size, devmem);
+		if (!(opt.flags & FLAG_QUIET) && num && size != (size_t)len)
+		{
+			fprintf(stderr, "Wrong DMI structures length: %u bytes "
+				"announced, only %lu bytes available.\n",
+				len, (unsigned long)size);
+		}
+		len = size;
+	}
+	else
+		buf = mem_chunk(base, len, devmem);
+
+	if (buf == NULL)
+	{
+		fprintf(stderr, "Failed to read table, sorry.\n");
 #ifndef USE_MMAP
-			" Try compiling dmidecode with -DUSE_MMAP."
+		if (!(flags & FLAG_NO_FILE_OFFSET))
+			fprintf(stderr,
+				"Try compiling dmidecode with -DUSE_MMAP.\n");
 #endif
-			"\n");
 		return;
 	}
 
@@ -4633,14 +4659,16 @@
 		case 0x021F:
 		case 0x0221:
 			if (!(opt.flags & FLAG_QUIET))
-				printf("SMBIOS version fixup (2.%d -> 2.%d).\n",
-				       ver & 0xFF, 3);
+				fprintf(stderr,
+					"SMBIOS version fixup (2.%d -> 2.%d).\n",
+					ver & 0xFF, 3);
 			ver = 0x0203;
 			break;
 		case 0x0233:
 			if (!(opt.flags & FLAG_QUIET))
-				printf("SMBIOS version fixup (2.%d -> 2.%d).\n",
-				       51, 6);
+				fprintf(stderr,
+					"SMBIOS version fixup (2.%d -> 2.%d).\n",
+					51, 6);
 			ver = 0x0206;
 			break;
 	}
@@ -4748,9 +4776,17 @@
 	int ret = 0;                /* Returned value */
 	int found = 0;
 	off_t fp;
+	size_t size;
 	int efi;
 	u8 *buf;
 
+	/*
+	 * We don't want stdout and stderr to be mixed up if both are
+	 * redirected to the same file.
+	 */
+	setlinebuf(stdout);
+	setlinebuf(stderr);
+
 	if (sizeof(u8) != 1 || sizeof(u16) != 2 || sizeof(u32) != 4 || '\0' != 0)
 	{
 		fprintf(stderr, "%s: compiler incompatibility\n", argv[0]);
@@ -4817,8 +4853,9 @@
 	 * contain one of several types of entry points, so read enough for
 	 * the largest one, then determine what type it contains.
 	 */
+	size = 0x20;
 	if (!(opt.flags & FLAG_NO_SYSFS)
-	 && (buf = read_file(0x20, SYS_ENTRY_FILE)) != NULL)
+	 && (buf = read_file(&size, SYS_ENTRY_FILE)) != NULL)
 	{
 		if (!(opt.flags & FLAG_QUIET))
 			printf("Getting SMBIOS data from sysfs.\n");
@@ -4864,8 +4901,17 @@
 		goto exit_free;
 	}
 
-	if (smbios_decode(buf, opt.devmem, 0))
-		found++;
+	if (memcmp(buf, "_SM3_", 5) == 0)
+	{
+		if (smbios3_decode(buf, opt.devmem, 0))
+			found++;
+	}
+	else if (memcmp(buf, "_SM_", 4) == 0)
+	{
+		if (smbios_decode(buf, opt.devmem, 0))
+			found++;
+	}
+
 	goto done;
 
 memory_scan:
