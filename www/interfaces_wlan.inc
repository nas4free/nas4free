<?php
/*
	interfaces_wlan.inc

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
require 'interfaces.inc';

function wireless_config_init() {
	global $optcfg, $pconfig;

	$pconfig['standard'] = !empty($optcfg['wireless']['standard']) ? $optcfg['wireless']['standard'] : "";
	$pconfig['ssid'] = !empty($optcfg['wireless']['ssid']) ? $optcfg['wireless']['ssid'] : "";
	$pconfig['scan_ssid'] = isset($optcfg['wireless']['scan_ssid']) ? true : false;
	$pconfig['channel'] = !empty($optcfg['wireless']['channel']) ? $optcfg['wireless']['channel'] : "";

	$pconfig['encryption'] = "none";
	if (isset($optcfg['wireless']['wep']['enable'])) {
		$pconfig['encryption'] = "wep";
	} else if (isset($optcfg['wireless']['wpa']['enable'])) {
		$pconfig['encryption'] = "wpa";
	}

	if (!empty($optcfg['wireless']['wep']) && is_array($optcfg['wireless']['wep'])) {
		$pconfig['wep_key'] = $optcfg['wireless']['wep']['key'];
	}

	if (!empty($optcfg['wireless']['wpa']) && is_array($optcfg['wireless']['wpa'])) {
		$pconfig['wpa_keymgmt'] = $optcfg['wireless']['wpa']['keymgmt'];
		$pconfig['wpa_pairwise'] = $optcfg['wireless']['wpa']['pairwise'];
		$pconfig['wpa_psk'] = $optcfg['wireless']['wpa']['psk'];
	}
}

function wireless_config_post() {
	global $optcfg, $pconfig;

	unset($input_errors);

	// Input validation
	if ($_POST['enable']) {
		$reqdfields = ['standard','ssid','channel'];
		$reqdfieldsn = [gtext('Standard'),gtext('SSID'),gtext('Channel')];

		switch ($_POST['encryption']) {
			case "wep":
				$reqdfields = array_merge($reqdfields, ['wep_key']);
				$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Key')]);
				break;

			case "wpa":
				$reqdfields = array_merge($reqdfields, ['wpa_psk']);
				$reqdfieldsn = array_merge($reqdfieldsn, [gtext('PSK')]);
				break;

			default;
				break;
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		if ($_POST['encryption'] == "wpa" && !empty($_POST['wpa_psk'])
		    && (strlen($_POST['wpa_psk']) < 8 || strlen($_POST['wpa_psk']) > 63)) {
			$input_errors[] = sprintf(gtext("The attribute '%s' is required within %d or more characters to %d characters."), gtext("PSK"), 8, 63);
		}
	}

	if (!$input_errors) {
		$optcfg['wireless']['standard'] = $_POST['standard'];
		$optcfg['wireless']['ssid'] = $_POST['ssid'];
		$optcfg['wireless']['scan_ssid'] = isset($_POST['scan_ssid']);
		$optcfg['wireless']['channel'] = $_POST['channel'];
		$optcfg['wireless']['wep']['key'] = $_POST['wep_key'];
		$optcfg['wireless']['wpa']['keymgmt'] = $_POST['wpa_keymgmt'];
		$optcfg['wireless']['wpa']['pairwise'] = $_POST['wpa_pairwise'];
		$optcfg['wireless']['wpa']['psk'] = $_POST['wpa_psk'];

		switch ($_POST['encryption']) {
			case "wep":
				$optcfg['wireless']['wep']['enable'] = true;
				$optcfg['wireless']['wpa']['enable'] = false;
				break;

			case "wpa":
				$optcfg['wireless']['wep']['enable'] = false;
				$optcfg['wireless']['wpa']['enable'] = true;
				break;

			default:
				$optcfg['wireless']['wep']['enable'] = false;
				$optcfg['wireless']['wpa']['enable'] = false;
				break;
		}
	}

	return $input_errors;
}

function wireless_config_print() {
	global $optcfg, $pconfig;
	
	$l_standard = [];
	foreach (interfaces_wireless_get_standards($optcfg['if']) as $standardv):
		$l_standard[$standardv] = sprintf('802.%s',$standardv);
	endforeach;
	$l_option = [];
	$l_option['any'] = gtext('Auto');
	foreach(interfaces_wireless_get_channellist($optcfg['if']) as $channelk => $channelv):
		if($channelv['mode'] === '11g'):
			$mode = '11b/g';
		else:
			$mode = $channelv['mode'];
		endif;
		$l_option[$channelk] = sprintf('%1$s (%2$s MHz, %3$s)',$channelv['channel'],$channelv['freq'],$mode);
	endforeach;
	$l_encryption = [
		'none' => gtext('None'),
		'wep' => gtext('WEP'),
		'wpa' => sprintf('%s / %s',gtext('WPA'),gtext('WPA2'))
	];
	$l_wpa_keymgmt = [
		'WPA-PSK' => gtext('WPA-PSK (Pre Shared Key)'),
		'WPA-EAP' => gtext('WPA-EAP (Extensible Authentication Protocol)'),
		'WPA-PSK WPA-EAP' => gtext('WPA-PSK WPA-EAP')
	];
	$l_wpa_pairwise =  [
		'CCMP' => gtext('CCMP'),
		'CCMP TKIP' => gtext('CCMP TKIP'),
		'TKIP' => gtext('TKIP')
	];
	html_separator();
	html_titleline(gtext('Wireless configuration'));
	html_combobox('standard',gtext('Standard'),$pconfig['standard'],$l_standard,'',true);
	html_inputbox('ssid',gtext('SSID'),$pconfig['ssid'],gtext('Set the desired Service Set Identifier (aka network name).'),true,20);
	html_checkbox('scan_ssid',gtext('Connect to hidden network'),$pconfig['scan_ssid'],gtext('Allows to connect to a hidden network'));
	html_combobox('channel',gtext('Channel'),$pconfig['channel'],$l_option,'',true);
	html_combobox('encryption',gtext("Encryption"),$pconfig['encryption'],$l_encryption,'',true,false,'encryption_change()');
	html_passwordbox('wep_key',gtext('Key'),$pconfig['wep_key'],gtext("A key will be either 5 or 13 characters (40 or 104 bits) depending of the local network and the capabilities of the adaptor. It may be specified either as a plain string or as a string of hexadecimal digits preceded by '0x'."),true,40);
	html_combobox('wpa_keymgmt',gtext('Key Management Protocol'),$pconfig['wpa_keymgmt'],$l_wpa_keymgmt,'',true);
	html_combobox('wpa_pairwise',gtext('Pairwise'),$pconfig['wpa_pairwise'],$l_wpa_pairwise,gtext('List of acceptable pairwise (unicast) ciphers for WPA. One or more of: CCMP (AES in Counter mode with CBC-MAC, RFC 3610, IEEE 802.11i/D7.0), TKIP (Temporal Key Integrity Protocol, IEEE 802.11i/D7.0).'),true);
	html_passwordbox('wpa_psk',gtext('PSK'),$pconfig['wpa_psk'],gtext('Enter the passphrase that will be used in WPA-PSK mode. This must be between 8 and 63 characters long.'),true,40);
}
?>
