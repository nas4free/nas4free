--- src/VBox/Devices/Graphics/DevVGA.h.orig	2015-11-11 06:22:03.000000000 +0900
+++ src/VBox/Devices/Graphics/DevVGA.h	2015-11-18 18:37:37.005913000 +0900
@@ -445,9 +445,11 @@
     /** The R0 vram pointer... */
     R0PTRTYPE(uint8_t *)        vram_ptrR0;
 
+#if 0
 # if HC_ARCH_BITS == 32
     uint32_t                    Padding3;
 # endif
+#endif
 
 # ifdef VBOX_WITH_VMSVGA
     VMSVGAState                 svga;
