<?php
/*
	fbegin.inc

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

function gentitle($title) {
	$navlevelsep = "|"; // Navigation level separator string.
	return join($navlevelsep, $title);
}

function genhtmltitle($title) {
	return htmlspecialchars(system_get_hostname()) . " - " . gentitle($title);
}

$isAdminSession = Session::isAdmin();
// Menu items.
// System
$menu['system']['desc'] = gtext('System');
$menu['system']['visible'] = TRUE;
$menu['system']['link'] = '';
$menu['system']['menuitem'] = [];
$menu['system']['menuitem'][] = ['desc' => gtext('General'), 'link' => 'system.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['desc' => gtext('Advanced'), 'link' => 'system_advanced.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['desc' => gtext('Password'), 'link' => 'userportal_system_password.php', 'visible' => !$isAdminSession];
$menu['system']['menuitem'][] = ['type' => 'separator', 'visible' =>  $isAdminSession];
if ('full' == $g['platform']) {
	$menu['system']['menuitem'][] = ['desc' => gtext('Packages'), 'link' => 'system_packages.php', 'visible' => $isAdminSession];
} else {
	$menu['system']['menuitem'][] = ['desc' => gtext('Firmware Update'), 'link' => 'system_firmware.php', 'visible' => $isAdminSession];
}
$menu['system']['menuitem'][] = ['desc' => gtext('Backup/Restore'), 'link' => 'system_backup.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['desc' => gtext('Factory Defaults'), 'link' => 'system_defaults.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['type' => 'separator', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['desc' => gtext('Reboot'), 'link' => 'reboot.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['desc' => gtext('Shutdown'), 'link' => 'shutdown.php', 'visible' => $isAdminSession];
$menu['system']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['system']['menuitem'][] = ['desc' => gtext('Logout'), 'link' => 'logout.php', 'visible' => true];

// Network
$menu['network']['desc'] = gtext('Network');
$menu['network']['visible'] = $isAdminSession;
$menu['network']['link'] = '';
$menu['network']['menuitem'] = [];
$menu['network']['menuitem'][] = ['desc' => gtext('Interface Management'), 'link' => 'interfaces_assign.php', 'visible' => true];
$menu['network']['menuitem'][] = ['desc' => gtext('LAN Management'), 'link' => 'interfaces_lan.php', 'visible' => true];
for ($i = 1; isset($config['interfaces']['opt' . $i]); $i++) {
	$desc = $config['interfaces']['opt'.$i]['descr'];
	$menu['network']['menuitem'][] = ['desc' => "{$desc}", 'link' => "interfaces_opt.php?index={$i}", 'visible' => true];
}
$menu['network']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['network']['menuitem'][] = ['desc' => gtext('Hosts'), 'link' => 'system_hosts.php', 'visible' => true];
$menu['network']['menuitem'][] = ['desc' => gtext('Static Routes'), 'link' => 'system_routes.php', 'visible' => true];
$menu['network']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['network']['menuitem'][] = ['desc' => gtext('Proxy'), 'link' => 'system_proxy.php', 'visible' => true];
$menu['network']['menuitem'][] = ['desc' => gtext('Firewall'), 'link' => 'system_firewall.php', 'visible' => true];

// Disks
$menu['disks']['desc'] = gtext('Disks');
$menu['disks']['visible'] = $isAdminSession;
$menu['disks']['link'] = '';
$menu['disks']['menuitem'] = [];
$menu['disks']['menuitem'][] = ['desc' => gtext('Management'), 'link' => 'disks_manage.php', 'visible' => true];
$menu['disks']['menuitem'][] = ['desc' => gtext('Software RAID'), 'link' => 'disks_raid_geom.php', 'visible' => true];
$menu['disks']['menuitem'][] = ['desc' => gtext('ZFS'), 'link' => 'disks_zfs_zpool.php', 'visible' => true];
$menu['disks']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['disks']['menuitem'][] = ['desc' => gtext('Encryption'), 'link' => 'disks_crypt.php', 'visible' => true];
$menu['disks']['menuitem'][] = ['desc' => gtext('Mount Point'), 'link' => 'disks_mount.php', 'visible' => true];

// Services
$menu['services']['desc'] = gtext('Services');
$menu['services']['visible'] = $isAdminSession;
$menu['services']['link'] = 'status_services.php';
$menu['services']['menuitem'] = [];
if ('dom0' !== $g['arch']) {
	$menu['services']['menuitem'][] = ['desc' => gtext('HAST'), 'link' => 'services_hast.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Samba AD'), 'link' => 'services_samba_ad.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['type' => 'separator', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('CIFS/SMB'), 'link' => 'services_samba.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('FTP'), 'link' => 'services_ftp.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('TFTP'), 'link' => 'services_tftp.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('SSH'), 'link' => 'services_sshd.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('NFS'), 'link' => 'services_nfs.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('AFP'), 'link' => 'services_afp.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Rsync'), 'link' => 'services_rsyncd.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Syncthing'), 'link' => 'services_syncthing.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Unison'), 'link' => 'services_unison.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('iSCSI Target'), 'link' => 'services_iscsitarget.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('DLNA/UPnP'), 'link' => 'services_fuppes.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('iTunes/DAAP'), 'link' => 'services_daap.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Dynamic DNS'), 'link' => 'services_dynamicdns.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('SNMP'), 'link' => 'services_snmp.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('UPS'), 'link' => 'services_ups.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('Webserver'), 'link' => 'services_websrv.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('BitTorrent'), 'link' => 'services_bittorrent.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('LCDproc'), 'link' => 'services_lcdproc.php', 'visible' => true];
} else {
	$menu['services']['menuitem'][] = ['desc' => gtext('SSH'), 'link' => 'services_sshd.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('NFS'), 'link' => 'services_nfs.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('iSCSI Target'), 'link' => 'services_iscsitarget.php', 'visible' => true];
	$menu['services']['menuitem'][] = ['desc' => gtext('UPS'), 'link' => 'services_ups.php', 'visible' => true];
}

// VM
if ('x64' == $g['arch']) {
	$menu['vm']['desc'] = gtext('VM');
	$menu['vm']['visible'] = $isAdminSession;
	$menu['vm']['link'] = '';
	$menu['vm']['menuitem'] = [];
}
if ('dom0' !== $g['arch']) {
	$menu['vm']['menuitem'][] = ['desc' => gtext('VirtualBox'), 'link' => 'vm_vbox.php', 'visible' => $isAdminSession];
} else {
	$menu['vm']['menuitem'][] = ['desc' => gtext('Virtual Machine'), 'link' => 'vm_xen.php', 'visible' => true];
}

// Access
$menu['access']['desc'] = gtext('Access');
$menu['access']['visible'] = $isAdminSession;
$menu['access']['link'] = '';
$menu['access']['menuitem'] = [];
$menu['access']['menuitem'][] = ['desc' => gtext('Users & Groups'), 'link' => 'access_users.php', 'visible' => true];
if ('dom0' !== $g['arch']) {
	$menu['access']['menuitem'][] = ['desc' => gtext('Active Directory'), 'link' => 'access_ad.php', 'visible' => true];
	$menu['access']['menuitem'][] = ['desc' => gtext('LDAP'), 'link' => 'access_ldap.php', 'visible' => true];
	$menu['access']['menuitem'][] = ['desc' => gtext('NIS'), 'link' => 'notavailable.php', 'visible' => false];
}

// Status
$menu['status']['desc'] = gtext('Status');
$menu['status']['visible'] = $isAdminSession;
$menu['status']['link'] = 'index.php';
$menu['status']['menuitem'] = [];
$menu['status']['menuitem'][] = ['desc' => gtext('System'), 'link' => 'index.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Process'), 'link' => 'status_process.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Services'), 'link' => 'status_services.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Interfaces'), 'link' => 'status_interfaces.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Disks'), 'link' => 'status_disks.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Graph'), 'link' => 'status_graph.php', 'visible' => true];
$menu['status']['menuitem'][] = ['desc' => gtext('Email Report'), 'link' => 'status_report.php', 'visible' => true];

// Advanced
$menu['advanced']['desc'] = gtext('Advanced');
$menu['advanced']['visible'] = TRUE;
$menu['advanced']['link'] = '';
$menu['advanced']['menuitem'] = [];
$menu['advanced']['menuitem'][] = ['desc' => gtext('File Editor'), 'link' => 'system_edit.php', 'visible' => $isAdminSession];
if (!isset($config['system']['disablefm'])) {
	$menu['advanced']['menuitem'][] = ['desc' => gtext('File Manager'), 'link' => '/quixplorer/system_filemanager.php', 'visible' => true];
}
$menu['advanced']['menuitem'][] = ['type' => 'separator', 'visible' => $isAdminSession];
$menu['advanced']['menuitem'][] = ['desc' => gtext('Command'), 'link' => 'exec.php', 'visible' => $isAdminSession];

// Diagnostics
$menu['diagnostics']['desc'] = gtext('Diagnostics');
$menu['diagnostics']['visible'] = $isAdminSession;
$menu['diagnostics']['link'] = '';
$menu['diagnostics']['menuitem'] = [];
$menu['diagnostics']['menuitem'][] = ['desc' => gtext('Log'), 'link' => 'diag_log.php', 'visible' => true];
$menu['diagnostics']['menuitem'][] = ['desc' => gtext('Information'), 'link' => 'diag_infos.php', 'visible' => true];
$menu['diagnostics']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['diagnostics']['menuitem'][] = ['desc' => gtext('Ping/Traceroute'), 'link' => 'diag_ping.php', 'visible' => true];
$menu['diagnostics']['menuitem'][] = ['desc' => gtext('ARP Tables'), 'link' => 'diag_arp.php', 'visible' => true];
$menu['diagnostics']['menuitem'][] = ['desc' => gtext('Routes'), 'link' => 'diag_routes.php', 'visible' => true];

// Help
$menu['help']['desc'] = gtext('Help');
$menu['help']['visible'] = TRUE;
$menu['help']['link'] = '';
$menu['help']['menuitem'] = [];
$menu['help']['menuitem'][] = ['desc' => gtext('Forum'), 'link' => 'http://forums.nas4free.org', 'visible' => TRUE, 'target' => '_blank'];
$menu['help']['menuitem'][] = ['desc' => gtext('Information & Manual'), 'link' => 'http://wiki.nas4free.org', 'visible' => TRUE, 'target' => '_blank'];
$menu['help']['menuitem'][] = ['desc' => gtext('IRC Live Support'), 'link' => 'http://webchat.freenode.net/?channels=#nas4free', 'visible' => TRUE, 'target' => '_blank'];
$menu['help']['menuitem'][] = ['type' => 'separator', 'visible' => true];
$menu['help']['menuitem'][] = ['desc' => gtext('Release Notes'), 'link' => 'changes.php', 'visible' => true];
$menu['help']['menuitem'][] = ['desc' => gtext('License & Credits'), 'link' => 'license.php', 'visible' => true];
$menu['help']['menuitem'][] = ['desc' => gtext('Donate'), 'link' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=info%40nas4free%2eorg&lc=US&item_name=NAS4Free%20Project&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest', 'visible' => TRUE, 'target' => '_blank'];
function display_menu($menuid) {
	global $menu;

	// Is menu visible?
	if (!$menu[$menuid]['visible'])
		return;

	$link = $menu[$menuid]['link'];
	if ($link == '') $link = 'index.php';
	echo "<li>\n";
	$agent = $_SERVER['HTTP_USER_AGENT']; // Put browser name into local variable for desktop/mobile detection
	if ((preg_match('/iPhone/i', $agent)) || (preg_match('/android/i', $agent))) {
		echo "<a href=\"javascript:mopen('{$menuid}');\" onmouseout=\"mclosetime()\">".$menu[$menuid]['desc']."</a>\n";
	} else {
//		echo "<a href=\"{$link}\" onmouseover=\"mopen('{$menuid}')\" onmouseout=\"mclosetime()\">".$menu[$menuid]['desc']."</a>\n";
		echo "<a href=\"{$link}\" onclick=\"spinner()\" onmouseover=\"mopen('{$menuid}')\" onmouseout=\"mclosetime()\">".$menu[$menuid]['desc']."</a>\n";
	}
	echo "	<div id=\"{$menuid}\" onmouseover=\"mcancelclosetime()\" onmouseout=\"mclosetime()\">\n";

	# Display menu items.
	foreach ($menu[$menuid]['menuitem'] as $menuk => $menuv) {
		# Is menu item visible?
		if (!$menuv['visible']) {
			continue;
		}
		if (!isset($menuv['type']) || 'separator' !== $menuv['type']) {
			# Display menuitem.
			$link = $menuv['link'];
			if ($link == '') $link = 'index.php';
//			echo "<a href=\"{$link}\" target=\"" . (empty($menuv['target']) ? "_self" : $menuv['target']) . "\" title=\"".$menuv['desc']."\">".$menuv['desc']."</a>\n";
			echo '<a href="', $link, '" onclick="spinner()" target="', (empty($menuv['target']) ? "_self" : $menuv['target']), '" title="', $menuv['desc'], '">', $menuv['desc'], '</a>', "\n";
		} else {
			# Display separator.
			echo "<span class=\"tabseparator\">&nbsp;</span>";
		}
	}

	echo "	</div>\n";
	echo "</li>\n";
}
?>
<?php header('Content-Type: text/html; charset=' . system_get_language_codeset());?>
<?php
  // XML declarations
/*
  some browser might be broken.
  echo '<?xml version="1.0" encoding="'.system_get_language_codeset().'"?>';
  echo "\n";
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=system_get_language_code();?>" lang="<?=system_get_language_code();?>">
<head>
	<title><?=genhtmltitle($pgtitle);?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=system_get_language_codeset();?>" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<?php if (isset($pgrefresh) && $pgrefresh):?>
	<meta http-equiv="refresh" content="<?=$pgrefresh;?>" />
	<?php endif;?>
	<link href="/css/gui.css" rel="stylesheet" type="text/css" />
	<link href="/css/navbar.css" rel="stylesheet" type="text/css" />
	<link href="/css/tabs.css" rel="stylesheet" type="text/css" />	
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="/js/gui.js"></script>
	<?php include("js/spinner.inc");?>
	<script type="text/javascript" src="/js/spin.min.js"></script>
	<?php
	if (isset($pglocalheader) && !empty($pglocalheader)) {
		if (is_array($pglocalheader)) {
			foreach ($pglocalheader as $pglocalheaderv) {
		 		echo $pglocalheaderv;
				echo "\n";
			}
		} else {
			echo $pglocalheader;
			echo "\n";
		}
	}
	?>
</head>
<body>
<div id="header">
	<div id="headerlogo">
		<a title="www.<?=get_product_url();?>" href="http://<?=get_product_url();?>" target="_blank"><img src="/images/header_logo.png" alt="logo" /></a>
	</div>
	<div id="headerrlogo">
		<div class="hostname">
			<span><?=system_get_hostname();?>&nbsp;</span>
		</div>
	</div>
</div>
<div id="headernavbar">
	<ul id="navbarmenu">
		<?=display_menu('system');?>
		<?=display_menu('network');?>
		<?=display_menu('disks');?>
		<?=display_menu('services');?>
		<!-- Begin extension section -->
		<?php if ($isAdminSession && is_dir("{$g['www_path']}/ext")):?>
		<li>
		<?php
			$agent = $_SERVER['HTTP_USER_AGENT']; // Put browser name into local variable for desktop/mobile detection
			if ((preg_match('/iPhone/i', $agent)) || (preg_match('/android/i', $agent))) {
				echo "<a href=\"javascript:mopen('extensions');\" onmouseout=\"mclosetime()\">".gtext('Extensions')."</a>";
			} else {
//				echo "<a href=\"index.php\" onmouseover=\"mopen('extensions')\" onmouseout=\"mclosetime()\">".gtext('Extensions')."</a>";
				echo "<a href=\"index.php\" onclick=\"spinner()\" onmouseover=\"mopen('extensions')\" onmouseout=\"mclosetime()\">".gtext('Extensions')."</a>";
			}
		?>
		<div id="extensions" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
		<?php
			$dh = @opendir("{$g['www_path']}/ext");
			if ($dh) {
				while (($extd = readdir($dh)) !== false) {
					if (($extd === '.') || ($extd === '..'))
						continue;
					@include("{$g['www_path']}/ext/" . $extd . "/menu.inc");
				}
				closedir($dh);
		}?>
		</div>
		</li>
		<?php endif;?>
		<!-- End extension section -->
		<?=display_menu('vm');?>
		<?=display_menu('access');?>
		<?=display_menu('status');?>
		<?=display_menu('diagnostics');?>
		<?=display_menu('advanced');?>
		<?=display_menu('help');?>
	</ul>
	<div style="clear:both"></div>
</div>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// init spinner on tabnav
	$("#tabnav").on('click', function() { spinner(); });
	// and his mate
	$("#tabnav2").on('click', function() { spinner(); });
});
//]]>
</script>
<div id="pagecontent">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr valign="top">
<td>
	<table width="100%" border="0" cellpadding="10" cellspacing="0">
	<tr>
	<td>
<?php if (!isset($pgtitle_omit) || !$pgtitle_omit): ?>
	<p class="pgtitle"><?=gentitle($pgtitle);?></p>
<?php endif;?>
