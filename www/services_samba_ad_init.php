<?php
/*
	services_samba_ad_init.php

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

$errormsg="";
$do_init = false;

list($pconfig['dns_forwarder']) = get_ipv4dnsserver();
if($pconfig['dns_forwarder'] == '127.0.0.1'):
	$pconfig['dns_forwarder'] = '';
endif;
$pconfig['dns_domain'] = strtolower($config['system']['domain'] ?? '');
if(preg_match('/^([^\.]+)\./', $pconfig['dns_domain'], $m)):
	$pconfig['netbios_domain'] = strtoupper($m[1]);
else:
	$pconfig['netbios_domain'] = strtoupper($pconfig['dns_domain']);
	$errormsg .= gtext('Domain is missing 2nd level name.');
	$errormsg .= "<br/>";
endif;
$pconfig['path'] = '';
$pconfig['fstype'] = 's3fs';
$pconfig['user_shares'] = false;
$realm = strtoupper($pconfig['dns_domain']);
$hostname = $config['system']['hostname'] ?? '';
$netbiosname = strtoupper($config['system']['hostname'] ?? '');
if(isset($config['interfaces']['lan']['ipaddr'])):
	if($config['interfaces']['lan']['ipaddr'] == "dhcp"):
		$errormsg .= gtext('Cannot use DHCP for LAN interface.');
		$errormsg .= "<br/>";
	endif;
else:
	$errormsg .= gtext('LAN interface is not configured.');
	$errormsg .= "<br/>";
endif;
array_make_branch($config,'system','dnsserver');
array_make_branch($config,'system','ipv6dnsserver');
$dns_configured = false;
foreach($config['system']['dnsserver'] as $dnsserver):
	if(is_string($dnsserver) && preg_match('/\S/',$dnsserver)):
		$dns_configured = true;
		break; // break loop
	endif;
endforeach;
foreach($config['system']['ipv6dnsserver'] as $dnsserver):
	if(is_string($dnsserver) && preg_match('/\S/',$dnsserver)):
		$dns_configured = true;
		break; // break loop
	endif;
endforeach;
if(!dns_configured):
	$errormsg .= gtext('No DNS server have been configured.');
	$errormsg .= "<br/>";
endif;
/*
if((!empty($config['system']['dnsserver']) && $config['system']['dnsserver'][0] == "") && (!empty($config['system']['ipv6dnsserver']) && $config['system']['ipv6dnsserver'][0] == "")) {
	$errormsg .= gtext("DNS server is empty.");
	$errormsg .= "<br/>";
}
*/
if(isset($config['system']['ntp']['enable'])):
else:
	$errormsg .= gtext('NTP is not enabled.');
	$errormsg .= "<br/>";
endif;
if(isset($config['samba']['enable'])):
	$errormsg .= gtext("CIFS/SMB is enabled.");
	$errormsg .= "<br/>";
endif;

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	unset($do_init);

	$pconfig = $_POST;

	if (!file_exists($_POST['path'])) {
		$input_errors[] = gtext("Not found path.");
	} else if (file_exists($_POST['path']."/sysvol")) {
		$input_errors[] = gtext("sysvol exist in path.");
	}
	if ($_POST['password'] != $_POST['password_confirm']) {
		$input_errors[] = gtext("The confirmed password does not match. Please ensure the passwords match exactly.");
	} else if ($_POST['password'] == "") {
		//$input_errors[] = gtext("The admin password is empty.");
	}
	if ($_POST['dns_forwarder'] == "") {
		$input_errors[] = gtext("DNS server is empty.");
	}

	if (empty($input_errors)) {
		$do_init = true;
		$config['sambaad']['enable'] = false;
		$config['sambaad']['path'] = $_POST['path'];
		$config['sambaad']['fstype'] = $_POST['fstype'];
		$config['sambaad']['dns_forwarder'] = $_POST['dns_forwarder'];
		$config['sambaad']['dns_domain'] = $_POST['dns_domain'];
		$config['sambaad']['netbios_domain'] = $_POST['netbios_domain'];
		$config['sambaad']['user_shares'] = isset($_POST['user_shares']) ? true : false;

		$realm = strtoupper($config['sambaad']['dns_domain']);
		$domain = strtoupper($config['sambaad']['netbios_domain']);
		$password = $_POST['password'];
		$path = $config['sambaad']['path'];

		$cmd = "/usr/local/bin/samba-tool domain provision";
		$cmsargs = [];
		$cmdargs[] = escapeshellarg("--use-rfc2307");
		if ($config['sambaad']['fstype'] == "ntvfs")
			$cmdargs[] = escapeshellarg("--use-ntvfs");
		$cmdargs[] = escapeshellarg("--function-level=2008_R2");
		$cmdargs[] = escapeshellarg("--realm=${realm}");
		$cmdargs[] = escapeshellarg("--domain=${domain}");
		$cmdargs[] = escapeshellarg("--server-role=dc");
		$cmdargs[] = escapeshellarg("--dns-backend=SAMBA_INTERNAL");
		if (!empty($password))
			$cmdargs[] = escapeshellarg("--adminpass=${password}");
		$cmdargs[] = escapeshellarg("--option=cache directory = ${path}");
		$cmdargs[] = escapeshellarg("--option=lock directory = ${path}");
		$cmdargs[] = escapeshellarg("--option=state directory = ${path}");
		$cmdargs[] = escapeshellarg("--option=private dir = ${path}/private");
		$cmdargs[] = escapeshellarg("--option=smb passwd file = ${path}/private/smbpasswd");
		$cmdargs[] = escapeshellarg("--option=usershare path = ${path}/usershares");
		$cmdargs[] = escapeshellarg("--option=nsupdate command = /usr/local/bin/samba-nsupdate -g");

		// adjust DNS server
		unset($config['system']['dnsserver']);
		$config['system']['dnsserver'][] = "127.0.0.1";

		write_config();
		$retval = 0;
		if (isset($config['samba']['enable'])) {
			$config['samba']['enable'] = false;
			write_config();
			config_lock();
			$retval |= rc_update_service("samba");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}
		if (file_exists("/var/etc/smb4.conf")) {
			if (unlink("/var/etc/smb4.conf") == FALSE) {
				$input_errors[] = sprintf(gtext("Failed to remove: %s"), "/var/etc/smb4.conf");
			}
		}
	}
}
$pgtitle = [gtext('Services'),gtext('Samba AD'),gtext('Initialize')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var val = !($('#enable').prop('checked') || enable_change);
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
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="services_samba_ad.php"><span><?=gtext("Settings");?></span></a></li>
		<li class="tabact"><a href="services_samba_ad_init.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Initialize");?></span></a></li>
	</ul></td></tr>
	<tr>
		<td class="tabcont">
			<form action="services_samba_ad_init.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php
				if (!empty($errormsg)) {
					print_error_box($errormsg);
				}
				if (!empty($input_errors)) {
					print_input_errors($input_errors);
				}
				if (!empty($savemsg)) {
					print_info_box($savemsg);
				}
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline(gtext("Samba Active Directory Domain Controller"));
					html_text("hostname", gtext("Hostname"), htmlspecialchars($hostname));
					html_text("netniosname", gtext("NetBIOS Name"), htmlspecialchars($netbiosname));
					html_inputbox("dns_forwarder", gtext("DNS Forwarder"), $pconfig['dns_forwarder'], "", true, 40);
					html_inputbox("dns_domain", gtext("DNS Domain"), $pconfig['dns_domain'], "", true, 40);
					html_inputbox("netbios_domain", gtext("NetBIOS Domain"), $pconfig['netbios_domain'], "", true, 40);
					//html_text("realm", gtext("Kerberos realm"), htmlspecialchars($realm));
					html_passwordconfbox("password", "password_confirm", gtext("Admin Password"), "", "", gtext("Generate password if leave empty."), true);
					html_filechooser("path", gtext("Path"), $pconfig['path'], sprintf(gtext("Permanent samba data path (e.g. %s)."), "/mnt/data/samba4"), $g['media_path'], true);
					html_combobox("fstype", gtext("Fileserver"), $pconfig['fstype'],['s3fs' => 's3fs','ntvfs' => 'ntvfs'], "", true);
					html_checkbox("user_shares", gtext("User Shares"), !empty($pconfig['user_shares']) ? true : false, gtext("Append user defined shares"), "", false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Initialize");?>" />
				</div>
				<?php if ($do_init) {
					echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
					echo('<pre class="cmdoutput">');
					ob_end_flush();
					$cmd .= " ".implode(" ", $cmdargs);
					//echo "$cmd\n";
					echo gtext("Initializing...")."\n";
/*
					mwexec2("$cmd 2>&1", $rawdata, $result);
					foreach ($rawdata as $line) {
						echo htmlspecialchars($line)."\n";
					}
*/
					$handle = popen("$cmd 2>&1", "r");
					while (!feof($handle)) {
						$line = fgets($handle);
						echo htmlspecialchars($line);
						ob_flush();
						flush();
					}
					$result = pclose($handle);
					echo('</pre>');
					if ($result == 0) {
						rename("/var/etc/smb4.conf", "${path}/smb4.conf.created");
						rc_exec_service("resolv");
					}
				}?>
				<div id="remarks">
					<?php
					$helpinghand = '<a href="'
						. 'system.php'
						. '">'
						. gtext('Check System|General Setup before initializing')
						. '</a>.';
					html_remark("note", gtext('Note'), sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li></ul></div>", gtext("All data in the path is overwritten. To avoid invalid data/permission, using an empty UFS directory is recommended."), $helpinghand));
					?>
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
