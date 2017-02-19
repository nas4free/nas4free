<?php
/*
	system.php

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
require("services.inc");

$pgtitle = array(gtext("System"), gtext("General Setup"));

$pconfig['hostname'] = $config['system']['hostname'];
$pconfig['domain'] = $config['system']['domain'];
list($pconfig['dns1'],$pconfig['dns2']) = get_ipv4dnsserver();
list($pconfig['ipv6dns1'],$pconfig['ipv6dns2']) = get_ipv6dnsserver();
$pconfig['username'] = $config['system']['username'];
$pconfig['webguiproto'] = $config['system']['webgui']['protocol'];
$pconfig['webguiport'] = !empty($config['system']['webgui']['port']) ? $config['system']['webgui']['port'] : "";
$pconfig['webguihostsallow'] = !empty($config['system']['webgui']['hostsallow']) ? $config['system']['webgui']['hostsallow'] : "";
$pconfig['language'] = $config['system']['language'];
$pconfig['timezone'] = $config['system']['timezone'];
$pconfig['datetimeformat'] = !empty($config['system']['datetimeformat']) ? $config['system']['datetimeformat'] : 'default';
$pconfig['ntp_enable'] = isset($config['system']['ntp']['enable']);
$pconfig['ntp_timeservers'] = $config['system']['ntp']['timeservers'];
$pconfig['ntp_updateinterval'] = $config['system']['ntp']['updateinterval'];
$pconfig['language'] = $config['system']['language'];
$pconfig['certificate'] = base64_decode($config['system']['webgui']['certificate']);
$pconfig['privatekey'] = base64_decode($config['system']['webgui']['privatekey']);

// Set default values if necessary.
if (!$pconfig['language'])
	$pconfig['language'] = "English";
if (!$pconfig['timezone'])
	$pconfig['timezone'] = "Etc/UTC";
if (!$pconfig['webguiproto'])
	$pconfig['webguiproto'] = "http";
if (!$pconfig['username'])
	$pconfig['username'] = "admin";
if (!$pconfig['ntp_timeservers'])
	$pconfig['ntp_timeservers'] = "pool.ntp.org";
if (!isset($pconfig['ntp_updateinterval']))
	$pconfig['ntp_updateinterval'] = 300;

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	$reqdfields = explode(" ", "hostname username");
	$reqdfieldsn = array(gtext("Hostname"), gtext("Username"));
	$reqdfieldst = explode(" ", "hostname alias");

	if (!empty($_POST['domain'])) {
		$reqdfields = array_merge($reqdfields, array("domain"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("Domain")));
		$reqdfieldst = array_merge($reqdfieldst, array("domain"));
	}

	if (isset($_POST['ntp_enable'])) {
		$reqdfields = array_merge($reqdfields, explode(" ", "ntp_timeservers ntp_updateinterval"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("NTP time server"), gtext("Time update interval")));
		$reqdfieldst = array_merge($reqdfieldst, explode(" ", "string numeric"));
	}

	if ("https" === $_POST['webguiproto']) {
		$reqdfields = array_merge($reqdfields, explode(" ", "certificate privatekey"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("Certificate"), gtext("Private key")));
		$reqdfieldst = array_merge($reqdfieldst, explode(" ", "certificate privatekey"));
	}

	if (!empty($_POST['webguiport'])) {
		$reqdfields = array_merge($reqdfields, array("webguiport"));
		$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("Port")));
		$reqdfieldst = array_merge($reqdfieldst, array("port"));
	}

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	if (!empty($_POST['webguihostsallow'])) {
		foreach (explode(' ', $_POST['webguihostsallow']) as $a) {
			list($hp,$np) = explode('/', $a);
			if (!is_ipaddr($hp) || (!empty($np) && !is_subnet($a))) {
				$input_errors[] = gtext("A valid IP address or CIDR notation must be specified for the hosts allow.");
			}
		}
	}

	if (($_POST['dns1'] && !is_ipv4addr($_POST['dns1'])) || ($_POST['dns2'] && !is_ipv4addr($_POST['dns2']))) {
		$input_errors[] = gtext("A valid IPv4 address must be specified for the primary/secondary DNS server.");
	}

	if (($_POST['ipv6dns1'] && !is_ipv6addr($_POST['ipv6dns1'])) || ($_POST['ipv6dns2'] && !is_ipv6addr($_POST['ipv6dns2']))) {
		$input_errors[] = gtext("A valid IPv6 address must be specified for the primary/secondary DNS server.");
	}

	if (isset($_POST['ntp_enable'])) {
		$t = (int)$_POST['ntp_updateinterval'];
		if (($t < 0) || (($t > 0) && ($t < 6)) || ($t > 1440)) {
			$input_errors[] = gtext("The time update interval must be either between 6 and 1440.");
		}

		foreach (explode(' ', $_POST['ntp_timeservers']) as $ts) {
			if (!is_domain($ts)) {
				$input_errors[] = gtext("A NTP time server name may only contain the characters a-z, 0-9, '-' and '.'.");
			}
		}
	}

	// Check if port is already used.
	if (services_is_port_used(!empty($_POST['webguiport']) ? $_POST['webguiport'] : 80, "sysgui")) {
		$input_errors[] = sprintf(gtext("Port %ld is already used by another service."), (!empty($_POST['webguiport']) ? $_POST['webguiport'] : 80));
	}

	// Check Webserver document root if auth is required
	if (isset($config['websrv']['enable'])
	    && isset($config['websrv']['authentication']['enable'])
	    && !is_dir($config['websrv']['documentroot'])) {
		$input_errors[] = gtext("Webserver document root is missing.");
	}

	if (empty($input_errors)) {
		// Store old values for later processing.
		$oldcert = $config['system']['webgui']['certificate'];
		$oldkey = $config['system']['webgui']['privatekey'];
		$oldwebguiproto = $config['system']['webgui']['protocol'];
		$oldwebguiport = $config['system']['webgui']['port'];
		$oldwebguihostsallow = $config['system']['webgui']['hostsallow'];
		$oldlanguage = $config['system']['language'];

		$config['system']['hostname'] = strtolower($_POST['hostname']);
		$config['system']['domain'] = strtolower($_POST['domain']);
		$config['system']['username'] = $_POST['username'];
		$config['system']['webgui']['protocol'] = $_POST['webguiproto'];
		$config['system']['webgui']['port'] = $_POST['webguiport'];
		$config['system']['webgui']['hostsallow'] = $_POST['webguihostsallow'];
		$config['system']['language'] = $_POST['language'];
		$config['system']['timezone'] = $_POST['timezone'];
		$config['system']['datetimeformat'] = $_POST['datetimeformat'];
		$config['system']['ntp']['enable'] = isset($_POST['ntp_enable']) ? true : false;
		$config['system']['ntp']['timeservers'] = strtolower($_POST['ntp_timeservers']);
		$config['system']['ntp']['updateinterval'] = $_POST['ntp_updateinterval'];
		$config['system']['webgui']['certificate'] = base64_encode($_POST['certificate']);
		$config['system']['webgui']['privatekey'] =  base64_encode($_POST['privatekey']);

		unset($config['system']['dnsserver']);
		// Only store IPv4 DNS servers when using static IPv4.
		if ("dhcp" !== $config['interfaces']['lan']['ipaddr']) {
			unset($config['system']['dnsserver']);
			if ($_POST['dns1'])
				$config['system']['dnsserver'][] = $_POST['dns1'];
			if ($_POST['dns2'])
				$config['system']['dnsserver'][] = $_POST['dns2'];
		}
		// Only store IPv6 DNS servers when using static IPv6.
		if ("auto" !== $config['interfaces']['lan']['ipv6addr']) {
			unset($config['system']['ipv6dnsserver']);
			if ($_POST['ipv6dns1'])
				$config['system']['ipv6dnsserver'][] = $_POST['ipv6dns1'];
			if ($_POST['ipv6dns2'])
				$config['system']['ipv6dnsserver'][] = $_POST['ipv6dns2'];
		}

		$olddnsallowoverride = isset($config['system']['dnsallowoverride']);
		$config['system']['dnsallowoverride'] = isset($_POST['dnsallowoverride']) ? true : false;

		write_config();
		set_php_timezone();

		// Check if a reboot is required.
		if (($oldwebguiproto != $config['system']['webgui']['protocol']) ||
			($oldwebguiport != $config['system']['webgui']['port'])) {
			touch($d_sysrebootreqd_path);
		}
		if ($oldwebguihostsallow != $config['system']['webgui']['hostsallow']) {
			// XXX shoud be fixed for more better way
			touch($d_sysrebootreqd_path);
		}
		if (($config['system']['webgui']['certificate'] != $oldcert) || ($config['system']['webgui']['privatekey'] != $oldkey)) {
			touch($d_sysrebootreqd_path);
		}

		$retval = 0;

		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_exec_service("rcconf");
			$retval |= rc_exec_service("timezone");
			$retval |= rc_exec_service("resolv");
			$retval |= rc_exec_service("hosts");
			$retval |= rc_restart_service("hostname");
			$retval |= rc_exec_service("userdb");
			$retval |= rc_exec_service("htpasswd");
			$retval |= rc_exec_service("websrv_htpasswd");
 			$retval |= rc_update_service("ntpdate");
 			$retval |= rc_update_service("mdnsresponder");
 			$retval |= rc_update_service("bsnmpd");
 			$retval |= rc_update_service("cron");
			config_unlock();
		}

		if (($pconfig['systime'] !== "Not Set") && (!empty($pconfig['systime']))) {
			$timestamp = strtotime($pconfig['systime']);
			if (FALSE !== $timestamp) {
				$timestamp = strftime("%g%m%d%H%M", $timestamp);
				// The date utility exits 0 on success, 1 if unable to set the date,
				// and 2 if able to set the local date, but unable to set it globally.
				$retval |= mwexec("/bin/date -n {$timestamp}");
				$pconfig['systime'] = "Not Set";
			}
		}

		$savemsg = get_std_save_message($retval);

		// Update DNS server controls.
		list($pconfig['dns1'],$pconfig['dns2']) = get_ipv4dnsserver();
		list($pconfig['ipv6dns1'],$pconfig['ipv6dns2']) = get_ipv6dnsserver();

		// Reload page if language has been changed, otherwise page is displayed
		// in previous selected language.
		if ($oldlanguage !== $config['system']['language']) {
			header("Location: system.php");
			exit;
		}
	}
}

$pglocalheader = <<< EOD
<link rel="stylesheet" type="text/css" href="js/datechooser.css" />
<script type="text/javascript" src="js/datechooser.js"></script>
EOD;
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function ntp_change() {
	switch(document.iform.ntp_enable.checked) {
		case false:
			showElementById('ntp_timeservers_tr','hide');
			showElementById('ntp_updateinterval_tr','hide');
			break;

		case true:
			showElementById('ntp_timeservers_tr','show');
			showElementById('ntp_updateinterval_tr','show');
			break;
	}
}

function webguiproto_change() {
	switch(document.iform.webguiproto.selectedIndex) {
		case 0:
			showElementById('privatekey_tr','hide');
			showElementById('certificate_tr','hide');
			break;

		default:
			showElementById('privatekey_tr','show');
			showElementById('certificate_tr','show');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabact"><a href="system.php" title="<?=gtext('Reload page');?>"><span><?=gtext("General");?></span></a></li>
      	<li class="tabinact"><a href="system_password.php"><span><?=gtext("Password");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
			<form action="system.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
			    <?php html_titleline(gtext("WebGUI"));?>
					<?php html_inputbox("username", gtext("Username"), $pconfig['username'], gtext("It's recommended to change the default username and password for accessing the WebGUI, enter the username here."), false, 21);?>
					<?php html_combobox("webguiproto", gtext("Protocol"), $pconfig['webguiproto'], array("http" => "HTTP", "https" => "HTTPS"), gtext("Select Hypertext Transfer Protocol (HTTP) or Hypertext Transfer Protocol Secure (HTTPS) for the WebGUI."), false, false, "webguiproto_change()");?>
					<?php html_inputbox("webguiport", gtext("Port"), $pconfig['webguiport'], gtext("Enter a custom port number for the WebGUI if you want to override the default (80 for HTTP, 443 for HTTPS)."), false, 6);?>
					<?php html_inputbox("webguihostsallow", gtext("Hosts allow"), $pconfig['webguihostsallow'], gtext("Space delimited set of IP or CIDR notation that permitted to access the WebGUI. (empty is the same network of LAN interface)"), false, 60);?>
					<?php html_textarea("certificate", gtext("Certificate"), $pconfig['certificate'], gtext("Paste a signed certificate in X.509 PEM format here."), true, 65, 7, false, false);?>
					<?php html_textarea("privatekey", gtext("Private key"), $pconfig['privatekey'], gtext("Paste an private key in PEM format here."), true, 65, 7, false, false);?>
					<?php html_languagecombobox("language", gtext("Language"), $pconfig['language'], gtext("Select the language of the WebGUI."), "", false);?>
					<?php html_separator();?>
			  	<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gtext("Hostname");?></td>
					</tr>
					<?php html_inputbox("hostname", gtext("Hostname"), $pconfig['hostname'], sprintf(gtext("Name of the NAS host, without domain part e.g. %s."), "<em>" . strtolower(get_product_name()) ."</em>"), true, 40);?>
					<?php html_inputbox("domain", gtext("Domain"), $pconfig['domain'], sprintf(gtext("e.g. %s"), "<em>com, local</em>"), false, 40);?>
					<?php html_separator();?>
					<?php html_titleline(gtext("DNS"));?>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gtext("IPv4 DNS servers");?></td>
			      <td width="78%" class="vtable">
							<?php $readonly = ("dhcp" === $config['interfaces']['lan']['ipaddr']) ? "readonly=\"readonly\"" : "";?>
							<input name="dns1" type="text" class="formfld" id="dns1" size="20" value="<?=htmlspecialchars($pconfig['dns1']);?>" <?=$readonly;?> /><br />
							<input name="dns2" type="text" class="formfld" id="dns2" size="20" value="<?=htmlspecialchars($pconfig['dns2']);?>" <?=$readonly;?> /><br />
							<span class="vexpl"><?=gtext("IPv4 addresses");?></span><br />
			      </td>
			    </tr>
				  <tr>
			      <td width="22%" valign="top" class="vncell"><?=gtext("IPv6 DNS servers");?></td>
			      <td width="78%" class="vtable">
							<?php $readonly = (!isset($config['interfaces']['lan']['ipv6_enable']) || ("auto" === $config['interfaces']['lan']['ipv6addr'])) ? "readonly=\"readonly\"" : "";?>
							<input name="ipv6dns1" type="text" class="formfld" id="ipv6dns1" size="20" value="<?=htmlspecialchars($pconfig['ipv6dns1']);?>" <?=$readonly;?> /><br />
							<input name="ipv6dns2" type="text" class="formfld" id="ipv6dns2" size="20" value="<?=htmlspecialchars($pconfig['ipv6dns2']);?>" <?=$readonly;?> /><br />
							<span class="vexpl"><?=gtext("IPv6 addresses");?></span><br />
			      </td>
			    </tr>
			    <?php html_separator();?>
					<?php html_titleline(gtext("Time"));?>
					<?php html_timezonecombobox("timezone", gtext("Time zone"), $pconfig['timezone'], gtext("Select the location closest to you."), false);?>
					<?php html_combobox("datetimeformat", gtext("Date format"), $pconfig['datetimeformat'], get_datetime_locale_samples(), gtext("Select a date format."), false);?>
				<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("System time");?></td>
						<td width="78%" class="vtable">
							<input id="systime" size="20" maxlength="20" name="systime" type="text" value="" />
							<img src="images/cal.gif" onclick="showChooser(this, 'systime', 'chooserSpan', 2000, 2050, Date.patterns.Default, true);" alt="" />							
							<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div><br />
							<span class="vexpl"><?=gtext("Enter desired system time directly (format mm/dd/yyyy hh:mm) or use icon to select it.");?></span>
						</td>
			    </tr>
					<?php html_checkbox("ntp_enable", gtext("Enable NTP"), !empty($pconfig['ntp_enable']) ? true : false, gtext("Use the specified NTP server."), "", false, "ntp_change()");?>
					<?php html_inputbox("ntp_timeservers", gtext("NTP time server"), $pconfig['ntp_timeservers'], gtext("Use a space to separate multiple hosts (only one required). Remember to set up at least one DNS server if you enter a host name here!"), true, 40);?>
					<?php html_inputbox("ntp_updateinterval", gtext("Time Synchronization"), $pconfig['ntp_updateinterval'], gtext("Minutes between the next network time synchronization."), true, 20);?>
			  </table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" />
				</div>
				<?php include("formend.inc");?>
			</form>
		</td>
  </tr>
</table>
<script type="text/javascript">
<!--
ntp_change();
webguiproto_change();
//-->
</script>
<?php include("fend.inc");?>
