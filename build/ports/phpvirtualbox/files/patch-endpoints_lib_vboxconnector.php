--- endpoints/lib/vboxconnector.php.orig	2017-01-25 02:44:18.865581000 +0100
+++ endpoints/lib/vboxconnector.php	2017-01-25 16:03:40.000000000 +0100
@@ -1126,7 +1126,7 @@
 			// Try to register medium.
 			foreach($checks as $iso) {
 				try {
-					$gem = $this->vbox->openMedium($iso,'DVD','ReadOnly');
+					$gem = $this->vbox->openMedium($iso,'DVD','ReadOnly',null);
 					break;
 				} catch (Exception $e) {
 					// Ignore
@@ -1358,7 +1358,7 @@
 			$src = $nsrc->machine;
 		}
 		/* @var $m IMachine */
-		$m = $this->vbox->createMachine($this->vbox->composeMachineFilename($args['name'],null,null),$args['name'],null,null,null,false);
+		$m = $this->vbox->createMachine($this->vbox->composeMachineFilename($args['name'],null,null,null),$args['name'],null,null,null,false);
 		$sfpath = $m->settingsFilePath;
 
 		/* @var $cm CloneMode */
@@ -1522,7 +1522,7 @@
 									$md->releaseRemote();
 								}
 							} else {
-								$med = $this->vbox->openMedium($ma['medium']['location'],$ma['type']);
+								$med = $this->vbox->openMedium($ma['medium']['location'],$ma['type'],null,null);
 							}
 						} else {
 							$med = null;
@@ -1591,7 +1591,7 @@
 			if($state != 'Saved') {
 
 				// Network properties
-				$eprops = $n->getProperties();
+				$eprops = $n->getProperties(null);
 				$eprops = array_combine($eprops[1],$eprops[0]);
 				$iprops = array_map(create_function('$a','$b=explode("=",$a); return array($b[0]=>$b[1]);'),preg_split('/[\r|\n]+/',$args['networkAdapters'][$i]['properties']));
 				$inprops = array();
@@ -2028,7 +2028,7 @@
 						}
 					} else {
 						/* @var $med IMedium */
-						$med = $this->vbox->openMedium($ma['medium']['location'],$ma['type']);
+						$med = $this->vbox->openMedium($ma['medium']['location'],$ma['type'], null, null);
 					}
 				} else {
 					$med = null;
@@ -2111,7 +2111,7 @@
 			*/
 
 			// Network properties
-			$eprops = $n->getProperties();
+			$eprops = $n->getProperties(null);
 			$eprops = array_combine($eprops[1],$eprops[0]);
 			$iprops = array_map(create_function('$a','$b=explode("=",$a); return array($b[0]=>$b[1]);'),preg_split('/[\r|\n]+/',$args['networkAdapters'][$i]['properties']));
 			$inprops = array();
@@ -2519,7 +2519,7 @@
 	 */
 	public function remote_vboxGetEnumerationMap($args) {
 
-		$c = new $args['class'];
+		$c = new $args['class'](null, null);
 		return (@isset($args['ValueMap']) ? $c->ValueMap : $c->NameMap);
 	}
 
@@ -3697,7 +3697,7 @@
 			$hds = array();
 			$delete = $machine->unregister('DetachAllReturnHardDisksOnly');
 			foreach($delete as $hd) {
-				$hds[] = $this->vbox->openMedium($hd->location,'HardDisk')->handle;
+				$hds[] = $this->vbox->openMedium($hd->location,'HardDisk',null,null)->handle;
 			}
 
 			/* @var $progress IProgress */
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
@@ -3772,7 +3772,7 @@
 			$args['name'] = $_SESSION['user'] . '_' . $args['name'];
 
 		/* Check if file exists */
-		$filename = $this->vbox->composeMachineFilename($args['name'],($this->settings->phpVboxGroups ? '' : $args['group']),$this->vbox->systemProperties->defaultMachineFolder);
+		$filename = $this->vbox->composeMachineFilename($args['name'],($this->settings->phpVboxGroups ? '' : $args['group']),$this->vbox->systemProperties->defaultMachineFolder,null);
 
 		if($this->remote_fileExists(array('file'=>$filename))) {
 			return array('exists' => $filename);
@@ -3874,7 +3874,7 @@
 
 				$sc->releaseRemote();
 
-				$m = $this->vbox->openMedium($args['disk'],'HardDisk');
+				$m = $this->vbox->openMedium($args['disk'],'HardDisk',null,null);
 
 				$this->session->machine->attachDevice(trans($HDbusType,'UIMachineSettingsStorage'),0,0,'HardDisk',$m->handle);
 
@@ -3941,7 +3941,7 @@
 			if($at == 'NAT') $nd = $n->NATEngine; /* @var $nd INATEngine */
 			else $nd = null;
 
-			$props = $n->getProperties();
+			$props = $n->getProperties(null);
 			$props = implode("\n",array_map(create_function('$a,$b','return "$a=$b";'),$props[1],$props[0]));
 
 			$adapters[] = array(
@@ -4690,7 +4690,7 @@
 			$machine->lockMachine($this->session->handle, ((string)$machine->sessionState == 'Unlocked' ? 'Write' : 'Shared'));
 
 			/* @var $progress IProgress */
-			list($progress, $snapshotId) = $this->session->machine->takeSnapshot($args['name'], $args['description']);
+			list($progress, $snapshotId) = $this->session->machine->takeSnapshot($args['name'], $args['description'], null);
 
 			// Does an exception exist?
 			try {
@@ -4853,7 +4853,7 @@
 	    // Connect to vboxwebsrv
 	    $this->connect();
 
-	    $m = $this->vbox->openMedium($args['medium'],'HardDisk');
+	    $m = $this->vbox->openMedium($args['medium'],'HardDisk',null,null);
 
 	    $retval = $m->checkEncryptionPassword($args['password']);
 
@@ -4874,7 +4874,7 @@
 	    // Connect to vboxwebsrv
 	    $this->connect();
 
-	    $m = $this->vbox->openMedium($args['medium'], 'HardDisk', 'ReadWrite');
+	    $m = $this->vbox->openMedium($args['medium'], 'HardDisk', 'ReadWrite', null);
 
 	    /* @var $progress IProgress */
 	    $progress = $m->changeEncryption($args['old_password'],
@@ -4915,7 +4915,7 @@
 		// Connect to vboxwebsrv
 		$this->connect();
 
-		$m = $this->vbox->openMedium($args['medium'], 'HardDisk');
+		$m = $this->vbox->openMedium($args['medium'], 'HardDisk', null, null);
 
 		/* @var $progress IProgress */
 		$progress = $m->resize($args['bytes']);
@@ -4953,7 +4953,7 @@
 		$mid = $target->id;
 
 		/* @var $src IMedium */
-		$src = $this->vbox->openMedium($args['src'], 'HardDisk');
+		$src = $this->vbox->openMedium($args['src'], 'HardDisk', null, null);
 
 		$type = array(($args['type'] == 'fixed' ? 'Fixed' : 'Standard'));
 		if($args['split']) $type[] = 'VmdkSplit2G';
@@ -4991,7 +4991,7 @@
 		$this->connect();
 
 		/* @var $m IMedium */
-		$m = $this->vbox->openMedium($args['medium'], 'HardDisk');
+		$m = $this->vbox->openMedium($args['medium'], 'HardDisk', null, null);
 		$m->type = $args['type'];
 		$m->releaseRemote();
 
@@ -5074,7 +5074,7 @@
 		// Connect to vboxwebsrv
 		$this->connect();
 
-		return $this->vbox->composeMachineFilename($args['name'],($this->settings->phpVboxGroups ? '' : $args['group']),$this->vbox->systemProperties->defaultMachineFolder);
+		return $this->vbox->composeMachineFilename($args['name'],($this->settings->phpVboxGroups ? '' : $args['group']),$this->vbox->systemProperties->defaultMachineFolder,null);
 
 	}
 
@@ -5129,7 +5129,7 @@
 		$this->connect();
 
 		/* @var $m IMedium */
-		$m = $this->vbox->openMedium($args['medium'],$args['type']);
+		$m = $this->vbox->openMedium($args['medium'],$args['type'], null, null);
 		$mediumid = $m->id;
 
 		// connected to...
@@ -5211,7 +5211,7 @@
 		if(!$args['type']) $args['type'] = 'HardDisk';
 
 		/* @var $m IMedium */
-		$m = $this->vbox->openMedium($args['medium'],$args['type']);
+		$m = $this->vbox->openMedium($args['medium'],$args['type'], null, null);
 
 		if($args['delete'] && @$this->settings->deleteOnRemove && (string)$m->deviceType == 'HardDisk') {
 
@@ -5380,7 +5380,7 @@
 			// Normal medium
 			} else {
 				/* @var $med IMedium */
-				$med = $this->vbox->openMedium($args['medium']['location'],$args['medium']['deviceType']);
+				$med = $this->vbox->openMedium($args['medium']['location'],$args['medium']['deviceType'],null,null);
 			}
 		}
 
@@ -5445,7 +5445,7 @@
 		}
 
 		// For $fixed value
-		$mvenum = new MediumVariant();
+		$mvenum = new MediumVariant(null, null);
 		$variant = 0;
 
 		foreach($m->variant as $mv) {
@@ -5825,4 +5825,3 @@
 		return @$rcodes['0x'.strtoupper(dechex($c))] . ' (0x'.strtoupper(dechex($c)).')';
 	}
 }
-
