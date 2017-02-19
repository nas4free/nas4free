<?php
/*
	system_swap.php

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

$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Swap')];

$pconfig['enable'] = isset($config['system']['swap']['enable']);
$pconfig['type'] = $config['system']['swap']['type'];
$pconfig['mountpoint'] = !empty($config['system']['swap']['mountpoint']) ? $config['system']['swap']['mountpoint'] : "";
$pconfig['devicespecialfile'] = !empty($config['system']['swap']['devicespecialfile']) ? $config['system']['swap']['devicespecialfile'] : "";
$pconfig['size'] = !empty($config['system']['swap']['size']) ? $config['system']['swap']['size'] : "";

//$swapdevice = "NONE";
//if (file_exists("{$g['etc_path']}/swapdevice"))
//	$swapdevice = trim(file_get_contents("{$g['etc_path']}/swapdevice"));
//if (empty($_POST) && (empty($pconfig['enable']) || $pconfig['enable'] === false)) {
//	if ($swapdevice != "NONE")
//		$infomsg = sprintf("%s (%s)", gtext("This server uses default swap."), $swapdevice);
//}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['enable'])) {
		$reqdfields = ['type'];
		$reqdfieldsn = [gtext('Type')];
		$reqdfieldst = ['string'];

		if ("device" === $_POST['type']) {
			$reqdfields = array_merge($reqdfields, ['devicespecialfile']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Device')]);
			$reqdfieldst = array_merge($reqdfieldst, ['string']);
		} else {
			$reqdfields = array_merge($reqdfields, ['mountpoint','size']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Mount Point'),gtext('Size')]);
			$reqdfieldst = array_merge($reqdfieldst, ['string','numeric']);
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	if (empty($input_errors)) {
		$config['system']['swap']['enable'] = isset($_POST['enable']) ? true : false;
		$config['system']['swap']['type'] = $_POST['type'];
		$config['system']['swap']['mountpoint'] = $_POST['mountpoint'];
		$config['system']['swap']['devicespecialfile'] = $_POST['devicespecialfile'];
		$config['system']['swap']['size'] = $_POST['size'];

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("swap");
			config_unlock();
			if (isset($_POST['enable']) && (false !== preg_match('/\S/', $_POST['devicespecialfile']))) {
				$cmd = sprintf('swapon %s', escapeshellarg($_POST['devicespecialfile']));
				write_log($cmd);
				mwexec($cmd);
			}
		}
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.type.disabled = endis;
	document.iform.mountpoint.disabled = endis;
	document.iform.size.disabled = endis;
	document.iform.devicespecialfile.disabled = endis;
}

function type_change() {
	switch (document.iform.type.value) {
	case "file":
		showElementById('mountpoint_tr','show');
		showElementById('size_tr','show');
		showElementById('devicespecialfile_tr','hide');
		break;

	case "device":
		showElementById('mountpoint_tr','hide');
		showElementById('size_tr','hide');
		showElementById('devicespecialfile_tr','show');
		break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="system_advanced.php"><span><?=gtext("Advanced");?></span></a></li>
			<li class="tabinact"><a href="system_email.php"><span><?=gtext("Email");?></span></a></li>
			<li class="tabinact"><a href="system_email_reports.php"><span><?=gtext("Email Reports");?></span></a></li>
			<li class="tabinact"><a href="system_monitoring.php"><span><?=gtext("Monitoring");?></span></a></li>
			<li class="tabact"><a href="system_swap.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Swap");?></span></a></li>
			<li class="tabinact"><a href="system_rc.php"><span><?=gtext("Command Scripts");?></span></a></li>
			<li class="tabinact"><a href="system_cron.php"><span><?=gtext("Cron");?></span></a></li>
			<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext("loader.conf");?></span></a></li>
			<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext("rc.conf");?></span></a></li>
			<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext("sysctl.conf");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="system_swap.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<?php if (!empty($infomsg)) print_info_box($infomsg); ?>
				<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Swap Memory"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
					<?php $swapinfo = system_get_swap_info(); if (!empty($swapinfo)):?>
					<tr>
					<td width="25%" class="vncellt"><?=gtext("This server uses default swap!");?></td>
					<td width="75%" style="background-color:#EEEEEE;" class="listr">
						<table width="100%" border="0" cellspacing="10" cellpadding="1">
							<?php
							array_sort_key($swapinfo, "device");
							$ctrlid = 0;
							foreach ($swapinfo as $swapk => $swapv) {
								$ctrlid++;
								$percent_used = rtrim($swapv['capacity'], "%");
								$tooltip_used = sprintf(gtext("%sB used of %sB"), $swapv['used'], $swapv['total']);
								$tooltip_available = sprintf(gtext("%sB available of %sB"), $swapv['avail'], $swapv['total']);

								echo "<tr><td><div id='swapusage'>";
								echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
								echo "<img src='images/bar_blue.gif' name='swapusage_{$ctrlid}_bar_used' id='swapusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
								echo "<img src='images/bar_gray.gif' name='swapusage_{$ctrlid}_bar_free' id='swapusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_available}' alt='' />";
								echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
								echo sprintf(gtext("%s of %sB"),
									"<span name='swapusage_{$ctrlid}_capacity' id='swapusage_{$ctrlid}_capacity' class='capacity'>{$swapv['capacity']}</span>",
									$swapv['total']);
								echo "<br />";
								echo sprintf(gtext("Device: %s | Total: %s | Used: %s | Free: %s"),
									"<span name='swapusage_{$ctrlid}_device' id='swapusage_{$ctrlid}_device' class='device'>{$swapv['device']}</span>",
									"<span name='swapusage_{$ctrlid}_total' id='swapusage_{$ctrlid}_total' class='total'>{$swapv['total']}</span>",
									"<span name='swapusage_{$ctrlid}_used' id='swapusage_{$ctrlid}_used' class='used'>{$swapv['used']}</span>",
									"<span name='swapusage_{$ctrlid}_free' id='swapusage_{$ctrlid}_free' class='free'>{$swapv['avail']}</span>");
								echo "</div></td></tr>";

								if ($ctrlid < count($swapinfo))
										echo "<tr><td><hr size='1' /></td></tr>";
							}?>
						</table>
					</td>
				</tr>
				<?php endif;?>
				<tr>
					<?php html_combobox("type", gtext("Type"), $pconfig['type'], array("file" => gtext("File"), "device" => gtext("Device")), "", true, false, "type_change()");?>
					<?php html_mountcombobox("mountpoint", gtext("Mount Point"), $pconfig['mountpoint'], gtext("Select mount point where to create the swap file."), true);?>
					<?php html_inputbox("size", gtext("Size"), $pconfig['size'], gtext("The size of the swap file in MB."), true, 10);?>
					<?php html_inputbox("devicespecialfile", gtext("Device"), $pconfig['devicespecialfile'], sprintf(gtext("Name of the device to use as swap device, e.g. %s."), "/dev/da0s2b"), true, 20);?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" onclick="enable_change(true)" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(false);
type_change(false);
//-->
</script>
<?php include 'fend.inc';?>
