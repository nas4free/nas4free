<?php
/*
	disks_crypt_tools.php

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

$pgtitle = array(gtext("Disks"), gtext("Encryption"), gtext("Tools"));

// Omit no-cache headers because it confuses IE with file downloads.
$omit_nocacheheaders = true;

if (!isset($config['geli']['vdisk']) || !is_array($config['geli']['vdisk']))
	$config['geli']['vdisk'] = array();

if (!isset($config['mounts']['mount']) || !is_array($config['mounts']['mount']))
	$config['mounts']['mount'] = array();

array_sort_key($config['geli']['vdisk'], "devicespecialfile");
$a_geli = &$config['geli']['vdisk'];

array_sort_key($config['mounts']['mount'], "devicespecialfile");
$a_mount = &$config['mounts']['mount'];

if ($config['system']['webgui']['protocol'] === "http") {
	$nohttps_error = gtext("You should use HTTPS as WebGUI protocol for sending passphrase.");
}

if ($_POST) {
	unset($input_errors);

	// Input validation.
	$reqdfields = explode(" ", "disk action");
	$reqdfieldsn = array(gtext("Disk"), gtext("Command"));

	if (isset($_POST['action']) && $_POST['action'] === "attach") {
		$reqdfields = array_merge($reqdfields, explode(" ", "passphrase"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("Passphrase")));
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (0 != mwexec("/sbin/kldstat -q -m aesni")) {
		mwexec("/sbin/kldload -q aesni.ko");
	}

	if (empty($input_errors)) {
		$pconfig['do_action'] = true;

		// Action = 'detach' => Check if device is mounted
		if (($_POST['action'] === "detach") && (1 == disks_ismounted_ex($_POST['disk'], "devicespecialfile"))) {
			$helpinghand = sprintf('disks_mount_tools.php?disk=%1$s&action=umount', $_POST['disk']);
			$link = sprintf('<a href="%1$s">%2$s</a>', $helpinghand, gtext('Unmount this disk first before proceeding.'));
			$errormsg = gtext('The encrypted device is currently mounted!') . ' ' . $link;
			$pconfig['do_action'] = false;
		}

		$pconfig['action'] = $_POST['action'];
		$pconfig['disk'] = $_POST['disk'];
		$pconfig['oldpassphrase'] = $_POST['oldpassphrase'];
		$pconfig['passphrase'] = $_POST['passphrase'];

		// Get configuration.
		$id = array_search_ex($pconfig['disk'], $a_geli, "devicespecialfile");
		$geli = $a_geli[$id];
	}
}

if (!isset($pconfig['action'])) {
	$pconfig['do_action'] = false;
	$pconfig['action'] = "";
	$pconfig['disk'] = "";
	$pconfig['oldpassphrase'] = "";
	$pconfig['passphrase'] = "";
}

if (isset($_GET['disk'])) {
  $pconfig['disk'] = $_GET['disk'];
}

if (isset($_GET['action'])) {
  $pconfig['action'] = $_GET['action'];
}

if ("backup" === $pconfig['action']) {
	$fn = "/var/tmp/{$geli['name']}.metadata";
	mwexec("/sbin/geli backup {$geli['device'][0]} {$fn}");
	$fs = get_filesize($fn);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename={$geli['name']}.metadata");
	header("Content-Length: {$fs}");
	readfile($fn);
	unlink($fn);
	exit;
}

if ("restore" === $pconfig['action']) {
	if (is_uploaded_file($_FILES['backupfile']['tmp_name'])) {
		$fn = "/var/tmp/{$geli['name']}.metadata";
		// Move the metadata backup file so PHP won't delete it.
		move_uploaded_file($_FILES['backupfile']['tmp_name'], $fn);
	} else {
		$errormsg = sprintf("%s %s", gtext("Failed to upload file."),
			$g_file_upload_error[$_FILES['backupfile']['error']]);
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function action_change() {
	switch(document.iform.action.value) {
		case "attach":
			showElementById('passphrase_tr','show');
			showElementById('oldpassphrase_tr','hide');
			showElementById('backupfile_tr','hide');
			break;
		case "setkey":
			showElementById('passphrase_tr','show');
			showElementById('oldpassphrase_tr','show');
			showElementById('backupfile_tr','hide');
			break;
		case "restore":
			showElementById('passphrase_tr','hide');
			showElementById('oldpassphrase_tr','hide');
			showElementById('backupfile_tr','show');
			break;
		default:
			showElementById('passphrase_tr','hide');
			showElementById('oldpassphrase_tr','hide');
			showElementById('backupfile_tr','hide');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
        <li class="tabinact"><a href="disks_crypt.php"><span><?=gtext("Management");?></span></a></li>
        <li class="tabact"><a href="disks_crypt_tools.php" title="<?=gtext('Reload page');?>" ><span><?=gtext("Tools");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
    	<?php if (!empty($nohttps_error)) print_warning_box($nohttps_error);?>
      <?php if (!empty($input_errors)) print_input_errors($input_errors);?>
      <?php if (!empty($errormsg)) print_error_box($errormsg);?>
			<form action="disks_crypt_tools.php" method="post" name="iform" id="iform" enctype="multipart/form-data" onsubmit="spinner()">
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
          <tr>
            <td width="22%" valign="top" class="vncellreq"><?=gtext("Disk");?></td>
            <td width="78%" class="vtable">
              <select name="disk" class="formfld" id="disk">
              	<option value=""><?=gtext("Must choose one");?></option>
                <?php foreach ($a_geli as $geliv):?>
								<option value="<?=$geliv['devicespecialfile'];?>" <?php if ($geliv['devicespecialfile'] === $pconfig['disk']) echo "selected=\"selected\"";?>>
								<?php echo htmlspecialchars("{$geliv['name']}: {$geliv['size']} ({$geliv['desc']})");?>
                </option>
                <?php endforeach;?>
              </select>
            </td>
      		</tr>
					<?php $options = array("attach" => "attach", "detach" => "detach", "setkey" => "setkey", "list" => "list", "status" => "status", "backup" => "backup", "restore" => "restore");?>
					<?php html_combobox("action", gtext("Command"), $pconfig['action'], $options, "", true, false, "action_change()");?>
          <tr id="oldpassphrase_tr" style="display: none">
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Old passphrase");?></td>
						<td width="78%" class="vtable">
							<input name="oldpassphrase" type="password" class="formfld" id="oldpassphrase" size="20" />
						</td>
					</tr>
          <tr id="passphrase_tr" style="display: none">
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Passphrase");?></td>
						<td width="78%" class="vtable">
							<input name="passphrase" type="password" class="formfld" id="passphrase" size="20" />
						</td>
					</tr>
					<tr id="backupfile_tr" style="display: none">
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Backup file");?></td>
						<td width="78%" class="vtable">
							<input name="backupfile" type="file" class="formfld" size="40" /><br />
							<span class="vexpl"><?=gtext("Restore metadata from the given file to the given provider.");?></span>
						</td>
					</tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Execute");?>" />
				</div>
				<?php if ($pconfig['do_action']) {
					echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
					echo('<pre class="cmdoutput">');
					//ob_end_flush();

					switch($pconfig['action']) {
			  		case "attach":
			        $result = disks_geli_attach($geli['device'][0], $pconfig['passphrase'], true);
			        // When attaching the disk, then also mount it.
							if (FALSE !== ($cnid = array_search_ex($geli['devicespecialfile'], $a_mount, "mdisk"))) {
								echo("<br />" . gtext("Mounting device.") . "<br />");
								echo((0 == disks_mount($a_mount[$cnid])) ? gtext("Successful.") : gtext("Failed."));
							}
			        break;

			      case "detach":
							$result = disks_geli_detach($geli['devicespecialfile'], true);
							echo((0 == $result) ? gtext("Done.") : gtext("Failed."));
			        break;

						case "setkey":
							disks_geli_setkey($geli['devicespecialfile'], $pconfig['oldpassphrase'], $pconfig['passphrase'], true);
					  	break;

					  case "list":
					  	system("/sbin/geli list");
					  	break;

					  case "status":
					  	system("/sbin/geli status");
					  	break;

					  case "restore":
							$fn = "/var/tmp/{$geli['name']}.metadata";
							if (file_exists($fn)) {
					  		system("/sbin/geli restore -v {$fn} {$geli['devicespecialfile']}");
					  		unlink($fn);
					  	} else {
					  		echo gtext("Failed to upload metadata backup file.");
							}
					  	break;
					}

					echo('</pre>');
				}?>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
action_change();
//-->
</script>
<?php include("fend.inc");?>
