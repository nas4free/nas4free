<?php
/*
	disks_zfs_snapshot_add.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Snapshot'),gtext('Add')];

$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)):
else:
	array_sort_key($a_pool,'name');
endif;

function get_zfs_paths() {
	$result = [];
	mwexec2("zfs list -H -o name -t filesystem,volume 2>&1", $rawdata);
	foreach ($rawdata as $line) {
		$a = preg_split("/\t/", $line);
		$r = [];
		$name = $a[0];
		$r['path'] = $name;
		if (preg_match('/^([^\/\@]+)(\/([^\@]+))?$/', $name, $m)) {
			$r['pool'] = $m[1];
		} else {
			$r['pool'] = 'unknown'; // XXX
		}
		$result[] = $r;
	}
	return $result;
}
$a_path = get_zfs_paths();

if (!isset($uuid) && (!sizeof($a_pool))) {
	$link = sprintf('<a href="%1$s">%2$s</a>', 'disks_zfs_zpool.php', gtext('pools'));
	$helpinghand = gtext('No configured pools.') . ' ' . gtext('Please add new %s first.');
	$helpinghand = sprintf($helpinghand, $link);
	$errormsg = $helpinghand;
}

$cnid = FALSE;
if (isset($uuid) && (FALSE !== $cnid)) {
	// not supported
	$pconfig = [];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['snapshot'] = "";
	$pconfig['path'] = "";
	$pconfig['name'] = "";
	//$pconfig['pool'] = "";
	$pconfig['recursive'] = false;
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: disks_zfs_snapshot.php");
		exit;
	}
	if (!isset($_POST['path']))
		$pconfig['path'] = "";

	// Input validation
	$reqdfields = ['path','name'];
	$reqdfieldsn = [gtext('Path'),gtext('Name')];
	$reqdfieldst = ['string','string'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (preg_match("/(\\s|\\@|\\'|\\\")+/", $_POST['name'])) {
		$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Name"));
	}

	if (empty($input_errors)) {
		$snapshot = [];
		$snapshot['uuid'] = $_POST['uuid'];
		$snapshot['path'] = $_POST['path'];
		$snapshot['name'] = $_POST['name'];
		$snapshot['snapshot'] =	$snapshot['path'].'@'.$snapshot['name'];
		$snapshot['recursive'] = isset($_POST['recursive']) ? true : false;

		//$mode = UPDATENOTIFY_MODE_NEW;
		//updatenotify_set("zfssnapshot", $mode, serialize($snapshot));

		//header("Location: disks_zfs_snapshot.php");
		//exit;

		$ret = zfs_snapshot_configure($snapshot);
		if ($ret['retval'] == 0) {
			header("Location: disks_zfs_snapshot.php");
			exit;
		}
		$errormsg = implode("\n", $ret['output']);
	}
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	document.iform.name.disabled = !enable_change;
}
// -->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext("Pools");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext("Datasets");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext("Volumes");?></span></a></li>
				<li class="tabact"><a href="disks_zfs_snapshot.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Snapshots");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext("Configuration");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabact"><a href="disks_zfs_snapshot.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Snapshot");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot_clone.php"><span><?=gtext("Clone");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot_auto.php"><span><?=gtext("Auto Snapshot");?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot_info.php"><span><?=gtext("Information");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="disks_zfs_snapshot_add.php" method="post" name="iform" id="iform">
				<?php if (!empty($errormsg)) print_error_box($errormsg);?>
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (file_exists($d_sysrebootreqd_path)) print_info_box(get_std_save_message(0));?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_titleline(gtext("Snapshot"));?>
					<?php $a_pathlist = []; foreach ($a_path as $pathv) { $a_pathlist[$pathv['path']] = htmlspecialchars($pathv['path']); }?>
					<?php html_combobox("path", gtext("Path"), $pconfig['path'], $a_pathlist, "", true);?>
					<?php html_inputbox("name", gtext("Name"), $pconfig['name'], "", true, 20);?>
					<?php html_checkbox("recursive", gtext("Recursive"), !empty($pconfig['recursive']) ? true : false, gtext("Creates the recursive snapshot."), "", false);?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Add");?>" onclick="enable_change(true)" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
<?php if (isset($uuid) && (FALSE !== $cnid)):?>
<!-- Disable controls that should not be modified anymore in edit mode. -->
enable_change(false);
<?php endif;?>
//-->
</script>
<?php include 'fend.inc';?>
