<?php
/*
	interfaces_wlan_edit.php

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
require("interfaces.inc");

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = array(gettext("Network"), gettext("Interface Management"), gettext("WLAN"), isset($uuid) ? gettext("Edit") : gettext("Add"));

if (!isset($config['vinterfaces']['wlan']) || !is_array($config['vinterfaces']['wlan']))
	$config['vinterfaces']['wlan'] = array();

$a_wlans = &$config['vinterfaces']['wlan'];
array_sort_key($a_wlans, "if");

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_wlans, "uuid")))) {
	$pconfig['enable'] = isset($a_wlans[$cnid]['enable']);
	$pconfig['uuid'] = $a_wlans[$cnid]['uuid'];
	$pconfig['if'] = $a_wlans[$cnid]['if'];
	$pconfig['wlandev'] = $a_wlans[$cnid]['wlandev'];
	$pconfig['desc'] = $a_wlans[$cnid]['desc'];

	$pconfig['apmode'] = isset($a_wlans[$cnid]['apmode']);
	$pconfig['ap_ssid'] = $a_wlans[$cnid]['ap_ssid'];
	$pconfig['ap_channel'] = $a_wlans[$cnid]['ap_channel'];
	$pconfig['ap_encryption'] = $a_wlans[$cnid]['ap_encryption'];
	$pconfig['ap_keymgmt'] = $a_wlans[$cnid]['ap_keymgmt'];
	$pconfig['ap_pairwise'] = $a_wlans[$cnid]['ap_pairwise'];
	$pconfig['ap_psk'] = $a_wlans[$cnid]['ap_psk'];
	$pconfig['ap_extraoptions'] = $a_wlans[$cnid]['ap_extraoptions'];
	$pconfig['auxparam'] = "";
	if (isset($a_wlans[$cnid]['auxparam']) && is_array($a_wlans[$cnid]['auxparam']))
		$pconfig['auxparam'] = implode("\n", $a_wlans[$cnid]['auxparam']);
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['if'] = "wlan" . get_nextwlan_id();
	$pconfig['wlandev'] = "";
	$pconfig['desc'] = "";

	$pconfig['apmode'] = false;
	$pconfig['ap_ssid'] = "";
	$pconfig['ap_channel'] = "1";
	$pconfig['ap_encryption'] = "wpa";
	$pconfig['ap_keymgmt'] = "WPA-PSK";
	$pconfig['ap_pairwise'] = "CCMP";
	$pconfig['ap_psk'] = "";
	$pconfig['ap_extraoptions'] = "";
	$pconfig['auxparam'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: interfaces_wlan.php");
		exit;
	}

	// Input validation.
	$reqdfields = explode(" ", "wlandev");
	$reqdfieldsn = array(gettext("Physical interface"));
	$reqdfieldst = explode(" ", "string numeric");

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	if (isset($_POST['apmode'])) {
		$reqdfields = explode(" ", "ap_ssid ap_channel ap_psk");
		$reqdfieldsn = array(gettext("SSID"), gettext("Channel"), gettext("PSK"));
		$reqdfieldst = explode(" ", "string string string");

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		if (preg_match("/\ |,|\'|\"/", $_POST['ap_ssid']))
			$input_errors[] = sprintf(gettext("The attribute '%s' contains invalid characters."), gettext("SSID"));
		if (preg_match("/\ |,|\'|\"/", $_POST['ap_channel']))
			$input_errors[] = sprintf(gettext("The attribute '%s' contains invalid characters."), gettext("Channel"));
		if (!empty($_POST['ap_psk']) && (strlen($_POST['ap_psk']) < 8 || strlen($_POST['ap_psk']) > 63)) {
			$input_errors[] = sprintf(gettext("The attribute '%s' is required within %d or more characters to %d characters."), gettext("PSK"), 8, 63);
		}
	}

	if (empty($input_errors)) {
		$wlan = array();
		$wlan['enable'] = !empty($_POST['enable']) ? true : false;
		$wlan['uuid'] = $_POST['uuid'];
		$wlan['if'] = $_POST['if'];
		$wlan['wlandev'] = $_POST['wlandev'];
		$wlan['desc'] = $_POST['desc'];

		$wlan['apmode'] = isset($_POST['apmode']) ? true : false;
		$wlan['ap_ssid'] = $_POST['ap_ssid'];
		$wlan['ap_channel'] = $_POST['ap_channel'];
		$wlan['ap_encryption'] = $_POST['ap_encryption'];
		$wlan['ap_keymgmt'] = $_POST['ap_keymgmt'];
		$wlan['ap_pairwise'] = $_POST['ap_pairwise'];
		$wlan['ap_extraoptions'] = $_POST['ap_extraoptions'];
		$wlan['ap_psk'] = $_POST['ap_psk'];

		// Write additional parameters.
		unset($wlan['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$wlan['auxparam'][] = $auxparam;
		}

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_wlans[$cnid] = $wlan;
		} else {
			$a_wlans[] = $wlan;
		}

		write_config();
		touch($d_sysrebootreqd_path);

		header("Location: interfaces_wlan.php");
		exit;
	}
}

function get_nextwlan_id() {
	global $config;

	$id = 0;
	$a_wlan = $config['vinterfaces']['wlan'];

	if (false !== array_search_ex("wlan" . strval($id), $a_wlan, "if")) {
		do {
			$id++; // Increase ID until a unused one is found.
		} while (false !== array_search_ex("wlan" . strval($id), $a_wlan, "if"));
	}

	return $id;
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function apmode_change(apmode_change) {
		var val = !($('#apmode').prop('checked') || apmode_change);
		$('#ap_ssid').prop('disabled', val);
		$('#ap_channel').prop('disabled', val);
		$('#ap_encryption').prop('disabled', val);
		$('#ap_keymgmt').prop('disabled', val);
		$('#ap_pairwise').prop('disabled', val);
		$('#ap_psk').prop('disabled', val);
		$('#ap_extraoptions').prop('disabled', val);
		$('#auxparam').prop('disabled', val);
	}
	$('#apmode').click(function(){
		apmode_change(false);
	});
	$('input:submit').click(function(){
		apmode_change(true);
	});
	apmode_change(false);
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="interfaces_assign.php"><span><?=gettext("Management");?></span></a></li>
			<li class="tabact"><a href="interfaces_wlan.php" title="<?=gettext("Reload page");?>"><span><?=gettext("WLAN");?></span></a></li>
			<li class="tabinact"><a href="interfaces_vlan.php"><span><?=gettext("VLAN");?></span></a></li>
			<li class="tabinact"><a href="interfaces_lagg.php"><span><?=gettext("LAGG");?></span></a></li>
			<li class="tabinact"><a href="interfaces_bridge.php"><span><?=gettext("Bridge");?></span></a></li>
			<li class="tabinact"><a href="interfaces_carp.php"><span><?=gettext("CARP");?></span></a></li>
		</ul>
	</td>
</tr>
<tr>
	<td class="tabcont">
		<form action="interfaces_wlan_edit.php" method="post" name="iform" id="iform">
			<?php if ($input_errors) print_input_errors($input_errors);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php $a_if = array(); foreach (get_interface_wlist() as $ifk => $ifv) { if (preg_match('/wlan/i', $ifk)) { continue; } $a_if[$ifk] = htmlspecialchars("{$ifk} ({$ifv['mac']})"); };?>
				<?php html_combobox("wlandev", gettext("Physical interface"), $pconfig['wlandev'], $a_if, "", true);?>
				<?php html_inputbox("desc", gettext("Description"), $pconfig['desc'], gettext("You may enter a description here for your reference."), false, 40);?>
				<?php html_separator();?>
				<?php html_titleline_checkbox("apmode", gettext("AP mode"), !empty($pconfig['apmode']) ? true : false, gettext("Enable"), "");?>
				<?php html_inputbox("ap_ssid", gettext("SSID"), $pconfig['ap_ssid'], gettext("Set the desired Service Set Identifier (aka network name)."), true, 20);?>
				<?php html_inputbox("ap_channel", gettext("Channel"), $pconfig['ap_channel'], "", true, 10);?>
				<?php html_combobox("ap_encryption", gettext("Encryption"), $pconfig['ap_encryption'], array("wpa" => sprintf("%s / %s", gettext("WPA"), gettext("WPA2"))), "", true, false, "encryption_change()");?>
				<?php html_combobox("ap_keymgmt", gettext("Key Management Protocol"), $pconfig['ap_keymgmt'], array("WPA-PSK" => gettext("WPA-PSK (Pre Shared Key)")), "", true);?>
				<?php html_combobox("ap_pairwise", gettext("Pairwise"), $pconfig['ap_pairwise'], array("CCMP" => gettext("CCMP"), "CCMP TKIP" => gettext("CCMP TKIP")), "", true);?>
				<?php html_passwordbox("ap_psk", gettext("PSK"), $pconfig['ap_psk'], gettext("Enter the passphrase that will be used in WPA-PSK mode. This must be between 8 and 63 characters long."), true, 40);?>
				<?php html_inputbox("ap_extraoptions", gettext("Extra options"), $pconfig['ap_extraoptions'], gettext("Extra options to ifconfig (usually empty)."), false, 60);?>
				<?php html_textarea("auxparam", gettext("Auxiliary parameters"), $pconfig['auxparam'], sprintf(gettext("These parameters are added to %s."), "hostapd.conf") . " " . sprintf(gettext("Please check the <a href='%s' target='_blank'>documentation</a>."), "http://www.freebsd.org/cgi/man.cgi?query=hostapd.conf"), false, 65, 5, false, false);?>
			</table>
			<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gettext("Save") : gettext("Add")?>" />
				<input name="Cancel" type="submit" class="formbtn" value="<?=gettext("Cancel");?>" />
				<input name="enable" type="hidden" value="<?=$pconfig['enable'];?>" />
				<input name="if" type="hidden" value="<?=$pconfig['if'];?>" />
				<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
			</div>
			<?php include("formend.inc");?>
		</form>
	</td>
</tr>
</table>
<?php include("fend.inc");?>
