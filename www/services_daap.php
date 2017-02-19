<?php
/*
	services_daap.php

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
require 'services.inc';

array_make_branch($config,'daap');
$pconfig['enable'] = isset($config['daap']['enable']);
$pconfig['servername'] = !empty($config['daap']['servername']) ? $config['daap']['servername'] : "";
$pconfig['port'] = $config['daap']['port'];
$pconfig['dbdir'] = $config['daap']['dbdir'];
$pconfig['content'] = !empty($config['daap']['content']) ? $config['daap']['content'] : [];
$pconfig['compdirs'] = $config['daap']['compdirs'];
$pconfig['concatcomps'] = isset($config['daap']['concatcomps']);
$pconfig['rescaninterval'] = $config['daap']['rescaninterval'];
$pconfig['alwaysscan'] = isset($config['daap']['alwaysscan']);
$pconfig['skipfirst'] = isset($config['daap']['skipfirst']);
$pconfig['scantype'] = $config['daap']['scantype'];
$pconfig['admin_pw'] = $config['daap']['admin_pw'];

// Set default values.
if (!$pconfig['servername']) $pconfig['servername'] = $config['system']['hostname'];
if (!$pconfig['port']) $pconfig['port'] = "3689";
if (!$pconfig['rescaninterval']) $pconfig['rescaninterval'] = "0";
if (!$pconfig['alwaysscan']) $pconfig['alwaysscan'] = false;
if (!$pconfig['skipfirst']) $pconfig['skipfirst'] = false;
if (!$pconfig['scantype']) $pconfig['scantype'] = "0";
if (!$pconfig['concatcomps']) $pconfig['concatcomps'] = false;
if (!$pconfig['compdirs']) $pconfig['compdirs'] = "";

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = ['servername','port','dbdir','content','admin_pw'];
		$reqdfieldsn = [gtext('Name'),gtext('Port'),gtext('Database Directory'),gtext('Content'),gtext('Password')];
		$reqdfieldst = ['string','port','string','array','password'];

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		$reqdfields = array_merge($reqdfields, ['rescaninterval']);
		$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Rescan interval')]);
		$reqdfieldst = array_merge($reqdfieldst, ['numeric']);

		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		// Check if port is already used.
		if (services_is_port_used($_POST['port'], "daap"))
			$input_errors[] = sprintf(gtext("Port %ld is already used by another service."), $_POST['port']);
	}

	if (empty($input_errors)) {
		$config['daap']['enable'] = isset($_POST['enable']) ? true : false;
		$config['daap']['servername'] = $_POST['servername'];
		$config['daap']['port'] = $_POST['port'];
		$config['daap']['dbdir'] = $_POST['dbdir'];
		$config['daap']['content'] = !empty($_POST['content']) ? $_POST['content'] : [];
		$config['daap']['compdirs'] = $_POST['compdirs'];
		$config['daap']['concatcomps'] = isset($_POST['concatcomps']) ? true : false;
		$config['daap']['rescaninterval'] = $_POST['rescaninterval'];
		$config['daap']['alwaysscan'] = isset($_POST['alwaysscan']) ? true : false;
		$config['daap']['skipfirst'] = isset($_POST['skipfirst']) ? true : false;
		$config['daap']['scantype'] = $_POST['scantype'];
		$config['daap']['admin_pw'] = $_POST['admin_pw'];
		write_config();
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service('mt-daapd');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}
$pgtitle = [gtext('Services'),gtext('iTunes/DAAP')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.servername.disabled = endis;
	document.iform.port.disabled = endis;
	document.iform.dbdir.disabled = endis;
	document.iform.dbdirbrowsebtn.disabled = endis;
	document.iform.content.disabled = endis;
	document.iform.contentaddbtn.disabled = endis;
	document.iform.contentchangebtn.disabled = endis;
	document.iform.contentdeletebtn.disabled = endis;
	document.iform.contentdata.disabled = endis;
	document.iform.contentbrowsebtn.disabled = endis;
	document.iform.compdirs.disabled = endis;
	document.iform.concatcomps.disabled = endis;
	document.iform.rescaninterval.disabled = endis;
	document.iform.alwaysscan.disabled = endis;
	document.iform.skipfirst.disabled = endis;
	document.iform.scantype.disabled = endis;
	document.iform.admin_pw.disabled = endis;
}
//-->
</script>
<form action="services_daap.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
				<?php 
				if(!empty($input_errors)):
					print_input_errors($input_errors);
				endif;
				if(!empty($savemsg)):
					print_info_box($savemsg);
				endif;
				if (!isset($config['system']['zeroconf'])):
					$link = '<a href="'
						. 'system_advanced.php'
						. '">'
						. gtext('Zeroconf/Bonjour')
						. '</a>';
					print_error_box(sprintf(gtext('You have to activate %s to advertise this service to clients.'), $link));
				endif;
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("Digital Audio Access Protocol"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");
					html_inputbox("servername", gtext("Name"), $pconfig['servername'], gtext("This is both the name of the server as advertised via Zeroconf/Bonjour/Rendezvous, and the name of the database exported via DAAP."), true, 20);
					html_inputbox("port", gtext("Port"), $pconfig['port'], gtext("Port to listen on. Default iTunes port is 3689."), true, 5);
					html_filechooser("dbdir", gtext("Database Directory"), $pconfig['dbdir'], gtext("Location where the content database file will be stored."), $g['media_path'], true, 60);
					html_folderbox("content", gtext("Content"), !empty($pconfig['content']) ? $pconfig['content'] : [], gtext("Location of the files to share."), $g['media_path'], true);
					html_inputbox("compdirs", gtext("Compilations Directories"), $pconfig['compdirs'], gtext("Tracks whose path contains one or more of these comma separated strings will be treated as a compilation."), false, 40);
					html_checkbox("concatcomps", gtext("Group Compilations"), !empty($pconfig['concatcomps']) ? true : false, "", gtext("Whether compilations should be shown together under Various Artists."), false);
					html_inputbox("rescaninterval", gtext("Rescan Interval"), $pconfig['rescaninterval'], gtext("Scan file system every N seconds to see if any files have been added or removed. Set to 0 to disable background scanning. If background rescanning is disabled, a scan can still be forced from the status page of the administrative web interface."), false, 5);
					html_checkbox("alwaysscan", gtext("Always Scan"), !empty($pconfig['alwaysscan']) ? true : false, "", gtext("Whether scans should be skipped if there are no users connected. This allows the drive to spin down when no users are connected."), false);
					html_checkbox("skipfirst", gtext("Skip First Scan"), !empty($pconfig['skipfirst']) ? true : false, "", gtext("Whether to skip initial boot-up scan."), false);
					html_combobox("scantype", gtext("Scan Mode"), $pconfig['scantype'], ['0' => gtext('Normal'),'1' => gtext('Aggressive'),'2' => gtext('Painfully aggressive')], "", false);
					html_separator();
					html_titleline(gtext("Administrative WebGUI"));
					html_passwordbox("admin_pw", gtext("Password"), $pconfig['admin_pw'], sprintf("%s %s", gtext("Password for the administrative pages."), gtext("Default user name is 'admin'.")), true, 20);
					$if = get_ifname($config['interfaces']['lan']['if']);
					$ipaddr = get_ipaddr($if);
					$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}");
					$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					html_text("url", gtext("URL"), $text);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="onsubmit_content(); enable_change(true)" />
				</div>
				<div id="remarks">
					<?php
					$link = '<a href="'
						. 'system_advanced.php'
						. '">'
						. gtext('Zeroconf/Bonjour')
						. '</a>';
					html_remark("note", gtext('Note'), sprintf(gtext('You have to activate %s to advertise this service to clients.'), $link));
					?>
				</div>
			</td>
		</tr>
	</table>
	<?php include 'formend.inc';?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>
<?php include 'fend.inc';?>
