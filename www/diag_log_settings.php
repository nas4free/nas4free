<?php
/*
	diag_log_settings.php

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
require("diag_log.inc");

$pgtitle = array(gtext("Diagnostics"), gtext("Log"), gtext("Settings"));

$pconfig['reverse']  = isset($config['syslogd']['reverse']);
$pconfig['nentries'] = $config['syslogd']['nentries'];
$pconfig['resolve']  = isset($config['syslogd']['resolve']);
$pconfig['disablecomp'] = isset($config['syslogd']['disablecomp']);
$pconfig['disablesecure'] = isset($config['syslogd']['disablesecure']);
if (!empty($config['syslogd']['remote']) && is_array($config['syslogd']['remote'])) {
	$pconfig['enable'] = isset($config['syslogd']['remote']['enable']);
	$pconfig['ipaddr'] = $config['syslogd']['remote']['ipaddr'];
	$pconfig['daemon'] = isset($config['syslogd']['remote']['daemon']);
	$pconfig['ftp']    = isset($config['syslogd']['remote']['ftp']);
	$pconfig['rsyncd'] = isset($config['syslogd']['remote']['rsyncd']);
	$pconfig['smartd'] = isset($config['syslogd']['remote']['smartd']);
	$pconfig['sshd']   = isset($config['syslogd']['remote']['sshd']);
	$pconfig['system'] = isset($config['syslogd']['remote']['system']);
}

if (!$pconfig['nentries'])
	$pconfig['nentries'] = 50;

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	/* input validation */
	if (isset($_POST['enable']) && $_POST['enable'] && !is_ipaddr($_POST['ipaddr'])) {
		$input_errors[] = gtext("A valid IP address must be specified.");
	}
	if (($_POST['nentries'] < 5) || ($_POST['nentries'] > 1000)) {
		$input_errors[] = gtext("Number of log entries to show must be between 5 and 1000.");
	}

	if (empty($input_errors)) {
		$config['syslogd']['reverse'] = isset($_POST['reverse']) ? true : false;
		$config['syslogd']['nentries'] = (int)$_POST['nentries'];
		$config['syslogd']['resolve'] = isset($_POST['resolve']) ? true : false;
		$config['syslogd']['disablecomp'] = isset($_POST['disablecomp']) ? true : false;
		$config['syslogd']['disablesecure'] = isset($_POST['disablesecure']) ? true : false;
		$config['syslogd']['remote']['enable'] = isset($_POST['enable']) ? true : false;
		$config['syslogd']['remote']['ipaddr'] = $_POST['ipaddr'];
		$config['syslogd']['remote']['system'] = isset($_POST['system']) ? true : false;
		$config['syslogd']['remote']['ftp'] = isset($_POST['ftp']) ? true : false;
		$config['syslogd']['remote']['rsyncd'] = isset($_POST['rsyncd']) ? true : false;
		$config['syslogd']['remote']['sshd'] = isset($_POST['sshd']) ? true : false;
		$config['syslogd']['remote']['smartd'] = isset($_POST['smartd']) ? true : false;
		$config['syslogd']['remote']['daemon'] = isset($_POST['daemon']) ? true : false;

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval = rc_restart_service("syslogd");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.ipaddr.disabled = endis;
	document.iform.sshd.disabled = endis;
	document.iform.system.disabled = endis;
	document.iform.ftp.disabled = endis;
	document.iform.rsyncd.disabled = endis;
	document.iform.smartd.disabled = endis;
	document.iform.daemon.disabled = endis;
}
// -->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="diag_log.php"><span><?=gtext("Log");?></span></a></li>
				<li class="tabact"><a href="diag_log_settings.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Settings");?></span></a></li>
			</ul>
		</td>
	</tr>
<tr>
    <td class="tabcont">
			<form action="diag_log_settings.php" method="post" name="iform" id="iform" onsubmit="spinner()">
			<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
			<?php if (!empty($savemsg)) print_info_box($savemsg);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline(gtext("Log Settings"));?>
		   <tr>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gtext("Log order");?></td>
			<td width="78%" class="vtable">
			<input name="reverse" type="checkbox" id="reverse" value="yes" <?php if (!empty($pconfig['reverse'])) echo "checked=\"checked\""; ?> />
			<?=gtext("Show log entries in reverse order (newest entries on top).");?>
		   </td>
		</tr>
	        <tr>
		<td width="22%" valign="top" class="vncell"><?=gtext("Max entries");?></td>
		<td width="78%" class="vtable">
			<?=gtext("Number of log entries to show");?>:
			<input name="nentries" id="nentries" type="text" class="formfld" size="4" value="<?=htmlspecialchars($pconfig['nentries']);?>" /></td>
		   </tr>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gtext("Resolve IP");?></td>
			<td width="78%" class="vtable">
			<input name="resolve" type="checkbox" id="resolve" value="yes" <?php if (!empty($pconfig['resolve'])) echo "checked=\"checked\""; ?> />
			<?=gtext("Resolve IP addresses to hostnames.");?><br />
			<?php
			echo gtext('Hint'), ': ', gtext('If this option is checked, IP addresses in the server logs are resolved to their hostnames where possible.'), '<br><font color="red">', gtext('Warning'), '</font>: ', gtext('This can cause a huge delay in loading the log page!');
			?>
		   </td>
		</tr>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gtext("Compression");?></td>
			<td width="78%" class="vtable">
			<input name="disablecomp" type="checkbox" id="disablecomp" value="yes" <?php if (!empty($pconfig['disablecomp'])) echo "checked=\"checked\""; ?> />
			<?=gtext("Disable the compression of repeated line.");?></td>
		   </tr>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gtext("Remote syslog messages");?></td>
			<td width="78%" class="vtable">
			<input name="disablesecure" type="checkbox" id="disablesecure" value="yes" <?php if (!empty($pconfig['disablesecure'])) echo "checked=\"checked\""; ?> />
			<?=gtext("Accept remote syslog messages.");?></td>
			<?php html_separator();?>
		   </tr>
		<tr>
			<?php html_titleline_checkbox("enable", gtext("Remote Syslog Server"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
			<td width="22%" valign="top" class="vncell"><?=gtext("IP address");?></td>
			<td width="78%" class="vtable">
			<input name="ipaddr" id="ipaddr" type="text" class="formfld" size="17" value="<?=htmlspecialchars($pconfig['ipaddr']);?>" />
			<br /><?=gtext("IP address of remote syslog server.");?>
		   </tr>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gtext("Event selection");?></td>
			<td width="78%" class="vtable">
			<input name="system" id="system" type="checkbox" value="yes" <?php if (!empty($pconfig['system'])) echo "checked=\"checked\""; ?> />
			<?=gtext("System events");?><br />
			<input name="ftp" id="ftp" type="checkbox" value="yes" <?php if (!empty($pconfig['ftp'])) echo "checked=\"checked\""; ?> />
			<?=gtext("FTP events");?><br />
			<input name="rsyncd" id="rsyncd" type="checkbox" value="yes" <?php if (!empty($pconfig['rsyncd'])) echo "checked=\"checked\""; ?> />
			<?=gtext("RSYNC events");?><br />
			<input name="sshd" id="sshd" type="checkbox" value="yes" <?php if (!empty($pconfig['sshd'])) echo "checked=\"checked\""; ?> />
			<?=gtext("SSH events");?><br />
			<input name="smartd" id="smartd" type="checkbox" value="yes" <?php if (!empty($pconfig['smartd'])) echo "checked=\"checked\""; ?> />
			<?=gtext("S.M.A.R.T. events");?><br />
			<input name="daemon" id="daemon" type="checkbox" value="yes" <?php if (!empty($pconfig['daemon'])) echo "checked=\"checked\""; ?> />
			<?=gtext("Daemon events");?><br />
		   </td>
		</tr>
	</table>
		<div id="submit">
			<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" onclick="enable_change(true)" />
			</div>
			<div id="remarks">
			<?php html_remark("note", gtext("Note"), sprintf(gtext("Syslog sends UDP datagrams to port 514 on the specified remote syslog server. Be sure to set syslogd on the remote server to accept syslog messages from this server.")));?>
			</div>
			<?php include("formend.inc");?>
		   </form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
