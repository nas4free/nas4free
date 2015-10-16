--- js/phpvirtualbox.js.orig	2015-10-06 17:01:44.551054000 +0900
+++ js/phpvirtualbox.js	2015-10-06 19:16:37.225658000 +0900
@@ -931,7 +931,8 @@
 						   rowStr += ' <img src="images/vbox/blank.gif" style="vspace:0px;hspace:0px;height2px;width:10px;" /> (' + chost + ':' + d['VRDEServerInfo']['port'] + ')';
 					   // VNC   
 					   } else {
-						   rowStr = " <a href='vnc://" + chost + ':' + d['VRDEServerInfo']['port'] + "'>" + d['VRDEServerInfo']['port'] + "</a>";						   
+						   //rowStr = " <a href='vnc://" + chost + ':' + d['VRDEServerInfo']['port'] + "'>" + d['VRDEServerInfo']['port'] + "</a>";						   
+						   rowStr = " <a href='/novnc/vnc.html?host=" + chost + '&port=' + d['VRDEServerInfo']['port'] + "' target='_blank'>" + d['VRDEServerInfo']['port'] + "</a>";						   
 						   rowStr += ' <img src="images/vbox/blank.gif" style="vspace:0px;hspace:0px;height2px;width:10px;" /> (' + chost + ':' + d['VRDEServerInfo']['port'] + ')';
 					   }
 				   } else {
