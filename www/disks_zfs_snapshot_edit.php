<?php
/*
	disks_zfs_snapshot_edit.php

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
require("zfs.inc");

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = array(gtext("Disks"), gtext("ZFS"), gtext("Snapshots"), gtext("Snapshot"), gtext("Edit"));

if (!isset($config['zfs']['pools']['pool']) || !is_array($config['zfs']['pools']['pool']))
	$config['zfs']['pools']['pool'] = array();

array_sort_key($config['zfs']['pools']['pool'], "name");
$a_pool = &$config['zfs']['pools']['pool'];

function get_zfs_paths() {
	$result = array();
	mwexec2("zfs list -H -o name -t filesystem,volume 2>&1", $rawdata);
	foreach ($rawdata as $line) {
		$a = preg_split("/\t/", $line);
		$r = array();
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

if (isset($_GET['snapshot']))
	$snapshot = $_GET['snapshot'];
if (isset($_POST['snapshot']))
	$snapshot = $_POST['snapshot'];
$cnid = FALSE;
if (isset($snapshot) && !empty($snapshot)) {
	$pconfig['uuid'] = uuid();
	$pconfig['snapshot'] = $snapshot;
	if (preg_match('/^([^\/\@]+)(\/([^\@]+))?\@(.*)$/', $pconfig['snapshot'], $m)) {
		$pconfig['pool'] = $m[1];
		$pconfig['path'] = $m[1].$m[2];
		$pconfig['name'] = $m[4];
	} else {
		$pconfig['pool'] = "";
		$pconfig['path'] = "";
		$pconfig['name'] = "";
	}
	$pconfig['newpath'] = "";
	$pconfig['newname'] = "";
	$pconfig['recursive'] = false;
	$pconfig['action'] = "clone";
} else {
	// not supported
	$pconfig = array();
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: disks_zfs_snapshot.php");
		exit;
	}

	if (isset($_POST['action'])) {
		$action = $_POST['action'];
	}
	if (empty($action)) {
		$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Action"));
	} else {
		switch($action) {
			case 'clone':
				// Input validation
				$reqdfields = explode(" ", "newpath");
				$reqdfieldsn = array(gtext("Path"));
				$reqdfieldst = explode(" ", "string");

				do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
				do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

				if (preg_match("/(\\s|\\@|\\'|\\\")+/", $_POST['newpath'])) {
					$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Path"));
				}

				if (empty($input_errors)) {
					$snapshot = array();
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['pool'] = $_POST['pool'];
					$snapshot['path'] = $_POST['newpath'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$ret = zfs_snapshot_clone($snapshot);
					if ($ret['retval'] == 0) {
						header("Location: disks_zfs_snapshot.php");
						exit;
					}
					$errormsg = implode("\n", $ret['output']);
				}
				break;
			case 'delete':
				// Input validation not required
				if (empty($input_errors)) {
					$snapshot = [];
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$snapshot['recursive'] = isset($_POST['recursive']) ? true : false;
					$ret = zfs_snapshot_destroy($snapshot);
					if ($ret['retval'] == 0) {
						header("Location: disks_zfs_snapshot.php");
						exit;
					}
					$errormsg = implode("\n", $ret['output']);
				}
				break;
			case 'rollback':
				// Input validation not required
				if (empty($input_errors)) {
					$snapshot = [];
					$snapshot['uuid'] = $_POST['uuid'];
					$snapshot['snapshot'] =	$_POST['snapshot'];
					$snapshot['force_delete'] = isset($_POST['force_delete']) ? true : false;
					$ret = zfs_snapshot_rollback($snapshot);
					if ($ret['retval'] == 0) {
						header("Location: disks_zfs_snapshot.php");
						exit;
					}
					$errormsg = implode("\n", $ret['output']);
				}
				break;
			default:
				$input_errors[] = sprintf(gtext("The attribute '%s' is invalid."), 'action');
				break;
		}
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
function enable_change(enable_change) {
	document.iform.name.disabled = !enable_change;
}
function action_change() {
	showElementById('newpath_tr','hide');
	showElementById('recursive_tr','hide');
	showElementById('force_delete_tr','hide');
	var action = document.iform.action.value;
	switch (action) {
		case "clone":
			showElementById('newpath_tr','show');
			showElementById('recursive_tr','hide');
			showElementById('force_delete_tr','hide');
			break;
		case "delete":
			showElementById('newpath_tr','hide');
			showElementById('recursive_tr','show');
			showElementById('force_delete_tr','hide');
			break;
		case "rollback":
			showElementById('newpath_tr','hide');
			showElementById('recursive_tr','hide');
			showElementById('force_delete_tr','show');
			break;
		default:
			break;
	}
}
//]]>
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
			<form action="disks_zfs_snapshot_edit.php" method="post" name="iform" id="iform">
				<?php
					if (!empty($errormsg)) print_error_box($errormsg);
					if (!empty($input_errors)) print_input_errors($input_errors);
					if (file_exists($d_sysrebootreqd_path)) print_info_box(get_std_save_message(0));
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
						html_text("snapshot", gtext("Snapshot"), htmlspecialchars($pconfig['snapshot']));
						$a_action = array("clone" => gtext("Clone"), "delete" => gtext("Delete"), "rollback" => gtext("Rollback"));
						html_combobox("action", gtext("Action"), $pconfig['action'], $a_action, "", true, false, "action_change()");
						html_inputbox("newpath", gtext("Path"), $pconfig['newpath'], "", true, 30);
						html_checkbox("recursive", gtext("Recursive"), !empty($pconfig['recursive']) ? true : false, gtext("Deletes the recursive snapshot."), "", false);
						html_checkbox("force_delete", gtext("Force delete"), !empty($pconfig['force_delete']) ? true : false, gtext("Destroy any snapshots and bookmarks more recent than the one specified."), "", false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Execute");?>" onclick="enable_change(true)" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
					<input name="snapshot" type="hidden" value="<?=$pconfig['snapshot'];?>" />
					<input name="pool" type="hidden" value="<?=$pconfig['pool'];?>" />
					<input name="path" type="hidden" value="<?=$pconfig['path'];?>" />
				</div>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(true);
action_change();
//-->
</script>
<?php include("fend.inc");?>
