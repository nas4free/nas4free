--- src/VBox/Devices/Graphics/DevVGA.h.orig	2015-07-10 23:30:38.000000000 +0900
+++ src/VBox/Devices/Graphics/DevVGA.h	2015-10-07 18:48:30.170405000 +0900
@@ -420,9 +420,11 @@
     R0PTRTYPE(uint8_t *)        vram_ptrR0;
 
 #ifdef VBOX_WITH_VMSVGA
+#if 0
 # if HC_ARCH_BITS == 32
     uint32_t                    Padding3;
 # endif
+#endif
     VMSVGAState                 svga;
 #endif
 
