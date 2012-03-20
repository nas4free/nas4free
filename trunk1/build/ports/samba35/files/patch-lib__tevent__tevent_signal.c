--- ./lib/tevent/tevent_signal.c.orig	2010-04-01 22:26:22.000000000 +0900
+++ ./lib/tevent/tevent_signal.c	2010-05-04 15:47:34.000000000 +0900
@@ -30,7 +30,7 @@
 #include "tevent_internal.h"
 #include "tevent_util.h"
 
-#define TEVENT_NUM_SIGNALS 64
+#define TEVENT_NUM_SIGNALS SIGRTMAX
 
 /* maximum number of SA_SIGINFO signals to hold in the queue.
   NB. This *MUST* be a power of 2, in order for the ring buffer
