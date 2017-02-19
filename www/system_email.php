<?php
/*
	system_email.php

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
require 'email.inc';

$sphere_scriptname = basename(__FILE__);

$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Email')];
$gt_sendtestemailbuttonvalue = gtext('Send Test Email');

if (!isset($config['system']['email']) || !is_array($config['system']['email'])) {
	$config['system']['email'] = [];
};

$pconfig['server'] = $config['system']['email']['server'];
$pconfig['port'] = $config['system']['email']['port'];
$pconfig['auth'] = isset($config['system']['email']['auth']);
$pconfig['authmethod'] = $config['system']['email']['authmethod'];
$pconfig['starttls'] = isset($config['system']['email']['starttls']);
$pconfig['tls_certcheck'] = $config['system']['email']['tls_certcheck'];
$pconfig['security'] = $config['system']['email']['security'];
$pconfig['username'] = $config['system']['email']['username'];
$pconfig['password'] = $config['system']['email']['password'];
$pconfig['passwordconf'] = $pconfig['password'];
$pconfig['from'] = $config['system']['email']['from'];
$pconfig['sendto'] = isset($config['system']['email']['sendto']) ? $config['system']['email']['sendto'] : (isset($config['system']['email']['from']) ? $config['system']['email']['from'] : '');


if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	$reqdfields = [];
	$reqdfieldsn = [];
	$reqdfieldst = [];

	if (isset($_POST['auth'])) {
		$reqdfields = ['username', 'password'];
		$reqdfieldsn = [gtext('Username'), gtext('Password')];
		$reqdfieldst = ['string','string'];
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	// Check for a password mismatch.
	if (isset($_POST['auth']) && ($_POST['password'] !== $_POST['passwordconf'])) {
		$input_errors[] = gtext('The passwords do not match.');
	}

	if (empty($input_errors)) {
		$config['system']['email']['server'] = $_POST['server'];
		$config['system']['email']['port'] = $_POST['port'];
		$config['system']['email']['auth'] = isset($_POST['auth']) ? true : false;
		$config['system']['email']['authmethod'] = $_POST['authmethod'];
		$config['system']['email']['security'] = $_POST['security'];
		$config['system']['email']['starttls'] = isset($_POST['starttls']) ? true : false;
		$config['system']['email']['tls_certcheck'] = $_POST['tls_certcheck'];
		$config['system']['email']['username'] = $_POST['username'];
		$config['system']['email']['password'] = $_POST['password'];
		$config['system']['email']['from'] = $_POST['from'];
		$config['system']['email']['sendto'] = $_POST['sendto'];

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_exec_service('msmtp');
			config_unlock();
		}

		// Send test email.
		if(isset($_POST['SendTestEmail']) && $_POST['SendTestEmail']) {
//		if(stristr($_POST['Submit'], $gt_sendtestemailbuttonvalue)) {
			$subject = sprintf(gtext('Test email from host: %s'), system_get_hostname());
			$message = gtext('This email has been sent to validate your email configuration.');
			$retval = @email_send($config['system']['email']['sendto'], $subject, $message, $error);
			if (0 == $retval) {
				$savemsg = gtext('Test email successfully sent.');
				write_log(sprintf('Test email successfully sent to: %s.', $config['system']['email']['sendto']));
			} else {
				$failmsg = gtext('Failed to send test email.')
					. ' '
					. '<a href="' . 'diag_log.php' . '">'
					. gtext('Please check the log files')
					. '</a>.';
				write_log(sprintf('Failed to send test email to: %s.', $config['system']['email']['sendto']));
			}
		} else {
			$savemsg = get_std_save_message($retval);
		}
	}
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	auth_change();
});
function auth_change() {
	switch (document.iform.auth.checked) {
		case false:
			showElementById('username_tr','hide');
			showElementById('password_tr','hide');
			showElementById('authmethod_tr','hide');
			break;
		case true:
			showElementById('username_tr','show');
			showElementById('password_tr','show');
			showElementById('authmethod_tr','show');
		break;
	}
}
function enable_change(enable_change) {
	document.iform.starttls.disabled = endis;
}
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="system_advanced.php"><span><?=gtext('Advanced');?></span></a></li>
			<li class="tabact"><a href="system_email.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Email');?></span></a></li>
			<li class="tabinact"><a href="system_swap.php"><span><?=gtext('Swap');?></span></a></li>
			<li class="tabinact"><a href="system_rc.php"><span><?=gtext('Command Scripts');?></span></a></li>
			<li class="tabinact"><a href="system_cron.php"><span><?=gtext('Cron');?></span></a></li>
			<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext('loader.conf');?></span></a></li>
			<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext('rc.conf');?></span></a></li>
			<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext('sysctl.conf');?></span></a></li>
		</ul>
	</td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if (!empty($input_errors)) {
		print_input_errors($input_errors);
	}
	if (!empty($savemsg)) {
		print_info_box($savemsg);
	}
	if (!empty($failmsg)) {
		print_error_box($failmsg);
	}
	?>
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php
			html_titleline2(gtext('Email'));
			?>
		</thead>
		<tbody>
			<?php
			html_inputbox2('from', gtext('From Email Address'), $pconfig['from'], gtext('From email address for sending system messages.'), true, 62);
			html_inputbox2('sendto', gtext('To Email Address'), $pconfig['sendto'], gtext('Destination email address. Separate email addresses by semi-colon.'), true, 62);
			html_inputbox2('server', gtext('SMTP Server'), $pconfig['server'], gtext('Outgoing SMTP mail server address.'), true, 62);
			html_inputbox2('port', gtext('Port'), $pconfig['port'], gtext('The default SMTP mail server port, e.g. 25 or 587.'), true, 5);
			$l_security = [
				"none" => gtext('None'),
				"ssl" => 'SSL',
				"tls" => 'TLS'
			];
			html_combobox2('security', gtext('Security'), $pconfig['security'], $l_security, '', true);
			html_checkbox2('starttls', gtext('TLS mode'), !empty($pconfig['starttls']) ? true : false, gtext("Enable STARTTLS encryption. This doesn't mean you have to use TLS, you can use SSL."), gtext('This is a way to take an existing insecure connection, and upgrade it to a secure connection using SSL/TLS.'), false);
			$l_tls_certcheck = [
				'tls_certcheck off' => gtext('Off'),
				'tls_certcheck on' => gtext('On')
			];
			html_combobox2('tls_certcheck', gtext('TLS Server Certificate Check'), $pconfig['tls_certcheck'], $l_tls_certcheck, gtext('Enable or disable checks of the server certificate.'), '', false);
			html_checkbox2('auth', gtext('Authentication'), !empty($pconfig['auth']) ? true : false, gtext("Enable SMTP authentication."), '', false, false, 'auth_change()');
			html_inputbox2('username', gtext('Username'), $pconfig['username'], '', true, 40);
			html_passwordconfbox2('password', 'passwordconf', gtext('Password'), $pconfig['password'], $pconfig['passwordconf'], '', true);
			$l_authmethod = [
				'plain' => gtext('Plain-text'),
				'cram-md5' => 'Cram-MD5',
				'digest-md5' => 'Digest-MD5',
				'gssapi' => 'GSSAPI',
				'external' => 'External',
				'login' => gtext('Login'),
				'ntlm' => 'NTLM',
				'on' => gtext('Best available')
			];
			html_combobox2('authmethod', gtext('Authentication method'), $pconfig['authmethod'], $l_authmethod, '', true);
			?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>" />
		<input name="SendTestEmail" id="sendnow" type="submit" class="formbtn" value="<?=$gt_sendtestemailbuttonvalue;?>"/>
	</div>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
