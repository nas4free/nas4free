<?php
/*
	disks_raid_geom_edit.php

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
require("auth.inc");
require("guiconfig.inc");
require 'disks_raid_geom_fun.inc';

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: disks_raid_geom.php';
$sphere_notifier = 'raidgeom';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_loc = gtext('RAID device is already in use.');
$gt_record_opn = gtext('RAID device can be removed.');
$gt_confirm_mirror = gtext('Do you want to create a RAID-1 from selected disks?');
$gt_confirm_raid5 = gtext('Do you want to create a RAID-5 from selected disks?');
$gt_confirm_stripe = gtext('Do you want to create a RAID-0 from selected disks?');
$gt_confirm_concat = gtext('Do you want to create a JBOD from selected disks?');
$prerequisites_ok = true; // flag to indicate lack of information / resources
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png'
];
// detect page mode (GET, POST, ADD)
$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD);
// process cancel event, allow only submit or action
if (PAGE_MODE_POST == $mode_page) { // POST is Cancel
	if ((isset($_POST['Cancel']) && $_POST['Cancel']) && !(isset($_POST['Submit']) && $_POST['Submit']) && !(isset($_POST['Action']) && $_POST['Action'])) {
		header($sphere_header_parent);
		exit;
	}
}
// get/set uuid based on page mode (GET, POST, ADD)
if ((PAGE_MODE_POST == $mode_page) && isset($_POST['uuid']) && is_uuid_v4($_POST['uuid'])) {
	$sphere_record['uuid'] = $_POST['uuid'];
} else {
	if ((PAGE_MODE_EDIT == $mode_page) && isset($_GET['uuid']) && is_uuid_v4($_GET['uuid'])) {
		$sphere_record['uuid'] = $_GET['uuid'];
	} else {
		$mode_page = PAGE_MODE_ADD; // Force ADD
		$sphere_record['uuid'] = uuid();
	}
}
// read configuration data
geomraid_config_get($sphere_array);
// array_sort_key($sphere_array, 'name'); // no need to sort the array, we're in single record mode
// get additional processing information
$a_process = geomraid_processinfo_get();
// scan for pending tasks
$mode_updatenotify = UPDATENOTIFY_MODE_UNKNOWN;
foreach ($a_process as $r_process) {
	if (UPDATENOTIFY_MODE_UNKNOWN === $mode_updatenotify) {
		$mode_updatenotify = updatenotify_get_mode($r_process['x-notifier'], $sphere_record['uuid']); // get updatenotify mode for uuid
	} else {
		break;
	}
}
// find index of uuid in the main array
$index = array_search_ex($sphere_record['uuid'], $sphere_array, 'uuid');
// determine record mode, exit page if information doesn't mke sense
$mode_record = RECORD_ERROR;
if (false !== $index) { // record for uuid found in configuration 
	if ((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))) { // POST or EDIT
		switch ($mode_updatenotify) {
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		}
	}
} else { // record for uuid not found in configuration
	if ((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page)) { // POST or ADD
		switch ($mode_updatenotify) {
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		}
	}
}
if (RECORD_ERROR == $mode_record) { // oops, someone tries to cheat, over and out
	header($sphere_header_parent);
	exit;
}
$isrecordnew = (RECORD_NEW === $mode_record);
$isrecordnewmodify = (RECORD_NEW_MODIFY == $mode_record);
$isrecordmodify = (RECORD_MODIFY === $mode_record);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);

// get all known softraids (config)
$a_config_sraid = get_conf_sraid_disks_list();
// get all disks that are softraid-formatted 
$a_sdisk = get_conf_disks_filtered_ex('fstype', 'softraid');
if (!sizeof($a_sdisk)) {
	$errormsg = gtext('No softraid-formatted disks available.');
	$prerequisites_ok = false;
}
if (PAGE_MODE_POST == $mode_page) { // We know POST is "Submit" or "Action", already checked at the beginning
	if (isset($_POST['Submit']) && $_POST['Submit']) { // Submit is coming from save button which is only shown on RECORD_MODIFY
		$sphere_record['balance']           = isset($sphere_array[$index]['balance']) ? $sphere_array[$index]['balance'] : '';
		$sphere_record['desc']              = (isset($_POST['desc']) ? $_POST['desc'] : '');
		$sphere_record['device']            = $sphere_array[$index]['device'];
		$sphere_record['init']              = false;
		$sphere_record['name']              = $sphere_array[$index]['name'];
		$sphere_record['size']              = $sphere_array[$index]['size'];
		$sphere_record['type']              = $sphere_array[$index]['type'];
		$sphere_record['devicespecialfile'] = sprintf('%1$s/%2$s', $a_process[$sphere_record['type']]['x-devdir'], $sphere_record['name']);
	} elseif (isset($_POST['Action']) && preg_match('/\S/', $_POST['Action'])) { // Action is coming from action buttons which are only shown on RECORD_NEW or RECORD_NEW_MODIFY
		$sphere_record['balance']           = (isset($_POST['balance']) ? $_POST['balance'] : '');
		$sphere_record['desc']              = (isset($_POST['desc']) ? $_POST['desc'] : '');
		$sphere_record['device']            = (isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : []);
		$sphere_record['init']              = isset($_POST['init']);
		$sphere_record['name']              = (isset($_POST['name']) ? substr($_POST['name'], 0, 15) : ''); // Make sure name is only 15 chars long (GEOM limitation).
		$sphere_record['size']              = 'Unknown';
		$sphere_record['type']              = $_POST['Action'];
		$sphere_record['devicespecialfile'] = sprintf('%1$s/%2$s', $a_process[$sphere_record['type']]['x-devdir'], $sphere_record['name']);
	} else { // something went wrong with POST, we exit
		header($sphere_header_parent);
		exit;
	}
	// start validation
	unset($input_errors);
	// input validation
	$reqdfields = ['name'];
	$reqdfieldsn = [gtext('RAID Name')];
	do_input_validation($sphere_record, $reqdfields, $reqdfieldsn, $input_errors);
	// logic validation
	if ($prerequisites_ok && empty($input_errors)) { // check for a valid RAID name.
		if (($sphere_record['name'] && !is_validaliasname($sphere_record['name']))) {
			$input_errors[] = gtext('The name of the RAID may only consist of the characters a-z, A-Z, 0-9.');
		}
	}
	if ($prerequisites_ok && empty($input_errors)) { // check for existing RAID names
		switch ($mode_record) { // verify config
			case RECORD_NEW:
				foreach ($a_config_sraid as $r_config_sraid) { // RAID name must not exist in config at all
					if ($r_config_sraid['name'] === $sphere_record['name']) {
						$input_errors[] = gtext('The name of the RAID is already in use.');
						break; // break loop
					}
				}
			break;
			case RECORD_NEW_MODIFY: 
				if ($sphere_record['name'] !== $sphere_array[$index]['name']) { // if the RAID name has changed it shouldn't be found in config
					foreach ($a_config_sraid as $r_config_sraid) {
						if ($r_config_sraid['name'] === $sphere_record['name']) {
							$input_errors[] = gtext('The name of the RAID is already in use.');
							break; // break loop
						}
					}
				}
				break;
			case RECORD_MODIFY: 
				if ($sphere_record['name'] !== $sphere_array[$index]['name']) { // should never happen because sphere_record['name'] should be set to $sphere_array[$index]['name']
					$input_errors[] = gtext('The name of the RAID cannot be changed.');
				}
				break;
		}
	}
	if ($prerequisites_ok && empty($input_errors)) { // check the number of disk for RAID volume
		$helpinghand = count($sphere_record['device']);
		switch ($sphere_record['type']) {
			case 'JBOD': if ($helpinghand < 1) { $input_errors[] = gtext('1 or more disks are required to build a JBOD volume.'); } break;
			case '1'   : if ($helpinghand < 1) { $input_errors[] = gtext('1 or more disks are required to build a RAID-1 volume.'); } break;
			case '5'   : if ($helpinghand < 3) { $input_errors[] = gtext('3 or more disks are required to build a RAID-5 volume.'); } break;
			case '0'   : if ($helpinghand < 2) { $input_errors[] = gtext('2 or more disks are required to build a RAID-0 volume.'); } break;
		}
	}
	// process POST
	if ($prerequisites_ok && empty($input_errors)) {
		$sphere_notifier = $a_process[$sphere_record['type']]['x-notifier'];
		switch ($mode_record) {
			case RECORD_NEW:
				if ($sphere_record['init']) { // create new RAID
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_NEW, $sphere_record['uuid']);
				} else { // existing RAID
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
				}
				unset($sphere_record['init']); // lifetime ends here
				$sphere_array[] = $sphere_record;
				break;
			case RECORD_NEW_MODIFY:
				if ($sphere_record['init']) { // create new RAID
				} else { // existing RAID
					updatenotify_clear($sphere_notifier, $sphere_record['uuid']); // clear NEW
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
				}
				unset($sphere_record['init']); // lifetime ends here
				$sphere_array[$index] = $sphere_record;
				break;
			case RECORD_MODIFY:
				if (UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify) {
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
				}
				unset($sphere_record['init']); // lifetime ends here
				$sphere_array[$index] = $sphere_record;
				break;
		}
		geomraid_config_set($sphere_array);
		write_config();
		header($sphere_header_parent);
		exit;
	}
} else { // EDIT / ADD
	switch ($mode_record) {
		case RECORD_NEW:
			$sphere_record['name'] = '';
			$sphere_record['type'] = '1'; // RAID1 by default
			$sphere_record['init'] = false;
			$sphere_record['balance'] = 'round-robin';
			$sphere_record['device'] = [];
			$sphere_record['devicespecialfile'] = '';
			$sphere_record['desc'] = gtext('GEOM Software RAID');
			break;
		case RECORD_NEW_MODIFY:
			$sphere_record['name'] = (isset($sphere_array[$index]['name']) ? $sphere_array[$index]['name'] : '');
			$sphere_record['type'] = (isset($sphere_array[$index]['type']) ? $sphere_array[$index]['type'] : '');
			$sphere_record['balance'] = (isset($sphere_array[$index]['balance']) ? $sphere_array[$index]['balance'] : '');
			$sphere_record['init'] = true; // it must have been set because the previous status of RECORD_NEW_MODIFY can only be RECOD_NEW.
			$sphere_record['device'] = (isset($sphere_array[$index]['device']) ? $sphere_array[$index]['device'] : []);
			$sphere_record['devicespecialfile'] = $sphere_array[$index]['devicespecialfile'];
			$sphere_record['desc'] = (isset($sphere_array[$index]['desc']) ? $sphere_array[$index]['desc'] : '');
			break;
		case RECORD_MODIFY:
			$sphere_record['name'] = (isset($sphere_array[$index]['name']) ? $sphere_array[$index]['name'] : '');
			$sphere_record['type'] = (isset($sphere_array[$index]['type']) ? $sphere_array[$index]['type'] : '');
			$sphere_record['balance'] = (isset($sphere_array[$index]['balance']) ? $sphere_array[$index]['balance'] : '');
			$sphere_record['init'] = false;
			$sphere_record['device'] = (isset($sphere_array[$index]['device']) ? $sphere_array[$index]['device'] : []);
			$sphere_record['devicespecialfile'] = $sphere_array[$index]['devicespecialfile'];
			$sphere_record['desc'] = (isset($sphere_array[$index]['desc']) ? $sphere_array[$index]['desc'] : '');
			break;
	}
}
// compile list of devices
$a_device = [];
foreach ($a_sdisk as $r_sdisk) {
	$helpinghand = $r_sdisk['devicespecialfile'] . (isset($r_sdisk['zfsgpt']) ? $r_sdisk['zfsgpt'] : '');
	$r_device = [];
	$r_device['name']              = isset($r_sdisk['name']) ? htmlspecialchars($r_sdisk['name']) : '';
	$r_device['uuid']              = isset($r_sdisk['uuid']) ? $r_sdisk['uuid'] : '';
	$r_device['model']             = isset($r_sdidk['model']) ? htmlspecialchars($r_sdisk['model']) : '';
	$r_device['devicespecialfile'] = htmlspecialchars($helpinghand);
	$r_device['partition']         = ((isset($r_sdisk['zfsgpt']) && (!empty($r_sdisk['zfsgpt'])))? $r_sdisk['zfsgpt'] : gtext('Entire Device'));
	$r_device['controller']        = (isset($r_sdisk['controller']) ? $r_sdisk['controller'] : '?') . (isset($r_sdisk['controller_id']) ?  $r_sdisk['controller_id'] : '');
	if (isset($r_sdisk['controller_desc'])) {
		$r_device['controller']   .= (' (' . $r_sdisk['controller_desc'] . ')');
	}
	$r_device['size']              = isset($r_sdisk['size']) ? $r_sdisk['size'] : '';
	$r_device['serial']            = isset($r_sdisk['serial']) ? $r_sdisk['serial'] : '';
	$r_device['desc']              = isset($r_sdisk['desc']) ? htmlspecialchars($r_sdisk['desc']) : '';
	$r_device['isnotinasraid']     = (false === array_search_ex($r_device['devicespecialfile'], $a_config_sraid, 'device'));
	$r_device['isinthissraid']     = (isset($sphere_record['device']) && is_array($sphere_record['device']) && in_array($r_device['devicespecialfile'], $sphere_record['device']));
	$a_device[$helpinghand] = $r_device;
}
// prepare comboboxes
$l_balance = [
	'round-robin' => gtext('Round-robin read'),
	'split' => gtext('Split request'),
	'load' => gtext('Read from lowest load'),
	'prefer' => gtext('Read from biggest priority')
];
// give it a title
$pgtitle = array(gtext('Disks'), gtext('Software RAID'), gtext('GEOM'), ($isrecordnew) ? gtext('Add') : gtext('Edit'));
?>
<?php include("fbegin.inc"); ?>
<?php if ($isrecordnewornewmodify):?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("input[name='<?=$checkbox_member_name;?>[]").click(function() {
		controlactionbuttons(this, '<?=$checkbox_member_name;?>[]');
	});
	$("#togglebox").click(function() {
		toggleselection($(this)[0], "<?=$checkbox_member_name;?>[]");
	});
	$("#button_raid1").click(function () { return confirm('<?=$gt_confirm_mirror;?>'); });
	$("#button_raid5").click(function () { return confirm('<?=$gt_confirm_raid5;?>'); });
	$("#button_raid0").click(function () { return confirm('<?=$gt_confirm_stripe;?>'); });
	$("#button_jbod").click(function () { return confirm('<?=$gt_confirm_concat;?>'); });
	controlactionbuttons(this,'<?=$checkbox_member_name;?>[]');
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
});
function disableactionbuttons(n) {
	var ab_element;
	var ab_disable = [];
	if (typeof(n) !== 'number') { n = 0; }
 	switch (n) { //            mirror, raid5 , stripe, concat
		case  0: ab_disable = [true  , true  , true  , true ]; break;
		case  1: ab_disable = [false , true  , true  , false]; break;
		case  2: ab_disable = [false , true  , false , false]; break;
		default: ab_disable = [false , false , false , false]; break; // setting for 3 or more disks
	}		
	ab_element = document.getElementById('button_raid1'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[0])) { ab_element.disabled = ab_disable[0]; }
	ab_element = document.getElementById('button_raid5'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[1])) { ab_element.disabled = ab_disable[1]; }
	ab_element = document.getElementById('button_raid0'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[2])) { ab_element.disabled = ab_disable[2]; }
	ab_element = document.getElementById('button_jbod'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[3])) { ab_element.disabled = ab_disable[3]; }
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	var n = 0;
	for (; i < n_trigger; i++) {
		if ((a_trigger[i].type === 'checkbox') && !a_trigger[i].disabled && a_trigger[i].checked) {
			n++;
		}
	}
	disableactionbuttons(n);
}
function toggleselection(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	var n = 0;
	for (; i < n_trigger; i++) {
		if ((a_trigger[i].type === 'checkbox') && !a_trigger[i].disabled) {
			a_trigger[i].checked = !a_trigger[i].checked;
			if (a_trigger[i].checked) {
				n++;
			}
		}
	}
	disableactionbuttons(n);
	$("#togglebox").prop("checked", false);
}
//]]>
</script>
<?php endif;?>
<table id="area_navigator">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="disks_raid_geom.php" title="<?=gtext('Reload page');?>"><span><?=gtext("GEOM");?></span></a></li>
				<li class="tabinact"><a href="disks_raid_gvinum.php"><span><?=gtext('RAID 0/1/5');?></span></a></li>
			</ul>
		</td>
	</tr>
</table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php 
		if (!empty($errormsg)) { print_error_box($errormsg); }
		if (!empty($input_errors)) { print_input_errors($input_errors); }
		if (file_exists($d_sysrebootreqd_path)) { print_info_box(get_std_save_message(0)); }
	?>
	<?php if ($isrecordnewornewmodify):?>
		<div id="submit" style="margin-bottom:10px">
			<?php foreach ($a_process as $r_process):?>
				<button name="Action" id="<?=$r_process['x-button'];?>" type="submit" class="formbtn" value="<?=$r_process['type'];?>"><?=$r_process['gt-type'];?></button>
			<?php endforeach;?>
		</div>
	<?php endif;?>
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Settings'));?>
		</thead>
		<tbody>
			<?php
				html_inputbox2('name', gtext('RAID Name'), $sphere_record['name'], '', true, 15, $isrecordmodify); // readonly if not new and not new-modify
				if ($isrecordmodify) {
					html_inputbox2('type', gtext('RAID Type'), $a_process[$sphere_record['type']]['gt-type'], '', false, 40, true);
				}
				$helpinghand = [
					[gtext('Select read balance algorithm.')],
					[gtext('This option applies to GEOM RAID-1 only.')]
				];
				html_combobox2('balance', gtext('Balance Algorithm'), $sphere_record['balance'], $l_balance, $helpinghand, false, $isrecordmodify);
				$helpinghand = [
					[gtext('Do not activate this option if you want to add an existing RAID.')],
					[gtext('All data will be lost when you activate this option!'), 'red']
				];
				html_checkbox2('init', gtext('Initialize'), !empty($sphere_record['init']) ? true : false, gtext('Create and initialize RAID.'), $helpinghand, false, $isrecordmodify);
				html_inputbox2('desc', gtext('Description'), $sphere_record['desc'], gtext('You may enter a description here for your reference.'), false, 48);
				html_separator2();
			?>
		</tbody>
	</table>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"> <!--// checkbox -->
			<col style="width:10%"><!--// Device -->
			<col style="width:10%"><!--// Partition -->
			<col style="width:15%"><!--// Model -->
			<col style="width:10%"><!--// Serial -->
			<col style="width:10%"><!--// Size -->
			<col style="width:20%"><!--// Controller -->
			<col style="width:15%"><!--// Description -->
			<col style="width:5%"> <!--// Icons -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Device List'), 9);?>
			<tr>
				<td class="lhelc">
					<?php if ($isrecordnewornewmodify):?>
						<input type="checkbox" id="togglebox" name="togglebox" title="<?=gtext('Invert Selection');?>"/>
					<?php else:?>
						<input type="checkbox" id="togglebox" name="togglebox" disabled="disabled"/>
					<?php endif;?>
				</td>
				<td class="lhell"><?=gtext('Device');?></td>
				<td class="lhell"><?=gtext('Partition');?></td>
				<td class="lhell"><?=gtext('Model');?></td>
				<td class="lhell"><?=gtext('Serial Number');?></td>
				<td class="lhell"><?=gtext('Size');?></td>
				<td class="lhell"><?=gtext('Controller');?></td>
				<td class="lhell"><?=gtext('Name');?></td>
				<td class="lhebl">&nbsp;</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($a_device as $r_device):?>
				<?php 
					$isnotinasraid = $r_device['isnotinasraid'];
					$isinthissraid = $r_device['isinthissraid'];
				?>
				<?php if ($isrecordnewornewmodify):?>
					<?php if ($isnotinasraid || $isinthissraid):?>
						<tr>
							<td class="lcelc">
								<?php if ($isinthissraid):?>
									<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" id="<?=$r_device['uuid'];?>" checked="checked"/>
								<?php else:?>
									<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" id="<?=$r_device['uuid'];?>"/>
								<?php endif;?>	
							</td>
							<td class="lcell"><?=htmlspecialchars($r_device['name']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['partition']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['model']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['serial']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['size']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['controller']);?>&nbsp;</td>
							<td class="lcell"><?=htmlspecialchars($r_device['desc']);?>&nbsp;</td>
							<td class="lcebcd">
								<?php if ($isinthissraid):?>
									<img src="<?=$img_path['unl'];?>" title="<?=$gt_record_opn;?>" alt="<?=$gt_record_opn;?>" />
								<?php else:?>
									&nbsp;
								<?php endif;?>
							</td>
						</tr>
					<?php endif;?>
				<?php endif;?>
				<?php if ($isrecordmodify):?>
					<?php if ($isinthissraid):?>
						<tr>
							<td class="lcelcd">
								<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" id="<?=$r_device['uuid'];?>" checked="checked" disabled="disabled"/>
							</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['name']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['partition']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['model']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['serial']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['size']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['controller']);?>&nbsp;</td>
							<td class="lcelld"><?=htmlspecialchars($r_device['desc']);?>&nbsp;</td>
							<td class="lcebcd">
								<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>" />
							</td>
						</tr>
					<?php endif;?>
				<?php endif;?>
			<?php endforeach;?>
		</tbody>
	</table>
	<div id="submit">
		<?php if ($isrecordmodify):?>
			<input name="Submit" id="submit_button" type="submit" class="formbtn" value="<?=gtext('Save');?>"/>
		<?php endif;?>
		<input name="Cancel" id="cancel_button" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>" />
	</div>
	<?php include("formend.inc");?>
</form></td></tr></tbody></table>
<?php include("fend.inc");?>
