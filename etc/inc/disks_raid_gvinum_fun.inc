<?php
/*
	disks_raid_gvinum_fun.inc

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
 * returns an array with additional geom vinum information
 */
function gvinum_processinfo_get() {
	$a_return = [];
	$a_return['1'] = [
		'type' =>  '1',
		'x-devdir' => '/dev/gvinum',
		'x-notifier' => 'raid_gvinum',
		'x-processor' => 'gvinum_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gvinum_tools.php',
		'x-page-information' => 'disks_raid_gvinum_info.php',
		'x-button' => 'button_raid1',
		'gt-type' => gtext('RAID-1')
	];
	$a_return['5'] = [
		'type' =>  '5',
		'x-devdir' => '/dev/gvinum',
		'x-notifier' => 'raid_gvinum',
		'x-processor' => 'gvinum_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gvinum_tools.php',
		'x-page-information' => 'disks_raid_gvinum_info.php',
		'x-button' => 'button_raid5',
		'gt-type' => gtext('RAID-5')
	];
	$a_return['0'] = [
		'type' =>  '0',
		'x-devdir' => '/dev/gvinum',
		'x-notifier' => 'raid_gvinum',
		'x-processor' => 'gvinum_process_updatenotification',
		'x-page-maintenance' => 'disks_raid_gvinum_tools.php',
		'x-page-information' => 'disks_raid_gvinum_info.php',
		'x-button' => 'button_raid0',
		'gt-type' => gtext('RAID-0')
	];
	return $a_return;
}
function is_gvinum_mounted($devicespecialfile, &$a_mount) {
	return (false !== array_search_ex($devicespecialfile, $a_mount, 'mdisk'));
}
/*
 * GEOM vinum process update notifications
 */
function gvinum_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			$retval |= disks_raid_gvinum_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start vinum');
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (is_array($config['gvinum']['vdisk'])) {
				$index = array_search_ex($data, $config['gvinum']['vdisk'], 'uuid');
				if (false !== $index) {
					unset($config['gvinum']['vdisk'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gvinum_delete($data);
			if (is_array($config['gvinum']['vdisk'])) {
				$index = array_search_ex($data, $config['gvinum']['vdisk'], 'uuid');
				if (false !== $index) {
					unset($config['gvinum']['vdisk'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}
