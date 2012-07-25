--- web/javascript/torrent-row.js.orig	2012-07-24 03:59:05.297474000 +0200
+++ web/javascript/torrent-row.js	2012-07-25 14:25:16.000000000 +0200
@@ -230,7 +230,7 @@
 	render: function(controller, t, root)
 	{
 		// name
-		setTextContent(root._name_container, t.getName());
+		setInnerHTML(root._name_container, t.getName());
 
 		// progressbar
 		TorrentRendererHelper.renderProgressbar(controller, t, root._progressbar);
@@ -239,11 +239,11 @@
 		var has_error = t.getError() !== Torrent._ErrNone;
 		var e = root._peer_details_container;
 		$(e).toggleClass('error',has_error);
-		setTextContent(e, this.getPeerDetails(t));
+		setInnerHTML(e, this.getPeerDetails(t));
 
 		// progress details
 		e = root._progress_details_container;
-		setTextContent(e, this.getProgressDetails(controller, t));
+		setInnerHTML(e, this.getProgressDetails(controller, t));
 
 		// pause/resume button
 		var is_stopped = t.isStopped();
@@ -319,13 +319,13 @@
 		var is_stopped = t.isStopped();
 		var e = root._name_container;
 		$(e).toggleClass('paused', is_stopped);
-		setTextContent(e, t.getName());
+		setInnerHTML(e, t.getName());
 
 		// peer details
 		var has_error = t.getError() !== Torrent._ErrNone;
 		e = root._details_container;
 		$(e).toggleClass('error', has_error);
-		setTextContent(e, this.getPeerDetails(t));
+		setInnerHTML(e, this.getPeerDetails(t));
 
 		// progressbar
 		TorrentRendererHelper.renderProgressbar(controller, t, root._progressbar);
