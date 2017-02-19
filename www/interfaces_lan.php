<?php
/*
	interfaces_lan.php

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

$pgtitle = array(gtext("Network"), gtext("LAN Management"));

$lancfg = &$config['interfaces']['lan'];
$optcfg = &$config['interfaces']['lan']; // Required for WLAN.

// Get interface informations.
$ifinfo = get_interface_info(get_ifname($lancfg['if']));

if (strcmp($lancfg['ipaddr'], "dhcp") == 0) {
	$pconfig['type'] = "DHCP";
	$pconfig['ipaddr'] = get_ipaddr($lancfg['if']);
	$pconfig['subnet'] = get_subnet_bits($lancfg['if']);
} else {
	$pconfig['type'] = "Static";
	$pconfig['ipaddr'] = $lancfg['ipaddr'];
	$pconfig['subnet'] = $lancfg['subnet'];
}
$pconfig['ipv6_enable'] = isset($lancfg['ipv6_enable']);
if (strcmp($lancfg['ipv6addr'], "auto") == 0) {
	$pconfig['ipv6type'] = "Auto";
	$pconfig['ipv6addr'] = get_ipv6addr($lancfg['if']);
} else {
	$pconfig['ipv6type'] = "Static";
	$pconfig['ipv6addr'] = $lancfg['ipv6addr'];
	$pconfig['ipv6subnet'] = $lancfg['ipv6subnet'];
}
$pconfig['gateway'] = get_defaultgateway();
$pconfig['ipv6gateway'] = get_ipv6defaultgateway();
$pconfig['mtu'] = !empty($lancfg['mtu']) ? $lancfg['mtu'] : "";
$pconfig['media'] = !empty($lancfg['media']) ? $lancfg['media'] : "autoselect";
$pconfig['mediaopt'] = !empty($lancfg['mediaopt']) ? $lancfg['mediaopt'] : "";
$pconfig['polling'] = isset($lancfg['polling']);
$pconfig['extraoptions'] = !empty($lancfg['extraoptions']) ? $lancfg['extraoptions'] : "";
if (!empty($ifinfo['wolevents']))
	$pconfig['wakeon'] = $lancfg['wakeon'];

/* Wireless interface? */
if (isset($lancfg['wireless'])) {
	require("interfaces_wlan.inc");
	wireless_config_init();
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	$reqdfields = array();
	$reqdfieldsn = array();
	$reqdfieldst = array();

	if ($_POST['type'] === "Static") {
		$reqdfields = explode(" ", "ipaddr subnet");
		$reqdfieldsn = array(gtext("IP address"),gtext("Subnet bit count"));

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		if (($_POST['ipaddr'] && !is_ipv4addr($_POST['ipaddr'])))
			$input_errors[] = gtext("A valid IPv4 address must be specified.");
		if ($_POST['subnet'] && !filter_var($_POST['subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32))))
			$input_errors[] = gtext("A valid network bit count (1-32) must be specified.");
	}

	if (isset($_POST['ipv6_enable']) && $_POST['ipv6_enable'] && ($_POST['ipv6type'] === "Static")) {
		$reqdfields = explode(" ", "ipv6addr ipv6subnet");
		$reqdfieldsn = array(gtext("IPv6 address"),gtext("Prefix"));

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		if (($_POST['ipv6addr'] && !is_ipv6addr($_POST['ipv6addr'])))
			$input_errors[] = gtext("A valid IPv6 address must be specified.");
		if ($_POST['ipv6subnet'] && !filter_var($_POST['ipv6subnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 128))))
			$input_errors[] = gtext("A valid prefix (1-128) must be specified.");
		if (($_POST['ipv6gatewayr'] && !is_ipv6addr($_POST['ipv6gateway'])))
			$input_errors[] = gtext("A valid IPv6 Gateway address must be specified.");
	}

	// Wireless interface?
	if (isset($lancfg['wireless'])) {
		$wi_input_errors = wireless_config_post();
		if ($wi_input_errors) {
			if (is_array($input_errors))
				$input_errors = array_merge($input_errors, $wi_input_errors);
			else
				$input_errors = $wi_input_errors;
		}
	}

	if (!$input_errors) {
		if (0 == strcmp($_POST['type'],"Static")) {
			$lancfg['ipaddr'] = $_POST['ipaddr'];
			$lancfg['subnet'] = $_POST['subnet'];
			$lancfg['gateway'] = $_POST['gateway'];
		} else if (0 == strcmp($_POST['type'],"DHCP")) {
			$lancfg['ipaddr'] = "dhcp";
		}

		$lancfg['ipv6_enable'] = isset($_POST['ipv6_enable']) ? true : false;

		if (0 == strcmp($_POST['ipv6type'],"Static")) {
			$lancfg['ipv6addr'] = $_POST['ipv6addr'];
			$lancfg['ipv6subnet'] = $_POST['ipv6subnet'];
			$lancfg['ipv6gateway'] = $_POST['ipv6gateway'];
		} else if (0 == strcmp($_POST['ipv6type'],"Auto")) {
			$lancfg['ipv6addr'] = "auto";
		}

		$lancfg['mtu'] = $_POST['mtu'];
		$lancfg['media'] = $_POST['media'];
		$lancfg['mediaopt'] = $_POST['mediaopt'];
		$lancfg['polling'] = isset($_POST['polling']) ? true : false;
		$lancfg['extraoptions'] = $_POST['extraoptions'];
		if (!empty($ifinfo['wolevents']))
			$lancfg['wakeon'] = $_POST['wakeon'];

		write_config();
		touch($d_sysrebootreqd_path);
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.ipv6_enable.checked || enable_change);

	if (enable_change.name == "ipv6_enable") {
		endis = !enable_change.checked;

		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
		document.iform.ipv6gateway.disabled = endis;
	} else {
		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
		document.iform.ipv6gateway.disabled = endis;
	}

	ipv6_type_change();
}

function type_change() {
	switch (document.iform.type.selectedIndex) {
		case 0: /* Static */
			document.iform.ipaddr.disabled = 0;
			document.iform.subnet.disabled = 0;
			document.iform.gateway.disabled = 0;
			break;

		case 1: /* DHCP */
			document.iform.ipaddr.disabled = 1;
			document.iform.subnet.disabled = 1;
			document.iform.gateway.disabled = 1;
			break;
	}
}

function ipv6_type_change() {
	switch (document.iform.ipv6type.selectedIndex) {
		case 0: /* Static */
			var endis = !(document.iform.ipv6_enable.checked);

			document.iform.ipv6addr.disabled = endis;
			document.iform.ipv6subnet.disabled = endis;
			document.iform.ipv6gateway.disabled = endis;
			break;

		case 1: /* Autoconfigure */
			document.iform.ipv6addr.disabled = 1;
			document.iform.ipv6subnet.disabled = 1;
			document.iform.ipv6gateway.disabled = 1;
			break;
	}
}

function media_change() {
	switch (document.iform.media.value) {
		case "autoselect":
			showElementById('mediaopt_tr','hide');
			break;

		default:
			showElementById('mediaopt_tr','show');
			break;
	}
}

<?php if (isset($lancfg['wireless'])):?>
function encryption_change() {
	switch (document.iform.encryption.value) {
		case "none":
			showElementById('wep_key_tr','hide');
			showElementById('wpa_keymgmt_tr','hide');
			showElementById('wpa_pairwise_tr','hide');
			showElementById('wpa_psk_tr','hide');
			break;

		case "wep":
			showElementById('wep_key_tr','show');
			showElementById('wpa_keymgmt_tr','hide');
			showElementById('wpa_pairwise_tr','hide');
			showElementById('wpa_psk_tr','hide');
			break;

		case "wpa":
			showElementById('wep_key_tr','hide');
			showElementById('wpa_keymgmt_tr','show');
			showElementById('wpa_pairwise_tr','show');
			showElementById('wpa_psk_tr','show');
			break;
	}
}
<?php endif;?>
// -->
</script>
<form action="interfaces_lan.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
				<?php
				if (!empty($input_errors)) {
					print_input_errors($input_errors);
				}
				if (file_exists($d_sysrebootreqd_path)) {
					print_info_box(get_std_save_message(0));
				}
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline(gtext("IPv4 Configuration"));
					html_combobox("type", gtext("Type"), $pconfig['type'], array("Static" => gtext("Static"), "DHCP" => "DHCP"), "", true, false, "type_change()");
					html_ipv4addrbox("ipaddr", "subnet", gtext("IP address"), $pconfig['ipaddr'], $pconfig['subnet'], "", true);
					html_inputbox("gateway", gtext("Gateway"), $pconfig['gateway'], "", true, 20);
					html_separator();
					html_titleline_checkbox("ipv6_enable", gtext("IPv6 Configuration"), !empty($pconfig['ipv6_enable']) ? true : false, gtext("Activate"), "enable_change(this)");
					html_combobox("ipv6type", gtext("Type"), $pconfig['ipv6type'], array("Static" => gtext("Static"), "Auto" => gtext("Auto")), "", true, false, "ipv6_type_change()");
					html_ipv6addrbox("ipv6addr", "ipv6subnet", gtext("IP address"), !empty($pconfig['ipv6addr']) ? $pconfig['ipv6addr'] : "", !empty($pconfig['ipv6subnet']) ? $pconfig['ipv6subnet'] : "", "", true);
					html_inputbox("ipv6gateway", gtext("Gateway"), !empty($pconfig['ipv6gateway']) ? $pconfig['ipv6gateway'] : "", "", true, 20);
					html_separator();
					html_titleline(gtext("Advanced Configuration"));
					html_inputbox("mtu", gtext("MTU"), $pconfig['mtu'], gtext("Set the maximum transmission unit of the interface to n, default is interface specific. The MTU is used to limit the size of packets that are transmitted on an interface. Not all interfaces support setting the MTU, and some interfaces have range restrictions."), false, 5);
					?>
<!--
					<?php html_checkbox("polling", gtext("Device polling"), $pconfig['polling'] ? true : false, gtext("Enable device polling"), gtext("Device polling is a technique that lets the system periodically poll network devices for new data instead of relying on interrupts. This can reduce CPU load and therefore increase throughput, at the expense of a slightly higher forwarding delay (the devices are polled 1000 times per second). Not all NICs support polling."), false);?>
-->
					<?php
					html_combobox("media", gtext("Media"), $pconfig['media'], array("autoselect" => gtext("Autoselect"), "10baseT/UTP" => "10baseT/UTP", "100baseTX" => "100baseTX", "1000baseTX" => "1000baseTX", "1000baseSX" => "1000baseSX",), "", false, false, "media_change()");
					html_combobox("mediaopt", gtext("Duplex"), $pconfig['mediaopt'], array("half-duplex" => "half-duplex", "full-duplex" => "full-duplex"), "", false);
					if (!empty($ifinfo['wolevents'])) {
						$wakeonoptions = array("off" => gtext("Off"), "wol" => gtext("On")); foreach ($ifinfo['wolevents'] as $woleventv) { $wakeonoptions[$woleventv] = $woleventv; };
						html_combobox("wakeon", gtext("Wake On LAN"), $pconfig['wakeon'], $wakeonoptions, "", false);
					}
					html_inputbox("extraoptions", gtext("Extra options"), $pconfig['extraoptions'], gtext("Extra options to ifconfig (usually empty)."), false, 40);
					if (isset($lancfg['wireless'])) {
						wireless_config_print();
					}
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
					<?php
					$helpinghand = gtext('After you click "Save" you may also have to do one or more of the following steps before you can access this server again:')
						. '<ul>'
						. '<li>' . gtext('Change the IP address of your computer') . '</li>'
						. '<li>' . gtext('Access the webGUI with the new IP address') . '</li>'
						. '</ul>';
					html_remark('warning', gtext('Warning'), $helpinghand);
					?>
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
type_change();
ipv6_type_change();
media_change();
enable_change(false);
<?php if (isset($lancfg['wireless'])):?>
encryption_change();
<?php endif;?>
//-->
</script>
<?php include("fend.inc");?>
