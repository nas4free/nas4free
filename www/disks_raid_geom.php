<?php
/*
	disks_raid_geom.php

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
require 'disks_raid_geom_fun.inc';

$sphere_scriptname = basename(__FILE__);
$sphere_scriptname_child = 'disks_raid_geom_edit.php';
$sphere_header = 'Location: ' . $sphere_scriptname;
$sphere_header_parent = $sphere_header;
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_add = gtext('Add RAID');
$gt_record_mod = gtext('Edit RAID');
$gt_record_del = gtext('RAID is marked for removal');
$gt_record_loc = gtext('RAID is protected');
$gt_record_unl = gtext('RAID is unlocked');
$gt_record_mai = gtext('Maintenance');
$gt_record_inf = gtext('Information');
$gt_selection_delete = gtext('Delete Selected RAID Volumes');
$gt_selection_delete_confirm = gtext('Do you want to delete selected RAID volumes?');
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
geomraid_config_get($sphere_array);
array_sort_key($sphere_array, 'name');
// get mounts from config
$a_config_mount = &array_make_branch($config,'mounts','mount');
// get all softraids (cli)
$a_system_sraid = get_sraid_disks_list();
// collect geom additional information
$a_process = geomraid_processinfo_get();

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			// Process notifications
			$savemsg = get_std_save_message($retval); // set default message
			foreach ($a_process as $r_process) {
				if ($retval == 0) {
					$retval |= updatenotify_process($r_process['x-notifier'], $r_process['x-processor']);
					if ($retval == 0) {
						updatenotify_delete($r_process['x-notifier']);
					} else {
						$savemsg = get_std_save_message($retval);
						break;
					}
				} else {
					break;
				}
			}
		}
		header($sphere_header);
		exit;
	}
	if (isset($_POST['delete_selected_rows']) && $_POST['delete_selected_rows']) {
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		foreach ($checkbox_member_array as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				if (!isset($sphere_array[$index]['protected'])) {
					$sphere_notifier = $a_process[$sphere_array[$index]['type']]['x-notifier']; // get the notifier
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
		}
		header($sphere_header);
		exit;
	}
}

$pgtitle = [gtext('Disks'), gtext('Software RAID'), gtext('GEOM'), gtext('Management')];
?>
<?php include 'fbegin.inc';?>
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
	$("input[name='<?=$checkbox_member_name;?>[]']").click(function() {
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
		if (a_trigger[i].type === 'checkbox') {
			if (!a_trigger[i].disabled) {
				a_trigger[i].checked = !a_trigger[i].checked;
				if (a_trigger[i].checked) {
					ab_disable = false;
				}
			}
		}
	}
	if (ego.type === 'checkbox') { ego.checked = false; }
	disableactionbuttons(ab_disable);
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var ab_disable = true;
	var i = 0;
	for (; i < n_trigger; i++) {
		if (a_trigger[i].type === 'checkbox') {
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
				<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('GEOM');?></span></a></li>
				<li class="tabinact"><a href="disks_raid_gvinum.php"><span><?=gtext('RAID 0/1/5');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
		if (!empty($errormsg)) { print_error_box($errormsg); }
		if (!empty($savemsg)) { print_info_box($savemsg); }
		foreach ($a_process as $r_process) {
			if (updatenotify_exists($r_process['x-notifier'])) {
				print_config_change_box();
				break;
			}
		}
	?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:20%">
			<col style="width:10%">
			<col style="width:15%">
			<col style="width:30%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'), 7);?>
			<tr>
				<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></th>
				<th class="lhell"><?=gtext('Volume Name');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($sphere_array as $sphere_record): ?>
				<?php
					$size = gtext('Unknown');
					$status = gtext('Stopped');
					if (is_array($a_system_sraid) && (false !== ($index = array_search_ex($sphere_record['name'], $a_system_sraid, 'name')))) {
						$size = $a_system_sraid[$index]['size'];
						$status = $a_system_sraid[$index]['state'];
					}
					$notificationmode = UPDATENOTIFY_MODE_UNKNOWN;
					foreach($a_process as $r_process) {
						if (UPDATENOTIFY_MODE_UNKNOWN === $notificationmode) {
							$notificationmode = updatenotify_get_mode($r_process['x-notifier'], $sphere_record['uuid']);
						} else {
							break;
						}
					}
					switch ($notificationmode) {
						case UPDATENOTIFY_MODE_NEW:
							$status = $size = gtext('Initializing');
							break;
						case UPDATENOTIFY_MODE_MODIFIED:
							$status = $size = gtext('Modifying');
							break;
						case UPDATENOTIFY_MODE_DIRTY:
						case UPDATENOTIFY_MODE_DIRTY_CONFIG:
							$status = gtext('Deleting');
							break;
					}
					$status = strtoupper($status);
					$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
					$notprotected = !isset($sphere_record['protected']);
					$notmounted = !is_geomraid_mounted($sphere_record['devicespecialfile'], $a_config_mount);
					$normaloperation = $notprotected && $notmounted;
				?>
				<tr>
					<td class="<?=$normaloperation ? "lcelc" : "lcelcd";?>">
						<?php if ($notdirty && $notprotected && $notmounted):?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
						<?php else:?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" disabled="disabled"/>
						<?php endif;?>
					</td>
					<td class="<?=$normaloperation ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['name']);?></td>
					<td class="<?=$normaloperation ? "lcell" : "lcelld";?>"><?=htmlspecialchars($a_process[$sphere_record['type']]['gt-type']);?></td>
					<td class="<?=$normaloperation ? "lcell" : "lcelld";?>"><?=$size;?>&nbsp;</td>
					<td class="<?=$normaloperation ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['desc']);?></td>
					<td class="<?=$normaloperation ? "lcelc" : "lcelcd";?>"><?=$status;?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
								<?php if ($notdirty && $notprotected):?>
									<a href="<?=$sphere_scriptname_child;?>?uuid=<?=$sphere_record['uuid'];?>"><img src="<?=$img_path['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>" /></a>
								<?php else:?>
									<?php if ($notprotected && $notmounted):?>
										<img src="<?=$img_path['del'];?>" title="<?=$gt_record_del;?>" alt="<?=$gt_record_del;?>"/>
									<?php else:?>
										<img src="<?=$img_path['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>"/>
									<?php endif;?>
								<?php endif;?>
							</td>
							<td><a href="<?=$a_process[$sphere_record['type']]['x-page-maintenance'];?>"><img src="<?=$img_path['mai'];?>" title="<?=$gt_record_mai;?>" alt="<?=$gt_record_mai;?>" /></a></td>
							<td><a href="<?=$a_process[$sphere_record['type']]['x-page-information'];?>"><img src="<?=$img_path['inf'];?>" title="<?=$gt_record_inf?>" alt="<?=$gt_record_inf?>" /></a></td>
						</tr></tbody></table>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="6"></th>
				<th class="lceadd"><a href="<?=$sphere_scriptname_child;?>"><img src="<?=$img_path['add'];?>" title="<?=$gt_record_add;?>" alt="<?=$gt_record_add;?>"/></a></th>
			</tr>
		</tfoot>
	</table>
	<div id="submit">
		<input name="delete_selected_rows" id="delete_selected_rows" type="submit" class="formbtn" value="<?=$gt_selection_delete;?>"/>
	</div>
	<table id="area_data_messages">
		<colgroup>
			<col id="area_data_messages_col_tag">
			<col id="area_data_messages_col_data">
		</colgroup>
		<thead>
			<?php
				html_separator2();
				html_titleline2(gtext('Message Board'));
			?>
		</thead>
		<tbody>
			<?php
				html_textinfo2("info", gtext('Info'), sprintf(gtext('%1$s is used to create %2$s volumes.'), 'GEOM', 'RAID'));
				$link = sprintf('<a href="%1$s">%2$s</a>', 'disks_mount.php', gtext('mount point'));
				$helpinghand = gtext('A mounted RAID volume cannot be deleted.') . ' ' . gtext('Remove the %s first before proceeding.');
				$helpinghand = sprintf($helpinghand, $link);
				html_textinfo2("warning", gtext('Warning'), $helpinghand);
			?>
		</tbody>
	</table>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
