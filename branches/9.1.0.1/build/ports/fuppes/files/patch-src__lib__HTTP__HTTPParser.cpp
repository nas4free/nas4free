--- src/lib/HTTP/HTTPParser.cpp.orig	2011-11-13 04:25:15.000000000 +0900
+++ src/lib/HTTP/HTTPParser.cpp	2011-11-13 08:06:19.000000000 +0900
@@ -52,7 +52,7 @@
   else if(rxResponse.Search(header)) {
 		sType    = rxResponse.Match(2);
 		nVersion = atoi(rxResponse.Match(1).c_str());
-		message->m_sRequest = rxRequest.Match(3);
+		message->m_sRequest = rxResponse.Match(3);
 	}
 	else {    
 		return false;
