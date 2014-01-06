--- mDNSCore/mDNS.c.orig	2013-10-20 18:55:01.000000000 +0200
+++ mDNSCore/mDNS.c	2014-01-06 11:36:32.000000000 +0200
@@ -14990,6 +14990,7 @@
 
     // If any deregistering records remain, send their deregistration announcements before we exit
     if (m->mDNSPlatformStatus != mStatus_NoError) DiscardDeregistrations(m);
+    else if (m->ResourceRecords) SendResponses(m);
 
     mDNS_Unlock(m);
 
