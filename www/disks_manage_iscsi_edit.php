<?php
/*
	disks_manage_iscsi_edit.php

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

$pgtitle = [gtext('Disks'),gtext('Management'),gtext('iSCSI Initiator'), isset($uuid) ? gtext('Edit') : gtext('Add')];

$a_iscsiinit = &array_make_branch($config,'iscsiinit','vdisk');
if(empty($a_iscsiinit)):
else:
	array_sort_key($$a_iscsiinit,'name');
endif;

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_iscsiinit, "uuid")))) {
	$pconfig['uuid'] = $a_iscsiinit[$cnid]['uuid'];
	$pconfig['name'] = $a_iscsiinit[$cnid]['name'];
	$pconfig['targetname'] = $a_iscsiinit[$cnid]['targetname'];
	$pconfig['targetaddress'] = $a_iscsiinit[$cnid]['targetaddress'];
	$pconfig['initiatorname'] = $a_iscsiinit[$cnid]['initiatorname'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = "";
	$pconfig['targetname'] = "";
	$pconfig['targetaddress'] = "";
	$pconfig['initiatorname'] = "iqn.2012-03.org.nas4free:nas4free";
}
if (isset($config['iscsitarget']['nodebase'])
    && !empty($config['iscsitarget']['nodebase'])) {
	$ex_nodebase = $config['iscsitarget']['nodebase'];
	$ex_disk = "disk0";
} else {
	$ex_nodebase = "iqn.2007-09.jp.ne.peach.istgt";
	$ex_disk = "disk0";
}
$ex_iscsitarget = $ex_nodebase.":".$ex_disk;

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	unset($do_crypt);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: disks_manage_iscsi.php");
		exit;
	}

	// Check for duplicates.
	foreach ($a_iscsiinit as $iscsiinit) {
		if (isset($uuid) && (FALSE !== $cnid) && ($iscsiinit['uuid'] === $uuid)) 
			continue;
		if (($iscsiinit['targetname'] === $_POST['targetname']) && ($iscsiinit['targetaddress'] === $_POST['targetaddress'])) {
			$input_errors[] = gtext("This couple targetname/targetaddress already exists in the disk list.");
			break;
		}
		if ($iscsiinit['name'] == $_POST['name']) {
			$input_errors[] = gtext("This name already exists in the disk list.");
			break;
		}
	}

	// Input validation
	$reqdfields = ['name','targetname','targetaddress','initiatorname'];
	$reqdfieldsn = [gtext('Name'),gtext('Target Name'),gtext('Target Address'),gtext('Initiator Name')];
	$reqdfieldst = ['alias','string','string','string'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (empty($input_errors)) {
		$iscsiinit = [];
		$iscsiinit['uuid'] = $_POST['uuid'];
		$iscsiinit['name'] = $_POST['name'];
		$iscsiinit['targetname'] = $_POST['targetname'];
		$iscsiinit['targetaddress'] = $_POST['targetaddress'];
		$iscsiinit['initiatorname'] = $_POST['initiatorname'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_iscsiinit[$cnid] = $iscsiinit;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_iscsiinit[] = $iscsiinit;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("iscsiinitiator", $mode, $iscsiinit['uuid']);
		write_config();

		header("Location: disks_manage_iscsi.php");
		exit;
	}
}
?>
<?php include 'fbegin.inc';?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td class="tabnavtbl">
	<ul id="tabnav">
      		<li class="tabinact"><a href="disks_manage.php"><span><?=gtext("HDD Management");?></span></a></li>
		<li class="tabinact"><a href="disks_init.php"><span><?=gtext("HDD Format");?></span></a></li>
      		<li class="tabinact"><a href="disks_manage_smart.php"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
		<li class="tabact"><a href="disks_manage_iscsi.php" title="<?=gtext('Reload page');?>"><span><?=gtext("iSCSI Initiator");?></span></a></li>
	</ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
		<form action="disks_manage_iscsi_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
		<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php html_titleline(gtext("iSCSI Initiator Settings"));?>
		<?php html_inputbox("name", gtext("Name"), $pconfig['name'], gtext("This is for information only. (not used during iSCSI negotiation)."), true, 20);?>
		<?php html_inputbox("initiatorname", gtext("Initiator Name"), $pconfig['initiatorname'], gtext("This name is for example: iqn.2005-01.il.ac.huji.cs:somebody."), true, 60);?>			
		<?php html_inputbox("targetname", gtext("Target Name"), $pconfig['targetname'], sprintf(gtext("This name is for example: %s."), $ex_iscsitarget), true, 60);?>
		<?php html_inputbox("targetaddress", gtext("Target Address"), $pconfig['targetaddress'], gtext("Enter the IP address or DNS name of the iSCSI target."), true, 20);?>
		</table>
		<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
		</div>
		<?php include 'formend.inc';?>
		</form>
	</td>
</tr>
</table>
<?php include 'fend.inc';?>
