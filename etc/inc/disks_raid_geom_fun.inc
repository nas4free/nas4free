<?php
/*
	disks_raid_geom_fun.inc

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

/*
 * returns an array with additional geom raid information
 */
function geomraid_processinfo_get() {
	$a_return = [];
	$a_return['1'] = [
		'type' =>  '1',
		'x-devdir' => '/dev/mirror',
		'x-notifier' => 'raid_gmirror',
		'x-processor' => 'gmirror_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gmirror_tools.php',
		'x-page-information' => 'disks_raid_gmirror_info.php',
		'x-button' => 'button_raid1',
		'gt-type' => gtext('RAID-1')
	];
	$a_return['5'] = [
		'type' =>  '5',
		'x-devdir' => '/dev/raid5',
		'x-notifier' => 'raid_graid5',
		'x-processor' => 'graid5_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_graid5_tools.php',
		'x-page-information' => 'disks_raid_graid5_info.php',
		'x-button' => 'button_raid5',
		'gt-type' => gtext('RAID-5')
	];
	$a_return['0'] = [
		'type' =>  '0',
		'x-devdir' => '/dev/stripe',
		'x-notifier' => 'raid_gstripe',
		'x-processor' => 'gstripe_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gstripe_tools.php',
		'x-page-information' => 'disks_raid_gstripe_info.php',
		'x-button' => 'button_raid0',
		'gt-type' => gtext('RAID-0')
	];
	$a_return['JBOD'] = [
		'type' => 'JBOD',
		'x-devdir' => '/dev/concat',
		'x-notifier' => 'raid_gconcat',
		'x-processor' => 'gconcat_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gconcat_tools.php',
		'x-page-information' => 'disks_raid_gconcat_info.php',
		'x-button' => 'button_jbod',
		'gt-type' => gtext('JBOD')
	];
	return $a_return;
}
/*
 * Compiles an array containing all GEOM RAID volumes listed in config.xml
 * Amends processing data
 */
function geomraid_config_get(&$a_record) {
	global $config;
	$a_process = geomraid_processinfo_get();
	$a_record = [];
	$sphere = 'gconcat';
	if (isset($config[$sphere]['vdisk']) && is_array($config[$sphere]['vdisk'])) {
		foreach ($config[$sphere]['vdisk'] as $r_record) {
			$r_record['balance'] = '';
			$a_record[] = $r_record;
		}
	}
	$sphere = 'gmirror';
	if (isset($config[$sphere]['vdisk']) && is_array($config[$sphere]['vdisk'])) {
		foreach ($config[$sphere]['vdisk'] as $r_record) {
			$a_record[] = $r_record;
		}
	}
	$sphere = 'graid5';
	if (isset($config[$sphere]['vdisk']) && is_array($config[$sphere]['vdisk'])) {
		foreach ($config[$sphere]['vdisk'] as $r_record) {
			$r_record['balance'] = '';
			$a_record[] = $r_record;
		}
	}
	$sphere = 'gstripe';
	if (isset($config[$sphere]['vdisk']) && is_array($config[$sphere]['vdisk'])) {
		foreach ($config[$sphere]['vdisk'] as $r_record) {
			$r_record['balance'] = '';
			$a_record[] = $r_record;
		}
	}
}
/*
 * Writes all array information into the appropriate section in config.xml
 * Strips processing data
 */
function geomraid_config_set(&$a_record) {
	global $config;
	$a_gconcat = [];
	$a_gconcat['vdisk'] = [];
	$a_gmirror = [];
	$a_mirror['vdisk'] = [];
	$a_graid5 = [];
	$a_graid5['vdisk'] = [];
	$a_gstripe = [];
	$a_gstripe['vdisk'] = [];
	foreach ($a_record as $r_record) {
		$r_config = $r_record; // copy array information
		// strip options
		switch ($r_config['type']) {
			case 'JBOD':
				unset($r_config['balance']);
				$a_gconcat['vdisk'][] = $r_config;
				break;
			case '1':
				$a_gmirror['vdisk'][] = $r_config;
				break;
			case '5':
				unset($r_config['balance']);
				$a_graid5['vdisk'][] = $r_config;
				break;
			case '0':
				unset($r_config['balance']);
				$a_gstripe['vdisk'][] = $r_config;
				break;
		}
	}
	unset($config['gconcat']);
	$config['gconcat'] = $a_gconcat;
	unset($config['gmirror']);
	$config['gmirror'] = $a_gmirror;
	unset($config['graid5']);
	$config['graid5'] = $a_graid5;
	unset($config['gstripe']);
	$config['gstripe'] = $a_gstripe;
}
function is_geomraid_mounted($devicespecialfile, &$a_mount) {
	return (false !== array_search_ex($devicespecialfile, $a_mount, 'mdisk'));
}
/*
 * GEOM concat process update notifications
 */
function gconcat_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load concat');
			$retval |= disks_raid_gconcat_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start concat');
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (isset($config['gconcat']) && isset($config['gconcat']['vdisk']) && is_array($config['gconcat']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gconcat']['vdisk'], 'uuid'))) {
					unset($config['gconcat']['vdisk'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gconcat_delete($data);
			if (isset($config['gconcat']) && isset($config['gconcat']['vdisk']) && is_array($config['gconcat']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gconcat']['vdisk'], 'uuid'))) {
					unset($config['gconcat']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}
/*
 * GEOM mirror process update notifications
 */
function gmirror_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load mirror');
			$retval |= disks_raid_gmirror_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start mirror');
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (isset($config['gmirror']) && isset($config['gmirror']['vdisk']) && is_array($config['gmirror']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gmirror']['vdisk'], 'uuid'))) {
					unset($config['gmirror']['vdisk'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gmirror_delete($data);
			if (isset($config['gmirror']) && isset($config['gmirror']['vdisk']) && is_array($config['gmirror']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gmirror']['vdisk'], 'uuid'))) {
					unset($config['gmirror']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}
/*
 * GEOM raid5 process update notifications
 */
function graid5_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load raid5');
			$retval |= rc_exec_service('geom tune raid5');
			$retval |= disks_raid_graid5_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start raid5');
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (isset($config['graid5']) && isset($config['graid5']['vdisk']) && is_array($config['graid5']['vdisk'])) {
				$index = array_search_ex($data, $config['graid5']['vdisk'], 'uuid');
				if (false !== $index) {
					unset($config['graid5']['vdisk'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_graid5_delete($data);
			if (isset($config['graid5']) && isset($config['graid5']['vdisk']) && is_array($config['graid5']['vdisk'])) {
				if (false !==  ($index = array_search_ex($data, $config['graid5']['vdisk'], 'uuid'))) {
					unset($config['graid5']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}
/*
 * GEOM stripe process update notifications
 */
function gstripe_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load stripe');
			$retval |= disks_raid_gstripe_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start stripe');
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (isset($config['gstripe']) && isset($config['gstripe']['vdisk']) && is_array($config['gstripe']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gstripe']['vdisk'], 'uuid'))) {
					unset($config['gstripe']['vdisk'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gstripe_delete($data);
			if (isset($config['gstripe']) && isset($config['gstripe']['vdisk']) && is_array($config['gstripe']['vdisk'])) {
				if (false !== ($index = array_search_ex($data, $config['gstripe']['vdisk'], 'uuid'))) {
					unset($config['gstripe']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}
?>
