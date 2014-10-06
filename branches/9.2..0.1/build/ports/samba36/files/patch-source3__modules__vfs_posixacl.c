--- ./source3/modules/vfs_posixacl.c.orig	2011-10-19 03:48:48.000000000 +0900
+++ ./source3/modules/vfs_posixacl.c	2011-11-09 17:33:18.753119000 +0900
@@ -166,6 +166,8 @@
 	case ACL_MASK:
 		ace->a_type = SMB_ACL_MASK;
 		break;
+	case ACL_EVERYONE:
+		return False;
 	default:
 		DEBUG(0, ("unknown tag type %d\n", (unsigned int)tag));
 		return False;
