Index: sys/netinet/ip_carp.c
===================================================================
--- sys/netinet/ip_carp.c	(revision 274659)
+++ sys/netinet/ip_carp.c	(working copy)
@@ -900,13 +900,17 @@
 		CARP_SCLOCK(sc);
 		if ((SC2IFP(sc)->if_flags & IFF_UP) &&
 		    (SC2IFP(sc)->if_drv_flags & IFF_DRV_RUNNING) &&
-		     sc->sc_state == MASTER)
+		    sc->sc_state == MASTER) {
+			CURVNET_SET(sc->sc_carpdev->if_vnet);
 			carp_send_ad_locked(sc);
+			CURVNET_RESTORE();
+		}
 		CARP_SCUNLOCK(sc);
 	}
 	mtx_unlock(&carp_mtx);
 }
 
+/* Send a periodic advertisement, executed in callout context. */
 static void
 carp_send_ad(void *v)
 {
@@ -913,7 +917,9 @@
 	struct carp_softc *sc = v;
 
 	CARP_SCLOCK(sc);
+	CURVNET_SET(sc->sc_carpdev->if_vnet);
 	carp_send_ad_locked(sc);
+	CURVNET_RESTORE();
 	CARP_SCUNLOCK(sc);
 }
 
@@ -1358,6 +1364,7 @@
 	return (NULL);
 }
 
+/* Master down timeout event, executed in callout context. */
 static void
 carp_master_down(void *v)
 {
@@ -1364,7 +1371,9 @@
 	struct carp_softc *sc = v;
 
 	CARP_SCLOCK(sc);
+	CURVNET_SET(sc->sc_carpdev->if_vnet);
 	carp_master_down_locked(sc);
+	CURVNET_RESTORE();
 	CARP_SCUNLOCK(sc);
 }
 
