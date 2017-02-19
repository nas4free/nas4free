<?php
/*
	services_unison.php

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

*
	Unison Installation Notes

	To work, unison requires an environment variable UNISON to point at
	a writable directory. Unison keeps information there between syncs to
	speed up the process.

	When a user runs the unison client, it will try to invoke ssh to
	connect to the this server. Giving the local ssh a UNISON environment
	variable without compromising ssh turned out to be non-trivial.
	The solution is to modify the default path found in /etc/login.conf.
	The path is seeded with "UNISON=/mnt" and this updated by the
	/etc/rc.d/unison file.

	Todo:
	* 	Arguably, a full client install could be done too to
	allow NAS4Free to NAS4Free syncing.
*/
require 'auth.inc';
require 'guiconfig.inc';

array_make_branch($config,'unison');
$pconfig['enable'] = isset($config['unison']['enable']);
$pconfig['workdir'] = $config['unison']['workdir'];
$pconfig['mkdir'] = isset($config['unison']['mkdir']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;
	// Input validation
	$reqdfields = [];
	$reqdfieldsn = [];
	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = array_merge($reqdfields, ['workdir']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Work Directory')]);
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		// Check if working directory exists
		if (empty($_POST['mkdir']) && !file_exists($_POST['workdir'])) {
			$input_errors[] = gtext("The work directory does not exist.");
		}
	}
	if (empty($input_errors)) {
		$config['unison']['workdir'] = $_POST['workdir'];
		$config['unison']['enable'] = isset($_POST['enable']) ? true : false;
		$config['unison']['mkdir'] = isset($_POST['mkdir']) ? true : false;
		write_config();
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("unison");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
array_make_branch($config,'mounts','mount');
array_sort_key($config['mounts']['mount'], "devicespecialfile");
$a_mount = &$config['mounts']['mount'];
$pgtitle = [gtext('Services'),gtext('Unison')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.workdir.disabled = endis;
	document.iform.mkdir.disabled = endis;
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_unison.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>	    
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox('enable', gtext("Unison File Synchronisation"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");
					html_filechooser("workdir", gtext("Work Directory"), $pconfig['workdir'], sprintf(gtext("Location where the work files will be stored, e.g. %s/backup/.unison"), $g['media_path']), $g['media_path'], true, 60);
					html_checkbox("mkdir", "", !empty($pconfig['mkdir']) ? true : false, gtext("Create work directory if it doesn't exist."), "", false);
					?>
				</table>
				<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save and Restart");?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
					<?php
					$helpinghand = gtext('Before a Unison Client can start to work, you need to perform the following:')
					. '<div id="enumeration"><ul>'
					. '<li>' . '<a href="' . 'services_sshd.php' . '">' . gtext('Enable service SSH when disabled') . '</a>.' . '</li>'
					. '<li>' . '<a href="' . 'access_users.php' . '">' . gtext('Setup user to get shell access') . '</a>.' . '</li>'
					. '</ul></div>';
					html_remark("note", gtext('Note'), $helpinghand );
					?>
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
