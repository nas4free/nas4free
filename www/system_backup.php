<?php
/*
	system_backup.php

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

$pgtitle = [gtext('System'),gtext('Backup/Restore')];

/* omit no-cache headers because it confuses IE with file downloads */
$omit_nocacheheaders = true;

// default is enable encryption
//$pconfig['encryption'] = "yes";

$old_default_password = "freenas";
$current_password = $config['system']['password'];
if (strcmp($current_password, $g['default_passwd']) === 0
   || strcmp($current_password, $old_default_password) === 0) {
	$errormsg = gtext("Current password is default password. You should use your own password.");
}

if ($_POST) {
	unset($errormsg);
	unset($input_errors);
	$pconfig['encryption'] = isset($_POST['encryption']) ? $_POST['encryption'] : '';

	$encryption = 0;
	if (!empty($_POST['encryption']))
		$encryption = 1;
	if (0 == strcmp($_POST['Submit'], gtext("Restore Configuration"))) {
		$mode = "restore";
	} else if (0 == strcmp($_POST['Submit'], gtext("Download Configuration"))) {
		$mode = "download";
	}

	if ($encryption) {
		$reqdfields = ['encrypt_password','encrypt_password_confirm'];
		$reqdfieldsn = [gtext('Encrypt password'),gtext('Encrypt password (confirmed)')];
		$reqdfieldst = ['password','password'];
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
		if ($_POST['encrypt_password'] !== $_POST['encrypt_password_confirm']) {
			$input_errors[] = gtext("The confirmed password does not match. Please ensure the passwords match exactly.");
		}
	}

	if (empty($input_errors) && $mode) {
		if ($mode === "download") {
			config_lock();

			if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
			@date_default_timezone_set(@date_default_timezone_get());
			if ($encryption) {
				$fn = "config-{$config['system']['hostname']}.{$config['system']['domain']}-" . date("YmdHis") . ".gz";
				$password = $_POST['encrypt_password'];
				//$password = $config['system']['password'];
				$data = config_encrypt($password);
				$fs = strlen($data);
			} else {
				$fn = "config-{$config['system']['hostname']}.{$config['system']['domain']}-" . date("YmdHis") . ".xml";
				$data = file_get_contents("{$g['conf_path']}/config.xml");
				$fs = get_filesize("{$g['conf_path']}/config.xml");
			}

			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename={$fn}");
			header("Content-Length: {$fs}");
			header("Pragma: hack");
			echo $data;
			config_unlock();

			exit;
		} else if ($mode === "restore") {
			$encrypted = 0;
			if (is_uploaded_file($_FILES['conffile']['tmp_name'])) {
				// Validate configuration backup
				$validate = 0;
				if (pathinfo($_FILES['conffile']['name'], PATHINFO_EXTENSION) == 'gz') {
					$encrypted = 1;
					$gz_config = file_get_contents($_FILES['conffile']['tmp_name']);
					$password = $_POST['decrypt_password'];
					//$password = $config['system']['password'];
					$data = config_decrypt($password, $gz_config);
					if ($data !== FALSE) {
						$tempfile = tempnam(sys_get_temp_dir(), 'cnf');
						file_put_contents($tempfile, $data);
						$validate = validate_xml_config($tempfile, $g['xml_rootobj']);
						if (!$validate) {
							unlink($tempfile);
						}
					}
				} else {
					$validate = validate_xml_config($_FILES['conffile']['tmp_name'], $g['xml_rootobj']);
				}
				if (!$validate) {
					$errormsg = sprintf(gtext("The configuration could not be restored. %s"),
						$encrypted ? gtext("Invalid file format or incorrect password.") : gtext("Invalid file format or incorrect password."));
				} else {
					// Install configuration backup
					if ($encrypted) {
						$ret = config_install($tempfile);
						unlink($tempfile);
					} else {
						$ret = config_install($_FILES['conffile']['tmp_name']);
					}
					if ($ret == 0) {
						system_reboot();
						$savemsg = sprintf(gtext("The configuration has been restored. The server is now rebooting."));
					} else {
						$errormsg = gtext("The configuration could not be restored.");
					}
				}
			} else {
				$errormsg = sprintf(gtext("The configuration could not be restored. No file was uploaded!"),
					$g_file_upload_error[$_FILES['conffile']['error']]);
			}
		}
	}
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function encrypt_change(encrypt_change) {
		var val = !($('#encryption').prop('checked') || encrypt_change);
		$('#encrypt_password').prop('disabled', val);
		$('#encrypt_password_confirm').prop('disabled', val);
		if (!encrypt_change) {
			if (val) {
				// disabled
				$('#encrypt_password_tr td:first').removeClass('vncellreq').addClass('vncell');
			} else {
				// enabled
				$('#encrypt_password_tr td:first').removeClass('vncell').addClass('vncellreq');
			}
		}
	}
	$('#encryption').click(function(){
		encrypt_change(false);
	});
	$('input:submit').click(function(){
		encrypt_change(true);
	});
	encrypt_change(false);
});
//]]>
</script>
<form action="system_backup.php" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($errormsg)) print_error_box($errormsg);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
			  <table width="100%" border="0" cellspacing="0" cellpadding="6">
			    <tr>
			      <td colspan="2" class="listtopic"><?=gtext("Backup Configuration");?></td>
			    </tr>
			    <tr>
					<td width="22%" valign="top" class="vncell"><?=gtext("Encryption");?></td>
					<td width="78%" class="vtable">
						<input name="encryption" type="checkbox" id="encryption" value="yes" <?php if (!empty($pconfig['encryption'])) echo "checked=\"checked\""; ?> />
					<?=gtext("Enable encryption.");?></td>
			    </tr>
			    <tr id="encrypt_password_tr">
					<td width="22%" valign="top" class="vncell"><label for="encrypt_password"><?=gtext("Encrypt Password");?></label></td>
					<td width="78%" class="vtable">
						<input name="encrypt_password" type="password" class="formfld" id="encrypt_password" size="25" value="" /><br />
						<input name="encrypt_password_confirm" type="password" class="formfld" id="encrypt_password_confirm" size="25" value="" />&nbsp;(<?=gtext("Confirmation");?>)
					</td>
			    </tr>
			    <tr>
					<td width="22%" valign="baseline" class="vncell">&nbsp;</td>
					<td width="78%" class="vtable">
						<?=gtext("Click this button to download the server configuration in encrypted GZIP file or XML format.");?><br />
						<div id="remarks">
							<?php html_remark("note", gtext("Note"), sprintf("%s", /*gtext("Current administrator password is used for encryption.")*/ gtext("Encrypted configuration is automatically gzipped.")));?>
						</div>
						<div id="submit">
							<input name="Submit" type="submit" class="formbtn" id="download" value="<?=gtext("Download Configuration");?>" />
						</div>
					</td>
			    </tr>
			    <tr>
			      <td colspan="2" class="list" height="12"></td>
			    </tr>
			    <tr>
			      <td colspan="2" class="listtopic"><?=gtext("Restore Configuration");?></td>
			    </tr>
			    <tr id="decrypt_password_tr">
				<td width="22%" valign="top" class="vncell"><label for="decrypt_password"><?=gtext("Decrypt Password");?></label></td>
				<td width="78%" class="vtable">
					<input name="decrypt_password" type="password" class="formfld" id="decrypt_password" size="25" value="" />
				</td>
			    </tr>
			    <tr>
					<td width="22%" valign="baseline" class="vncell">&nbsp;</td>
					<td width="78%" class="vtable">
						<?php echo sprintf(gtext("Select the server configuration encrypted GZIP file or XML file and click the button below to restore the configuration."));?><br />
						<div id="remarks">
							<?php html_remark("note", gtext("Note"), sprintf("%s", /*gtext("Current administrator password is used for decryption.")*/ gtext("The server will reboot after restoring the configuration.")));?>
						</div>
						<div id="submit">
						<input name="conffile" type="file" class="formfld" id="conffile" size="40" />
						</div>
						<div id="submit">
						<input name="Submit" type="submit" class="formbtn" id="restore" value="<?=gtext("Restore Configuration");?>" />
						</div>
					</td>
			    </tr>
		</table>
			<div id="remarks">
				<?php html_remark("warning", gtext("Warning"), sprintf(gtext("It is recommended to use encryption before you store the backup in a safe location.")));?>
				</div>
			</td>
		</tr>
</table>
<?php include 'formend.inc';?>
</form>
<?php include 'fend.inc';?>
