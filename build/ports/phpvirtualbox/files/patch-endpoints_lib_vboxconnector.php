--- endpoints/lib/vboxconnector.php.orig	2016-06-05 08:07:08.618662000 +0200
+++ endpoints/lib/vboxconnector.php	2016-06-10 18:05:35.000000000 +0200
@@ -3749,15 +3749,15 @@
 			if ( @isset($this->settings->vmQuotaPerUser) && @$this->settings->vmQuotaPerUser > 0 && !$_SESSION['admin'] )
 			{
 				$newresp = array('data' => array());
-				$vmlist = $this->vboxGetMachines(array(), $newresp);
-				if ( count($newresp['data']['vmlist']) >= $this->settings->vmQuotaPerUser )
+				$this->vboxGetMachines(array(), array(&$newresp));
+				if ( count($newresp['data']['responseData']) >= $this->settings->vmQuotaPerUser )
 				{
 					// we're over quota!
 					// delete the disk we just created
 					if ( isset($args['disk']) )
 					{
 						$this->mediumRemove(array(
-								'id' => $args['disk'],
+								'medium' => $args['disk'],
 								'type' => 'HardDisk',
 								'delete' => true
 							), $newresp);
