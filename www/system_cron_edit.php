<?php
/*
	system_cron_edit.php

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

$a_cronjob = &array_make_branch($config,'cron','job');
if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_cronjob, "uuid")))) {
	$pconfig['enable'] = isset($a_cronjob[$cnid]['enable']);
	$pconfig['uuid'] = $a_cronjob[$cnid]['uuid'];
	$pconfig['desc'] = $a_cronjob[$cnid]['desc'];
	$pconfig['minute'] = $a_cronjob[$cnid]['minute'];
	$pconfig['hour'] = $a_cronjob[$cnid]['hour'];
	$pconfig['day'] = $a_cronjob[$cnid]['day'];
	$pconfig['month'] = $a_cronjob[$cnid]['month'];
	$pconfig['weekday'] = $a_cronjob[$cnid]['weekday'];
	$pconfig['all_mins'] = $a_cronjob[$cnid]['all_mins'];
	$pconfig['all_hours'] = $a_cronjob[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_cronjob[$cnid]['all_days'];
	$pconfig['all_months'] = $a_cronjob[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_cronjob[$cnid]['all_weekdays'];
	$pconfig['who'] = $a_cronjob[$cnid]['who'];
	$pconfig['command'] = $a_cronjob[$cnid]['command'];
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['desc'] = "";
	$pconfig['all_mins'] = 1;
	$pconfig['all_hours'] = 1;
	$pconfig['all_days'] = 1;
	$pconfig['all_months'] = 1;
	$pconfig['all_weekdays'] = 1;
	$pconfig['who'] = "root";
	$pconfig['command'] = "";
}

$a_months = explode(" ",gtext("January February March April May June July August September October November December"));
$a_weekdays = explode(" ",gtext("Sunday Monday Tuesday Wednesday Thursday Friday Saturday"));

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: system_cron.php");
		exit;
	}

	// Input validation.
	$reqdfields = ['desc','who','command'];
	$reqdfieldsn = [gtext('Description'),gtext('Who'),gtext('Command')];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (gtext("Run now") !== $_POST['Submit']) {
		// Validate synchronization time
		do_input_validate_synctime($_POST, $input_errors);
	}

	if (empty($input_errors)) {
		$cronjob = [];
		$cronjob['enable'] = isset($_POST['enable']) ? true : false;
		$cronjob['uuid'] = $_POST['uuid'];
		$cronjob['desc'] = $_POST['desc'];
		$cronjob['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$cronjob['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$cronjob['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$cronjob['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$cronjob['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$cronjob['all_mins'] = $_POST['all_mins'];
		$cronjob['all_hours'] = $_POST['all_hours'];
		$cronjob['all_days'] = $_POST['all_days'];
		$cronjob['all_months'] = $_POST['all_months'];
		$cronjob['all_weekdays'] = $_POST['all_weekdays'];
		$cronjob['who'] = $_POST['who'];
		$cronjob['command'] = $_POST['command'];

		if (stristr($_POST['Submit'], gtext("Run now"))) {
			if ($_POST['who'] != "root") {
				mwexec2(escapeshellcmd("sudo -u {$_POST['who']} {$_POST['command']}"), $output, $retval);
			} else {
				mwexec2(escapeshellcmd($_POST['command']), $output, $retval);
			}
			if (0 == $retval) {
				$execmsg = gtext("The cron job has been executed successfully.");
				write_log("The cron job '{$_POST['command']}' has been executed successfully.");
			} else {
				$execfailmsg = gtext("Failed to execute cron job.");
				write_log("Failed to execute cron job '{$_POST['command']}'.");
			}
		} else {
			if (isset($uuid) && (FALSE !== $cnid)) {
				$a_cronjob[$cnid] = $cronjob;
				$mode = UPDATENOTIFY_MODE_MODIFIED;
			} else {
				$a_cronjob[] = $cronjob;
				$mode = UPDATENOTIFY_MODE_NEW;
			}

			updatenotify_set("cronjob", $mode, $cronjob['uuid']);
			write_config();

			header("Location: system_cron.php");
			exit;
		}
	}
}
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Cron'),isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="system_advanced.php"><span><?=gtext("Advanced");?></span></a></li>
			<li class="tabinact"><a href="system_email.php"><span><?=gtext("Email");?></span></a></li>
			<li class="tabinact"><a href="system_monitoring.php"><span><?=gtext("Monitoring");?></span></a></li>
			<li class="tabinact"><a href="system_email_reports.php"><span><?=gtext("Email Reports");?></span></a></li>
			<li class="tabinact"><a href="system_swap.php"><span><?=gtext("Swap");?></span></a></li>
			<li class="tabinact"><a href="system_rc.php"><span><?=gtext("Command Scripts");?></span></a></li>
			<li class="tabact"><a href="system_cron.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Cron");?></span></a></li>
			<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext("loader.conf");?></span></a></li>
			<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext("rc.conf");?></span></a></li>
			<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext("sysctl.conf");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="system_cron_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
			<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
			<?php if (!empty($execmsg)) print_info_box($execmsg);?>
			<?php if (!empty($execfailmsg)) print_error_box($execfailmsg);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline_checkbox("enable", gtext("Cron job"), $pconfig['enable'] ? true : false, gtext("Enable"));?>
			<?php html_inputbox("command", gtext("Command"), $pconfig['command'], gtext("Specifies the command to be run."), true, 60);?>
			<?php $a_user = []; foreach (system_get_user_list() as $userk => $userv) { $a_user[$userk] = htmlspecialchars($userk); }?>
			<?php html_combobox("who", gtext("Who"), $pconfig['who'], $a_user, "", true);?>
			<?php html_inputbox("desc", gtext("Description"), $pconfig['desc'], gtext("You may enter a description here for your reference."), true, 40);?>
		<tr>
			<td width="22%" valign="top" class="vncellreq"><?=gtext("Schedule time");?></td>
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
			<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="minute[]" id="minutes2" onchange="set_selected('all_mins')">
			<?php for ($i = 12; $i <= 23; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="minute[]" id="minutes3" onchange="set_selected('all_mins')">
			<?php for ($i = 24; $i <= 35; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="minute[]" id="minutes4" onchange="set_selected('all_mins')">
			<?php for ($i = 36; $i <= 47; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="minute[]" id="minutes5" onchange="set_selected('all_mins')">
			<?php for ($i = 48; $i <= 59; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
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
			<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="hour[]" id="hours2" onchange="set_selected('all_hours')">
			<?php for ($i = 12; $i <= 23; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
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
			<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="12" name="day[]" id="days2" onchange="set_selected('all_days')">
			<?php for ($i = 13; $i <= 24; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
			<?php endfor;?>
			</select>
		</td>
			<td valign="top">
			<select multiple="multiple" size="7" name="day[]" id="days3" onchange="set_selected('all_days')">
			<?php for ($i = 25; $i <= 31; $i++):?>
			<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected";?>><?=htmlspecialchars($i);?></option>
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
			<option value="<?=$i;?>" <?php if (isset($pconfig['month']) && in_array("$i", $pconfig['month'])) echo "selected";?>><?=htmlspecialchars($month);?></option>
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
			<option value="<?=$i;?>" <?php if (isset($pconfig['weekday']) && in_array("$i", $pconfig['weekday'])) echo "selected";?>><?=$day;?></option>
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
			</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
					<input name="Submit" id="runnow" type="submit" class="formbtn" value="<?=gtext("Run now");?>" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
<?php include 'formend.inc';?>
</form>
</td>
</tr>
</table>
<?php include 'fend.inc';?>
