--- mDNSCore/mDNS.c.orig	2012-08-30 01:27:16.000000000 +0200
+++ mDNSCore/mDNS.c	2012-12-20 04:12:17.000000000 +0100
@@ -12665,6 +12665,7 @@
 
     // If any deregistering records remain, send their deregistration announcements before we exit
     if (m->mDNSPlatformStatus != mStatus_NoError) DiscardDeregistrations(m);
+	else if (m->ResourceRecords) SendResponses(m);
 
     mDNS_Unlock(m);
 
