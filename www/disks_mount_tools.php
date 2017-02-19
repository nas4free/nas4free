<?php
/*
	disks_mount_tools.php

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

$pgtitle = array(gtext("Disks"), gtext("Mount Point"), gtext("Tools"));

if (isset($_GET['disk'])) {
	$index = array_search_ex($_GET['disk'], $config['mounts']['mount'], "mdisk");
	if (false !== $index) {
		$uuid = $config['mounts']['mount'][$index]['uuid'];
	}
}

if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

if (!isset($config['mounts']['mount']) || !is_array($config['mounts']['mount']))
	$config['mounts']['mount'] = array();

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	unset($do_action);

	// Input validation.
	$reqdfields = explode(" ", "mountpoint action");
	$reqdfieldsn = array(gtext("Mount point"), gtext("Command"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	// Check if mount point is used to store a swap file.
	if (("umount" === $_POST['action']) &&
			(isset($config['system']['swap']['enable'])) &&
			($config['system']['swap']['type'] === "file") &&
			($config['system']['swap']['mountpoint'] === $_POST['mountpoint'])) {
		$index = array_search_ex($_POST['mountpoint'], $config['mounts']['mount'], "uuid");
		$errormsg[] = gtext(sprintf("A swap file is located on the mounted device %s.",
		$config['mounts']['mount'][$index]['devicespecialfile']));
	}

	if ((empty($input_errors)) || (empty($errormsg))) {
		$do_action = true;
		$uuid = $_POST['mountpoint'];
		$action = $_POST['action'];
	}
}

if (!isset($do_action)) {
	$do_action = false;
}
?>
<?php include("fbegin.inc");?>
<?php if(!empty($errormsg)) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
        <li class="tabinact"><a href="disks_mount.php"><span><?=gtext("Management");?></span></a></li>
        <li class="tabact"><a href="disks_mount_tools.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Tools");?></span></a></li>
        <li class="tabinact"><a href="disks_mount_fsck.php"><span><?=gtext("Fsck");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <?php if ($input_errors) print_input_errors($input_errors);?>
			<form action="disks_mount_tools.php" method="post" name="iform" id="iform" onsubmit="spinner()">
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_mountcombobox("mountpoint", gtext("Mount point"), !empty($uuid) ? $uuid : "", "", true);?>
					<?php html_combobox("action", gtext("Command"), !empty($action) ? $action : "", array("mount" => gtext("mount"), "umount" => gtext("umount")), "", true);?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Execute");?>" />
				</div>
				<?php if(($do_action) && (empty($errormsg))) {
					echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
					echo('<pre class="cmdoutput">');
					//ob_end_flush();

					$index = array_search_ex($uuid, $config['mounts']['mount'], "uuid");
					if (false !== $index) {
						$mount = $config['mounts']['mount'][$index];

						switch ($action) {
						  case "mount":
						    echo(gtext("Mounting...") . "<br />");
								$result = disks_mount($mount);
						    break;

						  case "umount":
						    echo(gtext("Unmounting...") . "<br />");
								$result = disks_umount($mount);
						    break;
						}

						echo (0 == $result) ? gtext("Done.") : gtext("Failed.");
					}
					echo('</pre>');
				}?>
				<div id="remarks">
					<?php html_remark("note", gtext("Note"), gtext("You can't unmount a drive which is used by swap file, a iSCSI-target file or any other running process!"));?>
				</div>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
