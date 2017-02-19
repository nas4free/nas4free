<?php
/*
	services_sshd.php

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

array_make_branch($config,'sshd','auxparam');
$os_release = exec('uname -r | cut -d - -f1');
$pconfig['challengeresponseauthentication'] = isset($config['sshd']['challengeresponseauthentication']);
$pconfig['port'] = $config['sshd']['port'];
$pconfig['permitrootlogin'] = isset($config['sshd']['permitrootlogin']);
$pconfig['tcpforwarding'] = isset($config['sshd']['tcpforwarding']);
$pconfig['enable'] = isset($config['sshd']['enable']);
$pconfig['key'] = !empty($config['sshd']['private-key']) ? base64_decode($config['sshd']['private-key']) : "";
$pconfig['passwordauthentication'] = isset($config['sshd']['passwordauthentication']);
$pconfig['compression'] = isset($config['sshd']['compression']);
$pconfig['subsystem'] = !empty($config['sshd']['subsystem']) ? $config['sshd']['subsystem'] : "";
if(isset($config['sshd']['auxparam']) && is_array($config['sshd']['auxparam'])):
	$pconfig['auxparam'] = implode("\n", $config['sshd']['auxparam']);
endif;

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	$reqdfields = [];
	$reqdfieldsn = [];

	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = array_merge($reqdfields,['port']);
		$reqdfieldsn = [gtext('TCP Port')];
		$reqdfieldst = ['port'];
		
		if (!empty($_POST['key'])) {
			$reqdfields = array_merge($reqdfields, ['key']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Private Key')]);
			$reqdfieldst = array_merge($reqdfieldst, ['privatekey']);
		}
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (empty($input_errors)) {
		$config['sshd']['port'] = $_POST['port'];
		$config['sshd']['challengeresponseauthentication'] = isset($_POST['challengeresponseauthentication']);
		$config['sshd']['permitrootlogin'] = isset($_POST['permitrootlogin']) ? true : false;
		$config['sshd']['tcpforwarding'] = isset($_POST['tcpforwarding']) ? true : false;
		$config['sshd']['enable'] = isset($_POST['enable']) ? true : false;
		$config['sshd']['private-key'] = base64_encode($_POST['key']);
		$config['sshd']['passwordauthentication'] = isset($_POST['passwordauthentication']) ? true : false;
		$config['sshd']['compression'] = isset($_POST['compression']) ? true : false;
		$config['sshd']['subsystem'] = $_POST['subsystem'];

		# Write additional parameters.
		unset($config['sshd']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['sshd']['auxparam'][] = $auxparam;
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("sshd");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
$pgtitle = [gtext('Services'),gtext('SSH')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.port.disabled = endis;
	document.iform.challengeresponseauthentication.disabled = endis;
	document.iform.key.disabled = endis;
	document.iform.permitrootlogin.disabled = endis;
	document.iform.passwordauthentication.disabled = endis;
	document.iform.tcpforwarding.disabled = endis;
	document.iform.compression.disabled = endis;
	document.iform.subsystem.disabled = endis;
	document.iform.auxparam.disabled = endis;
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_sshd.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Secure Shell"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("TCP port");?></td>
						<td width="78%" class="vtable">
							<input name="port" type="text" class="formfld" id="port" size="20" value="<?=htmlspecialchars($pconfig['port']);?>" />
							<br /><?=gtext("Enter a custom port number if you want to override the default port. (Default is 22).");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Enable Challenge-Response Authentication");?></td>
						<td width="78%" class="vtable">
							<input name="challengeresponseauthentication" type="checkbox" id="challengeresponseauthentication" value="yes" <?php if (!empty($pconfig['challengeresponseauthentication'])) echo "checked=\"checked\""; ?> />
							<?=gtext("Specifies the usage of Challenge-Response Authentication.");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Permit Root Login");?></td>
						<td width="78%" class="vtable">
						<input name="permitrootlogin" type="checkbox" id="permitrootlogin" value="yes" <?php if (!empty($pconfig['permitrootlogin'])) echo "checked=\"checked\""; ?> />
							<?=gtext("Specifies whether it is allowed to login as superuser (root) directly.");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Password Authentication");?></td>
						<td width="78%" class="vtable">
							<input name="passwordauthentication" type="checkbox" id="passwordauthentication" value="yes" <?php if (!empty($pconfig['passwordauthentication'])) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable keyboard-interactive authentication.");?>
						</td>
					</tr>
					<tr>
					<td width="22%" valign="top" class="vncell"><?=gtext("TCP Forwarding");?></td>
						<td width="78%" class="vtable">
							<input name="tcpforwarding" type="checkbox" id="tcpforwarding" value="yes" <?php if (!empty($pconfig['tcpforwarding'])) echo "checked=\"checked\""; ?> />
							<?=gtext("Permit to do SSH Tunneling.");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Compression");?></td>
						<td width="78%" class="vtable">
							<input name="compression" type="checkbox" id="compression" value="yes" <?php if (!empty($pconfig['compression'])) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable compression.");?><br />
							<span class="vexpl"><?=gtext("Compression is worth using if your connection is slow. The efficiency of the compression depends on the type of the file, and varies widely. Useful for internet transfer only.");?></span>
						</td>
					</tr>
					<?php html_textarea("key", gtext("Private Key"), $pconfig['key'], gtext("Paste a RSA PRIVATE KEY in PEM format here."), false, 65, 7, false, false);?>
					<?php html_inputbox("subsystem", gtext("Subsystem"), $pconfig['subsystem'], gtext("Leave this field empty to use default settings."), false, 40);?>
					<?php
					$helpinghand = '<a href="' .
							'http://www.freebsd.org/cgi/man.cgi?query=sshd_config&amp;apropos=0&amp;sektion=0&amp;manpath=FreeBSD+' . $os_release . '-RELEASE&amp;format=html' .
							'" target="_blank">' .
							gtext('Please check the documentation').
							'</a>.';
					html_textarea("auxparam", gtext("Additional Parameters"), !empty($pconfig['auxparam']) ? $pconfig['auxparam'] : "", gtext("Extra options to /etc/ssh/sshd_config (usually empty). Note, incorrect entered options prevent SSH service to be started.") . " " . $helpinghand, false, 65, 5, false, false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>
<?php include 'fend.inc';?>
