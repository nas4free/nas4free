--- js/datamediator.js.orig	2016-06-05 08:07:08.624374000 +0200
+++ js/datamediator.js	2016-06-10 18:08:17.000000000 +0200
@@ -93,7 +93,7 @@
 			for(var i = 0; i < d.responseData.length; i++) {
 				
 				// Enforce VM ownership
-			    if($('#vboxPane').data('vboxConfig').enforceVMOwnership && !$('#vboxPane').data('vboxSession').admin && d.vmlist[i].owner != $('#vboxPane').data('vboxSession').user) {
+				if($('#vboxPane').data('vboxConfig').enforceVMOwnership && !$('#vboxPane').data('vboxSession').admin && d.responseData[i].owner != $('#vboxPane').data('vboxSession').user) {
 			    	continue;
 			    }
 
