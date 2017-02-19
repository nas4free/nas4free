<?php
/*
	disks_init.php

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
require('auth.inc');
require('guiconfig.inc');

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_loc = gtext('Record is locked');
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png',
	'mup' => 'images/up.png',
	'mdn' => 'images/down.png'
];
$prerequisites_ok = true;

function verify_filesystem_name($arg) {
	$returnvalue = false;
	switch ($arg) { // verify filesystem name
		default: // invalid parameter value
			break;
		case 'zfs':
		case 'softraid':
		case 'ufsgpt':
//		case 'ext2':
		case 'msdos':
			$returnvalue = true;
			break;
	}
	return $returnvalue;
}

$do_format = [];
$a_control_matrix = [
	1 => [
		'zfs'      => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
		'softraid' => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
		'ufsgpt'   => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
		'ext2'     => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
		'msdos'    => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
		'default'  => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0],
	],
	2 => [
		'zfs'      => ['page' => 2, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 2, 'aft4k' => 0, 'zfsgpt' => 2, 'notinitmbr' => 2],
		'softraid' => ['page' => 2, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 2],
		'ufsgpt'   => ['page' => 2, 'filesystem' => 1, 'minspace' => 2, 'volumelabel' => 2, 'aft4k' => 2, 'zfsgpt' => 0, 'notinitmbr' => 2],
		'ext2'     => ['page' => 2, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 2, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 2],
		'msdos'    => ['page' => 2, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 2, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 2],
		'default'  => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0]
	],
	3 => [
		'zfs'      => ['page' => 3, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 1, 'notinitmbr' => 1],
		'softraid' => ['page' => 3, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'ufsgpt'   => ['page' => 3, 'filesystem' => 1, 'minspace' => 1, 'volumelabel' => 1, 'aft4k' => 1, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'ext2'     => ['page' => 3, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'msdos'    => ['page' => 3, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'default'  => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0]
	],
	4 => [
		'zfs'      => ['page' => 4, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 1, 'notinitmbr' => 1],
		'softraid' => ['page' => 4, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'ufsgpt'   => ['page' => 4, 'filesystem' => 1, 'minspace' => 1, 'volumelabel' => 1, 'aft4k' => 1, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'ext2'     => ['page' => 4, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'msdos'    => ['page' => 4, 'filesystem' => 1, 'minspace' => 0, 'volumelabel' => 1, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 1],
		'default'  => ['page' => 1, 'filesystem' => 2, 'minspace' => 0, 'volumelabel' => 0, 'aft4k' => 0, 'zfsgpt' => 0, 'notinitmbr' => 0]
	]
];
$a_button_matrix = [
	1 => ['submit_value' => gtext('Next'  ), 'submit_name' => 'action1', 'submit_control' => 2, 'cancel_value' => gtext('Cancel'), 'cancel_name' => 'cancel1', 'cancel_control' => 0, 'checkbox_control' => 2],
	2 => ['submit_value' => gtext('Next'  ), 'submit_name' => 'action2', 'submit_control' => 2, 'cancel_value' => gtext('Back'  ), 'cancel_name' => 'cancel2', 'cancel_control' => 2, 'checkbox_control' => 2],
	3 => ['submit_value' => gtext('Format'), 'submit_name' => 'action3', 'submit_control' => 2, 'cancel_value' => gtext('Back'  ), 'cancel_name' => 'cancel3', 'cancel_control' => 2, 'checkbox_control' => 1],
	4 => ['submit_value' => gtext('OK'    ), 'submit_name' => 'action4', 'submit_control' => 2, 'cancel_value' => gtext('Back'  ), 'cancel_name' => 'cancel4', 'cancel_control' => 0, 'checkbox_control' => 1]
];
$l_filesystem = [
	'ufsgpt' => gtext('UFS (GPT and Soft Updates)'),
	'msdos' => gtext('FAT32'),
//	'ext2' => gtext('EXT2'),
	'softraid' => gtext('Software RAID'),
	'zfs' => gtext('ZFS Storage Pool')
];
$l_minspace = [
	'8' => '8%',
	'7' => '7%',
	'6' => '6%',
	'5' => '5%',
	'4' => '4%',
	'3' => '3%',
	'2' => '2%',
	'1' => '1%'
];
$a_option = (isset($_POST) && is_array($_POST)) ? $_POST : [];
if (isset($a_option['filesystem'])) {
//	$a_option['filesystem'] = array_key_exists($a_option['filesystem'], $l_filesystem) ? $a_option['filesystem'] : 'zfs';
} else {
	$a_option['filesystem'] = 'zfs';
}
if (isset($a_option['checkbox_member_array']) && is_array($a_option['checkbox_member_array'])) {
} else {
	$a_option['checkbox_member_array'] = [];
}
if (isset($a_option['volumelabel']) && preg_match('/\S/', $a_option['volumelabel'])) {
	$a_option['volumelabel'] = htmlspecialchars(trim($a_option['volumelabel']));
} else {
	$a_option['volumelabel'] = '';
}
if (isset($a_option['minspace']) && array_key_exists($a_option['minspace'], $l_minspace)) {
} else {
	$a_option['minspace'] = '8';
}
$a_option['aft4k'] = isset($a_option['aft4k']);
$a_option['zfsgpt'] = isset($a_option['zfsgpt']);
$a_option['notinitmbr'] = isset($a_option['notinitmbr']);

// Get OS partition
$bootdevice = trim(file_get_contents("{$g['etc_path']}/cfdevice"));
// Get list of all configured disks (physical and virtual).
$sphere_array = get_conf_all_disks_list_filtered();
// Protect devices which are invalid or in use
foreach ($sphere_array as &$sphere_record) {
	$sphere_record['protected.reason'] = '';
	if (0 === strcmp($sphere_record['size'], 'NA')) {
		$sphere_record['protected'] = true;
		$sphere_record['protected.reason'] = gtext('Unknown size');
	} elseif  (1 === disks_exists($sphere_record['devicespecialfile'])) {
		$sphere_record['protected'] = true;
		$sphere_record['protected.reason'] = gtext('Device not found');
	} elseif (disks_ismounted_ex($sphere_record['devicespecialfile'], "devicespecialfile")) {
		$sphere_record['protected'] = true;
		$sphere_record['protected.reason'] = gtext('Device is mounted');
	} elseif (1 === preg_match('/^' . $sphere_record['name'] . '/', $bootdevice)) {
		$sphere_record['protected'] = true;
		$sphere_record['protected.reason'] = gtext('Device contains boot partition');
	}
}
unset($sphere_record); // release pass by reference

// cleanup checkbox_member_array
// Remove checkbox_member_array records which are protected in $sphere_array
// Set enabled property in $sphere_array for those who can be selected
$a_member_update = [];
foreach ($a_option['checkbox_member_array'] as $checkbox_member_record) {
	if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
		if (!isset($sphere_array[$index]['protected'])) {
			$sphere_array[$index]['enabled'] = true;
			$a_member_update[] = $checkbox_member_record;
		}
	}
}
$a_option['checkbox_member_array'] = $a_member_update;

$page_index = 1;
$a_control = $a_control_matrix[$page_index]['default'];
$a_button = $a_button_matrix[$page_index];

if (isset($a_option['cancel1']) && $a_option['cancel1']) {
	// cancel button has been pressed on page 1, we want to stay on page 1
} elseif (isset($a_option['cancel2']) && $a_option['cancel2']) {
	// back button has been pressed on page 2, return to page 1
} elseif (isset($a_option['cancel3']) && $a_option['cancel3']) {
	// back button has been pressed on page 3, return to page 2
	if ($prerequisites_ok) {
		$page_index = 2;
		$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
		$a_button = $a_button_matrix[$page_index];
	}
} elseif (isset($a_option['cancel4']) && $a_option['cancel4']) {
	// back button has been pressed on page 4, return to page 1
} elseif (isset($a_option['action1']) && $a_option['action1']) { 
	// next button has been pressed on page 1, we want to display page 2
	// expectation: filesystem has been chosen.
	if ($prerequisites_ok) { // verify filesystem type
		$prerequisites_ok = (isset($a_option['filesystem']) && verify_filesystem_name($a_option['filesystem']));
		// filesystem type could be invalid, we need to return to page 1 to be able to select a valid filesystem. Nothing to do here because page 1 is set by default
	}
	if ($prerequisites_ok) {
		$page_index = 2;
		$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
		$a_button = $a_button_matrix[$page_index];
	}
} elseif (isset($a_option['action2']) && $a_option['action2']) {
	// next button has been pressed on page 2, we want to display page 3
	// expectation: filesystem has been chosen, disks have been selected.
	if ($prerequisites_ok) {  // verify filesystem type
		$prerequisites_ok = (isset($a_option['filesystem']) && verify_filesystem_name(htmlspecialchars($a_option['filesystem'])));
		// filesystem type could be invalid, we need to return to page 1 to be able to select a valid filesystem. Nothing to do here because page 1 is set by default
	}
	if ($prerequisites_ok) { // verify selected disks
		if (false === ($prerequisites_ok = (isset($a_option['checkbox_member_array']) && is_array($a_option['checkbox_member_array']) && (count($a_option['checkbox_member_array']) > 0)))) {
			// no disks selected, we stay on page 2
			$page_index = 2;
			$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
			$a_button = $a_button_matrix[$page_index];
		}
	}
	if ($prerequisites_ok) {
		if (preg_match('/^(ufsgpt|msdos)/', $a_option['filesystem']) && preg_match('/\S/', $a_option['volumelabel'])) {
			$helpinghand = preg_quote('[%', '/');
			if (preg_match('/^[a-z\d' . $helpinghand . ']+$/i', $a_option['volumelabel'])) {
				// additional check is required for adding serial number information to the label		
				$label_serial = [];
				$label_serial['trigger'] = '[';
				$label_serial['match'] = '([1-9]\d?)';
				$label_serial['regex'] = '/' . preg_quote($label_serial['trigger']) . $label_serial['match'] . '/';
				$label_serial['count'] = substr_count($a_option['volumelabel'], $label_serial['trigger']); // count occurrences of the initiating character
				if ($label_serial['count'] > 0) { // one or more occurrences found?
					if ($label_serial['count'] !== preg_match_all($label_serial['regex'], $a_option['volumelabel'])) { // count must match, otherwise something went wrong
						$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z] and [0-9]."), gtext('Volume Label'));
						$prerequisites_ok = false;
						// invalid volume label pattern, we stay on page 2
						$page_index = 2;
						$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
						$a_button = $a_button_matrix[$page_index];
					}
				}
			} else { // invalid volume label pattern, we stay on page 2
				$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z] and [0-9]."), gtext('Volume Label'));
				$prerequisites_ok = false;
				$page_index = 2;
				$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
				$a_button = $a_button_matrix[$page_index];
			}
		}
	}
	if ($prerequisites_ok) {
		if (preg_match('/^(zfs)/', $a_option['filesystem']) && preg_match('/\S/', $a_option['volumelabel'])) {
			$helpinghand = preg_quote('[%.-_', '/');
			if (preg_match('/^[a-z\d' . $helpinghand . ']+$/i', $a_option['volumelabel'])) {
				// additional check is required for adding serial number information to the label
				$label_serial = [];
				$label_serial['trigger'] = '[';
				$label_serial['match'] = '([1-9]\d?)';
				$label_serial['regex'] = '/' . preg_quote($label_serial['trigger']) . $label_serial['match'] . '/';
				$label_serial['count'] = substr_count($a_option['volumelabel'], $label_serial['trigger']); // count occurrences of the initiating character
				if ($label_serial['count'] > 0) { // one or more occurrences found?
					if ($label_serial['count'] !== preg_match_all($label_serial['regex'], $a_option['volumelabel'])) { // count must match, otherwise something went wrong
						$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z], [0-9] and [._-]."), gtext('Volume Label'));
						$prerequisites_ok = false;
						// invalid volume label defined, we stay on page 2
						$page_index = 2;
						$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
						$a_button = $a_button_matrix[$page_index];
					}
				}
			} else { // invalid volume label pattern, we stay on page 2
				$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z], [0-9] and [._-]."), gtext('Volume Label'));
				$prerequisites_ok = false;
				$page_index = 2;
				$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
				$a_button = $a_button_matrix[$page_index];
			}
		}
	}
	if ($prerequisites_ok) {
		$page_index = 3;
		$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
		$a_button = $a_button_matrix[$page_index];
	}
} elseif (isset($a_option['action3']) && $a_option['action3']) {
	// format button has been pressed on page 3, we want to format
	// expectation: filesystem has been chosen, disks have been selected, options have been set.
	if ($prerequisites_ok) { // verify filesystem type
		$prerequisites_ok = (isset($a_option['filesystem']) && verify_filesystem_name($a_option['filesystem']));
		// filesystem type could be invalid, we need to return to page 1 to be able to select a valid filesystem. Nothing to do here because page 1 is set by default
	}
	if ($prerequisites_ok) { // verify selected disks
		if (false === ($prerequisites_ok = (isset($a_option['checkbox_member_array']) && is_array($a_option['checkbox_member_array']) && (count($a_option['checkbox_member_array']) > 0)))) {
			// no disks selected, we need to return to page 2 to be able to select disks
			$page_index = 2;
			$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
			$a_button = $a_button_matrix[$page_index];
		}
	}
	if ($prerequisites_ok) {
		if (preg_match('/^(ufsgpt|msdos)/', $a_option['filesystem']) && preg_match('/\S/', $a_option['volumelabel'])) {
			$helpinghand = preg_quote('[%', '/');
			if (preg_match('/^[a-z\d' . $helpinghand . ']+$/i', $a_option['volumelabel'])) {
				// additional check is required for adding serial number information to the label
				$label_serial = [];
				$label_serial['trigger'] = '[';
				$label_serial['match'] = '([1-9]\d?)';
				$label_serial['regex'] = '/' . preg_quote($label_serial['trigger']) . $label_serial['match'] . '/';
				$label_serial['count'] = substr_count($a_option['volumelabel'], $label_serial['trigger']); // count occurrences of the initiating character
				if ($label_serial['count'] > 0) { // one or more occurrences found?
					if ($label_serial['count'] !== preg_match_all($label_serial['regex'], $a_option['volumelabel'])) { // count must match, otherwise something went wrong
						$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z] and [0-9]."), gtext('Volume Label'));
						$prerequisites_ok = false;
						// invalid volume label pattern, we stay on page 2
						$page_index = 2;
						$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
						$a_button = $a_button_matrix[$page_index];
					}
				}
			} else { // invalid volume label defined, we stay on page 3
				$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z] and [0-9]."), gtext('Volume Label'));
				$prerequisites_ok = false;
				$page_index = 3;
				$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
				$a_button = $a_button_matrix[$page_index];
			}
		}
	}
	if ($prerequisites_ok) {
		if (preg_match('/^(zfs)/', $a_option['filesystem']) && preg_match('/\S/', $a_option['volumelabel'])) {
			$helpinghand = preg_quote('[%.-_', '/');
			if (preg_match('/^[a-z\d' . $helpinghand . ']+$/i', $a_option['volumelabel'])) {
				// additional check is required when adding serial number information to the label
				$label_serial = [];
				$label_serial['trigger'] = '[';
				$label_serial['match'] = '([1-9]\d?)';
				$label_serial['regex'] = '/' . preg_quote($label_serial['trigger']) . $label_serial['match'] . '/';
				$label_serial['count'] = substr_count($a_option['volumelabel'], $label_serial['trigger']); // count occurrences of the initiating character
				if ($label_serial['count'] > 0) { // one or more occurrences found?
					if ($label_serial['count'] !== preg_match_all($label_serial['regex'], $a_option['volumelabel'])) { // count must match, otherwise something went wrong
						$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z], [0-9] and [._-]."), gtext('Volume Label'));
						$prerequisites_ok = false;
						// invalid volume label pattern, we stay on page 2
						$page_index = 2;
						$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
						$a_button = $a_button_matrix[$page_index];
					}
				}
			} else { // invalid volume label defined, we stay on page 2
				$input_errors[] = sprintf(gtext("The attribute '%s' may only consist of the characters [a-z], [A-Z], [0-9] and [._-]."), gtext('Volume Label'));
				$prerequisites_ok = false;
				$page_index = 2;
				$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
				$a_button = $a_button_matrix[$page_index];
			}
		}
	}
	if ($prerequisites_ok) {
		$page_index = 4 ;
		$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
		$a_button = $a_button_matrix[$page_index];
		// gather options and format selected disks
		$disk_options = [];
		$disk_options['zfsgpt'] = $a_option['zfsgpt'] ? 'p1' : ''; // set_conf_disk_fstype_opt knows how to deal with it if filesystem is not zfs
		// check for allowed characters, otherwise reset volumelabel
		$volumelabel_pattern = (preg_match('/(ufsgpt|msdos|zfs)/', $a_option['filesystem'])) ? $a_option['volumelabel'] : '';
		// check if counters are part of the volume label
		$label_counter = [];
		if (preg_match('/\S/', $volumelabel_pattern)) { // do we have a volumelabel pattern?
			$label_counter['trigger'] = '%';
			$label_counter['match'] = '(\d*)';
			$label_counter['regex'] = '/' . preg_quote($label_counter['trigger']) . $label_counter['match'] . '/';
			$label_counter['count'] = substr_count($volumelabel_pattern, $label_counter['trigger']); // count occurrences of the initiating character
			if ($label_counter['count'] > 0) { // one or more occurrences found?
				if ($label_counter['count'] === preg_match_all($label_counter['regex'], $volumelabel_pattern, $helpinghand)) { // count must match, otherwise something went wrong
					$label_counter['needle'] = $helpinghand[0];
					$label_counter['origin'] = $helpinghand[1];
					$label_counter['replacement'] = [];
					$label_counter['pattern'] = [];
					for($i = 0; $i < $label_counter['count']; $i++) {
						$label_counter['pattern'][$i] = '/' . preg_quote($label_counter['needle'][$i], '/') . '/'; // make regex pattern
						if(empty($label_counter['origin'][$i])) {  // using empty is ok
							$label_counter['replacement'][$i] = 0; // value of replacement if origin is empty
						} else {
							$label_counter['replacement'][$i] = $label_counter['origin'][$i]; // value of replacement if origin is not empty (starting number)
						}
					}
				} else {
					$label_counter = [];
					$volumelabel_pattern = '';
				}
				unset($helpinghand);
			} else {
				$label_counter = [];
			}
		}
		// check if the drive's serial number is part of the volume label
		$label_serial = [];
		if (preg_match('/\S/', $volumelabel_pattern)) { // do we have a volumelabel pattern?
			$label_serial['trigger'] = '[';
			$label_serial['match'] = '([1-9]\d?)';
			$label_serial['regex'] = '/' . preg_quote($label_serial['trigger']) . $label_serial['match'] . '/';
			$label_serial['count'] = substr_count($volumelabel_pattern, $label_serial['trigger']); // count occurrences of the initiating character
			if ($label_serial['count'] > 0) { // one or more occurrences found?
				if ($label_serial['count'] === preg_match_all($label_serial['regex'], $volumelabel_pattern, $helpinghand)) { // count must match, otherwise something went wrong
					$label_serial['needle'] = $helpinghand[0];
					$label_serial['origin'] = $helpinghand[1];
					$label_serial['replacement'] = [];
					$label_serial['pattern'] = [];
					for($i = 0; $i < $label_serial['count']; $i++) {
						$label_serial['pattern'][$i] = '/' . preg_quote($label_serial['needle'][$i], '/') . '/'; // make regex pattern
						if(empty($label_serial['origin'][$i])) {  // using empty is ok
							$label_serial['replacement'][$i] = ''; // value of replacement if origin is empty
						} else {
							$label_serial['replacement'][$i] = ''; // value of replacement if origin is not empty
						}
					}
				} else {
					$label_serial = [];
					$volumelabel_pattern = '';
				}
				unset($helpinghand);
			} else {
				$label_serial = [];
			}
		}
		foreach($a_option['checkbox_member_array'] as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				if (!isset($sphere_array[$index]['protected'])) {
					set_conf_disk_fstype_opt($sphere_array[$index]['devicespecialfile'], $a_option['filesystem'], $disk_options);
					$volumelabel = $volumelabel_pattern;
					// apply counter to label
					if (!empty($label_counter)) {
						$volumelabel = preg_replace($label_counter['pattern'], $label_counter['replacement'], $volumelabel, 1);
						// increase counter;
						for($i = 0; $i < $label_counter['count']; $i++) {
							$label_counter['replacement'][$i]++;
						}
					}
					// apply serial number to label
					if (!empty($label_serial)) {
						for ($i = 0; $i < $label_serial['count']; $i++) {
							if (false === ($label_serial['replacement'][$i] = substr($sphere_array[$index]['serial'], -$label_serial['origin'][$i], $label_serial['origin'][$i]))) {
								$label_serial['replacement'][$i] = '';
							}
						}
						$volumelabel = preg_replace($label_serial['pattern'], $label_serial['replacement'], $volumelabel, 1);
					}
					// prepare format
					$do_format[] = [
						'devicespecialfile' => $sphere_array[$index]['devicespecialfile'],
						'filesystem' => $a_option['filesystem'],
						'notinitmbr' => $a_option['notinitmbr'],
						'minspace' => $a_option['minspace'],
						'volumelabel' => $volumelabel,
						'aft4k' => $a_option['aft4k'],
						'zfsgpt' => $a_option['zfsgpt']
					];
				}
			}
		}
		write_config();
	}
} elseif (isset($a_option['action4']) && $a_option['action4']) {
//	$page_index = 1;
//	$a_control = $a_control_matrix[$page_index][$a_option['filesystem']];
//	$a_button = $a_button_matrix[$page_index];
}
$pgtitle = [gtext('Disks'), gtext('Management'), gtext('HDD Format'), sprintf('%1$s %2$d', gtext('Step'), $page_index)];
?>
<?php include("fbegin.inc"); ?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init toggle checkbox
	$("#togglemembers").click(function() { togglecheckboxesbyname(this, "<?=$checkbox_member_name;?>[]"); });
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
function togglecheckboxesbyname(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
			}
		}
	}
	if (ego.type == 'checkbox') { ego.checked = false; }
}
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="disks_manage.php"><span><?=gtext('HDD Management');?></span></a></li>
		<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('HDD Format');?></span></a></li>
		<li class="tabinact"><a href="disks_manage_smart.php"><span><?=gtext('S.M.A.R.T.');?></span></a></li>
		<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext('iSCSI Initiator');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform">
	<?php
	if (!empty($input_errors)) {
		print_input_errors($input_errors);
	}
	if (!empty($errormsg)) {
		print_error_box($errormsg);
	}
	?>
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php
			html_titleline2(gtext('Format Options'));
			?>
		</thead>
		<tfoot>
			<?php
			html_separator2();
			?>
		</tfoot>
		<tbody>
			<?php
			switch ($a_control['filesystem']) {
				case 2: html_combobox2('filesystem', gtext('File System'), $a_option['filesystem'], $l_filesystem, gtext('Select file system format.'), true, false); break;
				case 1: html_combobox2('filesystem', gtext('File System'), $a_option['filesystem'], $l_filesystem, '', false, true);
				case 0: echo '<input name="filesystem" type="hidden" value="', $a_option['filesystem'], '"/>', "\n"; break;
			}
			switch ($a_control['volumelabel']) {
				case 2: html_inputbox2('volumelabel', gtext('Volume Label'), $a_option['volumelabel'], gtext('Volume label of the new file system. Use % for a counter or %n for a counter starting at number n, Use [n for the rightmost n characters of the device serial number.'), false, 40, false); break;
				case 1: html_inputbox2('volumelabel', gtext('Volume Label'), $a_option['volumelabel'], '', false, 100, true); break;
				case 0: echo '<input name="volumelabel" type="hidden" value="', $a_option['volumelabel'], '"/>', "\n"; break;
			}
			switch ($a_control['minspace']) {
				case 2: html_combobox2('minspace', gtext('Minimum Free Space'), $a_option['minspace'], $l_minspace, gtext('Specifiy the percentage of disk space to be held back from normal usage. Lowering this threshold can adversely affect performance and auto-defragmentation!'), true, false); break;
				case 1: html_combobox2('minspace', gtext('Minimum Free Space'), $a_option['minspace'], $l_minspace, '', false, true);
				case 0: echo '<input name="minspace" type="hidden" value="', $a_option['minspace'], '"/>', "\n"; break;
			}
			switch ($a_control['aft4k']) {
				case 2: html_checkbox2('aft4k', gtext('Advanced Format'), $a_option['aft4k'], gtext('Enable Advanced Format (4KB Sector Size).'), '', false, false); break;
				case 1: html_checkbox2('aft4k', gtext('Advanced Format'), $a_option['aft4k'], gtext('Enable Advanced Format (4KB Sector Size).'), '', false, true);
				case 0: if (true === $a_option['aft4k']) { echo '<input name="aft4k" type="hidden" value="yes"/>', "\n"; } break;
			}
			switch ($a_control['zfsgpt']) {
				case 2: html_checkbox2('zfsgpt', gtext('GPT Partition'), $a_option['zfsgpt'], gtext('Create ZFS on a GPT partition.'), '', false, false); break;
				case 1: html_checkbox2('zfsgpt', gtext('GPT Partition'), $a_option['zfsgpt'], gtext('Create ZFS on a GPT partition.'), '', false, true);
				case 0: if (true === $a_option['zfsgpt']) { echo '<input name="zfsgpt" type="hidden" value="yes"/>', "\n"; } break;
			}
			switch ($a_control['notinitmbr']) {
				case 2: html_checkbox2('notinitmbr', gtext('Erase MBR'), $a_option['notinitmbr'], gtext("Do not erase the Master Boot Record (useful for some RAID controller cards)."), '', false, false); break;
				case 1: html_checkbox2('notinitmbr', gtext('Erase MBR'), $a_option['notinitmbr'], gtext("Do not erase the Master Boot Record (useful for some RAID controller cards)."), '', false, true);
				case 0: if (true === $a_option['notinitmbr']) { echo '<input name="notinitmbr" type="hidden" value="yes"/>', "\n"; } break;
			}
			?>
		</tbody>
	</table>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:15%"><!-- // Device Name -->
			<col style="width:15%"><!-- // Serial Number -->
			<col style="width:15%"><!-- // Size -->
			<col style="width:20%"><!-- // Device Path -->
			<col style="width:20%"><!-- // Reason Code -->
			<col style="width:10%"><!-- // Toolbox -->
		</colgroup>
		<thead>
			<?php
			html_titleline2(gtext('Disk Selection'), 7);
			?>
			<tr>
				<?php
				switch ($a_button['checkbox_control']) {
					case 2:	echo '<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="', gtext('Invert Selection'), '"/></th>', "\n"; break;
					case 1:	echo '<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="', gtext('Invert Selection'), '" disabled="disabled"/></th>', "\n"; break;
				}
				?>
				<th class="lhell"><?=gtext('Device Name');?></th>
				<th class="lhell"><?=gtext('Serial Number');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Device Path');?></th>
				<th class="lhell"><?=gtext('Reason Code');?></th>
				<th class="lhebc"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($sphere_array as $sphere_record):?>
			<tr>
				<?php 
				$enabled      = isset($sphere_record['enabled']);
				$notprotected = !isset($sphere_record['protected']);
				$tag_id       = ' id="'  . $sphere_record['uuid'] . '"';
				$tag_name     = ' name="' . $checkbox_member_name . '[]"';
				$tag_value    = ' value="' . $sphere_record['uuid'] . '"';
				$tag_disabled = ' disabled="disabled"';
				if ($notprotected) {
					$tag_checked = $enabled ? ' checked="checked"' : '';
					switch ($a_button['checkbox_control']) {
						case 2: 
							echo '<td class="lcelc"><input type="checkbox"', $tag_name, $tag_value, $tag_id, $tag_checked, '/></td>', "\n";
							break;
						case 1: 
							echo '<td class="lcelc"><input type="checkbox"', $tag_name, $tag_value, $tag_id, $tag_disabled, $tag_checked, '/></td>', "\n";
							if ($enabled) {
								echo '<input type="hidden"', $tag_name, $tag_value, '/>', "\n";
							}
							break;
						case 0: 
							echo '<td></td>', "\n";
							if ($enabled) {
								echo '<input type="hidden"', $tag_name, $tag_value, '/>', "\n";
							}
							break;
					}
				} else {
					echo '<td class="lcelcd"><input type="checkbox"', $tag_name, $tag_value, $tag_id, $tag_disabled, '/></td>', "\n";
				}
				?>
				<td class="<?=$notprotected ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($sphere_record['name']);?></td>
				<td class="<?=$notprotected ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($sphere_record['serial']);?></td>
				<td class="<?=$notprotected ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($sphere_record['size']);?></td>
				<td class="<?=$notprotected ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($sphere_record['devicespecialfile']);?></td>
				<td class="<?=$notprotected ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($sphere_record['protected.reason']);?></td>
				<td class="lcebld"><table id="area_data_selection_toolbox"><tbody><tr>
					<td>
						<?php
						if ($notprotected) {
						} else {
							echo '<img src="', $img_path['loc'], '" title="', $gt_record_loc, '" alt="', $gt_record_loc . '"/>', "\n";
						}
						?>
					</td>
					<td></td>
					<td></td>
				</tr></tbody></table></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div id="submit">
		<?php 
		switch ($a_button['submit_control']) {
			case 2: echo '<input type="submit" class="formbtn" name="', $a_button['submit_name'], '" value="', $a_button['submit_value'], '"/>', "\n"; break;
			case 1: echo '<input type="submit" class="formbtn" name="', $a_button['submit_name'], '" value="', $a_button['submit_value'], '" disabled="disabled"/>', "\n"; break;
		}
		switch ($a_button['cancel_control']) {
			case 2: echo '<input type="submit" class="formbtn" name="', $a_button['cancel_name'], '" value="', $a_button['cancel_value'], '"/>', "\n"; break;
			case 1: echo '<input type="submit" class="formbtn" name="', $a_button['cancel_name'], '" value="', $a_button['cancel_value'], '" disabled="disabled"/>', "\n"; break;
		}
		?>
	</div>
	<?php
	if (count($do_format) > 0) {
		foreach ($do_format as $do_format_disk) {
			echo(sprintf("<div id='cmdoutput'>%s</div>", sprintf(gtext("Command output") . " for disk %s :", $do_format_disk['devicespecialfile'])));
			echo('<pre class="cmdoutput">');
			disks_format($do_format_disk['devicespecialfile'], $do_format_disk['filesystem'], $do_format_disk['notinitmbr'], $do_format_disk['minspace'], $do_format_disk['volumelabel'], $do_format_disk['aft4k'], $do_format_disk['zfsgpt']);
			echo('</pre><br/>');
		}
	}
	?>
	<?php include('formend.inc');?>
</form></td></tr></tbody></table>
<?php include('fend.inc');?>
