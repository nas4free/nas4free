<?php
/*
	services_syncthing.php

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

$pgtitle = array(gtext("Services"),gtext("Syncthing"));

if (!isset($config['syncthing']) || !is_array($config['syncthing']))
	$config['syncthing'] = array();

$pconfig['enable'] = isset($config['syncthing']['enable']);
$pconfig['homedir'] = $config['syncthing']['homedir'];

$if = get_ifname($config['interfaces']['lan']['if']);
$gui_ipaddr = get_ipaddr($if);
$gui_port = 8384;
$syncthing_user = rc_getenv_ex("syncthing_user", "syncthing");
$syncthing_group = rc_getenv_ex("syncthing_group", "syncthing");

if ($_POST) {
	unset($input_errors);
	unset($errormsg);

	$pconfig = $_POST;

	if (isset($_POST['enable'])) {
		$reqdfields = explode(" ", "homedir");
		$reqdfieldsn = array(gtext("Home directory"));
		$reqdfieldst = explode(" ", "string");
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	$dir = $_POST['homedir'];
	if (!file_exists($dir)) {
		$input_errors[] = sprintf(gtext("The path '%s' does not exist."), $dir);
	}
	if (!is_dir($dir)) {
		$input_errors[] = sprintf(gtext("The path '%s' is not a directory."), $dir);
	}

	if (empty($input_errors)) {
		$config['syncthing']['enable'] = isset($_POST['enable']) ? true : false;
		$config['syncthing']['homedir'] = $_POST['homedir'];

		$dir = $_POST['homedir'];
		if ($dir != "/mnt" && file_exists($dir) && !file_exists("${dir}/config.xml")) {
			$user = $syncthing_user;
			$group = $syncthing_group;

			chmod($dir, 0700);
			chown($dir, $user);
			chgrp($dir, $group);

			// create default config
			$cmd = "/usr/local/bin/sudo -u {$user} /usr/local/bin/syncthing -generate=\"{$dir}\"";
			mwexec2("$cmd 2>&1", $rawdata, $result);
			// fix GUI address
			$cmd = "/usr/local/bin/sudo -u {$user} /usr/bin/sed -i '' 's/127.0.0.1:8384/${gui_ipaddr}:${gui_port}/' ${dir}/config.xml";
			mwexec2("$cmd 2>&1", $rawdata, $result);
		}

		write_config();
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("syncthing");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0 && !isset($config['syncthing']['enable']) && file_exists("/var/run/syncthing.pid")) {
			// remove pidfile if service is disabled
			unlink("/var/run/syncthing.pid");
		}
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var endis = !($('#enable').prop('checked') || enable_change);
		$('#homedir').prop('disabled', endis);
		$('#homedirbrowsebtn').prop('disabled', endis);
		if (endis) {
			$('#a_url').on('click', function(){ return false; });
		} else {
			$('#a_url').off('click');
		}
	}
	$('#enable').click(function(){
		enable_change(false);
	});
	$('input:submit').click(function(){
		enable_change(true);
	});
	enable_change(false);
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabcont">
      <form action="services_syncthing.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<?php if (!empty($errormsg)) print_error_box($errormsg);?>
	<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
	<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<?php html_titleline_checkbox("enable", gtext("Syncthing"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "");?>
	<?php html_filechooser("homedir", gtext("Home directory"), $pconfig['homedir'], gtext("Enter the path to the home directory. The config will be created under the specified directory."), $g['media_path'], false, 60);?>
	<?php html_separator();?>
	<?php html_titleline(gtext("Administrative WebGUI"));?>
	<?php
		$url = "http://${gui_ipaddr}:${gui_port}/";
		$text = "<a href='${url}' id='a_url' target='_blank'>{$url}</a>";
	?>
	<?php html_text("url", gtext("URL"), $text);?>
	</table>
	<div id="submit">
	  <input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" />
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
