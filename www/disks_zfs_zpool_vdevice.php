<?php
/*
	disks_zfs_zpool_vdevice.php

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

$sphere_scriptname = basename(__FILE__);
$sphere_scriptname_child = 'disks_zfs_zpool_vdevice_edit.php';
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = $sphere_header;
$sphere_notifier = 'zfsvdev';
$sphere_notifier_processor = 'zfsvdev_process_updatenotification';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_add = gtext('Add Virtual Device');
$gt_record_mod = gtext('Edit Virtual Device');
$gt_record_del = gtext('Virtual Device is marked for removal');
$gt_record_loc = gtext('Virtual Device is protected');
$gt_record_unl = gtext('Virtual Device is unlocked');
$gt_record_mai = gtext('Maintenance');
$gt_record_inf = gtext('Information');
$gt_selection_delete = gtext('Delete Selected Virtual Devices');
$gt_selection_delete_confirm = gtext('Do you want to delete selected virtual devices?');
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png'
];
// sunrise: verify if setting exists, otherwise run init tasks
if (!(isset($config['zfs']['vdevices']['vdevice']) && is_array($config['zfs']['vdevices']['vdevice']))) {
	$config['zfs']['vdevices']['vdevice'] = [];
}
array_sort_key($config['zfs']['vdevices']['vdevice'], 'name');
$sphere_array = &$config['zfs']['vdevices']['vdevice'];

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
//		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process($sphere_notifier, $sphere_notifier_processor);
			$savemsg = get_std_save_message($retval);
			if ($retval === 0) {
				updatenotify_delete($sphere_notifier);
			}
			header($sphere_header);
			exit;
//		}
	}
	if (isset($_POST['delete_selected_rows']) && $_POST['delete_selected_rows']) {
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		foreach ($checkbox_member_array as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
				switch ($mode_updatenotify) {
					case UPDATENOTIFY_MODE_NEW:
						updatenotify_clear($sphere_notifier, $sphere_array[$index]['uuid']);
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY_CONFIG, $sphere_array[$index]['uuid']);
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						updatenotify_clear($sphere_notifier, $sphere_array[$index]['uuid']);
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $sphere_array[$index]['uuid']);
						break;
					case UPDATENOTIFY_MODE_UNKNOWN:
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_DIRTY, $sphere_array[$index]['uuid']);
						break;
				}
			}
		}
		header($sphere_header);
		exit;
	}
}

function zfsvdev_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
		case UPDATENOTIFY_MODE_DIRTY:
			$index = array_search_ex($data, $config['zfs']['vdevices']['vdevice'], 'uuid');
			if (false !== $index) {
				unset($config['zfs']['vdevices']['vdevice'][$index]);
				write_config();
			}
			break;
	}
	return $retval;
}

if (!(isset($config['zfs']['pools']['pool']) && is_array($config['zfs']['pools']['pool']))) {
	$config['zfs']['pools']['pool'] = [];
}
$a_pool = &$config['zfs']['pools']['pool'];
 
$pgtitle = array(gtext('Disks'), gtext('ZFS'), gtext('Pools'), gtext('Virtual Device'));
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init action buttons
	$("#delete_selected_rows").click(function () {
		return confirm('<?=$gt_selection_delete_confirm;?>');
	});
	// Disable action buttons.
	disableactionbuttons(true);
	// Init toggle checkbox
	$("#togglemembers").click(function() {
		togglecheckboxesbyname(this, "<?=$checkbox_member_name;?>[]");
	});
	// Init member checkboxes
	$("input[name='<?=$checkbox_member_name;?>[]").click(function() {
		controlactionbuttons(this, '<?=$checkbox_member_name;?>[]');
	});
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
});
function disableactionbuttons(ab_disable) {
	$("#delete_selected_rows").prop("disabled", ab_disable);
}
function togglecheckboxesbyname(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
				if (a_trigger[i].checked) {
					ab_disable = false;
				}
			}
		}
	}
	if (ego.type == 'checkbox') { ego.checked = false; }
	disableactionbuttons(ab_disable);
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type == 'checkbox') {
			if (a_trigger[i].checked) {
				ab_disable = false;
				break;
			}
		}
	}
	disableactionbuttons(ab_disable);
}
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="disks_zfs_zpool.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('Virtual Device');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Management');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool_tools.php"><span><?=gtext('Tools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool_info.php"><span><?=gtext('Information');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool_io.php"><span><?=gtext('I/O Statistics');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
		if (!empty($savemsg)) {
			print_info_box($savemsg);
		} else {
			if (file_exists($d_sysrebootreqd_path)) {
				print_info_box(get_std_save_message(0));
			}
		}
		if (updatenotify_exists($sphere_notifier)) { print_config_change_box(); }
	?>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:15%"><!-- // Name -->
			<col style="width:15%"><!-- // Type -->
			<col style="width:55%"><!-- // Description -->
			<col style="width:10%"><!-- // Toolbox -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'), 5);?>
			<tr>
				<td class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></td>
				<td class="lhell"><?=gtext('Name');?></td>
				<td class="lhell"><?=gtext('Type');?></td>
				<td class="lhell"><?=gtext('Description');?></td>
				<td class="lhebl"><?=gtext('Toolbox');?></td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="4"></th>
				<th class="lceadd"><a href="<?=$sphere_scriptname_child;?>"><img src="<?=$img_path['add'];?>" title="<?=$gt_record_add;?>" alt="<?=$gt_record_add;?>"/></a></th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($sphere_array as $sphere_record):?>
				<?php
					$notificationmode = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']);
					$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
					$notprotected = !isset($sphere_record['protected']);
					$isnotmemberofapool = (false === array_search_ex($sphere_record['name'], $a_pool, 'vdevice'));
				?>
				<tr>
					<td class="lcelc">
						<?php if ($notdirty && $notprotected && $isnotmemberofapool):?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
						<?php else:?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" disabled="disabled"/>
						<?php endif;?>
					</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['name']);?></td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['type']);?></td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['desc']);?>&nbsp;</td>
					<td class="lcebld">
						<table id="area_data_selection_toolbox"><tbody><tr>
							<td>
								<?php if ($notdirty && $notprotected):?>
									<a href="<?=$sphere_scriptname_child;?>?uuid=<?=$sphere_record['uuid'];?>"><img src="<?=$img_path['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>" /></a>
								<?php else:?>
									<?php if ($notprotected && $isnotmemberofapool):?>
										<img src="<?=$img_path['del'];?>" title="<?=$gt_record_del;?>" alt="<?=$gt_record_del;?>"/>
									<?php else:?>
										<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>"/>
									<?php endif;?>
								<?php endif;?>
							</td>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<div id="submit">
		<input name="delete_selected_rows" id="delete_selected_rows" type="submit" class="formbtn" value="<?=$gt_selection_delete;?>"/>
	</div>
	<?php include("formend.inc");?>
</form></td></tr></tbody></table>
<?php include("fend.inc");?>
