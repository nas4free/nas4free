--- source3/lib/sessionid_tdb.c.orig	2015-07-21 11:47:49.000000000 +0200
+++ source3/lib/sessionid_tdb.c	2016-04-14 15:20:01.000000000 +0200
@@ -67,6 +67,12 @@
 	case SMB3_DIALECT_REVISION_302:
 		fstrcpy(session.protocol_ver, "SMB3_02");
 		break;
+	case SMB3_DIALECT_REVISION_310:
+		fstrcpy(session.protocol_ver, "SMB3_10");
+		break;
+	case SMB3_DIALECT_REVISION_311:
+		fstrcpy(session.protocol_ver, "SMB3_11");
+		break;
 	default:
 		fstr_sprintf(session.protocol_ver, "Unknown (0x%04x)",
 			     global->connection_dialect);
