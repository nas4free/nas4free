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
require 'auth.inc';
require 'guiconfig.inc';
require 'services.inc';

$pconfig['hostname'] = $config['system']['hostname'];
$pconfig['domain'] = $config['system']['domain'];
list($pconfig['dns1'],$pconfig['dns2']) = get_ipv4dnsserver();
list($pconfig['ipv6dns1'],$pconfig['ipv6dns2']) = get_ipv6dnsserver();
$pconfig['username'] = $config['system']['username'];
$pconfig['webguiproto'] = $config['system']['webgui']['protocol'];
$pconfig['webguiport'] = !empty($config['system']['webgui']['port']) ? $config['system']['webgui']['port'] : "";
$pconfig['webguihostsallow'] = !empty($config['system']['webgui']['hostsallow']) ? $config['system']['webgui']['hostsallow'] : "";
$pconfig['language'] = $config['system']['language'];
if(isset($config['system']['webgui']['auxparam']) && is_array($config['system']['webgui']['auxparam'])):
	$pconfig['auxparam'] = implode("\n", $config['system']['webgui']['auxparam']);
else:
	$pconfig['auxparam'] = '';
endif;
$pconfig['timezone'] = $config['system']['timezone'];
$pconfig['datetimeformat'] = !empty($config['system']['datetimeformat']) ? $config['system']['datetimeformat'] : 'default';
$pconfig['ntp_enable'] = isset($config['system']['ntp']['enable']);
$pconfig['ntp_timeservers'] = $config['system']['ntp']['timeservers'];
$pconfig['ntp_updateinterval'] = $config['system']['ntp']['updateinterval'];
$pconfig['language'] = $config['system']['language'];
$pconfig['certificate'] = base64_decode($config['system']['webgui']['certificate']);
$pconfig['privatekey'] = base64_decode($config['system']['webgui']['privatekey']);
// Set default values if necessary.
if(!$pconfig['language']):
	$pconfig['language'] = "English";
endif;
if(!$pconfig['timezone']):
	$pconfig['timezone'] = "Etc/UTC";
endif;
if(!$pconfig['webguiproto']):
	$pconfig['webguiproto'] = "http";
endif;
if(!$pconfig['username']):
	$pconfig['username'] = "admin";
endif;
if(!$pconfig['ntp_timeservers']):
	$pconfig['ntp_timeservers'] = "pool.ntp.org";
endif;
if(!isset($pconfig['ntp_updateinterval'])):
	$pconfig['ntp_updateinterval'] = 300;
endif;
if($_POST) {
	unset($input_errors);
	$input_errors = [];
	$reboot_required = false;
	// must be here, auxparam is array in config.xml but string in $_POST
	if(!$reboot_required):
		if(isset($_POST['auxparam']) && (strcmp($pconfig['auxparam'],$_POST['auxparam']) !== 0)):
			$reboot_required = true;
		endif;
	endif;
	$pconfig = $_POST;
	// Input validation.
	$reqdfields = ['hostname','username'];
	$reqdfieldsn = [gtext('Hostname'),gtext('Username')];
	$reqdfieldst = ['hostname','alias'];
	if(!empty($_POST['domain'])):
		$reqdfields = array_merge($reqdfields,['domain']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Domain')]);
		$reqdfieldst = array_merge($reqdfieldst,['domain']);
	endif;
	if(isset($_POST['ntp_enable'])):
		$reqdfields = array_merge($reqdfields,['ntp_timeservers','ntp_updateinterval']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('NTP time server'),gtext('Time update interval')]);
		$reqdfieldst = array_merge($reqdfieldst,['string','numeric']);
	endif;
	if("https" === $_POST['webguiproto']):
		$reqdfields = array_merge($reqdfields,['certificate','privatekey']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Certificate'),gtext('Private key')]);
		$reqdfieldst = array_merge($reqdfieldst,['certificate','privatekey']);
	endif;
	if(!empty($_POST['webguiport'])):
		$reqdfields = array_merge($reqdfields,['webguiport']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Port')]);
		$reqdfieldst = array_merge($reqdfieldst,['port']);
	endif;
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	if(!empty($_POST['webguihostsallow'])):
		foreach(explode(' ', $_POST['webguihostsallow']) as $a):
			list($hp,$np) = explode('/', $a);
			if(!is_ipaddr($hp) || (!empty($np) && !is_subnet($a))):
				$input_errors[] = gtext("A valid IP address or CIDR notation must be specified for the hosts allow.");
			endif;
		endforeach;
	endif;
	if($_POST['dns1'] && !is_ipv4addr($_POST['dns1'])):
		$input_errors[] = gtext('A valid IPv4 address must be specified for the primary DNS server.');
	endif;
	if($_POST['dns2'] && !is_ipv4addr($_POST['dns2'])):
		$input_errors[] = gtext('A valid IPv4 address must be specified for the secondary DNS server.');
	endif;
	if($_POST['ipv6dns1'] && !is_ipv6addr($_POST['ipv6dns1'])):
		$input_errors[] = gtext("A valid IPv6 address must be specified for the primary DNS server.");
	endif;
	if($_POST['ipv6dns2'] && !is_ipv6addr($_POST['ipv6dns2'])):
		$input_errors[] = gtext("A valid IPv6 address must be specified for the secondary DNS server.");
	endif;
	if(isset($_POST['ntp_enable'])):
		$t = (int)$_POST['ntp_updateinterval'];
		if(($t < 0) || (($t > 0) && ($t < 6)) || ($t > 1440)):
			$input_errors[] = gtext("The time update interval must be either between 6 and 1440.");
		endif;
		foreach(explode(' ',$_POST['ntp_timeservers']) as $ts):
			if(!is_domain($ts)):
				$input_errors[] = gtext("A NTP time server name may only contain the characters a-z, 0-9, '-' and '.'.");
			endif;
		endforeach;
	endif;
	// Check if port is already used.
	if(services_is_port_used(!empty($_POST['webguiport']) ? $_POST['webguiport'] : 80, "sysgui")):
		$input_errors[] = sprintf(gtext("Port %ld is already used by another service."), (!empty($_POST['webguiport']) ? $_POST['webguiport'] : 80));
	endif;
	// Check Webserver document root if auth is required
	if(isset($config['websrv']['enable'])
		&& isset($config['websrv']['authentication']['enable'])
		&& !is_dir($config['websrv']['documentroot'])):
		$input_errors[] = gtext("Webserver document root is missing.");
	endif;
	if(empty($input_errors)):
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
		// Write auxiliary parameters.
		unset($config['system']['webgui']['auxparam']);
		foreach(explode("\n",$_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam, "\t\n\r");
			if(!empty($auxparam)):
				$config['system']['webgui']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		$config['system']['timezone'] = $_POST['timezone'];
		$config['system']['datetimeformat'] = $_POST['datetimeformat'];
		$config['system']['ntp']['enable'] = isset($_POST['ntp_enable']) ? true : false;
		$config['system']['ntp']['timeservers'] = strtolower($_POST['ntp_timeservers']);
		$config['system']['ntp']['updateinterval'] = $_POST['ntp_updateinterval'];
		$config['system']['webgui']['certificate'] = base64_encode($_POST['certificate']);
		$config['system']['webgui']['privatekey'] =  base64_encode($_POST['privatekey']);
		// Only store IPv4 DNS servers when using static IPv4.
		array_make_branch($config,'system','dnsserver');
		$config['system']['dnsserver'] = []; // OK clear configuration
		if('dhcp' !== $config['interfaces']['lan']['ipaddr']):
			if($_POST['dns1']):
				$config['system']['dnsserver'][] = $_POST['dns1'];
			endif;
			if($_POST['dns2']):
				$config['system']['dnsserver'][] = $_POST['dns2'];
			endif;
		endif;
		if(empty($config['system']['dnsserver'])):
			$config['system']['dnsserver'][] = '';
		endif;
		// Only store IPv6 DNS servers when using static IPv6.
		array_make_branch($config,'system','ipv6dnsserver');
		$config['system']['ipv6dnsserver'] = []; // OK
		if('auto' !== $config['interfaces']['lan']['ipv6addr']):
			if($_POST['ipv6dns1']):
				$config['system']['ipv6dnsserver'][] = $_POST['ipv6dns1'];
			endif;
			if($_POST['ipv6dns2']):
				$config['system']['ipv6dnsserver'][] = $_POST['ipv6dns2'];
			endif;
		endif;
		if(empty($config['system']['ipv6dnsserver'])):
			$config['system']['ipv6dnsserver'][] = '';
		endif;
		$olddnsallowoverride = isset($config['system']['dnsallowoverride']);
		$config['system']['dnsallowoverride'] = isset($_POST['dnsallowoverride']) ? true : false;
		write_config();
		set_php_timezone();
		// Check if a reboot is required.
		if(!$reboot_required):
			$reboot_required = ($oldwebguiproto != $config['system']['webgui']['protocol']);
		endif;
		if(!$reboot_required):
			$reboot_required = ($oldwebguiport != $config['system']['webgui']['port']);
		endif;
		if(!$reboot_required):
			$reboot_required = ($oldwebguihostsallow != $config['system']['webgui']['hostsallow']);
		endif;
		if(!$reboot_required):
			$reboot_required = ($config['system']['webgui']['certificate'] != $oldcert);
		endif;
		if(!$reboot_required):
			$reboot_required = ($config['system']['webgui']['privatekey'] != $oldkey);
		endif;
		if($reboot_required):
			touch($d_sysrebootreqd_path);
		endif;
		$retval = 0;
		if(!$reboot_required):
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
		endif;
		if(($pconfig['systime'] !== "Not Set") && (!empty($pconfig['systime']))):
			$timestamp = strtotime($pconfig['systime']);
			if(false !== $timestamp):
				$timestamp = strftime("%g%m%d%H%M", $timestamp);
				// The date utility exits 0 on success, 1 if unable to set the date,
				// and 2 if able to set the local date, but unable to set it globally.
				$retval |= mwexec("/bin/date -n {$timestamp}");
				$pconfig['systime'] = "Not Set";
			endif;
		endif;
		$savemsg = get_std_save_message($retval);
		// Update DNS server controls.
		list($pconfig['dns1'],$pconfig['dns2']) = get_ipv4dnsserver();
		list($pconfig['ipv6dns1'],$pconfig['ipv6dns2']) = get_ipv6dnsserver();
		// Reload page if language has been changed, otherwise page is displayed
		// in previous selected language.
		if($oldlanguage !== $config['system']['language']):
			header("Location: system.php");
			exit;
		endif;
	endif;
}
$pglocalheader = <<< EOD
<link rel="stylesheet" type="text/css" href="js/datechooser.css" />
<script type="text/javascript" src="js/datechooser.js"></script>
EOD;
$pgtitle = [gtext('System'),gtext('General Setup')];
?>
<?php include 'fbegin.inc';?>
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
				<?php
				if(file_exists($d_sysrebootreqd_path)):
					print_info_box(get_std_save_message(0));
				endif;
				if(!empty($input_errors)):
					print_input_errors($input_errors);
				endif;
				if(!empty($savemsg)):
					print_info_box($savemsg);
				endif;
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php 
					html_titleline(gtext("WebGUI"));
					html_inputbox("username", gtext("Username"), $pconfig['username'], gtext("It's recommended to change the default username and password for accessing the WebGUI, enter the username here."), true, 21);
					html_combobox("webguiproto", gtext("Protocol"), $pconfig['webguiproto'], array("http" => "HTTP", "https" => "HTTPS"), gtext("Select Hypertext Transfer Protocol (HTTP) or Hypertext Transfer Protocol Secure (HTTPS) for the WebGUI."), true, false, "webguiproto_change()");
					html_inputbox("webguiport", gtext("Port"), $pconfig['webguiport'], gtext("Enter a custom port number for the WebGUI if you want to override the default (80 for HTTP, 443 for HTTPS)."), true, 6);
					html_inputbox("webguihostsallow", gtext("Hosts Allow"), $pconfig['webguihostsallow'], gtext("Space delimited set of IP or CIDR notation that permitted to access the WebGUI. (empty is the same network of LAN interface)"), false, 60);
					html_textarea("certificate", gtext("Certificate"), $pconfig['certificate'], gtext("Paste a signed certificate in X.509 PEM format here."), true, 65, 7, false, false);
					html_textarea("privatekey", gtext("Private Key"), $pconfig['privatekey'], gtext("Paste an private key in PEM format here."), true, 65, 7, false, false);
					html_languagecombobox("language", gtext("Language"), $pconfig['language'], gtext("Select the language of the WebGUI."), "", false);
					$helpinghand = '<a href="'
						. 'http://redmine.lighttpd.net/projects/lighttpd/wiki'
						. '" target="_blank">'
						. gtext('Please check the documentation')
						. '</a>.';
					html_textarea('auxparam', gtext('Additional Parameters'), !empty($pconfig['auxparam']) ? $pconfig['auxparam'] : '', sprintf(gtext('These parameters will be added to %s.'), 'lighttpd.conf')  . ' ' . $helpinghand, false, 85, 7, false, false);
					html_separator();
					?>
					<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gtext("Hostname");?></td>
					</tr>
					<?php
					html_inputbox("hostname", gtext("Hostname"), $pconfig['hostname'], sprintf(gtext("Name of the NAS host, without domain part e.g. %s."), "<em>" . strtolower(get_product_name()) ."</em>"), true, 40);
					html_inputbox("domain", gtext("Domain"), $pconfig['domain'], sprintf(gtext("e.g. %s"), "<em>com, local</em>"), false, 40);
					html_separator();
					html_titleline(gtext("DNS"));
					?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("IPv4 DNS Servers");?></td>
						<td width="78%" class="vtable">
							<?php $readonly = ("dhcp" === $config['interfaces']['lan']['ipaddr']) ? "readonly=\"readonly\"" : "";?>
							<input name="dns1" type="text" class="formfld" id="dns1" size="20" value="<?=htmlspecialchars($pconfig['dns1']);?>" <?=$readonly;?> /><br />
							<input name="dns2" type="text" class="formfld" id="dns2" size="20" value="<?=htmlspecialchars($pconfig['dns2']);?>" <?=$readonly;?> /><br />
							<span class="vexpl"><?=gtext("IPv4 addresses");?></span><br />
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("IPv6 DNS Servers");?></td>
						<td width="78%" class="vtable">
							<?php $readonly = (!isset($config['interfaces']['lan']['ipv6_enable']) || ("auto" === $config['interfaces']['lan']['ipv6addr'])) ? "readonly=\"readonly\"" : "";?>
							<input name="ipv6dns1" type="text" class="formfld" id="ipv6dns1" size="20" value="<?=htmlspecialchars($pconfig['ipv6dns1']);?>" <?=$readonly;?> /><br />
							<input name="ipv6dns2" type="text" class="formfld" id="ipv6dns2" size="20" value="<?=htmlspecialchars($pconfig['ipv6dns2']);?>" <?=$readonly;?> /><br />
							<span class="vexpl"><?=gtext("IPv6 addresses");?></span><br />
						</td>
					</tr>
					<?php html_separator();?>
					<?php html_titleline(gtext("Time"));?>
					<?php html_timezonecombobox("timezone", gtext("Time Zone"), $pconfig['timezone'], gtext("Select the location closest to you."), false);?>
					<?php html_combobox("datetimeformat", gtext("Date Format"), $pconfig['datetimeformat'], get_datetime_locale_samples(), gtext("Select a date format."), false);?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("System Time");?></td>
						<td width="78%" class="vtable">
							<input id="systime" size="20" maxlength="20" name="systime" type="text" value="" />
							<img src="images/cal.gif" onclick="showChooser(this, 'systime', 'chooserSpan', 2000, 2050, Date.patterns.Default, true);" alt="" />							
							<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;"></div><br />
							<span class="vexpl"><?=gtext("Enter desired system time directly (format mm/dd/yyyy hh:mm) or use icon to select it.");?></span>
						</td>
					</tr>
					<?php html_checkbox("ntp_enable", gtext("Enable NTP"), !empty($pconfig['ntp_enable']) ? true : false, gtext("Use the specified NTP server."), "", false, "ntp_change()");?>
					<?php html_inputbox("ntp_timeservers", gtext("NTP Time Server"), $pconfig['ntp_timeservers'], gtext("Use a space to separate multiple hosts (only one required). Remember to set up at least one DNS server if you enter a host name here!"), true, 40);?>
					<?php html_inputbox("ntp_updateinterval", gtext("Time Synchronization"), $pconfig['ntp_updateinterval'], gtext("Minutes between the next network time synchronization."), true, 20);?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" />
				</div>
				<?php include 'formend.inc';?>
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
<?php include 'fend.inc';?>
