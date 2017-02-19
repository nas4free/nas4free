<?php
/*
	disks_manage.php

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

$pgtitle = [gtext('Disks'),gtext('Management'),gtext('HDD Management')];

if ($_POST) {
	$pconfig = $_POST;
	$clean_import = false;
	if (!empty($_POST['clear_import']) || !empty($_POST['clear_import_swraid'])) {
		$clean_import = true;
	}
	if (!empty($_POST['import']) || !empty($_POST['clear_import'])) {
		$retval = disks_import_all_disks($clean_import);
		if ($retval == 0) {
			$savemsg = gtext("No new disk found.");
		} else if ($retval > 0) {
			$savemsg = gtext("All disks are imported.");
		} else {
			$input_errors[] = gtext("Detected an error while importing.");
		}
		if ($retval >= 0) {
			disks_update_mounts();
		}
		//skip redirect
		//header("Location: disks_manage.php");
		//exit;
	}
	if (!empty($_POST['import_swraid']) || !empty($_POST['clear_import_swraid'])) {
		$retval = disks_import_all_swraid_disks($clean_import);
		if ($retval == 0) {
			$savemsg = gtext("No new software raid disk found.");
		} else if ($retval > 0) {
			$savemsg = gtext("All software raid disks are imported.");
		} else {
			$input_errors[] = gtext("Detected an error while importing.");
		}
		if ($retval >= 0) {
			disks_update_mounts();
		}
		//skip redirect
		//header("Location: disks_manage.php");
		//exit;
	}
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("device", "diskmanagement_process_updatenotification");
			config_lock();
			$retval |= rc_update_service("ataidle");
			$retval |= rc_update_service("smartd");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("device");
		}
		header("Location: disks_manage.php");
		exit;
	}
	if (isset($_POST['disks_rescan']) && $_POST['disks_rescan']) {
		$do_action = true;
		$disks_rescan = true;
	}
}

if (!isset($do_action)) {
	$do_action = false;
}

// Get all physical disks including CDROM.
$a_phy_disk = array_merge((array)get_physical_disks_list(), (array)get_cdrom_list());

$a_disk_conf = &array_make_branch($config,'disks','disk');
if(empty($a_disk_conf)):
else:
	array_sort_key($a_disk_conf,'name');
endif;

if (isset($_GET['act']) && $_GET['act'] === "del") {
	updatenotify_set("device", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	header("Location: disks_manage.php");
	exit;
}

function diskmanagement_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['disks']['disk'])) {
				$index = array_search_ex($data, $config['disks']['disk'], "uuid");
				if (false !== $index) {
					unset($config['disks']['disk'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
?>
<?php include 'fbegin.inc';?>
<?php
	// make sure detected disks have same ID in config.
	$verify_errors = disks_verify_all_disks($a_phy_disk);
	if (!empty($verify_errors)) {
		$errormsg .= gtext("The device(s) in config are different to actual device(s). Please remove the device(s) and re-add it or use 'Clear config and Import disks'.");
		$errormsg .= "<br />\n";
	}
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
  		<ul id="tabnav">
				<li class="tabact"><a href="disks_manage.php" title="<?=gtext('Reload page');?>"><span><?=gtext("HDD Management");?></span></a></li>
				<li class="tabinact"><a href="disks_init.php"><span><?=gtext("HDD Format");?></span></a></li>
				<li class="tabinact"><a href="disks_manage_smart.php"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext("iSCSI Initiator");?></span></a></li>
  		</ul>
  	</td>
	</tr>
  <tr>
    <td class="tabcont">
			<form action="disks_manage.php" method="post" onsubmit="spinner()">
				<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
				<?php if (!empty($errormsg)) print_error_box($errormsg);?>
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (updatenotify_exists("device")) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<?php html_titleline2(gtext('HDD Management'), 10);?>
					<tr>
						<td width="5%" class="listhdrlr"><?=gtext("Device"); ?></td>
						<td width="15%" class="listhdrr"><?=gtext("Device Model"); ?></td>
						<td width="7%" class="listhdrr"><?=gtext("Size"); ?></td>
						<td width="15%" class="listhdrr"><?=gtext("Serial Number"); ?></td>
						<td width="5%" class="listhdrr"><?=gtext("Controller"); ?></td>
						<td width="18%" class="listhdrr"><?=gtext("Controller Model"); ?></td>
						<td width="9%" class="listhdrr"><?=gtext("Standby"); ?></td>
						<td width="10%" class="listhdrr"><?=gtext("Filesystem"); ?></td>
						<td width="8%" class="listhdrr"><?=gtext("Status"); ?></td>
						<td width="8%" class="list"></td>
					</tr>
				<?php foreach ($a_disk_conf as $disk):?>
				<?php
				$notificationmode = updatenotify_get_mode("device", $disk['uuid']);
				switch ($notificationmode) {
					case UPDATENOTIFY_MODE_NEW:
						$status = gtext("Initializing");
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						$status = gtext("Modifying");
						break;
					case UPDATENOTIFY_MODE_DIRTY:
						$status = gtext("Deleting");
						break;
					default:
						if ($disk['type'] == 'HAST') {
							$role = $a_phy_disk[$disk['name']]['role'];
							$status = sprintf("%s (%s)", (0 == disks_exists($disk['devicespecialfile'])) ? gtext("ONLINE") : gtext("MISSING"), $role);
							$disk['size'] = $a_phy_disk[$disk['name']]['size'];
						} else {
							if (isset($verify_errors[$disk['name']]) && is_array($verify_errors[$disk['name']])) {
							switch ( $verify_errors[$disk['name']]['error'] ){
								case 1:
									$status = sprintf("%s : %s", gtext('MOVED TO') , $verify_errors[$disk['name']]['new_devicespecialfile']);
									break;
								case 2:
									if(empty($verify_errors[$disk['name']]['old_serial']) === FALSE){
									$old_serial = htmlspecialchars($verify_errors[$disk['name']]['old_serial']);
						} else {
							$old_serial = gtext("n/a");
						};

						if(empty($verify_errors[$disk['name']]['new_serial']) === FALSE){
						$new_serial = htmlspecialchars($verify_errors[$disk['name']]['new_serial']);
						} else {
							$new_serial = gtext("n/a");
						};
									$status = sprintf("%s (%s : '%s' %s '%s')", gtext('CHANGED'), gtext('Device Serial'), $old_serial, gtext('to'), $new_serial);
									break;
								case 4:
									$status = sprintf("%s (%s : '%s' %s '%s')", gtext('CHANGED'), gtext('Controller'), htmlspecialchars($verify_errors[$disk['name']]['config_controller']), gtext('to'), htmlspecialchars($verify_errors[$disk['name']]['new_controller']) );
									break;
								case 8:
									$status = sprintf("%s (%s)", gtext("MISSING"), $disk['devicespecialfile']);
									break;
								default:
									$status = gtext("ONLINE");
									}
							} else {
								$status = gtext("ONLINE");
							}
						}
					break;
				}
				?>
				<tr>
				<?php
					$start_tag = '<td class="listr">';
					$end_tag = "</td>\n";
					$status_start_tag = '<td class="listbg">';
					$status_end_tag = $end_tag;

					if (isset($verify_errors[$disk['name']]) && is_array($verify_errors[$disk['name']])) {
						if ($verify_errors[$disk['name']]['error'] > 0){
							$start_tag = $start_tag . '<span style="color: #ff0000;font-weight:bold;">';
							$end_tag = '</span>&nbsp;' . $end_tag;
							$status_start_tag = $status_start_tag . '<span style="color: #ff0000;font-weight:bold;">';
							$status_end_tag = '</span>&nbsp;'. $end_tag;
						}
					}

					if (isset($verify_errors[$disk['name']]) && is_array($verify_errors[$disk['name']])) {
						if($verify_errors[$disk['name']]['error'] == 8){
							$start_tag = $start_tag . '<del>';
							$end_tag = '</del>'. $end_tag;
						}
					}

					print $start_tag . htmlspecialchars($disk['name']) . $end_tag;
					print $start_tag . htmlspecialchars($disk['model']) . $end_tag;
					print $start_tag . htmlspecialchars($disk['size']) . $end_tag;
					print $start_tag . ((empty($disk['serial']) ) === FALSE ? htmlspecialchars($disk['serial']) : gtext("n/a")) . $end_tag;
					print $start_tag . htmlspecialchars($disk['controller'].$disk['controller_id']) . $end_tag;
					print $start_tag . htmlspecialchars($disk['controller_desc']) . $end_tag;
					print $start_tag . ($disk['harddiskstandby'] ? htmlspecialchars($disk['harddiskstandby']) : gtext("Always On")) . $end_tag;
					print $start_tag . ((!empty($disk['fstype'])) ? htmlspecialchars(get_fstype_shortdesc($disk['fstype'])) : gtext("Unknown or unformatted")) . $end_tag;
					print $status_start_tag . htmlspecialchars($status) . $status_end_tag;
				?>

					<?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
					<td valign="middle" nowrap="nowrap" class="list">
						<a href="disks_manage_edit.php?uuid=<?=$disk['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit disk");?>" border="0" alt="<?=gtext("Edit disk");?>" /></a>&nbsp;
						<a href="disks_manage.php?act=del&amp;uuid=<?=$disk['uuid'];?>" onclick="return confirm('<?=gtext("Do you really want to delete this disk? All elements that still use it will become invalid (e.g. share)!"); ?>')"><img src="images/delete.png" title="<?=gtext("Delete disk"); ?>" border="0" alt="<?=gtext("Delete disk"); ?>" /></a>
					</td>
					<?php else:?>
					<td valign="middle" nowrap="nowrap" class="list">
						<img src="images/delete.png" border="0" alt="" />
					</td>
					<?php endif;?>
				</tr>
				<?php endforeach;?>
				<tr>
					<td class="list" colspan="9"></td>
					<td class="list"> <a href="disks_manage_edit.php"><img src="images/add.png" title="<?=gtext("Add disk"); ?>" border="0" alt="<?=gtext("Add disk"); ?>" /></a></td>
				</tr>
				</table>
				<div id="submit">
					<input name="import" type="submit" class="formbtn" value="<?=gtext("Import Disks");?>" onclick="return confirm('<?=gtext("Do you really want to import?\\nThe existing config may be overwritten.");?>');" />
					<input name="clear_import" type="submit" class="formbtn" value="<?=gtext("Clear Config & Import Disks");?>" onclick="return confirm('<?=gtext("Do you really want to clear and import?\\nThe existing config will be cleared and overwritten.");?>');" />
					<input name="disks_rescan" type="submit" class="formbtn" value="<?=gtext("Rescan Disks");?>" />
					<br />
					<br />
					<input name="import_swraid" type="submit" class="formbtn" value="<?=gtext("Import Software Raid Disks");?>" onclick="return confirm('<?=gtext("Do you really want to import?\\nThe existing config may be overwritten.");?>');" />
					<input name="clear_import_swraid" type="submit" class="formbtn" value="<?=gtext("Clear Config & Import Software Raid Disks");?>" onclick="return confirm('<?=gtext("Do you really want to clear and import?\\nThe existing config will be cleared and overwritten.");?>');" />
				</div>
				<?php
				if ($do_action) {
					echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
					echo('<pre class="cmdoutput">');
					//ob_end_flush();
					if (true == $disks_rescan) {
						disks_rescan();
					}
					echo('</pre>');
					echo('<script type="text/javascript">');
					echo('window.location.href="disks_manage.php"');
					echo('</script>');
				}?>
				<?php include 'formend.inc';?>
		</form>
	</td>
</tr>
</table>
<?php include 'fend.inc';?>