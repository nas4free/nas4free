<?php
/*
	disks_zfs_dataset.php

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
require 'auth.inc';
require 'guiconfig.inc';
require 'zfs.inc';

$sphere_scriptname = basename(__FILE__);
$sphere_scriptname_child = 'disks_zfs_dataset_edit.php';
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = $sphere_header;
$sphere_notifier = 'zfsdataset';
$sphere_notifier_processor = 'zfsdataset_process_updatenotification';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_add = gtext('Add Dataset');
$gt_record_mod = gtext('Edit Dataset');
$gt_record_del = gtext('Dataset is marked for deletion');
$gt_record_loc = gtext('Dataset is protected');
$gt_record_unl = gtext('Dataset is unlocked');
$gt_record_mai = gtext('Maintenance');
$gt_record_inf = gtext('Information');
$gt_selection_delete = gtext('Delete Selected Datasets');
$gt_selection_delete_confirm = gtext('Do you want to delete selected datasets?');

// sunrise: verify if setting exists, otherwise run init tasks
$sphere_array = &array_make_branch($config,'zfs','datasets','dataset');
if(empty($sphere_array)):
else:
	array_sort_key($sphere_array,'name');
endif;

if ($_POST):
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)):
			// Process notifications
			$retval |= updatenotify_process($sphere_notifier, $sphere_notifier_processor);
		endif;
		$savemsg = get_std_save_message($retval);
		if ($retval == 0):
			updatenotify_delete($sphere_notifier);
		endif;
		header($sphere_header);
		exit;
	endif;
	if(isset($_POST['delete_selected_rows']) && $_POST['delete_selected_rows']):
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		foreach ($checkbox_member_array as $checkbox_member_record):
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))):
				if (!isset($sphere_array[$index]['protected'])):
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
					switch ($mode_updatenotify):
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
					endswitch;
				endif;
			endif;
		endforeach;
		header($sphere_header);
		exit;
	endif;
endif;

function zfsdataset_process_updatenotification($mode, $data) {
	global $config;
	$retval = 0;
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
			$retval |= zfs_dataset_configure($data);
			if(isset($config['rrdgraphs']['enable'])):
				if(!file_exists($d_sysrebootreqd_path)):
					config_lock();
					$retval |= rc_update_service("cron");
					config_unlock();
				endif;
			endif;
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= zfs_dataset_properties($data);
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if(false !== ($index = array_search_ex($data, $config['zfs']['datasets']['dataset'], 'uuid'))):
				unset($config['zfs']['datasets']['dataset'][$index]);
				write_config();
			endif;
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if(false !== ($index = array_search_ex($data, $config['zfs']['datasets']['dataset'], 'uuid'))):
				$retval |= zfs_dataset_destroy($data);
				if ($retval === 0):
					unset($config['zfs']['datasets']['dataset'][$index]);
					write_config();
				endif;
			endif;
			break;
	endswitch;
	return $retval;
}
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Datasets'),gtext('Dataset')];
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
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('Dataset');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset_info.php"><span><?=gtext('Information');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if (!empty($savemsg)):
		print_info_box($savemsg);
	else:
		if (file_exists($d_sysrebootreqd_path)):
			print_info_box(get_std_save_message(0));
		endif;
	endif;
	if (updatenotify_exists($sphere_notifier)):
		print_config_change_box();
	endif;
	?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:10%">
			<col style="width:45%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'),6);?>
			<tr>
				<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Compression');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($sphere_array as $sphere_record):?>
				<?php
				$notificationmode = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$notprotected = !isset($sphere_record['protected']);
				?>
				<tr>
					<td class="lcelc">
						<?php if ($notdirty && $notprotected):?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
						<?php else:?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" disabled="disabled"/>
						<?php endif;?>
					</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['pool'][0]);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['name']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['compression']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($sphere_record['desc']);?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
								<?php if ($notdirty && $notprotected):?>
									<a href="<?=$sphere_scriptname_child;?>?uuid=<?=$sphere_record['uuid'];?>"><img src="<?=$g_img['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>"/></a>
								<?php else:?>
									<?php if ($notprotected):?>
										<img src="<?=$g_img['del'];?>" title="<?=$gt_record_del;?>" alt="<?=$gt_record_del;?>"/>
									<?php else:?>
										<img src="<?=$g_img['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>"/>
									<?php endif;?>
								<?php endif;?>
							</td>
							<td></td>
							<td><a href="disks_zfs_dataset_info.php"><img src="<?=$g_img['inf'];?>" title="<?=$gt_record_inf?>" alt="<?=$gt_record_inf?>"/></a></td>
						</tr></tbody></table>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="5"></th>
				<th class="lceadd"><a href="<?=$sphere_scriptname_child;?>"><img src="<?=$g_img['add'];?>" title="<?=$gt_record_add;?>" alt="<?=$gt_record_add;?>"/></a></th>
			</tr>
		</tfoot>
	</table>
	<div id="submit">
		<input name="delete_selected_rows" id="delete_selected_rows" type="submit" class="formbtn" value="<?=$gt_selection_delete;?>"/>
	</div>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
