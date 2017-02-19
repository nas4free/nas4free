<?php
/*
	services_rsyncd_local_edit.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

/* Global arrays. */
$a_months = explode(" ",gtext("January February March April May June July August September October November December"));
$a_weekdays = explode(" ",gtext("Sunday Monday Tuesday Wednesday Thursday Friday Saturday"));

$a_rsynclocal = &array_make_branch($config,'rsync','rsynclocal');

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_rsynclocal, "uuid")))) {
	$pconfig['enable'] = isset($a_rsynclocal[$cnid]['enable']);
	$pconfig['uuid'] = $a_rsynclocal[$cnid]['uuid'];
	$pconfig['source'] = $a_rsynclocal[$cnid]['source'];
	$pconfig['destination'] = $a_rsynclocal[$cnid]['destination'];
	$pconfig['minute'] = $a_rsynclocal[$cnid]['minute'];
	$pconfig['hour'] = $a_rsynclocal[$cnid]['hour'];
	$pconfig['day'] = $a_rsynclocal[$cnid]['day'];
	$pconfig['month'] = $a_rsynclocal[$cnid]['month'];
	$pconfig['weekday'] = $a_rsynclocal[$cnid]['weekday'];
	//$pconfig['sharetosync'] = $a_rsynclocal[$cnid]['sharetosync'];
	$pconfig['all_mins'] = $a_rsynclocal[$cnid]['all_mins'];
	$pconfig['all_hours'] = $a_rsynclocal[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_rsynclocal[$cnid]['all_days'];
	$pconfig['all_months'] = $a_rsynclocal[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_rsynclocal[$cnid]['all_weekdays'];
	$pconfig['description'] = $a_rsynclocal[$cnid]['description'];
	$pconfig['who'] = $a_rsynclocal[$cnid]['who'];
	$pconfig['recursive'] = isset($a_rsynclocal[$cnid]['options']['recursive']);
	$pconfig['times'] = isset($a_rsynclocal[$cnid]['options']['times']);
	$pconfig['compress'] = isset($a_rsynclocal[$cnid]['options']['compress']);
	$pconfig['archive'] = isset($a_rsynclocal[$cnid]['options']['archive']);
	$pconfig['delete'] = isset($a_rsynclocal[$cnid]['options']['delete']);
	$pconfig['delete_algorithm'] = $a_rsynclocal[$cnid]['options']['delete_algorithm'];
	$pconfig['quiet'] = isset($a_rsynclocal[$cnid]['options']['quiet']);
	$pconfig['perms'] = isset($a_rsynclocal[$cnid]['options']['perms']);
	$pconfig['xattrs'] = isset($a_rsynclocal[$cnid]['options']['xattrs']);
	$pconfig['extraoptions'] = $a_rsynclocal[$cnid]['options']['extraoptions'];
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['source'] = "";
	$pconfig['destination'] = "";
	$pconfig['minute'] = [];
	$pconfig['hour'] = [];
	$pconfig['day'] = [];
	$pconfig['month'] = [];
	$pconfig['weekday'] = [];
	//$pconfig['sharetosync'] = "";
	$pconfig['all_mins'] = 0;
	$pconfig['all_hours'] = 0;
	$pconfig['all_days'] = 0;
	$pconfig['all_months'] = 0;
	$pconfig['all_weekdays'] = 0;
	$pconfig['description'] = "";
	$pconfig['who'] = "root";
	$pconfig['recursive'] = false;
	$pconfig['times'] = false;
	$pconfig['compress'] = false;
	$pconfig['archive'] = true;
	$pconfig['delete'] = false;
	$pconfig['delete_algorithm'] = "default";
	$pconfig['quiet'] = false;
	$pconfig['perms'] = false;
	$pconfig['xattrs'] = false;
	$pconfig['extraoptions'] = "";
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: services_rsyncd_local.php");
		exit;
	}

	// Input validation
	$reqdfields = ['source','destination','who'];
	$reqdfieldsn = [gtext('Source Share'),gtext('Destination Share'),gtext('Who')];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (!empty($_POST['Submit']) && gtext("Execute now") !== $_POST['Submit']) {
		// Validate synchronization time
		do_input_validate_synctime($_POST, $input_errors);
	}

	if (empty($input_errors)) {
		$rsynclocal = [];
		$rsynclocal['enable'] = isset($_POST['enable']) ? true : false;
		$rsynclocal['uuid'] = $_POST['uuid'];
		$rsynclocal['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$rsynclocal['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$rsynclocal['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$rsynclocal['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$rsynclocal['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$rsynclocal['source'] = $_POST['source'];
		$rsynclocal['destination'] = $_POST['destination'];
		$rsynclocal['all_mins'] = $_POST['all_mins'];
		$rsynclocal['all_hours'] = $_POST['all_hours'];
		$rsynclocal['all_days'] = $_POST['all_days'];
		$rsynclocal['all_months'] = $_POST['all_months'];
		$rsynclocal['all_weekdays'] = $_POST['all_weekdays'];
		$rsynclocal['description'] = $_POST['description'];
		$rsynclocal['who'] = $_POST['who'];
		$rsynclocal['options']['recursive'] = isset($_POST['recursive']) ? true : false;
		$rsynclocal['options']['times'] = isset($_POST['times']) ? true : false;
		$rsynclocal['options']['compress'] = isset($_POST['compress']) ? true : false;
		$rsynclocal['options']['archive'] = isset($_POST['archive']) ? true : false;
		$rsynclocal['options']['delete'] = isset($_POST['delete']) ? true : false;
		$rsynclocal['options']['delete_algorithm'] = $_POST['delete_algorithm'];
		$rsynclocal['options']['quiet'] = isset($_POST['quiet']) ? true : false;
		$rsynclocal['options']['perms'] = isset($_POST['perms']) ? true : false;
		$rsynclocal['options']['xattrs'] = isset($_POST['xattrs']) ? true : false;
		$rsynclocal['options']['extraoptions'] = $_POST['extraoptions'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_rsynclocal[$cnid] = $rsynclocal;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_rsynclocal[] = $rsynclocal;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("rsynclocal", $mode, $rsynclocal['uuid']);
		write_config();

		if (!empty($_POST['Submit']) && stristr($_POST['Submit'], gtext("Execute now"))) {
			$retval = 0;

			// Update scripts and execute it.
			config_lock();
			$retval |= rc_exec_service("rsync_local");
			$retval |= rc_update_service("cron");
			config_unlock();
			if ($retval == 0) {
				updatenotify_clear("rsynclocal", $rsynclocal['uuid']);
			}

			$retval |= rc_exec_script_async("su -m {$rsynclocal['who']} -c '/bin/sh /var/run/rsync_local_{$rsynclocal['uuid']}.sh'");

			$savemsg = get_std_save_message($retval);
		} else {
			header("Location: services_rsyncd_local.php");
			exit;
		}
	}
}
$pgtitle = [gtext('Services'),gtext('Rsync'),gtext('Local'),isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}

function delete_change() {
	switch(document.getElementById('delete').checked) {
		case false:
			showElementById('delete_algorithm_tr','hide');
			break;

		case true:
			showElementById('delete_algorithm_tr','show');
			break;
	}
}
// -->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_rsyncd.php"><span><?=gtext("Server");?></span></a></li>
				<li class="tabinact"><a href="services_rsyncd_client.php"><span><?=gtext("Client");?></span></a></li>
				<li class="tabact"><a href="services_rsyncd_local.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Local");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="services_rsyncd_local_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Rsync Job"), !empty($pconfig['enable']) ? true : false, gtext("Enable"));?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Source Share");?></td>
						<td width="78%" class="vtable">
							<input name="source" type="text" class="formfld" id="source" size="60" value="<?=htmlspecialchars($pconfig['source']);?>" />
							<input name="browse" type="button" class="formbtn" id="Browse" onclick='ifield = form.source; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield; window.slash_source = 1;' value="..." /><br />
							<span class="vexpl"><?=gtext("Source directory to be synchronized.");?></span>
					  </td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Destination Share");?></td>
						<td width="78%" class="vtable">
							<input name="destination" type="text" class="formfld" id="destination" size="60" value="<?=htmlspecialchars($pconfig['destination']);?>" />
							<input name="browse2" type="button" class="formbtn" id="Browse2" onclick='ifield2 = form.destination; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield2.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield2; window.ifield = ifield2; window.slash_destination = 1;' value="..." /><br />
							<span class="vexpl"><?=gtext("Target directory.");?></span>
					  </td>
					</tr>
					<?php $a_user = []; foreach (system_get_user_list() as $userk => $userv) { $a_user[$userk] = htmlspecialchars($userk); }?>
					<?php html_combobox("who", gtext("Who"), $pconfig['who'], $a_user, "", true);?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Synchronization Time");?></td>
						<td width="78%" class="vtable">
							<table width="100%" border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td class="listhdrlr"><?=gtext("Minutes");?></td>
									<td class="listhdrr"><?=gtext("Hours");?></td>
									<td class="listhdrr"><?=gtext("Days");?></td>
									<td class="listhdrr"><?=gtext("Months");?></td>
									<td class="listhdrr"><?=gtext("Week days");?></td>
								</tr>
								<tr>
									<td class="listlr">
										<input type="radio" name="all_mins" id="all_mins1" value="1" <?php if (1 == $pconfig['all_mins']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_mins" id="all_mins2" value="0" <?php if (1 != $pconfig['all_mins']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes1" onchange="set_selected('all_mins')">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes2" onchange="set_selected('all_mins')">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes3" onchange="set_selected('all_mins')">
														<?php for ($i = 24; $i <= 35; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes4" onchange="set_selected('all_mins')">
														<?php for ($i = 36; $i <= 47; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes5" onchange="set_selected('all_mins')">
														<?php for ($i = 48; $i <= 59; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
										<br />
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_hours" id="all_hours1" value="1" <?php if (1 == $pconfig['all_hours']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_hours" id="all_hours2" value="0" <?php if (1 != $pconfig['all_hours']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="hour[]" id="hours1" onchange="set_selected('all_hours')">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['hour']) && is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="hour[]" id="hours2" onchange="set_selected('all_hours')">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['hour']) && is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_days" id="all_days1" value="1" <?php if (1 == $pconfig['all_days']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_days" id="all_days2" value="0" <?php if (1 != $pconfig['all_days']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="day[]" id="days1" onchange="set_selected('all_days')">
														<?php for ($i = 1; $i <= 12; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="day[]" id="days2" onchange="set_selected('all_days')">
														<?php for ($i = 13; $i <= 24; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="7" name="day[]" id="days3" onchange="set_selected('all_days')">
														<?php for ($i = 25; $i <= 31; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_months" id="all_months1" value="1" <?php if (1 == $pconfig['all_months']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_months" id="all_months2" value="0" <?php if (1 != $pconfig['all_months']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="month[]" id="months" onchange="set_selected('all_months')">
														<?php $i = 1; foreach ($a_months as $month):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['month']) && in_array("$i", $pconfig['month'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($month);?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_weekdays" id="all_weekdays1" value="1" <?php if (1 == $pconfig['all_weekdays']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_weekdays" id="all_weekdays2" value="0" <?php if (1 != $pconfig['all_weekdays']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="7" name="weekday[]" id="weekdays" onchange="set_selected('all_weekdays')">
														<?php $i = 0; foreach ($a_weekdays as $day):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['weekday']) && in_array("$i", $pconfig['weekday'])) echo "selected=\"selected\"";?>><?=$day;?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<span class="vexpl"><?=gtext("Note: Ctrl-click (or command-click on the Mac) to select and de-select minutes, hours, days and months.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Description");?></td>
						<td width="78%" class="vtable">
							<input name="description" type="text" class="formfld" id="description" size="40" value="<?=htmlspecialchars($pconfig['description']);?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2" class="list" height="12"></td>
					</tr>
					<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gtext("Advanced Options");?></td>
					</tr>
					<?php
					html_checkbox("recursive", gtext("Recursive"), !empty($pconfig['recursive']) ? true : false, gtext("Recurse into directories."), "", false);
					html_checkbox("times", gtext("Times"), !empty($pconfig['times']) ? true : false, gtext("Preserve modification times."), "", false);
					html_checkbox("compress", gtext("Compress"), !empty($pconfig['compress']) ? true : false, gtext("Compress file data during the transfer."), "", false);
					html_checkbox("archive", gtext("Archive"), !empty($pconfig['archive']) ? true : false, gtext("Archive mode."), "", false);
					html_checkbox("delete", gtext("Delete"), !empty($pconfig['delete']) ? true : false, gtext("Delete files on the receiving side that don't exist on sender."), "", false, "delete_change()");
					$helpinghand = '</span><div id="enumeration"><ul>'
						. '<li>' . gtext("Default - Rsync will choose the 'during' algorithm when talking to rsync 3.0.0 or newer and the 'before' algorithm when talking to an older rsync.") . '</li>'
						. '<li>' . gtext('Before - File-deletions will be performed before the transfer starts.') . '</li>'
						. '<li>' . gtext('During - File-deletions will be done incrementally as the transfer happens.') . '</li>'
						. '<li>' . gtext('Delay - File-deletions will be computed during the transfer and will be done after the transfer.') . ' </li>'
						. '<li>' . gtext('After - File-deletions will be done after the transfer has completed.') . '</li>'
						. '</ul></div><span>';
					$l_delalgol = [
						'default' => gtext('Default'),
						'before' => gtext('Before'),
						'during' => gtext('During'),
						'delay' => gtext('Delay'),
						'after' => gtext('After')
					];
					html_combobox("delete_algorithm", gtext("Delete algorithm"), $pconfig['delete_algorithm'], $l_delalgol, $helpinghand, false);
					?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Quiet");?></td>
						<td width="78%" class="vtable">
							<input name="quiet" id="quiet" type="checkbox" value="yes" <?php if (!empty($pconfig['quiet'])) echo "checked=\"checked\""; ?> /> <?=gtext("Suppress non-error messages."); ?><br />
						</td>
					</tr>
					<?php
					html_checkbox("perms", gtext("Preserve Permissions"), !empty($pconfig['perms']) ? true : false, gtext("This option causes the receiving rsync to set the destination permissions to be the same as the source permissions."), "", false);
					html_checkbox("xattrs", gtext("Preserve Extended attributes"), !empty($pconfig['xattrs']) ? true : false, gtext("This option causes rsync to update the remote extended attributes to be the same as the local ones."), "", false);
					$helpinghand = '<a href="' . 'http://rsync.samba.org/ftp/rsync/rsync.html' . '" target="_blank">'
						. gtext('Please check the documentation')
						. '</a>.';
					html_inputbox("extraoptions", gtext("Extra Options"), !empty($pconfig['extraoptions']) ? $pconfig['extraoptions'] : "", gtext("Extra options to rsync (usually empty).") . " " . $helpinghand, false, 40);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
					<?php if (isset($uuid) && (FALSE !== $cnid)):?>
					<input name="Submit" id="execnow" type="submit" class="formbtn" value="<?=gtext("Execute now");?>" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<?php endif;?>
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
delete_change();
//-->
</script>
<?php include 'fend.inc';?>
