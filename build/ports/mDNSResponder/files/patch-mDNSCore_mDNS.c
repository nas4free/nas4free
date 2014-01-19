--- mDNSCore/mDNS.c.orig	2011-08-04 07:06:51.000000000 +0900
+++ mDNSCore/mDNS.c	2011-10-02 08:49:51.000000000 +0900
@@ -11412,6 +11412,7 @@
 
 	// If any deregistering records remain, send their deregistration announcements before we exit
 	if (m->mDNSPlatformStatus != mStatus_NoError) DiscardDeregistrations(m);
+	else if (m->ResourceRecords) SendResponses(m);
 
 	mDNS_Unlock(m);
 
