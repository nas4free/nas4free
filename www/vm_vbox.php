<?php
/*
	vm_vbox.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gettext("VM"), gettext("VirtualBox"));

$pconfig['enable'] = isset($config['vbox']['enable']);
$pconfig['homedir'] = $config['vbox']['homedir'];

if ($_POST) {
	unset($input_errors);
	unset($errormsg);

	$pconfig = $_POST;

	if (isset($_POST['enable'])) {
		$reqdfields = explode(" ", "homedir");
		$reqdfieldsn = array(gettext("Home directory"));
		$reqdfieldst = explode(" ", "string");
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	} else {
		// disable VirtualBox
		config_lock();
		$retval |= rc_exec_script("/etc/rc.d/vbox onestop");
		config_unlock();
	}

	if (empty($input_errors)) {
		$config['vbox']['enable'] = isset($_POST['enable']) ? true : false;
		$config['vbox']['homedir'] = $_POST['homedir'];

		$dir = $config['vbox']['homedir'];
		if ($dir == '' || !file_exists($dir))
			$dir = "/nonexistent";

		// update homedir
		$user = "vboxusers";
		$group = "vboxusers";
		$opt = "-c \"Virtualbox user\" -d \"{$dir}\" -s /usr/sbin/nologin";
		$index = array_search_ex($user, $config['system']['usermanagement']['user'], "name");
		if ($index != false) {
			$config['system']['usermanagement']['user'][$index]['extraoptions'] = $opt;
		}

		write_config();
		$retval = 0;
		config_lock();
		$retval |= rc_exec_service("userdb");
		config_unlock();

		if ($dir != "/nonexistent" && file_exists($dir)) {
			// adjust permission
			chmod($dir, 0755);
			chown($dir, $user);
			chgrp($dir, $group);

			// update auth method
			$cmd = "/usr/local/bin/sudo -u {$user} /usr/local/bin/VBoxManage setproperty websrvauthlibrary null";
			mwexec2("$cmd 2>&1", $rawdata, $result);

			if (!file_exists($d_sysrebootreqd_path)) {
				config_lock();
				$retval |= rc_update_service("vbox");
				config_unlock();
			}
		}

		$savemsg = get_std_save_message($retval);
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
			$('#a_url1').on('click', function(){ return false; });
			$('#a_url2').on('click', function(){ return false; });
		} else {
			$('#a_url1').off('click');
			$('#a_url2').off('click');
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
      <form action="vm_vbox.php" method="post" name="iform" id="iform">
	<?php if (!empty($errormsg)) print_error_box($errormsg);?>
	<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
	<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<?php html_titleline_checkbox("enable", gettext("VirtualBox"), !empty($pconfig['enable']) ? true : false, gettext("Enable"), "");?>
	<?php html_filechooser("homedir", gettext("Home directory"), $pconfig['homedir'], gettext("Enter the path to the home directory of VirtualBox. VM config and HDD image will be created under the specified directory."), $g['media_path'], false, 60);?>
	<?php html_separator();?>
	<?php html_titleline(sprintf("%s (%s)", gettext("Administrative WebGUI"), gettext("phpVirtualBox")));?>
	<?php
		$if = get_ifname($config['interfaces']['lan']['if']);
		$ipaddr = get_ipaddr($if);
		$url = htmlspecialchars("/phpvirtualbox/index.html");
		$text = "<a href='${url}' id='a_url1' target='_blank'>{$url}</a>";
	?>
	<?php html_text("url1", gettext("URL"), $text);?>
	<?php html_separator();?>
	<?php html_titleline(sprintf("%s (%s)", gettext("Administrative WebGUI"), gettext("noVNC")));?>
	<?php
		$url = htmlspecialchars("/novnc/vnc.html");
		$text = "<a href='${url}?host=$ipaddr' id='a_url2' target='_blank'>{$url}</a>";
	?>
	<?php html_text("url2", gettext("URL"), $text);?>
	</table>
	<div id="submit">
	  <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save and Restart");?>" />
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
