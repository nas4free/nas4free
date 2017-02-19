<?php
/*
	zfs.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
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

/*
 * Get list of available zpools
 * @return An array containg the requested informations:
 * @code
 * [poolname] => array(
 *   [name] => pool
 *   [size] => 4.66G
 *   [alloc]
 *   [free]
 *   [expandsz]
 *   [frag] => 0%
 *   [cap] => 0%
 *   [dedup] => 1.00x
 *   [health] => ONLINE
 *   [altroot] => -)
 *   [used] => 112K
 *   [avail] => 4.66G
 * @endcode
 */
function zfs_get_pool_list() {
	$a_result = [];
	$a_zpool = cli_zpool_info();
	if (!empty($a_zpool)) {
		foreach ($a_zpool as $r_pool) {
			// map names of zpool list -o options to local options
			$poolname = $r_pool['name'];
			$r_poolinfo = [];
			$r_poolinfo['name'] = $poolname;
			$r_poolinfo['size'] = $r_pool['size'];
			$r_poolinfo['alloc'] = $r_pool['alloc'];
			$r_poolinfo['free'] = $r_pool['free'];
			$r_poolinfo['expandsz'] = $r_pool['expandsz'];
			$r_poolinfo['frag'] = $r_pool['frag'];
			$r_poolinfo['cap'] = $r_pool['cap'];
			$r_poolinfo['dedup'] = $r_pool['dedup'];
			$r_poolinfo['health'] = $r_pool['health'];
			$r_poolinfo['altroot'] = $r_pool['altroot'];
			$a_zfs = cli_zfs_info($poolname, 'used,avail');
			if (!empty($a_zfs)) {
				foreach ($a_zfs as $r_zfs) { // there should be only one row
					$r_poolinfo['used'] = $r_zfs['used'];
					$r_poolinfo['avail'] = $r_zfs['avail'];
				}
			} else { // populate a complete set
				$r_poolinfo['used'] = 'Unknown';
				$r_poolinfo['avail'] = 'Unknown';
			}
			$a_result[$poolname] = $r_poolinfo;
		}
	}
	return $a_result;
}
/*
 * Get zpool information via cli
 * If you don't specify options the preset options will be returned.
 * If you specify options they must be separated with a comma. Whitespaces are ignored
 * Option keys returned are lowercase.
 * The function returns an empty array on error or when the zpool is unknown to zfs.
 */
function cli_zpool_info($name = '', $options = 'name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot') {
	$a_return = [];
	$a_key = [];
	$a_val = [];
	$cliname = '';
	if (preg_match('/\S/', $name)) {
		$name = ltrim($name);
		$cliname = escapeshellarg($name);
	}
	if (!preg_match('/\S/', $options)) { // options must not be blank
		$options = 'name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot';
	}
	$clioptions = preg_replace('/\s+/', '', $options); // remove all whitespaces
	$a_key = preg_split('/,/', $clioptions); // get the key fields
	if (false !== array_search('all', $a_key)) { // has the all option been selected?
		$clioptions = 'all'; // reset other options, they are included in 'all'
		$a_key = []; // undetermined
		$clicmd = sprintf('zpool list -o %1$s %2$s', $clioptions, $cliname);
		if (isset($a_line)) { unset($a_line); }
		mwexec2($clicmd, $a_line, $exitcode);
		if ((0 == $exitcode) && (0 < count($a_line))) {  // we need the first line to collect the header names
			preg_match_all('/S+/', $a_line[0], $a_key);
		}	
	}	
	if (!empty($a_key)) {
		$a_key = array_map('strtolower', $a_key); // $a_key to lowercase
		$clicmd = sprintf('zpool list -H -o %1$s %2$s', $clioptions, $cliname);
		if (isset($a_line)) { unset($a_line); }
		mwexec2($clicmd, $a_line, $exitcode);
		if ((0 == $exitcode) && (0 < count($a_line))) {  // at least 1 line must be returned
			foreach ($a_line as $r_line) {
				$a_val = preg_split('/\t/', $r_line);
				if (count($a_key) === count($a_val)) {
					$a_return[] = array_combine($a_key, $a_val);
				}
			}
		}
	}
	return $a_return;
}

/*
 * Get zfs option information via cli
 * If you don't specify options the preset options will be returned.
 * If you specify options they must be separated with a comma. Whitespaces are ignored
 * Option keys returned are lowercase.
 * The function returns an empty array on error or when the zpool is unknown to zfs.
 */
function cli_zfs_info($name = '', $options = 'name,used,avail,refer,mountpoint') {
	$a_return = [];
	$a_key = [];
	$a_val = [];
	$cliname = '';
	if (preg_match('/\S/', $name)) {
		$name = ltrim($name);
		$cliname = escapeshellarg($name);
	}
	if (!preg_match('/\S/', $options)) { // options must not be blank
		$options = 'name,used,avail,refer,mountpoint';
	}
	$clioptions = preg_replace('/\s+/', '', $options); // remove all whitespaces
	$a_key = preg_split('/,/', $clioptions); // get the key fields
	if (false !== array_search('all', $a_key)) { // has the all option been selected?
		$clioptions = 'all'; // reset other options, they are included in 'all'
		$a_key = []; // undetermined
		$clicmd = sprintf('zfs list -o %1$s %2$s', $clioptions, $cliname);
		if (isset($a_line)) { unset($a_line); }
		mwexec2($clicmd, $a_line, $exitcode);
		if ((0 == $exitcode) && (0 < count($a_line))) {  // we need the first line to collect the header names
			preg_match_all('/S+/', $a_line[0], $a_key);
		}	
	}	
	if (!empty($a_key)) {
		$a_key = array_map('strtolower', $a_key); // $a_key to lowercase
		$clicmd = sprintf('zfs list -H -o %1$s %2$s', $clioptions, $cliname);
		if (isset($a_line)) { unset($a_line); }
		mwexec2($clicmd, $a_line, $exitcode);
		if ((0 == $exitcode) && (0 < count($a_line))) {  // at least 1 line must be returned
			foreach ($a_line as $r_line) {
				$a_val = preg_split('/\t/', $r_line);
				if (count($a_key) === count($a_val)) {
					$a_return[] = array_combine($a_key, $a_val);
				}
			}
		}
	}
	return $a_return;
}
/**
 * Configure, create and start a zpool.
 * @param[in] uuid	The UUID of the zpool.
 * @return Return 0 if successful, otherwise 1
 */
function zfs_zpool_configure($uuid) {
	global $config;
	$param = '';
	// get pool
	if (!(isset($config['zfs']) && isset($config['zfs']['pools']) && isset($config['zfs']['pools']['pool']) && is_array($config['zfs']['pools']['pool']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['pools']['pool'], 'uuid'))) { return 1; }
	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool)) { return 1; }
	// get pool name
	if (!(isset($pool['name']) && preg_match('/\S/', $pool['name']))) { return 1; }
	$poolname = ltrim($pool['name']);
	// get mount point
	$mountpoint = sprintf('/mnt/%1$s', $poolname); // default mountmoint
	if (isset($pool['mountpoint']) && preg_match('/\S/', $pool['mountpoint'])) { // alternative mountpoint replaces default mountpoint
		$mountpoint = ltrim($pool['mountpoint']);
	}
	$param .= sprintf('-m %1$s ', escapeshellarg($mountpoint));
	// add force parameter if set
	if (isset($pool['force'])) {
		$param .= '-f ';
	}
	// add root parameter if set
	if (isset($pool['root']) && preg_match('/\S/', $pool['root'])) {
		$param .= sprintf('-R %1$s ', escapeshellarg($pool['root']));
	}
	// create the pool, keep blank - more parameters to follow
	$cmd = sprintf('zpool create %1$s %2$s ', $param, escapeshellarg($pool['name']));
	// Load nop class
	if (@mwexec("/sbin/kldstat -q -m g_nop") != 0) {
		write_log("Load NOP GEOM class");
		@mwexec("/sbin/kldload geom_nop.ko");
	}
	// Set minimum ashift=12 (4KB)
	if (isset($pool['ashift']) && $pool['ashift'] == 9) {
		@mwexec('/sbin/sysctl -q vfs.zfs.min_auto_ashift=9');
	} else {
		@mwexec('/sbin/sysctl -q vfs.zfs.min_auto_ashift=12');
	}
	$dev_cache = "";
	$dev_log = "";
	foreach ($pool['vdevice'] as $vdevicev) {
		$index = array_search_ex($vdevicev, $config['zfs']['vdevices']['vdevice'], "name");
		if (false === $index) { continue; }

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
					if ($result != 0) { return 1; }
					$cmd .= "{$vdevicev}.nop ";
				} else {
					$cmd .= "{$vdevicev} ";
				}
			}
		}
	}
	$cmd .= "{$dev_cache} ";
	$cmd .= "{$dev_log} ";

	write_log($cmd);
	$result = mwexec($cmd, true);
	if (0 != $result) { return 1; }
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
	// get pool
	if (!(isset($config['zfs']) && isset($config['zfs']['pools']) && isset($config['zfs']['pools']['pool']) && is_array($config['zfs']['pools']['pool']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['pools']['pool'], 'uuid'))) { return 1; }
	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool)) { return 1; }
	// get pool name
	if (!(isset($pool['name']) && preg_match('/\S/', $pool['name']))) { return 1; }
	$poolname = ltrim($pool['name']);
	// get mount point
	$mountpoint = sprintf('/mnt/%1$s', $poolname);
	if (isset($pool['mountpoint']) && preg_match('/\S/', $pool['mountpoint'])) {
		$mountpoint = ltrim($pool['mountpoint']);
	}
	// try to unmount first
	// XXX
	// XXX
	// XXX
	// destroy pool
	$cmd = sprintf('zpool destroy %1$s', escapeshellarg($poolname));
	write_log($cmd);
	$result = mwexec($cmd, true);
	// remove mount moint
	@rmdir($mountpoint);
	// destroy gnop devices
	if ($result == 0) {
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
	// get pool
	if (!(isset($config['zfs']) && isset($config['zfs']['pools']) && isset($config['zfs']['pools']['pool']) && is_array($config['zfs']['pools']['pool']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['pools']['pool'], 'uuid'))) { return 1; }
	$pool = $config['zfs']['pools']['pool'][$index];
	if (!is_array($pool)) { return 1; }
	// get pool name
	if (!(isset($pool['name']) && preg_match('/\S/', $pool['name']))) { return 1; }
	$poolname = ltrim($pool['name']);
	// get mount point
	$mountpoint = sprintf('/mnt/%1$s', $poolname);
	if (isset($pool['mountpoint']) && preg_match('/\S/', $pool['mountpoint'])) {
		$mountpoint = ltrim($pool['mountpoint']);
	}

	$result = 0;

	// unmount the pool
	$cmd = 'zfs umount -a';
	write_log($cmd);
	$result |= mwexec($cmd, true);
	// update properties
	foreach (explode(' ', 'mountpoint') as $attr) {
		$param = '';
		switch ($attr) {
			case "mountpoint":
				if (!empty($mountpoint))
					$param = sprintf('mountpoint=%1$s', escapeshellarg($mountpoint));
				break;
		}

		// Update zpool properties
		if (!empty($param)) {
			$cmd = sprintf('zfs set %1$s %2$s', $param, escapeshellarg($poolname));
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}
	// remount the pool
	$cmd = 'zfs mount -a';
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
	// get dataset
	if (!(isset($config['zfs']) && isset($config['zfs']['datasets']) && isset($config['zfs']['datasets']['dataset']) && is_array($config['zfs']['datasets']['dataset']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid"))) { return 1; }
	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset)) { return 1; }
	// get pool name
	if (!(isset($dataset['pool']) && is_array($dataset['pool']) && (0 < count($dataset['pool'])) && preg_match('/\S/', $dataset['pool'][0]))) { return 1; }
	$poolname = ltrim($dataset['pool'][0]);
	// get dataset name
	if (!(isset($dataset['name']) && preg_match('/\S/', $dataset['name']))) { return 1; }
	$datasetname = ltrim($dataset['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $datasetname);
	// additional parameter
	$param = '';
	if (isset($dataset['aclinherit']) && preg_match('/\S/', $dataset['aclinherit'])) {
		$param .= sprintf('-o aclinherit=%1$s ', trim($dataset['aclinherit']));
	}
	if (isset($dataset['aclmode']) && preg_match('/\S/', $dataset['aclmode'])) {
		$param .= sprintf('-o aclmode=%1$s ', trim($dataset['aclmode']));
	}
	if (isset($dataset['atime']) && preg_match('/\S/', $dataset['atime'])) {
		$param .= sprintf('-o atime=%1$s ', trim($dataset['atime']));
	}
	if (!isset($dataset['canmount'])) {
		$param .= '-o canmount=off ';
	}
	if (isset($dataset['casesensitivity']) && preg_match('/\S/', $dataset['casesensitivity'])) {
		$param .= sprintf('-o casesensitivity=%1$s ', trim($dataset['casesensitivity']));
	}
	if (isset($dataset['compression']) && preg_match('/\S/', $dataset['compression'])) {
		$param .= sprintf('-o compression=%1$s ', trim($dataset['compression']));
	}
	if (isset($dataset['dedup']) && preg_match('/\S/', $dataset['dedup'])) {
		$param .= sprintf('-o dedup=%1$s ', trim($dataset['dedup']));
	}
	if (isset($dataset['quota']) && preg_match('/\S/', $dataset['quota'])) {
		$param .= sprintf('-o quota=%1$s ', trim($dataset['quota']));
	}
	if (isset($dataset['readonly'])) {
		$param .= '-o readonly=on ';
	}
	if (isset($dataset['reservation']) && preg_match('/\S/', $dataset['reservation'])) {
		$param .= sprintf('-o reservation=%1$s ', trim($dataset['reservation']));
	}
	if (isset($dataset['snapdir'])) {
		$param .= '-o snapdir=visible ';
	}
	if (isset($dataset['sync']) && preg_match('/\S/', $dataset['sync'])) {
		$param .= sprintf('-o sync=%1$s ', trim($dataset['sync']));
	}
/* // not supported in freeBSD 
	if (!isset($dataset['xattr']))
		$param .= "-o xattr=off ";
*/
	// options
	$mounted = isset($dataset['canmount']) ? true : false;
	// create dataset
	$result = 0;
	$cmd = sprintf('zfs create  %1$s %2$s', $param, escapeshellarg($relpath));
	write_log($cmd);
	$result |= mwexec($cmd, true);
	if ($result != 0) {
		write_log(sprintf('Error: Failed to create dataset %1$s', $relpath));
		return $result;
	}
	// Check mountpoint
	$mountpoint = sprintf('/mnt/%1$s', $relpath);
	$cmd = sprintf('zfs list -H -t filesystem -o mountpoint %1$s', escapeshellarg($relpath));
	mwexec2($cmd, $rawdata2);
	if (!empty($rawdata2) && ($rawdata2[0] != 'none') && ($rawdata2[0] != 'legacy')) {
		$mountpoint = $rawdata2[0];
	}
	// set access restrictions
	if (!empty($mountpoint) && isset($dataset['canmount'])) {
		if (is_array($dataset['accessrestrictions'])) {
			$mode = $dataset['accessrestrictions']['mode'];
			$owner = $dataset['accessrestrictions']['owner'];
			$group = $dataset['accessrestrictions']['group'][0];
			write_log(sprintf('Access restrictions to be set: %1$s %2$s %3$s %4$s', $mountpoint, $mode, $owner, $group));
			if (file_exists($mountpoint) && is_writable($mountpoint)) {
				if (!@chmod($mountpoint, octdec($mode)) || !@chown($mountpoint, $owner) || !@chgrp($mountpoint, $group)) {
					write_log(sprintf('Error: Failed to set access restrictions on directory %1$s', $mountpoint));
				}
			}
		}
	}
	return $result;
}

/**
 * Delete a ZFS dataset.
 * @param[in] uuid	The UUID of the dataset to be deleted.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_dataset_destroy($uuid) {
	global $config;
	$result = 0;
	// get dataset
	if (!(isset($config['zfs']) && isset($config['zfs']['datasets']) && isset($config['zfs']['datasets']['dataset']) && is_array($config['zfs']['datasets']['dataset']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid"))) { return 1; }
	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset)) { return 1; }
	// get pool name
	if (!(isset($dataset['pool']) && is_array($dataset['pool']) && (0 < count($dataset['pool'])) && preg_match('/\S/', $dataset['pool'][0]))) { return 1; }
	$poolname = ltrim($dataset['pool'][0]);
	// get dataset name
	if (!(isset($dataset['name']) && preg_match('/\S/', $dataset['name']))) { return 1; }
	$datasetname = ltrim($dataset['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $datasetname);
	// destroy dataset
	$cmd = sprintf('zfs destroy -f %1$s', escapeshellarg($relpath));
	write_log($cmd);
	$result |= mwexec($cmd, true);
	if ($result != 0) {
		write_log(sprintf('Error: Failed to destroy dataset %1$s', $relpath));
	}
	return $result;
}

/**
 * Update properties of a ZFS dataset.
 * @param[in] uuid	The UUID of the dataset to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_dataset_properties($uuid) {
	global $config;
	// get dataset
	if (!(isset($config['zfs']) && isset($config['zfs']['datasets']) && isset($config['zfs']['datasets']['dataset']) && is_array($config['zfs']['datasets']['dataset']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['datasets']['dataset'], "uuid"))) { return 1; }
	$dataset = $config['zfs']['datasets']['dataset'][$index];
	if (!is_array($dataset)) { return 1; }
	// get pool name
	if (!(isset($dataset['pool']) && is_array($dataset['pool']) && (0 < count($dataset['pool'])) && preg_match('/\S/', $dataset['pool'][0]))) { return 1; }
	$poolname = ltrim($dataset['pool'][0]);
	// get dataset name
	if (!(isset($dataset['name']) && preg_match('/\S/', $dataset['name']))) { return 1; }
	$datasetname = ltrim($dataset['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $datasetname);
	// is the dataset mounted?
	$mounted = false;
	$cmd = sprintf('zfs list -H -t filesystem -o mounted %1$s', escapeshellarg($relpath));
	mwexec2($cmd, $rawdata);
	if (!empty($rawdata) && $rawdata[0] == "yes") {
		$mounted = true;
	}
	// determine mountpoint
	$mountpoint = sprintf('/mnt/%1$s', $relpath);
	$cmd = sprintf('zfs list -H -t filesystem -o mountpoint %1$s', escapeshellarg($relpath));
	mwexec2($cmd, $rawdata2);
	if (!empty($rawdata2) && ($rawdata2[0] != 'none') && ($rawdata2[0] != 'legacy')) {
		$mountpoint = ltrim($rawdata2[0]);
	}
	// properties 
	$result = 0;
	foreach (explode(' ', 'compression dedup sync atime aclinherit aclmode canmount readonly reservation quota xattr snapdir') as $attr) {
		$param = '';
		switch ($attr) {
			case 'aclinherit':
				if (isset($dataset['aclinherit']) && preg_match('/\S/', $dataset['aclinherit'])) {
					$param = sprintf('aclinherit=%1$s', trim($dataset['aclinherit']));
				} 
				break;
			case 'aclmode':
				if (isset($dataset['aclmode']) && preg_match('/\S/', $dataset['aclmode'])) {
					$param = sprintf('aclmode=%1$s', trim($dataset['aclmode']));
				}
				break;
			case 'atime':
				if (isset($dataset['atime']) && preg_match('/\S/', $dataset['atime'])) {
					$param = sprintf('atime=%1$s', trim($dataset['atime']));
				}
				break;
			case 'canmount':
				if (!isset($dataset['canmount'])) {
					$param = 'canmount=off';
				} else {
					$param = 'canmount=on';
				}
				break;
			case 'compression':
				if (isset($dataset['compression']) && preg_match('/\S/', $dataset['compression'])) {
					$param = sprintf('compression=%1$s', trim($dataset['compression']));
				}
				break;
			case 'dedup':
				if (isset($dataset['dedup']) && preg_match('/\S/', $dataset['dedup'])) {
					$param = sprintf('dedup=%1$s', trim($dataset['dedup']));
				}
				break;
			case 'quota':
				if (isset($dataset['quota']) && preg_match('/\S/', $dataset['quota'])) {
					$param = sprintf('quota=%1$s', trim($dataset['quota']));
				} else {
					$param = 'quota=none';
				}
				break;
			case 'readonly':
				if (isset($dataset['readonly'])) {
					$param = 'readonly=on';
				} else {
					$param = 'readonly=off';
				}
				break;
			case 'reservation': 
				if (isset($dataset['reservation']) && preg_match('/\S/', $dataset['reservation'])) {
					$param = sprintf('reservation=%1$s', trim($dataset['reservation']));
				} else {
					$param = 'reservation=none';
				}
				break;
			case 'snapdir':
				if (!isset($dataset['snapdir'])) {
					$param = 'snapdir=hidden';
				} else {
					$param = 'snapdir=visible';
				}
				break;
			case 'sync':
				if (isset($dataset['sync']) && preg_match('/\S/', $dataset['sync'])) {
					$param = sprintf('sync=%1$s', trim($dataset['sync']));
				}
				break;
			case 'xattr':
				/*
				if (!isset($dataset['xattr'])) {
					$param = 'xattr=off';
				} else {
					$param = 'xattr=on";
				}
				*/
				break;
		}
		// Update dataset properties
		if (!empty($param)) {
			$cmd = sprintf('zfs set %1$s %2$s', $param, escapeshellarg($relpath));
			write_log($cmd);
			$result |= mwexec($cmd, true);
		}
	}
	if (isset($dataset['canmount']) && !$mounted) {
		$cmd = sprintf('zfs mount %1$s', escapeshellarg($relpath));
		write_log($cmd);
		$result |= mwexec($cmd, true);
	}
	// set access restrictions
	if (!empty($mountpoint) && isset($dataset['canmount'])) {
		if (is_array($dataset['accessrestrictions'])) {
			$mode = $dataset['accessrestrictions']['mode'];
			$owner = $dataset['accessrestrictions']['owner'];
			$group = $dataset['accessrestrictions']['group'][0];
			write_log(sprintf('Access restrictions to be set: %1$s %2$s %3$s %4$s', $mountpoint, $mode, $owner, $group));
			if (file_exists($mountpoint) && is_writable($mountpoint)) {
				if (!@chmod($mountpoint, octdec($mode)) || !@chown($mountpoint, $owner) || !@chgrp($mountpoint, $group)) {
					write_log(sprintf('Error: Failed to set access restrictions on directory %1$s', $mountpoint));
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
	// get volume
	if (!(isset($config['zfs']) && isset($config['zfs']['volumes']) && isset($config['zfs']['volumes']['volume']) && is_array($config['zfs']['volumes']['volume']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], 'uuid'))) { return 1; }
	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume)) { return 1; }
	// get pool name
	if (!(isset($volume['pool']) && is_array($volume['pool']) && (0 < count($volume['pool'])) && preg_match('/\S/', $volume['pool'][0]))) { return 1; }
	$poolname = ltrim($volume['pool'][0]);
	// get volume name
	if (!(isset($volume['name']) && preg_match('/\S/', $volume['name']))) { return 1; }
	$volumename = ltrim($volume['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $volumename);
	// Additional parameter
	$param = '';
	if (isset($volume['sparse'])) {
		$param .= '-s ';
	}
	if (isset($volume['compression']) && preg_match('/\S/', $volume['compression'])) {
		$param .= sprintf('-o compression=%1$s ', trim($volume['compression']));
	}
	if (isset($volume['dedup']) && preg_match('/\S/', $volume['dedup'])) {
		$param .= sprintf('-o dedup=%1$s ', trim($volume['dedup']));
	}
	if (isset($volume['sync']) && preg_match('/\S/', $volume['sync'])) {
		$param .= sprintf('-o sync=%1$s ', trim($volume['sync']));
	}
	if (isset($volume['atime']) && preg_match('/\S/', $volume['atime'])) {
		$param .= sprintf('-o atime=%1$s ', trim($volume['atime']));
	}
	if (isset($volume['volmode']) && preg_match('/\S/', $volume['volmode']) && ($volume['volmode'] != 'default')) {
		$param .= sprintf('-o volmode=%1$s ', trim($volume['volmode']));
	}
	if (isset($volume['volblocksize']) && !empty($volume['volblocksize'])) {
		$param .= sprintf('-o volblocksize=%1$s ', trim($volume['volblocksize']));
	}
	// create volume
	$cmd = sprintf('zfs create -V %1$s %2$s %3$s', $volume['volsize'], $param, escapeshellarg($relpath));
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
	$result = 0;
	// get volume
	if (!(isset($config['zfs']) && isset($config['zfs']['volumes']) && isset($config['zfs']['volumes']['volume']) && is_array($config['zfs']['volumes']['volume']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], 'uuid'))) { return 1; }
	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume)) { return 1; }
	// get pool name
	if (!(isset($volume['pool']) && is_array($volume['pool']) && (0 < count($volume['pool'])) && preg_match('/\S/', $volume['pool'][0]))) { return 1; }
	$poolname = ltrim($volume['pool'][0]);
	// get volume name
	if (!(isset($volume['name']) && preg_match('/\S/', $volume['name']))) { return 1; }
	$volumename = ltrim($volume['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $volumename);
	// Destroy the volume
	$cmd = sprintf('zfs destroy %1$s', escapeshellarg($relpath));
	write_log($cmd);
	$result |= mwexec($cmd, true);
	if ($result != 0) {
		write_log(sprintf('Error: Failed to destroy volume %1$s', $relpath));
	}
	return $result;
}

function zfs_get_volume_reservation($volsize) {
	if (empty($volsize)) { return ''; }
	if (preg_match("/^(\d+(\.\d+)?)(.*)$/", $volsize, $m)) {
		$size = (float)$m[1] * 1.03;
		$suffix = $m[3];
		if (preg_match("/(m|k|^$)/", $suffix, $m)) {
			$size = ceil($size);
		}
		return "{$size}{$suffix}";
	}
	return false;
}

/**
 * Update properties of a ZFS volume.
 * @param[in] uuid	The UUID of the volume to be updated.
 * @return Return 0 if successful, otherwise 1.
 */
function zfs_volume_properties($uuid) {
	global $config;
	// get volume
	if (!(isset($config['zfs']) && isset($config['zfs']['volumes']) && isset($config['zfs']['volumes']['volume']) && is_array($config['zfs']['volumes']['volume']))) { return 1; }
	if (false === ($index = array_search_ex($uuid, $config['zfs']['volumes']['volume'], 'uuid'))) { return 1; }
	$volume = $config['zfs']['volumes']['volume'][$index];
	if (!is_array($volume)) { return 1; }
	// get pool name
	if (!(isset($volume['pool']) && is_array($volume['pool']) && (0 < count($volume['pool'])) && preg_match('/\S/', $volume['pool'][0]))) { return 1; }
	$poolname = ltrim($volume['pool'][0]);
	// get volume name
	if (!(isset($volume['name']) && preg_match('/\S/', $volume['name']))) { return 1; }
	$volumename = ltrim($volume['name']);
	$relpath = sprintf('%1$s/%2$s', $poolname, $volumename);
	if (isset($volume['volsize']) && !empty($volume['volsize'])) {
		if (false === ($reservation = zfs_get_volume_reservation($volume['volsize']))) { return 1; }
	}
	$result = 0;
	foreach (explode(' ', 'volsize sparse compression dedup sync atime volmode') as $attr) {
		$param = '';
		switch ($attr) {
			case 'volsize':
				if (isset($volume['volsize']) && !empty($volume['volsize']))
					$param = sprintf('volsize=%1$s', trim($volume['volsize']));
				break;
			case 'sparse':
				if (isset($volume['sparse'])) {
					$param = 'refreservation=none';
				} elseif (isset($volume['volsize']) && !empty($volume['volsize'])) {
					$param = sprintf('refreservation=%1$s', trim($volume['volsize']));
				}
				break;
			case 'compression':
				if (isset($volume['compression']) && preg_match('/\S/', $volume['compression'])) {
					$param = sprintf('compression=%1$s', trim($volume['compression']));
				}
				break;
			case 'dedup':
				if (isset($volume['dedup']) && preg_match('/\S/', $volume['dedup'])) {
					$param = sprintf('dedup=%1$s', trim($volume['dedup']));
				}
				break;
			case 'sync':
				if (isset($volume['sync']) && preg_match('/\S/', $volume['sync'])) {
					$param = sprintf('sync=%1$s', trim($volume['sync']));
				}
				break;
			case 'atime':
				if (isset($volume['atime']) && preg_match('/\S/', $volume['atime'])) {
					$param = sprintf('atime=%1$s', trim($volume['atime']));
				}
				break;
			case 'volmode':
				if (isset($volume['volmode']) && preg_match('/\S/', $volume['volmode'])) {
					$param = sprintf('volmode=%1$s', trim($volume['volmode']));
				}
				break;
		}		
		// Update volume properties
		if (!empty($param)) {
			$cmd = sprintf('zfs set %1$s %2$s', $param, escapeshellarg($relpath));
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

function zfs_snapshot_rollback($snapshot) {
	global $config;
	$result = array("output" => array("error"), "retval" => -1);

	if (empty($snapshot) || empty($snapshot['snapshot'])) {
		return $result;
	}
	if (!preg_match('/.+\@.+/', $snapshot['snapshot'])) {
		return $result;
	}

	// Additional parameter
	$param = "";

	if (!empty($snapshot['force_delete'])) {
		$param .= "-r ";
	}

	// Rollback snapshot
	$cmd = "zfs rollback {$param} {$snapshot['snapshot']}";
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

// Returns true if $name is valid.
function zfs_is_valid_poolname($name) {
	// Only strings allowed
	if (!is_string($name)) { return false; }
	// Empty pool names are not allowed.
	if (empty($name)) { return false; } 
	// Pool names beginning with "mirror", "raidz" and "spare" are not allowed, as are names beginning with the pattern "c[0-9]".
	if (preg_match('/^(mirror|raidz|spare|c[0-9])/', $name)) { return false; }
	// log is a reserved word
	if (0 == strcmp('log', $name)) { return false; } 

	// The pool name must begin with a letter, and can only contain
	// alphanumeric characters as well as underscore ("_"), dash ("-"), blank (" ") and
	// period (".").
	$helpinghand = preg_quote('.-_ ', '/');
	if (false !== preg_match('/^[a-z][a-z\d'.$helpinghand.']*$/i', $name)) {
		return true;
	}
	return false;
}
/*
 * The dataset name must begin with an alphanumeric character.
 * Following characters must be alphanumeric, underscore ("_"), dash ("-"), blank (" ") or period (".").
 */
function zfs_is_valid_dataset_name($name) {
	if (is_string($name) && preg_match('/\S/', $name)) { // only string with at least one non-whitespace characters allowed
		$helpinghand = preg_quote('.-_ ', '/');
		if (false !==  preg_match('/^[a-z\d][a-z\d'.$helpinghand.']*(?:\/[a-z\d][a-z\d'.$helpinghand.']*)*$/i', $name)) {
			return true;
		}
	}
	return false;
}
/*
 * The volume name must begin with an alphanumeric character.
 * Following characters must be alphanumeric, underscore ("_"), dash ("-"), blank (" ") or period (".").
 */
function zfs_is_valid_volume_name($name) {
	if (is_string($name) && preg_match('/\S/', $name)) { // only string with at least one non-whitespace characters allowed
		$helpinghand = preg_quote('.-_ ', '/');
		if (false !==  preg_match('/^[a-z\d][a-z\d'.$helpinghand.']*(?:\/[a-z\d][a-z\d'.$helpinghand.']*)*$/i', $name)) {
			return true;
		}
	}
	return false;
}
?>
