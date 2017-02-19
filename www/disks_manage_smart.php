<?php
/*
	disks_manage_smart.php

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
require("email.inc");

$pgtitle = array(gtext("Disks"), gtext("Management"), gtext("S.M.A.R.T."));

$pconfig['enable'] = isset($config['smartd']['enable']);
$pconfig['interval'] = $config['smartd']['interval'];
$pconfig['powermode'] = $config['smartd']['powermode'];
$pconfig['temp_diff'] = $config['smartd']['temp']['diff'];
$pconfig['temp_info'] = $config['smartd']['temp']['info'];
$pconfig['temp_crit'] = $config['smartd']['temp']['crit'];
$pconfig['email_enable'] = isset($config['smartd']['email']['enable']);
$pconfig['email_to'] = $config['smartd']['email']['to'];
$pconfig['email_testemail'] = isset($config['smartd']['email']['testemail']);
$pconfig['enablesmartmonondevice'] = isset($config['smartd']['enablesmartmonondevice']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = explode(" ", "interval powermode temp_diff temp_info temp_crit");
		$reqdfieldsn = array(gtext("Check interval"), gtext("Power mode"), gtext("Difference"), gtext("Informal"), gtext("Critical"));
		$reqdfieldst = explode(" ", "numericint string numericint numericint numericint");

		if (isset($_POST['email_enable']) && $_POST['email_enable']) {
			$reqdfields = array_merge($reqdfields, array("email_to"));
			$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("To email")));
			$reqdfieldst = array_merge($reqdfieldst, array("string"));
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		if (10 > $_POST['interval']) {
			$input_errors[] = gtext("Interval must be greater or equal than 10 seconds.");
		}
	}

	if (empty($input_errors)) {
		$config['smartd']['enable'] = isset($_POST['enable']) ? true : false;
		$config['smartd']['interval'] = $_POST['interval'];
		$config['smartd']['powermode'] = $_POST['powermode'];
		$config['smartd']['temp']['diff'] = $_POST['temp_diff'];
		$config['smartd']['temp']['info'] = $_POST['temp_info'];
		$config['smartd']['temp']['crit'] = $_POST['temp_crit'];
		$config['smartd']['email']['enable'] = isset($_POST['email_enable']) ? true : false;
		$config['smartd']['email']['to'] = !empty($_POST['email_to']) ? $_POST['email_to'] : "";
		$config['smartd']['email']['testemail'] = isset($_POST['email_testemail']) ? true : false;
		$config['smartd']['enablesmartmonondevice'] = isset($_POST['enablesmartmonondevice']) ? true : false;

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("smartssd", "smartssd_process_updatenotification");
			config_lock();
			$retval |= rc_update_service("smartd");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("smartssd");
		}
	}
}

if (!isset($config['disks']['disk']) || !is_array($config['disks']['disk']))
	$config['disks']['disk'] = array();

if (!isset($config['smartd']['selftest']) || !is_array($config['smartd']['selftest']))
	$config['smartd']['selftest'] = array();

$a_selftest = &$config['smartd']['selftest'];
$a_type = array( "S" => "Short Self-Test", "L" => "Long Self-Test", "C" => "Conveyance Self-Test", "O" => "Offline Immediate Test");

if (isset($_GET['act']) && $_GET['act'] === "del") {
	if ($_GET['uuid'] === "all") {
		foreach ($a_selftest as $selftestv) {
			updatenotify_set("smartssd", UPDATENOTIFY_MODE_DIRTY, $selftestv['uuid']);
		}
	} else {
		updatenotify_set("smartssd", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	}
	header("Location: disks_manage_smart.php");
	exit;
}

function smartssd_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['smartd']['selftest'])) {
				$index = array_search_ex($data, $config['smartd']['selftest'], "uuid");
				if (false !== $index) {
					unset($config['smartd']['selftest'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);

	if (enable_change.name == "email_enable") {
		endis = !enable_change.checked;

		document.iform.email_to.disabled = endis;
		document.iform.email_testemail.disabled = endis;
	} else {
		document.iform.interval.disabled = endis;
		document.iform.powermode.disabled = endis;
		document.iform.temp_diff.disabled = endis;
		document.iform.temp_info.disabled = endis;
		document.iform.temp_crit.disabled = endis;
		document.iform.email_enable.disabled = endis;
		document.iform.enablesmartmonondevice.disabled = endis;

		if (document.iform.enable.checked == true) {
			endis = !(document.iform.email_enable.checked || enable_change);
		}

		document.iform.email_to.disabled = endis;
		document.iform.email_testemail.disabled = endis;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="disks_manage.php"><span><?=gtext("HDD Management");?></span></a></li>
				<li class="tabinact"><a href="disks_init.php"><span><?=gtext("HDD Format");?></span></a></li>
				<li class="tabact"><a href="disks_manage_smart.php" title="<?=gtext('Reload page');?>"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext("iSCSI Initiator");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="disks_manage_smart.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php
				if (!empty($pconfig['enable']) && !empty($pconfig['email_enable']) && (0 !== email_validate_settings())) {
					$helpinghand = '<a href="'
						. 'system_email.php'
						. '">'
						. gtext('Make sure you have already configured your email settings')
						. '</a>.';
					print_error_box($helpinghand);
				}
				$smart = false;
				foreach ($config['disks']['disk'] as $device) {
					if (isset($device['smart'])) {
						$smart = true;
					}
				}
				if (false === $smart) {
					print_error_box(gtext("Make sure you have activated S.M.A.R.T. for your devices."));
				}
				if (!empty($input_errors)) {
					print_input_errors($input_errors);
				}
				if (!empty($savemsg)) {
					print_info_box($savemsg);
				}
				if (updatenotify_exists("smartssd")) {
					print_config_change_box();
				}
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("Self-Monitoring, Analysis & Reporting Technology"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(this)");
					html_inputbox("interval", gtext("Check interval"), $pconfig['interval'], gtext("Sets the interval between disk checks to N seconds. The minimum allowed value is 10."), true, 5);
					?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Power mode");?></td>
						<td width="78%" class="vtable">
							<select name="powermode" class="formfld" id="powermode">
								<?php $types = explode(" ", "Never Sleep Standby Idle"); $vals = explode(" ", "never sleep standby idle");?>
								<?php $j = 0; for ($j = 0; $j < count($vals); $j++):?>
								<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['powermode']) echo "selected=\"selected\"";?>><?=htmlspecialchars($types[$j]);?></option>
								<?php endfor;?>
							</select>
							<div id="enumeration">
								<ul>
									<li><?=gtext("Never - Poll (check) the device regardless of its power mode. This may cause a disk which is spun-down to be spun-up when it is checked.");?></li>
									<li><?=gtext("Sleep - Check the device unless it is in SLEEP mode.");?></li>
									<li><?=gtext("Standby - Check the device unless it is in SLEEP or STANDBY mode. In these modes most disks are not spinning, so if you want to prevent a laptop disk from spinning up each poll, this is probably what you want.");?></li>
									<li><?=gtext("Idle - Check the device unless it is in SLEEP, STANDBY or IDLE mode. In the IDLE state, most disks are still spinning, so this is probably not what you want.");?></li>
								</ul>
							</div>
						</td>
					</tr>
					<?php
					html_separator();
					html_titleline(gtext("Default Device Settings"));
					html_checkbox("enablesmartmonondevice", gtext("S.M.A.R.T. Monitoring"), !empty($pconfig['enablesmartmonondevice']) ? true : false, gtext("Enable S.M.A.R.T. monitoring of S.M.A.R.T. capable devices when they are added to the configuration."));
					html_separator();
					html_titleline(gtext("Temperature Monitoring"));
					?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Difference");?></td>
						<td width="78%" class="vtable">
							<input name="temp_diff" type="text" class="formfld" id="temp_diff" size="5" value="<?=htmlspecialchars($pconfig['temp_diff']);?>" />&nbsp;&deg;C<br />
							<span class="vexpl"><?=gtext("Report if the temperature had changed by at least N degrees Celsius since last report. Set to 0 to disable this report.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Informal");?></td>
						<td width="78%" class="vtable">
							<input name="temp_info" type="text" class="formfld" id="temp_info" size="5" value="<?=htmlspecialchars($pconfig['temp_info']);?>" />&nbsp;&deg;C<br />
							<span class="vexpl"><?=gtext("Report if the temperature is greater or equal than N degrees Celsius. Set to 0 to disable this report.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Critical");?></td>
						<td width="78%" class="vtable">
							<input name="temp_crit" type="text" class="formfld" id="temp_crit" size="5" value="<?=htmlspecialchars($pconfig['temp_crit']);?>" />&nbsp;&deg;C<br />
							<span class="vexpl"><?=gtext("Report if the temperature is greater or equal than N degrees Celsius. Set to 0 to disable this report.");?></span>
						</td>
					</tr>
					<?php html_separator();?>
					<?php html_titleline(gtext("Self-tests Management"));?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Scheduled tests");?></td>
						<td width="78%" class="vtable">
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td width="20%" class="listhdrlr"><?=gtext("Disk");?></td>
									<td width="30%" class="listhdrr"><?=gtext("Type");?></td>
									<td width="40%" class="listhdrr"><?=gtext("Description");?></td>
									<td width="10%" class="list"></td>
								</tr>
								<?php foreach($a_selftest as $selftest):?>
									<?php $notificationmode = updatenotify_get_mode("smartssd", $selftest['uuid']);?>
									<tr>
										<td class="listlr"><?=htmlspecialchars($selftest['devicespecialfile']);?>&nbsp;</td>
										<td class="listr"><?=gtext($a_type[$selftest['type']]);?>&nbsp;</td>
										<td class="listr"><?=htmlspecialchars($selftest['desc']);?>&nbsp;</td>
										<?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
											<td valign="middle" nowrap="nowrap" class="list">
												<a href="disks_manage_smart_edit.php?uuid=<?=$selftest['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit self-test");?>" border="0" alt="<?=gtext("Edit self-test");?>" /></a>
												<a href="disks_manage_smart.php?act=del&amp;uuid=<?=$selftest['uuid'];?>" onclick="return confirm('<?=gtext("Do you really want to delete this scheduled self-test?");?>')"><img src="images/delete.png" title="<?=gtext("Delete self-test");?>" border="0" alt="<?=gtext("Delete self-test");?>" /></a>
											</td>
										<?php else:?>
											<td valign="middle" nowrap="nowrap" class="list">
												<img src="images/delete.png" border="0" alt="" />
											</td>
										<?php endif;?>
									</tr>
								<?php endforeach;?>
								<tr>
									<td class="list" colspan="3"></td>
									<td class="list">
										<a href="disks_manage_smart_edit.php"><img src="images/add.png" title="<?=gtext("Add self-test");?>" border="0" alt="<?=gtext("Add self-test");?>" /></a>
										<?php if (!empty($a_selftest)):?>
											<a href="disks_manage_smart.php?act=del&amp;uuid=all" onclick="return confirm('<?=gtext("Do you really want to delete all scheduled self-tests?");?>')"><img src="images/delete.png" title="<?=gtext("Delete all self-tests");?>" border="0" alt="<?=gtext("Delete all self-tests");?>" /></a>
										<?php endif;?>
									</td>
								</tr>
							</table>
							<span class="vexpl"><?=gtext("Add additional scheduled self-test.");?></span>
						</td>
					</tr>
					<?php
					html_separator();
					html_titleline_checkbox("email_enable", gtext("Email Report"), !empty($pconfig['email_enable']) ? true : false, gtext("Activate"), "enable_change(this)");
					html_inputbox("email_to", gtext("To email"), !empty($pconfig['email_to']) ? $pconfig['email_to'] : "", sprintf("%s %s", gtext("Destination email address."), gtext("Separate email addresses by semi-colon.")), true, 40);
					html_checkbox("email_testemail", gtext("Test email"), !empty($pconfig['email_testemail']) ? true : false, gtext("Send a TEST warning email on startup."));
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save and Restart");?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
					<?php html_remark("note", gtext("Note"), gtext("Activate email report if you want to be notified if a failure or a new error has been detected, or if a S.M.A.R.T. command to a disk fails."));?>
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
