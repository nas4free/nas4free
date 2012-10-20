<?php
/*
	zfs.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard (olivier@freenas.org).
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met: 

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer. 
	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution. 

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies, 
	either expressed or implied, of the NAS4Free Project.
*/
require_once("config.inc");
require_once("util.inc");

/**
 * Get list of available zpools
 * @return An array containg the requested informations:
 * @code
 * [poolname] => array(
 *   [name] => pool
 *   [size] => 4.66G
 *   [used] => 112K
 *   [avail] => 4.66G
 *   [cap] => 0%
 *   [dedup] => 1.00x
 *   [health] => ONLINE
 *   [altroot] => -)
 * @endcode
 */
function zfs_get_pool_list() {
	$result = array();

	mwexec2("zpool list -H", $rawdata);
	foreach ($rawdata as $line) {
		$aline = preg_split("/\s+/", $line);
		if (7 > count($aline)) // Ensure we process the correct line data
			continue;

		$poolname = $aline[0];

		$result[$poolname] = array();
		$result[$poolname]['name'] = $aline[0];
		$result[$poolname]['size'] = $aline[1];
		$result[$poolname]['used'] = $aline[2];
		$result[$poolname]['avail'] = $aline[3];
		$result[$poolname]['cap'] = $aline[4];
		$result[$poolname]['dedup'] = $aline[5];		
		$result[$poolname]['health'] = $aline[6];
		$result[$poolname]['altroot'] = $aline[7];
		//$result[$poolname]['atime'] = $aline[8];

		// Get correct used/available disk space.
		// See http://bugs.opensolaris.org/bugdatabase/view_bug.do?bug_id=6308817
		mwexec2("zfs list -H -o used,available {$poolname}", $rawdata2);
		if (preg_match("/^(\S+)\s+(\S+)$/", $rawdata2[0], $matches)) {
			$result[$poolname]['used'] = $matches[1];
			$result[$poolname]['avail'] = $matches[2];
		}
		// Unset variable, otherwise data will be attached next loop.
		unset($rawdata2);
	}

	return $result;
}

/**
 * Configure, create and start a zpool.
 * @param[in] uuid	The UUID of the zpool.
 * @return Return 0 if successful, otherwise 1
 */
function zfs_zpool_configure($uuid) {
	global $config;

	if (!is_array($config['zfs']['pools']['pool']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['pools']['pool'], "uuid");
	if (false === $index)
		return 1;

	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool))
		 return 1;

	// Set the default mount point.
	$mountpoint = "/mnt/{$pool['name']}";
	if (!empty($pool['mountpoint']))
		$mountpoint = $pool['mountpoint'];

	// Additional parameter
	$param = "-m {$mountpoint} ";

	if (isset($pool['force']))
		$param .= "-f ";
	if (!empty($pool['root']))
		$param .= "-R {$pool['root']} ";

	// Create the pool
	$cmd = "zpool create {$param} {$pool['name']} ";

	// Load nop class
	if (@mwexec("/sbin/kldstat -q -m g_nop") != 0) {
		write_log("Load NOP GEOM class");
		@mwexec("/sbin/kldload geom_nop.ko");
	}

	$dev_cache = "";
	$dev_log = "";
	foreach ($pool['vdevice'] as $vdevicev) {
		$index = array_search_ex($vdevicev, $config['zfs']['vdevices']['vdevice'], "name");
		if (false === $index)
			continue;

		$vdevice = $config['zfs']['vdevices']['vdevice'][$index];

		switch ($vdevice['type']) {
			case "stripe":
			case "disk":
				break;

			case "cache":
				$dev_cache = "{$vdevice['type']} ";
				if (is_array($vdevice['device'])) {
					foreach ($vdevice['device'] as $vdevicev) {
						$dev_cache .= "{$vdevicev} ";
					}
				}
				continue 2;

			case "log":
				$dev_log = "{$vdevice['type']} ";
				if (is_array($vdevice['device'])) {
					foreach ($vdevice['device'] as $vdevicev) {
						$dev_log .= "{$vdevicev} ";
					}
				}
				continue 2;

			case "log-mirror":
				$dev_log = "log mirror ";
				if (is_array($vdevice['device'])) {
					foreach ($vdevice['device'] as $vdevicev) {
						$dev_log .= "{$vdevicev} ";
					}
				}
				continue 2;

			default:
				$cmd .= "{$vdevice['type']} ";
				break;
		}

		if (is_array($vdevice['device'])) {
			foreach ($vdevice['device'] as $vdevicev) {
				if (isset($vdevice['aft4k'])) {
					$gnop_cmd = "gnop create -S 4096 {$vdevicev}";
					write_log("$gnop_cmd");
					$result = mwexec($gnop_cmd, true);
					if ($result != 0)
						return 1;
					$cmd .= "{$vdevicev}.nop ";
				} else {
					$cmd .= "{$vdevicev} ";
				}
			}
		}
	}
	$cmd .= "{$dev_cache} ";
	$cmd .= "{$dev_log} ";

	write_log("$cmd");
	$result = mwexec($cmd, true);
	if (0 != $result)
		return 1;

	// Modify access restrictions.
	@chmod($mountpoint, octdec(777));

	return 0;
}

/**
 * Destroy a zpool.
 * @param[in] uuid	The UUID of the zpool.
 * @return 0 if successful, otherwise 1
 */
function zfs_zpool_destroy($uuid) {
	global $config;

	if (!is_array($config['zfs']['pools']['pool']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['pools']['pool'], "uuid");
	if (false === $index)
		return 1;

	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool))
		 return 1;

	// Destroy the pool
	$cmd ="zpool destroy {$pool['name']}";
	write_log($cmd);
	$result = mwexec($cmd, true);

	// Remove mount point.
	$mountpoint = "/mnt/{$pool['name']}";
	if (!empty($pool['mountpoint']))
		$mountpoint = $pool['mountpoint'];

	@rmdir($mountpoint);

	// destroy gnop devices
	if ($result != 0) {
		foreach ($pool['vdevice'] as $vdevicev) {
			$index = array_search_ex($vdevicev, $config['zfs']['vdevices']['vdevice'], "name");
			if (false === $index)
				continue;
			$vdevice = $config['zfs']['vdevices']['vdevice'][$index];
			if (is_array($vdevice['device'])) {
				foreach ($vdevice['device'] as $vdevicev) {
					if (isset($vdevice['aft4k'])) {
						$gnop_cmd = "gnop destroy {$vdevicev}.nop";
						write_log("$gnop_cmd");
						$result = mwexec($gnop_cmd, true);
					}
				}
			}
		}
	}

	return $result;
}

/**
 * Update properties of a zpool.
 * @param[in] uuid	The UUID of the zpool to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_zpool_properties($uuid) {
	global $config;

	if (!is_array($config['zfs']['pools']['pool']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['pools']['pool'], "uuid");
	if (false === $index)
		return 1;

	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool))
		 return 1;

	$result = 0;

	$mountpoint = "/mnt/{$pool['name']}";
	if (!empty($pool['mountpoint']))
		$mountpoint = $pool['mountpoint'];

	// unmount the pool
	$cmd = "zfs umount -a";
	write_log($cmd);
	$result |= mwexec($cmd, true);

	// update properties
	foreach (explode(" ", "mountpoint") as $attr) {
		$param = "";

		switch ($attr) {
			case "mountpoint":
				if (!empty($mountpoint))
					$param = "mountpoint={$mountpoint} ";
				break;
		}

		// Update zpool properties
		if (!empty($param)) {
			$cmd = "zfs set {$param} {$pool['name']}";
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}

	// remount the pool
	$cmd = "zfs mount -a";
	write_log($cmd);
	$result |= mwexec($cmd, true);

	return $result;
}

// Wrapper to execute zpool commands.
// $command - Command to execute (e.g. upgrade).
// $param   - The command parameter.
// $verbose - Display command results or hide them.
// $stderr  - Redirect stderr to stdout to display error messages too.
// Return 0 if successful, 1 if error.
function zfs_zpool_cmd($command, $param, $verbose = false, $stderr = true, $out = false, &$output = array()) {
	$result = 1;
	$cmd = "zpool {$command} {$param}";
	write_log($cmd);

	if (true === $verbose) {
		if (true === $stderr)
			$cmd .= " 2>&1"; // Redirect error message to stdout
		system($cmd, $result);
	}	else {
		if (true === $out) {
			mwexec2($cmd, $output, $result);
		} else {
			$result = mwexec($cmd, true);
		}
	}

	return $result;
}

/**
 * Configure, create and start a ZFS dataset.
 * @param[in] uuid	The UUID of the dataset to be configured.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_dataset_configure($uuid) {
	global $config;

	if (!is_array($config['zfs']['datasets']['dataset']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid");
	if (false === $index)
		return 1;

	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset))
		 return 1;

	// Additional parameter
	$param = "";

	if (!empty($dataset['compression']))
		$param .= "-o compression={$dataset['compression']} ";

	if (!empty($dataset['dedup']))
		$param .= "-o dedup={$dataset['dedup']} ";

	if (!empty($dataset['sync']))
		$param .= "-o sync={$dataset['sync']} ";

	if (!empty($dataset['atime']))
		$param .= "-o atime={$dataset['atime']} ";
	
	if (!isset($dataset['canmount']))
		$param .= "-o canmount=off ";

	if (isset($dataset['readonly']))
		$param .= "-o readonly=on ";

	if (!empty($dataset['reservation']))
		$param .= "-o reservation={$dataset['reservation']} ";

	if (!empty($dataset['quota']))
		$param .= "-o quota={$dataset['quota']} ";

	if (!isset($dataset['xattr']))
		$param .= "-o xattr=off ";

	if (isset($dataset['snapdir']))
		$param .= "-o snapdir=visible ";

	// Create the dataset
	$cmd = "zfs create {$param} {$dataset['pool'][0]}/{$dataset['name']}";
	write_log($cmd);

	return mwexec($cmd, true);
}

/**
 * Delete a ZFS dataset.
 * @param[in] uuid	The UUID of the dataset to be deleted.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_dataset_destroy($uuid) {
	global $config;

	if (!is_array($config['zfs']['datasets']['dataset']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid");
	if (false === $index)
		return 1;

	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset))
		 return 1;

	// Destroy the dataset
	$cmd = "zfs destroy {$dataset['pool'][0]}/{$dataset['name']}";
	write_log($cmd);

	return mwexec($cmd, true);
}

/**
 * Update properties of a ZFS dataset.
 * @param[in] uuid	The UUID of the dataset to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_dataset_properties($uuid) {
	global $config;

	if (!is_array($config['zfs']['datasets']['dataset']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid");
	if (false === $index)
		return 1;

	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset))
		 return 1;

	$mounted = false;
	mwexec2("zfs list -H -t filesystem -o mounted {$dataset['pool'][0]}/{$dataset['name']}", $rawdata);
	if (!empty($rawdata) && $rawdata[0] == "yes")
		$mounted = true;

	$mountpoint = "/mnt/{$dataset['pool'][0]}/{$dataset['name']}";
	mwexec2("zfs list -H -t filesystem -o mountpoint {$dataset['pool'][0]}/{$dataset['name']}", $rawdata2);
	if (!empty($rawdata2) && $rawdata2[0] != "none" && $rawdata2[0] != "legacy")
		$mountpoint = $rawdata2[0];

	$result = 0;

	foreach (explode(" ", "compression dedup sync atime canmount readonly reservation quota xattr snapdir") as $attr) {
		$param = "";

		switch ($attr) {
			case "compression":
				if (!empty($dataset['compression']))
					$param = "compression={$dataset['compression']}";
				break;

			case "dedup":
				if (!empty($dataset['dedup']))
					$param = "dedup={$dataset['dedup']}";
				break;

			case "sync":
				if (!empty($dataset['sync']))
					$param = "sync={$dataset['sync']}";
				break;

			case "atime":
				if (!empty($dataset['atime']))
					$param = "atime={$dataset['atime']}";
				break;

			case "canmount":
				if (!isset($dataset['canmount']))
					$param = "canmount=off";
				else
					$param = "canmount=on";
				break;

			case "readonly":
				if (isset($dataset['readonly']))
					$param = "readonly=on";
				else
					$param = "readonly=off";
				break;

			case "reservation":
				if (!empty($dataset['reservation']))
					$param = "reservation={$dataset['reservation']}";
				else
					$param = "reservation=none";
				break;

			case "quota":
				if (!empty($dataset['quota']))
					$param = "quota={$dataset['quota']}";
				else
					$param = "quota=none";
				break;

			case "xattr":
				/*
				if (!isset($dataset['xattr']))
					$param = "xattr=off";
				else
					$param = "xattr=on";
				*/
				break;

			case "snapdir":
				if (!isset($dataset['snapdir']))
					$param = "snapdir=hidden";
				else
					$param = "snapdir=visible";
				break;
		}

		// Update dataset properties
		if (!empty($param)) {
			$cmd = "zfs set {$param} {$dataset['pool'][0]}/{$dataset['name']}";
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}
	if (isset($dataset['canmount']) && !$mounted) {
		$cmd = "zfs mount {$dataset['pool'][0]}/{$dataset['name']}";
		write_log($cmd);
		$result |= mwexec($cmd, true);
	}
	if (!empty($mountpoint) && isset($dataset['canmount'])) {
		if (is_array($dataset['accessrestrictions'])) {
			$mode = $dataset['accessrestrictions']['mode'];
			$owner = $dataset['accessrestrictions']['owner'];
			$group = $dataset['accessrestrictions']['group'][0];
write_log("{$mountpoint} $mode $owner $group");
			if (file_exists($mountpoint) && is_writable($mountpoint)) {
				if (!@chmod($mountpoint, octdec($mode)) || !@chown($mountpoint, $owner) || !@chgrp($mountpoint, $group)) {
					write_log("Error: Failed to set access restrictions on directory '{$mountpoint}'");
				}
			}
		}
	}

	return $result;
}

/**
 * Configure, create and start a ZFS volume.
 * @param[in] uuid	The UUID of the volume to be configured.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_volume_configure($uuid) {
	global $config;

	if (!is_array($config['zfs']['volumes']['volume']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], "uuid");
	if (false === $index)
		return 1;

	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume))
		 return 1;

	// Additional parameter
	$param = "";

	if (isset($volume['sparse']))
		$param .= "-s ";

	if (!empty($volume['compression']))
		$param .= "-o compression={$volume['compression']} ";

	if (!empty($volume['dedup']))
		$param .= "-o dedup={$volume['dedup']} ";
		
	if (!empty($volume['sync']))
		$param .= "-o sync={$volume['sync']} ";
		
	if (!empty($volume['atime']))
		$param .= "-o atime={$volume['atime']} ";		

	// Create the volume
	$cmd = "zfs create -V {$volume['volsize']} {$param} {$volume['pool'][0]}/{$volume['name']}";
	write_log($cmd);

	return mwexec($cmd, true);
}

/**
 * Delete a ZFS volume.
 * @param[in] uuid	The UUID of the volume to be deleted.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_volume_destroy($uuid) {
	global $config;

	if (!is_array($config['zfs']['volumes']['volume']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], "uuid");
	if (false === $index)
		return 1;

	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume))
		 return 1;

	// Destroy the volume
	$cmd = "zfs destroy {$volume['pool'][0]}/{$volume['name']}";
	write_log($cmd);

	return mwexec($cmd, true);
}

function zfs_get_volume_reservation($volsize) {
	if (empty($volsize)) {
		return "";
	}
	if (preg_match("/^(\d+(\.\d+)?)(.*)$/", $volsize, $m)) {
		$size = (float)$m[1] * 1.03;
		$suffix = $m[3];
		if (preg_match("/(m|k|^$)/", $suffix, $m)) {
			$size = ceil($size);
		}
		return "{$size}{$suffix}";
	}
	return flase;
}

/**
 * Update properties of a ZFS volume.
 * @param[in] uuid	The UUID of the volume to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_volume_properties($uuid) {
	global $config;

	if (!is_array($config['zfs']['volumes']['volume']))
		return 1;

	$index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], "uuid");
	if (false === $index)
		return 1;

	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume))
		 return 1;

	$reservation = zfs_get_volume_reservation($volume['volsize']);
	if (false === $reservation)
		return 1;

	$result = 0;

	foreach (explode(" ", "volsize sparse compression dedup sync atime") as $attr) {
		$param = "";

		switch ($attr) {
			case "volsize":
				if (!empty($volume['volsize']))
					$param = "volsize={$volume['volsize']}";
				break;

			case "sparse":
				if (isset($volume['sparse']))
					$param = "refreservation=none";
				else if (!empty($volume['volsize']))
					$param = "refreservation={$volume['volsize']}";
				break;

			case "compression":
				if (!empty($volume['compression']))
					$param = "compression={$volume['compression']}";
				break;

			case "dedup":
				if (!empty($volume['dedup']))
					$param = "dedup={$volume['dedup']}";
				break;

			case "sync":
				if (!empty($volume['sync']))
					$param = "sync={$volume['sync']}";
				break;

			case "atime":
				if (!empty($volume['atime']))
					$param = "atime={$volume['atime']}";
				break;
		}		
		
		// Update volume properties
		if (!empty($param)) {
			$cmd = "zfs set {$param} {$volume['pool'][0]}/{$volume['name']}";
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}

	return $result;
}

/**
 * Configure, create and start a ZFS snapshot.
 * @param[in] snapshot	The name of the snapshot to be configured.
 * @return An array containg the result:
 * @code
 * [output] => array("lines")
 * [retval] => 0 if successful, otherwise 1
 * @endcode
 */
function zfs_snapshot_configure($snapshot) {
	global $config;
	$result = array("output" => array("error"), "retval" => -1);

	if (empty($snapshot) || empty($snapshot['snapshot']))
		return $result;
	if (!preg_match('/.+\@.+/', $snapshot['snapshot']))
		return $result;

	// Additional parameter
	$param = "";

	if (!empty($snapshot['recursive']))
		$param .= "-r ";

	// Create the snapshot
	$cmd = "zfs snapshot {$param} {$snapshot['snapshot']}";
	write_log($cmd);

//	return mwexec($cmd, true);
	// Execute the command, and save the log.
	$redirect = "2>&1";
	exec("{$cmd} {$redirect}", $output, $retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}

/**
 * Delete a ZFS snapshot.
 * @param[in] snapshot	The name of the snapshot to be deleted.
 * @return An array containg the result:
 * @code
 * [output] => array("lines")
 * [retval] => 0 if successful, otherwise 1
 * @endcode
 */
function zfs_snapshot_destroy($snapshot) {
	global $config;
	$result = array("output" => array("error"), "retval" => -1);

	if (empty($snapshot) || empty($snapshot['snapshot']))
		return $result;
	if (!preg_match('/.+\@.+/', $snapshot['snapshot']))
		return $result;

	// Additional parameter
	$param = "";

	if (!empty($snapshot['recursive']))
		$param .= "-r ";

	// Destroy the snapshot
	$cmd = "zfs destroy {$param} {$snapshot['snapshot']}";
	write_log($cmd);

//	return mwexec($cmd, true);
	// Execute the command, and save the log.
	$redirect = "2>&1";
	exec("{$cmd} {$redirect}", $output, $retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}

/**
 * Clone from a ZFS snapshot.
 * @param[in] snapshot	The name of the snapshot to be cloned.
 * @return An array containg the result:
 * @code
 * [output] => array("lines")
 * [retval] => 0 if successful, otherwise 1
 * @endcode
 */
function zfs_snapshot_clone($snapshot) {
	global $config;
	$result = array("output" => array("error"), "retval" => -1);

	if (empty($snapshot) || empty($snapshot['snapshot']))
		return $result;
	if (!preg_match('/.+\@.+/', $snapshot['snapshot']))
		return $result;
	//if (empty($snapshot['pool']) || empty($snapshot['name']))
	//	return $result;
	if (empty($snapshot['path']))
		return $result;

	// Additional parameter
	$param = "";

	// Clone the snapshot
	$cmd = "zfs clone {$param} {$snapshot['snapshot']} {$snapshot['path']}";
	write_log($cmd);

//	return mwexec($cmd, true);
	// Execute the command, and save the log.
	$redirect = "2>&1";
	exec("{$cmd} {$redirect}", $output, $retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}

/**
 * Update properties of a ZFS snapshot.
 * @param[in] snapshot	The name of the snapshot to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_snapshot_properties($snapshot) {
	global $config;

	if (empty($snapshot) || empty($snapshot['snapshot']))
		return -1;
	if (!preg_match('/.+\@.+/', $snapshot['snapshot']))
		return -1;

	$result = 0;

	foreach (explode(" ", "dummy") as $attr) {
		$param = "";

		switch ($attr) {
		}

		// Update snapshot properties
		if (!empty($param)) {
			$cmd = "zfs set {$param} {$snapshot['snapshot']}";
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}

	return $result;
}

/**
 * Delete a ZFS clone filesystem, volume.
 * @param[in] dataset	The name of the dataset to be deleted.
 * @return An array containg the result:
 * @code
 * [output] => array("lines")
 * [retval] => 0 if successful, otherwise 1
 * @endcode
 */
function zfs_clone_destroy($clone) {
	global $config;
	$result = array("output" => array("error"), "retval" => -1);

	if (empty($clone) || empty($clone['path']))
		return $result;
	if (!preg_match('/.+\/.+/', $clone['path']))
		return $result;

	// Additional parameter
	$param = "";

	// Destroy the snapshot
	$cmd = "zfs destroy {$param} {$clone['path']}";
	write_log($cmd);

//	return mwexec($cmd, true);
	// Execute the command, and save the log.
	$redirect = "2>&1";
	exec("{$cmd} {$redirect}", $output, $retval);
	$result['output'] = $output;
	$result['retval'] = $retval;
	return $result;
}

// Update notify for ZFS functions
function zfs_updatenotify_process($key, $function) {
	$result = array("output" => array("error"), "retval" => -1);
	$a_notification = updatenotify_get($key);
	if (!is_array($a_notification)) {
		$result = array("output" => array("empty"), "retval" => 0);
		return $result;
	}

	foreach ($a_notification as $notificationv) {
		$ret = call_user_func($function, $notificationv['mode'], $notificationv['data']);
		if (0 != $ret['retval']) {
			return $ret;
		}
	}
	$result = array("output" => array(), "retval" => 0);
	return $result;
}

// Returns true if $poolname is valid.
function zfs_is_valid_poolname($poolname) {
	if (!is_string($poolname))
		return false;

	// Empty pool names are not allowed.
	if (empty($poolname))
		return false;

	// The pool names "mirror", "raidz" and "spare" are reserved, as are
	// names beginning with the pattern "c[0-9]".
	if (preg_match("/^(mirror|raidz|spare|c[0-9]).*$/", $poolname))
		return false;

	// The pool name must begin with a letter, and can only contain
	// alphanumeric characters as well as underscore ("_"), dash ("-") and
	// period (".").
	if (preg_match("/^[a-zA-Z]([a-zA-Z0-9\_\-\.\s])*$/", $poolname))
		return true;

	return false;
}
?>
