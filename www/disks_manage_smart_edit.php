<?php
/*
	disks_manage_smart_edit.php

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

$a_months = [
	gtext('January'),
	gtext('February'),
	gtext('March'),
	gtext('April'),
	gtext('May'),
	gtext('June'),
	gtext('July'),
	gtext('August'),
	gtext('September'),
	gtext('October'),
	gtext('November'),
	gtext('December')
];
$a_weekdays = [
	gtext('Monday'),
	gtext('Tuesday'),
	gtext('Wednesday'),
	gtext('Thursday'),
	gtext('Friday'),
	gtext('Saturday'),
	gtext('Sunday')
];

$a_selftest = &array_make_branch($config,'smartd','selftest');

// Get list of all configured physical disks.
$a_disk = get_conf_physical_disks_list();

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_selftest, 'uuid')))) {
	$pconfig['uuid'] = $a_selftest[$cnid]['uuid'];
	$pconfig['devicespecialfile'] = $a_selftest[$cnid]['devicespecialfile'];
	$pconfig['type'] = $a_selftest[$cnid]['type'];
	//$pconfig['minute'] = $a_selftest[$cnid]['minute'];
	$pconfig['minute'] = [];
	$pconfig['hour'] = $a_selftest[$cnid]['hour'];
	$pconfig['day'] = $a_selftest[$cnid]['day'];
	$pconfig['month'] = $a_selftest[$cnid]['month'];
	$pconfig['weekday'] = $a_selftest[$cnid]['weekday'];
	//$pconfig['all_mins'] = $a_selftest[$cnid]['all_mins'];
	$pconfig['all_mins'] = 1;
	$pconfig['all_hours'] = $a_selftest[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_selftest[$cnid]['all_days'];
	$pconfig['all_months'] = $a_selftest[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_selftest[$cnid]['all_weekdays'];
	$pconfig['desc'] = $a_selftest[$cnid]['desc'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['type'] = "S";
	$pconfig['desc'] = "";
	$pconfig['all_mins'] = 1;
	$pconfig['all_hours'] = 1;
	$pconfig['all_days'] = 1;
	$pconfig['all_months'] = 1;
	$pconfig['all_weekdays'] = 1;
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: disks_manage_smart.php");
		exit;
	}

	// insert dummy minutes
	$pconfig['all_mins'] = $_POST['all_mins'] = 1;

	$reqdfields = ['disk','type'];
	$reqdfieldsn = [gtext('Disk'),gtext('Type')];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validate_synctime($_POST, $input_errors);

	if (empty($input_errors)) {
		$selftest = [];
		$selftest['uuid'] = $_POST['uuid'];
		$selftest['devicespecialfile'] = $_POST['disk'];
		$selftest['type'] = $_POST['type'];
		$selftest['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$selftest['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$selftest['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$selftest['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$selftest['all_hours'] = $_POST['all_hours'];
		$selftest['all_days'] = $_POST['all_days'];
		$selftest['all_months'] = $_POST['all_months'];
		$selftest['all_weekdays'] = $_POST['all_weekdays'];
		$selftest['desc'] = $_POST['desc'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_selftest[$cnid] = $selftest;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_selftest[] = $selftest;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("smartssd", $mode, $selftest['uuid']);
		write_config();

		header("Location: disks_manage_smart.php");
		exit;
	}
}

$pgtitle = [gtext('Disks'),gtext('Management'),gtext('S.M.A.R.T.'),gtext('Scheduled Self-Test'), isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}

function enable_change(enable_change) {
	document.iform.disk.disabled = !enable_change;
}
// -->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td class="tabnavtbl">
	<ul id="tabnav">
		<li class="tabinact"><a href="disks_manage.php"><span><?=gtext("HDD Management");?></span></a></li>
		<li class="tabinact"><a href="disks_init.php"><span><?=gtext("HDD Format");?></span></a></li>
		<li class="tabact"><a href="disks_manage_smart.php" title="<?=gtext('Reload page');?>"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
		<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext("iSCSI Initiator");?></span></a></li>
	</ul>
	</td>
	</tr>
	<tr>
	<td class="tabcont">
		<form action="disks_manage_smart_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
		<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php html_titleline(gtext("Scheduled Self-Test Settings"));?>
		<tr>
			<td valign="top" class="vncellreq"><?=gtext("Disk"); ?></td>
			<td class="vtable">
			<select name="disk" class="formfld" id="disk">
			<option value=""><?=gtext("Must choose one");?></option>
			<?php foreach ($a_disk as $diskv):?>
				<?php if (0 == strcmp($diskv['size'], "NA")) continue;?>
				<?php if (1 == disks_exists($diskv['devicespecialfile'])) continue;?>
				<?php if (!isset($diskv['smart'])) continue;?>
				<option value="<?=$diskv['devicespecialfile'];?>" <?php if ($diskv['devicespecialfile'] === $pconfig['devicespecialfile']) echo "selected=\"selected\"";?>>
				<?php $diskinfo = disks_get_diskinfo($diskv['devicespecialfile']); echo htmlspecialchars("{$diskv['name']}: {$diskinfo['mediasize_mbytes']}MB ({$diskv['desc']})");?>
				</option>
				<?php endforeach;?>
			</select><br />
			<span class="vexpl"><?=gtext("Select a disk that is enabled for S.M.A.R.T. monitoring.");?></span>
			</td>
			</tr>
			<tr>
			<td width="22%" valign="top" class="vncellreq"><?=gtext("Type");?></td>
			<td width="78%" class="vtable">
			<select name="type" class="formfld" id="type">
			<?php $types = [gtext('Short Self-Test'),gtext('Long Self-Test'),gtext('Conveyance Self-Test'),gtext('Offline Immediate Test')]; $vals = ['S','L','C','O'];?>
				<?php $j = 0; for ($j = 0; $j < count($vals); $j++):?>
				<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['type']) echo "selected=\"selected\"";?>>
				<?=htmlspecialchars($types[$j]);?>
			</option>
				<?php endfor;?>
			</select>
			</td>
			</tr>
			<tr>
			<td width="22%" valign="top" class="vncellreq"><?=gtext("Time");?></td>
			<td width="78%" class="vtable">
				<table width="100%" border="0" cellpadding="4" cellspacing="0">
				<tr>
				<td class="listhdrlr"><?=gtext("Hours");?></td>
				<td class="listhdrr"><?=gtext("Days");?></td>
				<td class="listhdrr"><?=gtext("Months");?></td>
				<td class="listhdrr"><?=gtext("Week days");?></td>
				</tr>
				<tr>
				<td class="listlr" valign="top">
				<input type="radio" name="all_hours" id="all_hours1" value="1" <?php if (1 == $pconfig['all_hours']) echo "checked=\"checked\"";?> />
				<?=gtext("All");?><br />
				<input type="radio" name="all_hours" id="all_hours2" value="0" <?php if (1 != $pconfig['all_hours']) echo "checked=\"checked\"";?> />
				<?=gtext("Selected");?> ..<br />
				<table>
				<tr>
				<td valign="top">
				<select multiple="multiple" size="12" name="hour[]" id="hours1" onchange="set_selected('all_hours')">
				<?php for ($i = 0; $i <= 11; $i++):?>
				<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
				<?php endfor;?>
				</select>
				</td>
				<td valign="top">
				<select multiple="multiple" size="12" name="hour[]" id="hours2" onchange="set_selected('all_hours')">
					<?php for ($i = 12; $i <= 23; $i++):?>
					<option value="<?=$i;?>" <?php if (is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
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
				<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
				<?php endfor;?>
				</select>
			</td>
				<td valign="top">
				<select multiple="multiple" size="12" name="day[]" id="days2" onchange="set_selected('all_days')">
				<?php for ($i = 13; $i <= 24; $i++):?>
				<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
				<?php endfor;?>
				</select>
			</td>
				<td valign="top">
				<select multiple="multiple" size="7" name="day[]" id="days3" onchange="set_selected('all_days')">
				<?php for ($i = 25; $i <= 31; $i++):?>
				<option value="<?=$i;?>" <?php if (is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
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
				<?php $i = 1; foreach ($a_weekdays as $day):?>
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
				<input name="desc" type="text" class="formfld" id="desc" size="40" value="<?=htmlspecialchars($pconfig['desc']);?>" />
				</td>
			</tr>
			</table>
				<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" onclick="enable_change(true)" />
				<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
				<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
	</td>
</tr>
</table>
<?php if (isset($uuid) && (FALSE !== $cnid)):?>
<script type="text/javascript">
<!-- Disable controls that should not be modified anymore in edit mode. -->
enable_change(false);
</script>
<?php endif;?>
<?php include 'fend.inc';?>
