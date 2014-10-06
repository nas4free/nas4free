--- include/fuppes_plugin.h.orig	2011-11-01 10:50:10.000000000 +0100
+++ include/fuppes_plugin.h	2014-01-03 05:27:05.000000000 +0100
@@ -83,9 +83,9 @@
 	arg_list_t* list = (arg_list_t*)malloc(sizeof(arg_list_t));
 	
 	list->key = (char*)malloc(sizeof(char));
-	list->key = '\0';
+	list->key[0] = '\0';
 	list->value = (char*)malloc(sizeof(char));
-	list->value = '\0';	
+	list->value[0] = '\0';	
 
 	list->next = NULL;
 	
