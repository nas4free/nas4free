--- js/phpvirtualbox.js.orig	2016-03-16 11:10:17.306411000 +0100
+++ js/phpvirtualbox.js	2016-03-16 11:10:17.333915000 +0100
@@ -930,7 +930,8 @@
 						   rowStr += ' <img src="images/vbox/blank.gif" style="vspace:0px;hspace:0px;height2px;width:10px;" /> (' + chost + ':' + d['VRDEServerInfo']['port'] + ')';
 					   // VNC   
 					   } else {
-						   rowStr = " <a href='vnc://" + chost + ':' + d['VRDEServerInfo']['port'] + "'>" + d['VRDEServerInfo']['port'] + "</a>";						   
+						   //rowStr = " <a href='vnc://" + chost + ':' + d['VRDEServerInfo']['port'] + "'>" + d['VRDEServerInfo']['port'] + "</a>";						   
+						   rowStr = " <a href='/novnc/vnc.html?host=" + chost + '&port=' + d['VRDEServerInfo']['port'] + "' target='_blank'>" + d['VRDEServerInfo']['port'] + "</a>";						   
 						   rowStr += ' <img src="images/vbox/blank.gif" style="vspace:0px;hspace:0px;height2px;width:10px;" /> (' + chost + ':' + d['VRDEServerInfo']['port'] + ')';
 					   }
 				   } else {
