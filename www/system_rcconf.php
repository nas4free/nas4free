<?php
/*
	system_rcconf.php

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
$sphere_scriptname_child = 'system_rcconf_edit.php';
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = $sphere_header;
$sphere_notifier = 'rcconf';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_record_add = gtext('Add option');
$gt_record_mod = gtext('Edit option');
$gt_record_del = gtext('Option is marked for deletion');
$gt_record_loc = gtext('Option is locked');
$gt_record_mup = gtext('Move up');
$gt_record_mdn = gtext('Move down');
$gt_selection_toggle = gtext('Toggle Selected Options');
$gt_selection_toggle_confirm = gtext('Do you want to toggle selected options?');
$gt_selection_enable = gtext('Enable Selected Options');
$gt_selection_enable_confirm = gtext('Do you want to enable selected options?');
$gt_selection_disable = gtext('Disable Selected Options');
$gt_selection_disable_confirm = gtext('Do you want to disable selected options?');
$gt_selection_delete = gtext('Delete Selected Options');
$gt_selection_delete_confirm = gtext('Do you want to delete selected options?');
$img_path = [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png'
];

// sunrise: verify if setting exists, otherwise run init tasks
if (!(isset($config['system']) && is_array($config['system']))) {
	$config['system'] = [];
}
if (!(isset($config['system']['rcconf']) && is_array($config['system']['rcconf']))) {
	$config['system']['rcconf'] = [];
}
if (!(isset($config['system']['rcconf']['param']) && is_array($config['system']['rcconf']['param']))) {
	$config['system']['rcconf']['param'] = [];
}
$sphere_array = &$config['system']['rcconf']['param'];
if (!empty($sphere_array)) {
	$key1 = array_column($sphere_array, 'name');
	$key2 = array_column($sphere_array, 'uuid');
	array_multisort($key1, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $key2, SORT_ASC, SORT_STRING | SORT_FLAG_CASE, $sphere_array);
}

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process($sphere_notifier, 'rcconf_process_updatenotification');
			config_lock();
			$retval |= rc_exec_service($sphere_notifier);
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete($sphere_notifier);
		}
		header($sphere_header);
		exit;
	}
	if (isset($_POST['enable_selected_rows']) && $_POST['enable_selected_rows']) {
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfig = false;
		foreach ($checkbox_member_array as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				if (!(isset($sphere_array[$index]['enable']))) {
					$sphere_array[$index]['enable'] = true;
					$updateconfig = true;
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
					if (UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify) {
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
					}
				}
			}
		}
		if ($updateconfig) {
			write_config();
			$updateconfig = false;
		}
		header($sphere_header);
		exit;
	}
	if (isset($_POST['disable_selected_rows']) && $_POST['disable_selected_rows']) {
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfig = false;
		foreach ($checkbox_member_array as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				if (isset($sphere_array[$index]['enable'])) {
					unset($sphere_array[$index]['enable']);
					$updateconfig = true;
					$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
					if (UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify) {
						updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
					}
				}
			}
		}
		if ($updateconfig) {
			write_config();
			$updateconfig = false;
		}
		header($sphere_header);
		exit;
	}
	if (isset($_POST['toggle_selected_rows']) && $_POST['toggle_selected_rows']) {
		$checkbox_member_array = isset($_POST[$checkbox_member_name]) ? $_POST[$checkbox_member_name] : [];
		$updateconfig = false;
		foreach ($checkbox_member_array as $checkbox_member_record) {
			if (false !== ($index = array_search_ex($checkbox_member_record, $sphere_array, 'uuid'))) {
				if (isset($sphere_array[$index]['enable'])) {
					unset($sphere_array[$index]['enable']);
				} else {
					$sphere_array[$index]['enable'] = true;
				}
				$updateconfig = true;
				$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_array[$index]['uuid']);
				if (UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify) {
					updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_array[$index]['uuid']);
				}
			}
		}
		if ($updateconfig) {
			write_config();
			$updateconfig = false;
		}
		header($sphere_header);
		exit;
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

function rcconf_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['system']['rcconf']['param'])) {
				$index = array_search_ex($data, $config['system']['rcconf']['param'], 'uuid');
				if (false !== $index) {
					mwexec2("/usr/local/sbin/rconf attribute remove {$config['system']['rcconf']['param'][$index]['name']}");
					unset($config['system']['rcconf']['param'][$index]);
					write_config();
				}
			}
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if (is_array($config['system']['rcconf']['param'])) {
				$index = array_search_ex($data, $config['system']['rcconf']['param'], 'uuid');
				if (false !== $index) {
					unset($config['system']['rcconf']['param'][$index]);
					write_config();
				}
			}
			break;
	}
	return $retval;
}

$enabletogglemode = isset($config['system']['enabletogglemode']);
$pgtitle = array(gtext('System'), gtext('Advanced'), gtext('rc.conf'));
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init action buttons
	<?php if ($enabletogglemode):?>
		$("#toggle_selected_rows").click(function () {
			return confirm('<?=$gt_selection_toggle_confirm;?>');
		});
	<?php else:?>
		$("#enable_selected_rows").click(function () {
			return confirm('<?=$gt_selection_enable_confirm;?>');
		});
		$("#disable_selected_rows").click(function () {
			return confirm('<?=$gt_selection_disable_confirm;?>');
		});
	<?php endif;?>
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
}); 
function disableactionbuttons(ab_disable) {
	var ab_element;
	<?php if ($enabletogglemode):?>
		ab_element = document.getElementById('toggle_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
	<?php else:?>
		ab_element = document.getElementById('enable_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
		ab_element = document.getElementById('disable_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
	<?php endif;?>
	ab_element = document.getElementById('delete_selected_rows'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable)) { ab_element.disabled = ab_disable; }
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
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="system_advanced.php"><span><?=gtext('Advanced');?></span></a></li>
		<li class="tabinact"><a href="system_email.php"><span><?=gtext('Email');?></span></a></li>
		<li class="tabinact"><a href="system_swap.php"><span><?=gtext('Swap');?></span></a></li>
		<li class="tabinact"><a href="system_rc.php"><span><?=gtext('Command Scripts');?></span></a></li>
		<li class="tabinact"><a href="system_cron.php"><span><?=gtext('Cron');?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext('loader.conf');?></span></a></li>
		<li class="tabact"><a href="<?=$sphere_scriptname;?>" title="<?=gtext('Reload page');?>"><span><?=gtext('rc.conf');?></span></a></li>
		<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext('sysctl.conf');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iframe" name="iframe">
	<?php
		if (!empty($savemsg)) {
			print_info_box($savemsg);
		} else {
			if (file_exists($d_sysrebootreqd_path)) {
				print_info_box(get_std_save_message(0));
			}
		}
		if (updatenotify_exists($sphere_notifier)) {
			print_config_change_box();
		}
	?>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:30%">
			<col style="width:20%">
			<col style="width:5%">
			<col style="width:30%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'), 6);?>
			<tr>
				<th class="lhelc"><input type="checkbox" id="togglemembers" name="togglemembers" title="<?=gtext('Invert Selection');?>"/></th>
				<th class="lhell"><?=gtext('Variable');?></th>
				<th class="lhell"><?=gtext('Value');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhell"><?=gtext('Comment');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="5"></td>
				<td class="lceadd"><a href="<?=$sphere_scriptname_child;?>"><img src="<?=$img_path['add'];?>" title="<?=$gt_record_add;?>" border="0" alt="<?=$gt_record_add;?>"/></a></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($sphere_array as $sphere_record):?>
				<tr>
					<?php 
						$notificationmode = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']);
						$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
						$enabled = isset($sphere_record['enable']);
						$notprotected = !isset($sphere_record['protected']);
					?>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
						<?php if ($notdirty && $notprotected):?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" onclick="javascript:controlactionbuttons(this,'<?=$checkbox_member_name;?>[]')"/>
						<?php else:?>
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>" disabled="disabled"/>
						<?php endif;?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['name']);?>&nbsp;</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['value']);?>&nbsp;</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>">
						<?php if ($enabled):?>
							<a title="<?=gtext('Enabled');?>"><center><img src="<?=$img_path['ena'];?>" border="0" alt=""/></center></a>
						<?php else:?>
							<a title="<?=gtext('Disabled');?>"><center><img src="<?=$img_path['dis'];?>" border="0" alt=""/></center></a>
						<?php endif;?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?= htmlspecialchars($sphere_record['comment']);?>&nbsp;</td>

					<td class="lcebld">
						<table id="area_data_selection_toolbox"><tbody><tr>
							<td>
								<?php if ($notdirty && $notprotected):?>
									<a href="<?=$sphere_scriptname_child;?>?uuid=<?=$sphere_record['uuid'];?>"><img src="<?=$img_path['mod'];?>" title="<?=$gt_record_mod;?>" alt="<?=$gt_record_mod;?>" /></a>
								<?php else:?>
									<?php if ($notprotected):?>
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
		<?php if ($enabletogglemode):?>
			<input type="submit" class="formbtn" name="toggle_selected_rows" id="toggle_selected_rows" value="<?=$gt_selection_toggle;?>"/>
		<?php else:?>
			<input type="submit" class="formbtn" name="enable_selected_rows" id="enable_selected_rows" value="<?=$gt_selection_enable;?>"/>
			<input type="submit" class="formbtn" name="disable_selected_rows" id="disable_selected_rows" value="<?=$gt_selection_disable;?>"/>
		<?php endif;?>
		<input type="submit" class="formbtn" name="delete_selected_rows" id="delete_selected_rows" value="<?=$gt_selection_delete;?>"/>
	</div>
	<div id="remarks">
		<?php html_remark2('note', gtext('Note'), gtext('These option(s) will be added to /etc/rc.conf. This allow you to overwrite options used by various generic startup scripts.'));?>
	</div>
	<?php include("formend.inc");?>
</form></td></tr></tbody></table>
<?php include("fend.inc");?>
