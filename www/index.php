<?php
/*
	index.php

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
// Configure page permission
$pgperm['allowuser'] = TRUE;

require("auth.inc");
require("guiconfig.inc");
require("zfs.inc");

$pgtitle = array(gtext("System Information"));
$pgtitle_omit = true;

if (!isset($config['vinterfaces']['carp']) || !is_array($config['vinterfaces']['carp']))
	$config['vinterfaces']['carp'] = array();

$smbios = get_smbios_info();
$cpuinfo = system_get_cpu_info();

function get_vip_status() {
	global $config;

	if (empty($config['vinterfaces']['carp']))
		return "";

	$a_vipaddrs = array();
	foreach ($config['vinterfaces']['carp'] as $carp) {
		$ifinfo = get_carp_info($carp['if']);
		//$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
		$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
	}
	return join(', ', $a_vipaddrs);
}

function get_ups_disp_status($ups_status) {
	if (empty($ups_status))
		return "";
	$status = explode(' ', $ups_status);
	foreach ($status as $condition) {
		if ($disp_status) $disp_status .= ', ';
		switch ($condition) {
		case 'WAIT':
			$disp_status .= gtext('UPS Waiting');
			break;
		case 'OFF':
			$disp_status .= gtext('UPS Off Line');
			break;
		case 'OL':
			$disp_status .= gtext('UPS On Line');
			break;
		case 'OB':
			$disp_status .= gtext('UPS On Battery');
			break;
		case 'TRIM':
			$disp_status .= gtext('SmartTrim');
			break;
		case 'BOOST':
			$disp_status .= gtext('SmartBoost');
			break;
		case 'OVER':
			$disp_status .= gtext('Overload');
			break;
		case 'LB':
			$disp_status .= gtext('Battery Low');
			break;
		case 'RB':
			$disp_status .= gtext('Replace Battery UPS');
			break;
		case 'CAL':
			$disp_status .= gtext('Calibration Battery');
			break;
		case 'CHRG':
			$disp_status .= gtext('Charging Battery');
			break;
		default:
			$disp_status .= $condition;
			break;
		}
	}
	return $disp_status;
}

function get_upsinfo() {
	global $config;

	if (!isset($config['ups']['enable']))
		return NULL;
	$ups = array();
	$cmd = "/usr/local/bin/upsc {$config['ups']['upsname']}@{$config['ups']['ip']}";
	exec($cmd,$rawdata);
	foreach($rawdata as $line) {
		$line = explode(':', $line);
		$ups[$line[0]] = trim($line[1]);
	}
	$disp_status = get_ups_disp_status($ups['ups.status']);
	$ups['disp_status'] = $disp_status;
	$value = !empty($ups['ups.load']) ? $ups['ups.load'] : 0;
	$ups['load'] = array(
		"percentage" => $value,
		"used" => sprintf("%.1f", $value),
		"tooltip_used" => sprintf("%s%%", $value),
		"tooltip_available" => sprintf(gtext("%s%% available"), 100 - $value),
	);
	$value = !empty($ups['battery.charge']) ? $ups['battery.charge'] : 0;
	$ups['battery'] = array(
		"percentage" => $value,
		"used" => sprintf("%.1f", $value),
		"tooltip_used" => sprintf("%s%%", $value),
		"tooltip_available" => sprintf(gtext("%s%% available"), 100 - $value),
	);
	return $ups;
}

function get_upsinfo2() {
	global $config;

	if (!isset($config['ups']['enable']) || !isset($config['ups']['ups2']))
		return NULL;
	$ups = array();
	$cmd = "/usr/local/bin/upsc {$config['ups']['ups2_upsname']}@{$config['ups']['ip']}";
	exec($cmd,$rawdata);
	foreach($rawdata as $line) {
		$line = explode(':', $line);
		$ups[$line[0]] = trim($line[1]);
	}
	$disp_status = get_ups_disp_status($ups['ups.status']);
	$ups['disp_status'] = $disp_status;
	$value = !empty($ups['ups.load']) ? $ups['ups.load'] : 0;
	$ups['load'] = array(
		"percentage" => $value,
		"used" => sprintf("%.1f", $value),
		"tooltip_used" => sprintf("%s%%", $value),
		"tooltip_available" => sprintf(gtext("%s%% available"), 100 - $value),
	);
	$value = !empty($ups['battery.charge']) ? $ups['battery.charge'] : 0;
	$ups['battery'] = array(
		"percentage" => $value,
		"used" => sprintf("%.1f", $value),
		"tooltip_used" => sprintf("%s%%", $value),
		"tooltip_available" => sprintf(gtext("%s%% available"), 100 - $value),
	);
	return $ups;
}

function get_vbox_vminfo($user, $uuid) {
	$vminfo = array();
	unset($rawdata);
	mwexec2("/usr/local/bin/sudo -u {$user} /usr/local/bin/VBoxManage showvminfo --machinereadable {$uuid}", $rawdata);
	foreach ($rawdata as $line) {
		if (preg_match("/^([^=]+)=(\"([^\"]+)\"|[^\"]+)/", $line, $match)) {
			$a = array();
			$a['raw'] = $match[0];
			$a['key'] = $match[1];
			$a['value'] = isset($match[3]) ? $match[3] : $match[2];
			$vminfo[$a['key']] = $a;
		}
	}
	return $vminfo;
}

function get_xen_info() {
	$info = array();
	unset($rawdata);
	mwexec2("/usr/local/sbin/xl info", $rawdata);
	foreach ($rawdata as $line) {
		if (preg_match("/^([^:]+)\s+:\s+(.+)\s*$/", $line, $match)) {
			$a = array();
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		}
	}
	return $info;
}

function get_xen_console($domid) {
	$info = array();
	unset($rawdata);
	mwexec2("/usr/local/bin/xenstore-ls /local/domain/{$domid}/console", $rawdata);
	foreach ($rawdata as $line) {
		if (preg_match("/^([^=]+)\s+=\s+\"(.+)\"$/", $line, $match)) {
			$a = array();
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		}
	}
	return $info;
}

if (is_ajax()) {
	$sysinfo = system_get_sysinfo();
	$vipstatus = get_vip_status();
	$sysinfo['vipstatus'] = $vipstatus;
	$upsinfo = get_upsinfo();
	$upsinfo2 = get_upsinfo2();
	$sysinfo['upsinfo'] = $upsinfo;
	$sysinfo['upsinfo2'] = $upsinfo2;
	render_ajax($sysinfo);
}

function tblrow ($name, $value, $symbol = null, $id = null) {
	if(!$value) return;

	if($symbol == '&deg;')
		$value = sprintf("%.1f", $value);

	if($symbol == 'Hz')
		$value = sprintf("%d", $value);

	
	if ($symbol == 'pre') {
		$value = '<pre>'.$value;
		$symbol = '</pre>';
	}

	print(<<<EOD
<tr id='{$id}'>
	<td>
		<div id='ups_status'>
			<span name='ups_status_name' id='ups_status_name' class='name'><b>{$name}</b></span><br />
			{$value}{$symbol}
		</div>
	</td>
</tr>
EOD
	."\n");
}

function tblrowbar ($id, $name, $value) {
		if(is_null($value)) return;
		$available = 100 - $value;
		$tooltip_used = sprintf("%s%%", $value);
		$tooltip_available = sprintf(gtext("%s%% available"), $available);
		$span_used = sprintf("%s%%", "<span name='ups_status_used' id='ups_status_{$id}_used' class='capacity'>".$value."</span>");
	
	print(<<<EOD
<tr>
  <td>
	<div id='ups_status'>
		<span name='ups_status_name' id='ups_status_{$id}_name' class='name'><b>{$name}</b></span><br />
		<img src="images/bar_left.gif" class="progbarl" alt="" /><img src="images/bar_blue.gif" name="ups_status_bar_used" id="ups_status_{$id}_bar_used" width="{$value}" class="progbarcf" title="{$tooltip_used}" alt="" /><img src="images/bar_gray.gif" name="ups_status_bar_free" id="ups_status_{$id}_bar_free" width="{$available}" class="progbarc" title="{$tooltip_available}" alt="" /><img src="images/bar_right.gif" class="progbarr" alt="" />
		{$span_used}
	</div>
  </td>
</tr>
EOD
	."\n");
}

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
     @date_default_timezone_set(@date_default_timezone_get());
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'index.php', null, function(data) {
		if ($('#vipstatus').length > 0)
			$('#vipstatus').text(data.vipstatus);
		if ($('#system_uptime').length > 0)
			$('#system_uptime').text(data.uptime);
		if ($('#system_datetime').length > 0)
			$('#system_datetime').text(data.date);
		if ($('#memusage').length > 0) {
			$('#memusage').val(data.memusage.caption);
			$('#memusageu').attr('width', data.memusage.percentage + 'px');
			$('#memusagef').attr('width', (100 - data.memusage.percentage) + 'px');
		}
		if ($('#loadaverage').length > 0)
			$('#loadaverage').val(data.loadaverage);
		if (typeof(data.cputemp) != 'undefined')
			if ($('#cputemp').length > 0)
				$('#cputemp').val(data.cputemp);
		if (typeof(data.cputemp2) != 'undefined') {
			for (var idx = 0; idx < data.cputemp2.length; idx++) {
				if ($('#cputemp'+idx).length > 0)
					$('#cputemp'+idx).val(data.cputemp2[idx]);
			}
		}
		if (typeof(data.cpufreq) != 'undefined')
			if ($('#cpufreq').length > 0)
				$('#cpufreq').val(data.cpufreq + 'MHz');
		if (typeof(data.cpuusage) != 'undefined') {
			if ($('#cpuusage').length > 0) {
				$('#cpuusage').val(data.cpuusage + '%');
				$('#cpuusageu').attr('width', data.cpuusage + 'px');
				$('#cpuusagef').attr('width', (100 - data.cpuusage) + 'px');
			}
		}
		if (typeof(data.cpuusage2) != 'undefined') {
			for (var idx = 0; idx < data.cpuusage2.length; idx++) {
				if ($('#cpuusage'+idx).length > 0) {
					$('#cpuusage'+idx).val(data.cpuusage2[idx] + '%');
					$('#cpuusageu'+idx).attr('width', data.cpuusage2[idx] + 'px');
					$('#cpuusagef'+idx).attr('width', (100 - data.cpuusage2[idx]) + 'px');
				}
			}
		}

		if (typeof(data.diskusage) != 'undefined') {
			for (var idx = 0; idx < data.diskusage.length; idx++) {
				var du = data.diskusage[idx];
				if ($('#diskusage_'+du.id+'_bar_used').length > 0) {
					$('#diskusage_'+du.id+'_name').text(du.name);
					$('#diskusage_'+du.id+'_bar_used').attr('width', du.percentage + 'px');
					$('#diskusage_'+du.id+'_bar_used').attr('title', du['tooltip'].used);
					$('#diskusage_'+du.id+'_bar_free').attr('width', (100 - du.percentage) + 'px');
					$('#diskusage_'+du.id+'_bar_free').attr('title', du['tooltip'].avail);
					$('#diskusage_'+du.id+'_capacity').text(du.capacity);
					$('#diskusage_'+du.id+'_capofsize').text(du.capofsize);
					$('#diskusage_'+du.id+'_size').text(du.size);
					$('#diskusage_'+du.id+'_used').text(du.used);
					$('#diskusage_'+du.id+'_avail').text(du.avail);
				}
			}
		}
		if (typeof(data.poolusage) != 'undefined') {
			for (var idx = 0; idx < data.poolusage.length; idx++) {
				var pu = data.poolusage[idx];
				if ($('#poolusage_'+pu.id+'_bar_used').length > 0) {
					$('#poolusage_'+pu.id+'_name').text(pu.name);
					$('#poolusage_'+pu.id+'_bar_used').attr('width', pu.percentage + 'px');
					$('#poolusage_'+pu.id+'_bar_used').attr('title', pu['tooltip'].used);
					$('#poolusage_'+pu.id+'_bar_free').attr('width', (100 - pu.percentage) + 'px');
					$('#poolusage_'+pu.id+'_bar_free').attr('title', pu['tooltip'].avail);
					$('#poolusage_'+pu.id+'_capacity').text(pu.capacity);
					$('#poolusage_'+pu.id+'_capofsize').text(pu.capofsize);
					$('#poolusage_'+pu.id+'_size').text(pu.size);
					$('#poolusage_'+pu.id+'_used').text(pu.used);
					$('#poolusage_'+pu.id+'_avail').text(pu.avail);
					$('#poolusage_'+pu.id+'_state').children().text(pu.health);
				}
			}
		}
		if (typeof(data.swapusage) != 'undefined') {
			for (var idx = 0; idx < data.swapusage.length; idx++) {
				var su = data.swapusage[idx];
				if ($('#swapusage_'+su.id+'_bar_used').length > 0) {
					$('#swapusage_'+su.id+'_name').text(su.name);
					$('#swapusage_'+su.id+'_bar_used').attr('width', su.percentage + 'px');
					$('#swapusage_'+su.id+'_bar_used').attr('title', su['tooltip'].used);
					$('#swapusage_'+su.id+'_bar_free').attr('width', (100 - su.percentage) + 'px');
					$('#swapusage_'+su.id+'_bar_free').attr('title', su['tooltip'].avail);
					$('#swapusage_'+su.id+'_capacity').text(su.capacity);
					$('#swapusage_'+su.id+'_capofsize').text(su.capofsize);
					$('#swapusage_'+su.id+'_size').text(su.size);
					$('#swapusage_'+su.id+'_used').text(su.used);
					$('#swapusage_'+su.id+'_avail').text(su.avail);
				}
			}
		}
		if (typeof(data.upsinfo) != 'undefined' && data.upsinfo !== null) {
			if ($('#ups_status_disp_status').length > 0)
				$('#ups_status_disp_status').text(data.upsinfo.disp_status);
			var ups_id = "load";
			var ui = data.upsinfo[ups_id];
			if ($('#ups_status_'+ups_id+'_bar_used').length > 0) {
				$('#ups_status_'+ups_id+'_bar_used').attr('width', ui.percentage + 'px');
				$('#ups_status_'+ups_id+'_bar_used').attr('title', ui.tooltip_used);
				$('#ups_status_'+ups_id+'_bar_free').attr('width', (100 - ui.percentage) + 'px');
				$('#ups_status_'+ups_id+'_bar_free').attr('title', ui.tooltip_available);
				$('#ups_status_'+ups_id+'_used').text(ui.used);
			}
			var ups_id = "battery";
			var ui = data.upsinfo[ups_id];
			if ($('#ups_status_'+ups_id+'_bar_used').length > 0) {
				$('#ups_status_'+ups_id+'_bar_used').attr('width', ui.percentage + 'px');
				$('#ups_status_'+ups_id+'_bar_used').attr('title', ui.tooltip_used);
				$('#ups_status_'+ups_id+'_bar_free').attr('width', (100 - ui.percentage) + 'px');
				$('#ups_status_'+ups_id+'_bar_free').attr('title', ui.tooltip_available);
				$('#ups_status_'+ups_id+'_used').text(ui.used);
			}
		}
		if (typeof(data.upsinfo2) != 'undefined' && data.upsinfo2 !== null) {
			if ($('#ups_status_disp_status2').length > 0)
				$('#ups_status_disp_status2').text(data.upsinfo2.disp_status);
			var ups_id = "load2";
			var ui = data.upsinfo2["load"];
			if ($('#ups_status_'+ups_id+'_bar_used').length > 0) {
				$('#ups_status_'+ups_id+'_bar_used').attr('width', ui.percentage + 'px');
				$('#ups_status_'+ups_id+'_bar_used').attr('title', ui.tooltip_used);
				$('#ups_status_'+ups_id+'_bar_free').attr('width', (100 - ui.percentage) + 'px');
				$('#ups_status_'+ups_id+'_bar_free').attr('title', ui.tooltip_available);
				$('#ups_status_'+ups_id+'_used').text(ui.used);
			}
			var ups_id = "battery2";
			var ui = data.upsinfo2["battery"];
			if ($('#ups_status_'+ups_id+'_bar_used').length > 0) {
				$('#ups_status_'+ups_id+'_bar_used').attr('width', ui.percentage + 'px');
				$('#ups_status_'+ups_id+'_bar_used').attr('title', ui.tooltip_used);
				$('#ups_status_'+ups_id+'_bar_free').attr('width', (100 - ui.percentage) + 'px');
				$('#ups_status_'+ups_id+'_bar_free').attr('title', ui.tooltip_available);
				$('#ups_status_'+ups_id+'_used').text(ui.used);
			}
		}
	});
});
//]]>
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<td>&nbsp;</td>
</table>
<?php
	// make sure normal user such as www can write to temporary
	$perms = fileperms("/tmp");
	if (($perms & 01777) != 01777) {
		$errormsg .= sprintf(gtext("Wrong permission on %s."), "/tmp");
		$errormsg .= "<br />\n";
	}
	$perms = fileperms("/var/tmp");
	if (($perms & 01777) != 01777) {
		$errormsg .= sprintf(gtext("Wrong permission on %s."), "/var/tmp");
		$errormsg .= "<br />\n";
	}
	// check DNS
	list($v4dns1,$v4dns2) = get_ipv4dnsserver();
	list($v6dns1,$v6dns2) = get_ipv6dnsserver();
	if (empty($v4dns1) && empty($v4dns2) && empty($v6dns1) && empty($v6dns2)) {
		// need by service/firmware check?
		if (!isset($config['system']['disablefirmwarecheck'])
		   || isset($config['ftpd']['enable'])) {
			$errormsg .= gtext("No DNS setting found.");
			$errormsg .= "<br />\n";
		}
	}
	if (!empty($errormsg)) print_error_box($errormsg);
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabcont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php html_titleline(gtext("System Information"));?>
	<?php if (!empty($config['vinterfaces']['carp'])):?>
	<?php html_textinfo("vipstatus", gtext("Virtual IP address"), htmlspecialchars(get_vip_status()));?>
	<?php endif;?>
	<?php html_textinfo("hostname", gtext("Hostname"), system_get_hostname());?>
	<?php html_textinfo("version", gtext("Version"), sprintf("<strong>%s %s</strong> (%s %s)", get_product_version(), get_product_versionname(), gtext("revision"), get_product_revision()));?>
	<?php html_textinfo("builddate", gtext("Compiled"), htmlspecialchars(get_datetime_locale(get_product_buildtimestamp())));?>
	<?php
		exec("/sbin/sysctl -n kern.version", $osversion);
	?>
	<?php html_textinfo("platform_os", gtext("Platform OS"), sprintf("%s", $osversion[0]));?>
	<?php html_textinfo("platform", gtext("Platform"), sprintf(gtext("%s on %s"), $g['fullplatform'], $cpuinfo['model']));?>
	<?php
		if (!empty($smbios['planar'])) {
			html_textinfo("system", gtext("System"), sprintf("%s %s", htmlspecialchars($smbios['planar']['maker']), htmlspecialchars($smbios['planar']['product'])));
		} else {
			html_textinfo("system", gtext("System"), sprintf("%s %s", htmlspecialchars($smbios['system']['maker']), htmlspecialchars($smbios['system']['product'])));
		}
	?>
	<?php html_textinfo("system_bios", gtext("System bios"), sprintf("%s %s %s %s", htmlspecialchars($smbios['bios']['vendor']), gtext("version:"), htmlspecialchars($smbios['bios']['version']), htmlspecialchars($smbios['bios']['reldate'])));?>
	<?php html_textinfo("system_datetime", gtext("System time"), htmlspecialchars(get_datetime_locale()));?>
	<?php html_textinfo("system_uptime", gtext("System uptime"), htmlspecialchars(system_get_uptime()));?>
	<?php if (Session::isAdmin()):?>
		<?php if ($config['lastchange']):?>
			<?php html_textinfo("last_config_change", gtext("System last config change"), htmlspecialchars(get_datetime_locale($config['lastchange'])));?>
		<?php endif;?>
		<?php if (!empty($cpuinfo['temperature2'])):
			echo "<tr>";
			echo "<td width='25%' class='vncellt'>".gtext("CPU temperature")."</td>";
			echo "<td width='75%' class='listr'>";
			echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td>\n";
			$cpus = system_get_cpus();
			for ($idx = 0; $idx < $cpus; $idx++) {
				if (empty($cpuinfo['temperature2'][$idx])) continue;
				echo "<tr><td>";
				echo "<input style='padding: 0; border: 0; background-color:#FCFCFC;' size='2' name='cputemp${idx}' id='cputemp${idx}' value='".htmlspecialchars($cpuinfo['temperature2'][$idx])."' />";
				echo $idx['temperature2']."&#176;C";
				echo "</td></tr>";
			}
			echo "</table></td>";
			echo "</tr>\n";
		?>
		<?php elseif (!empty($cpuinfo['temperature'])):?>
			<tr>
				<td width="25%" class="vncellt"><?=gtext("CPU temperature");?></td>
				<td width="75%" class="listr">
					<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="30" name="cputemp" id="cputemp" value="<?=htmlspecialchars($cpuinfo['temperature']);?>" />
				</td>
			</tr>
		<?php endif;?>
		<?php if (!empty($cpuinfo['freq'])):?>
			<tr>
				<td width="25%" class="vncellt"><?=gtext("CPU frequency");?></td>
				<td width="75%" class="listr">
					<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="30" name="cpufreq" id="cpufreq" value="<?=htmlspecialchars($cpuinfo['freq']);?>MHz" title="<?=sprintf(gtext("Levels (MHz/mW): %s"), $cpuinfo['freqlevels']);?>" />
				</td>
			</tr>
		<?php endif;?>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("CPU usage");?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
			<?php
				$percentage = 0;
				echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
				echo "<img src='images/bar_blue.gif' name='cpuusageu' id='cpuusageu' width='" . $percentage . "' class='progbarcf' alt='' />";
				echo "<img src='images/bar_gray.gif' name='cpuusagef' id='cpuusagef' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
				echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
			?>
				<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="30" name="cpuusage" id="cpuusage" value="<?=gtext("Updating in 5 seconds.");?>" />
			</td></tr>
			<?php
				$cpus = system_get_cpus();
				if ($cpus > 1) {
					echo "<tr><td><hr size='1' /></td></tr>";
					for ($idx = 0; $idx < $cpus; $idx++) {
						$percentage = 0;
						echo "<tr><td>";
						echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
						echo "<img src='images/bar_blue.gif' name='cpuusageu${idx}' id='cpuusageu${idx}' width='" . $percentage . "' class='progbarcf' alt='' />";
						echo "<img src='images/bar_gray.gif' name='cpuusagef${idx}' id='cpuusagef${idx}' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
						echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
						echo "<input style='padding: 0; border: 0; background-color:#FCFCFC;' size='30' name='cpuusage${idx}' id='cpuusage${idx}' value=\"".gtext("Updating in 5 seconds.")."\" />";
						echo "</td></tr>";
					}
				}
			?>
			</table></td>
		</tr>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("Memory usage");?></td>
			<td width="75%" class="listr">
			<?php
				$raminfo = system_get_ram_info();
				$percentage = round(($raminfo['used'] * 100) / $raminfo['total'], 0);
				echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
				echo "<img src='images/bar_blue.gif' name='memusageu' id='memusageu' width='" . $percentage . "' class='progbarcf' alt='' />";
				echo "<img src='images/bar_gray.gif' name='memusagef' id='memusagef' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
				echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
			?>
			<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="30" name="memusage" id="memusage" value="<?=sprintf(gtext("%d%% of %dMiB"), $percentage, round($raminfo['physical'] / 1024 / 1024));?>" />
			</td>
		</tr>
		<?php $a_swapusage = get_swap_usage(); if (!empty($a_swapusage)):?>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("Swap usage");?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<?php
				$index = 0;
				foreach ($a_swapusage as $r_swapusage) {
					$ctrlid = $r_swapusage['id'];
					$percent_used = $r_swapusage['percentage'];
					$tooltip_used = $r_swapusage['tooltip']['used'];
					$tooltip_avail = $r_swapusage['tooltip']['avail'];

					echo "<tr><td><div id='swapusage'>";
					echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
					echo "<img src='images/bar_blue.gif' name='swapusage_{$ctrlid}_bar_used' id='swapusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
					echo "<img src='images/bar_gray.gif' name='swapusage_{$ctrlid}_bar_free' id='swapusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_avail}' alt='' />";
					echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
					echo "<span name='swapusage_{$ctrlid}_capofsize' id='swapusage_{$ctrlid}_capofsize' class='capofsize'>{$r_swapusage['capofsize']}</span>";
					echo "<br />";
					echo sprintf(gtext("Device: %s | Total: %s | Used: %s | Free: %s"),
						"<span name='swapusage_{$ctrlid}_name' id='swapusage_{$ctrlid}_name' class='name'>{$r_swapusage['name']}</span>",
						"<span name='swapusage_{$ctrlid}_size' id='swapusage_{$ctrlid}_size' class='size'>{$r_swapusage['size']}</span>",
						"<span name='swapusage_{$ctrlid}_used' id='swapusage_{$ctrlid}_used' class='used'>{$r_swapusage['used']}</span>",
						"<span name='swapusage_{$ctrlid}_avail' id='swapusage_{$ctrlid}_avail' class='avail'>{$r_swapusage['avail']}</span>");
					echo "</div></td></tr>";

					if (++$index < count($a_swapusage))
						echo "<tr><td><hr size='1' /></td></tr>\n";
				}
			?>
			</table></td>
		</tr>
		<?php endif;?>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("Load averages");?></td>
			<td width="75%" class="listr">
			<?php
				exec("uptime", $result);
				$loadaverage = substr(strrchr($result[0], "load averages:"), 15);
				?>
				<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="18" name="loadaverage" id="loadaverage" value="<?=$loadaverage;?>" />
				<?="<small>[<a href='status_process.php'>".gtext("Show Process Information")."</a></small>]";?>
			</td>
		</tr>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("Disk space usage");?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<?php
				$a_diskusage = get_disk_usage();
				if (!empty($a_diskusage)) {
					$index = 0;
					foreach ($a_diskusage as $r_diskusage) {
						$ctrlid = $r_diskusage['id'];
						$percent_used = $r_diskusage['percentage'];
						$tooltip_used = $r_diskusage['tooltip']['used'];
						$tooltip_avail = $r_diskusage['tooltip']['avail'];

						echo "<tr><td><div id='diskusage'>";
						echo "<span name='diskusage_{$ctrlid}_name' id='diskusage_{$ctrlid}_name' class='name'>{$r_diskusage['name']}</span><br />";
						echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
						echo "<img src='images/bar_blue.gif' name='diskusage_{$ctrlid}_bar_used' id='diskusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
						echo "<img src='images/bar_gray.gif' name='diskusage_{$ctrlid}_bar_free' id='diskusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_avail}' alt='' />";
						echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
						echo "<span name='diskusage_{$ctrlid}_capofsize' id='diskusage_{$ctrlid}_capofsize' class='capofsize'>{$r_diskusage['capofsize']}</span>";
						echo "<br />";
						echo sprintf(gtext("Total: %s | Used: %s | Free: %s"),
							"<span name='diskusage_{$ctrlid}_size' id='diskusage_{$ctrlid}_size' class='size'>{$r_diskusage['size']}</span>",
							"<span name='diskusage_{$ctrlid}_used' id='diskusage_{$ctrlid}_used' class='used'>{$r_diskusage['used']}</span>",
							"<span name='diskusage_{$ctrlid}_avail' id='diskusage_{$ctrlid}_avail' class='avail'>{$r_diskusage['avail']}</span>");
						echo "</div></td></tr>";

						if (++$index < count($a_diskusage))
							echo "<tr><td><hr size='1' /></td></tr>\n";
					}
				}

				$a_poolusage = get_pool_usage();
				if (!empty($a_poolusage)) {
					$index = 0;
					if (!empty($a_diskusage))
						echo "<tr><td><hr size='1' /></td></tr>\n";
					foreach ($a_poolusage as $r_poolusage) {
						$ctrlid = $r_poolusage['id'];
						$percent_used = $r_poolusage['percentage'];
						$tooltip_used = $r_poolusage['tooltip']['used'];
						$tooltip_avail = $r_poolusage['tooltip']['avail'];

						echo "<tr><td><div id='poolusage'>";
						echo "<span name='poolusage_{$ctrlid}_name' id='poolusage_{$ctrlid}_name' class='name'>{$r_poolusage['name']}</span><br />";
						echo "<img src='images/bar_left.gif' class='progbarl' alt='' />";
						echo "<img src='images/bar_blue.gif' name='poolusage_{$ctrlid}_bar_used' id='poolusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
						echo "<img src='images/bar_gray.gif' name='poolusage_{$ctrlid}_bar_free' id='poolusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_avail}' alt='' />";
						echo "<img src='images/bar_right.gif' class='progbarr' alt='' /> ";
						echo "<span name='poolusage_{$ctrlid}_capofsize' id='poolusage_{$ctrlid}_capofsize' class='capofsize'>{$r_poolusage['capofsize']}</span>";
						echo "<br />";
						echo sprintf(gtext("Total: %s | Alloc: %s | Free: %s | State: %s"),
							"<span name='poolusage_{$ctrlid}_size' id='poolusage_{$ctrlid}_size' class='size'>{$r_poolusage['size']}</span>",
							"<span name='poolusage_{$ctrlid}_used' id='poolusage_{$ctrlid}_used' class='used'>{$r_poolusage['used']}</span>",
							"<span name='poolusage_{$ctrlid}_avail' id='poolusage_{$ctrlid}_avail' class='avail'>{$r_poolusage['avail']}</span>",
							"<span name='poolusage_{$ctrlid}_state' id='poolusage_{$ctrlid}_state' class='state'><a href='disks_zfs_zpool_info.php?pool={$r_poolusage['name']}'>{$r_poolusage['health']}</a></span>");
						echo "</div></td></tr>";

						if (++$index < count($a_poolusage))
							echo "<tr><td><hr size='1' /></td></tr>\n";
					}
				}

				if (empty($a_diskusage) && empty($a_poolusage)) {
					echo "<tr><td>";
					echo gtext("No disk configured");
					echo "</td></tr>";
				}
			?>
			</table></td>
		</tr>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("UPS Status")." ".$config["ups"]["upsname"];?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<?php if (!isset($config['ups']['enable'])):?>
				<tr>
					<td>
						<input style="padding: 0; border: 0; background-color:#FCFCFC;" size="18" name="upsstatus" id="upsstatus" value="<?=gtext("UPS disabled");?>" />
					</td>
				</tr>
			<?php else:?>
			<?php
				$cmd = "/usr/local/bin/upsc {$config['ups']['upsname']}@{$config['ups']['ip']}";
				$handle = popen($cmd, 'r');

				if ($handle) {
					$read = fread($handle, 4096);
					pclose($handle);

					$lines = explode("\n", $read);
					$ups = array();
					foreach($lines as $line) {
						$line = explode(':', $line);
						$ups[$line[0]] = trim($line[1]);
					}

					if (count($lines) == 1)
						tblrow('ERROR:', 'Data stale!');

					$disp_status = get_ups_disp_status($ups['ups.status']);
					tblrow(gtext('Status'), '<span id="ups_status_disp_status">'.$disp_status."</span>". "  <small>[<a href='diag_infos_ups.php'>" . gtext("Show UPS Information")."</a></small>]");
					tblrowbar("load", gtext('Load'), $ups['ups.load'], '%', '100-80', '79-60', '59-0');
					tblrowbar("battery", gtext('Battery Level'), $ups['battery.charge'], '%', '0-29' ,'30-79', '80-100');
				}

				unset($handle);
				unset($read);
				unset($lines);
				unset($status);
				unset($disp_status);
				unset($ups);
				unset($cmd);
			?>
			<?php endif;?>
			</table></td>
		</tr>
		<?php
        	if (isset($config['ups']['enable']) && isset($config['ups']['ups2'])) { ?>
			<td width="25%" class="vncellt"><?=gtext("UPS Status")." ".$config["ups"]["ups2_upsname"];?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
		    <?php
                $cmd = "/usr/local/bin/upsc {$config['ups']['ups2_upsname']}@{$config['ups']['ip']}";
                $handle = popen($cmd, 'r');
                
                if ($handle) {
                	$read = fread($handle, 4096);
                	pclose($handle);
                
                	$lines = explode("\n", $read);
                	$ups = array();
                	foreach($lines as $line) {
                		$line = explode(':', $line);
                		$ups[$line[0]] = trim($line[1]);
                	}
                
                	if (count($lines) == 1)
                		tblrow('ERROR:', 'Data stale!');
                
                	$disp_status = get_ups_disp_status($ups['ups.status']);
                	tblrow(gtext('Status'), '<span id="ups_status_disp_status2">'.$disp_status."</span>". "  <small>[<a href='diag_infos_ups.php'>" . gtext("Show UPS Information")."</a></small>]");
                	tblrowbar("load2", gtext('Load'), $ups['ups.load'], '%', '100-80', '79-60', '59-0');
                	tblrowbar("battery2", gtext('Battery Level'), $ups['battery.charge'], '%', '0-29' ,'30-79', '80-100');
                }
                
                unset($handle);
                unset($read);
                unset($lines);
                unset($status);
                unset($disp_status);
                unset($ups);
                unset($cmd);
                echo('</table></td>');
	            echo('</tr>');
            }
        ?>
		<?php
			unset($vmlist);
			mwexec2("/usr/bin/find /dev/vmm -type c", $vmlist);
			unset($vmlist2);
			$vbox_user = "vboxusers";
			$vbox_if = get_ifname($config['interfaces']['lan']['if']);
			$vbox_ipaddr = get_ipaddr($vbox_if);
			if (isset($config['vbox']['enable'])) {
				mwexec2("/usr/local/bin/sudo -u {$vbox_user} /usr/local/bin/VBoxManage list runningvms", $vmlist2);
			} else {
				$vmlist2 = array();
			}
			unset($vmlist3);
			if ($g['arch'] == "dom0") {
				$xen_if = get_ifname($config['interfaces']['lan']['if']);
				$xen_ipaddr = get_ipaddr($xen_if);
				$vmlist_json = shell_exec("/usr/local/sbin/xl list -l");
				$vmlist3 = json_decode($vmlist_json, true);
			} else {
				$vmlist3 = array();
			}
			if (!empty($vmlist) || !empty($vmlist2) || !empty($vmlist3)):
		?>
		<tr>
			<td width="25%" class="vncellt"><?=gtext("Virtual Machine");?></td>
			<td width="75%" class="listr">
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
			<?php
				$vmtype = "BHyVe";
				$index = 0;
				foreach ($vmlist as $vmpath) {
					$vm = basename($vmpath);
					unset($temp);
					exec("/usr/sbin/bhyvectl ".escapeshellarg("--vm=$vm")." --get-lowmem | sed -e 's/.*\\///'", $temp);
					$vram = $temp[0] / 1024 / 1024;
					echo "<tr><td><div id='vminfo_$index'>";
					echo htmlspecialchars("$vmtype: $vm ($vram MiB)");
					echo "</div></td></tr>\n";
					if (++$index < count($vmlist))
						echo "<tr><td><hr size='1' /></td></tr>\n";
				}

				$vmtype = "VBox";
				$index = 0;
				foreach ($vmlist2 as $vmline) {
					$vm = "";
					if (preg_match("/^\"(.+)\"\s*\{(\S+)\}$/", $vmline, $match)) {
						$vm = $match[1];
						$uuid = $match[2];
					}
					if ($vm == "")
						continue;
					$vminfo = get_vbox_vminfo($vbox_user, $uuid);
					$vram = $vminfo['memory']['value'];
					echo "<tr><td><div id='vminfo2_$index'>";
					echo htmlspecialchars("$vmtype: $vm ($vram MiB)");
					if (isset($vminfo['vrde']) && $vminfo['vrde']['value'] == "on") {
						$vncport = $vminfo['vrdeport']['value'];
						$url = htmlspecialchars("/novnc/vnc.html?host={$vbox_ipaddr}&port={$vncport}");
						echo " <a href='{$url}' target=_blank>";
						echo htmlspecialchars("vnc://{$vbox_ipaddr}:{$vncport}/");
						echo "</a>";
					}
					echo "</div></td></tr>\n";
					if (++$index < count($vmlist2))
						echo "<tr><td><hr size='1' /></td></tr>\n";
				}

				$vmtype = "Xen";
				$index = 0;
				$vncport_unused = 5900;
				foreach ($vmlist3 as $k => $v) {
					$domid = $v['domid'];
					$type = $v['config']['c_info']['type'];
					$vm = $v['config']['c_info']['name'];
					$vram = (int)(($v['config']['b_info']['target_memkb'] + 1023 ) / 1024);
					$vcpus = 1;
					if ($domid == 0) {
						$vcpus = @exec("/sbin/sysctl -q -n hw.ncpu");
						$info = get_xen_info();
						$cpus = $info['nr_cpus']['value'];
						$th = $info['threads_per_core']['value'];
						if (empty($th)) {
							$th = 1;
						}
						$core = (int)($cpus / $th);
						$mem = $info['total_memory']['value'];
						$ver = $info['xen_version']['value'];
					} else if (!empty($v['config']['b_info']['max_vcpus'])) {
						$vcpus = $v['config']['b_info']['max_vcpus'];
					}
					echo "<tr><td><div id='vminfo3_$index'>";
					echo htmlspecialchars("$vmtype $type: $vm ($vram MiB / $vcpus VCPUs)");
					if ($domid == 0) {
						echo " ";
						echo htmlspecialchars("Xen version {$ver} / {$mem} MiB / {$core} core".($th > 1 ? "/HT" : ""));
					} else if ($type == 'pv' && isset($v['config']['vfbs']) && isset($v['config']['vfbs'][0]['vnc'])) {
						$vnc = $v['config']['vfbs'][0]['vnc'];
						$vncport = "unknown";
						/*
						if (isset($vnc['display'])) {
							$vncdisplay = $vnc['display'];
							$vncport = 5900 + $vncdisplay;
						} else if (isset($vnc['findunused'])) {
							$vncport = $vncport_unused;
							$vncport_unused++;
						}
						*/
						$console = get_xen_console($domid);
						if (!empty($console) && isset($console['vnc-port'])) {
							$vncport = $console['vnc-port']['value'];
						}

						echo " ";
						echo htmlspecialchars("vnc://{$xen_ipaddr}:{$vncport}/");
					} else if ($type == 'hvm' && isset($v['config']['b_info']['type.hvm']['vnc']['enable'])) {
						$vnc = $v['config']['b_info']['type.hvm']['vnc'];
						$vncport = "unknown";
						/*
						if (isset($vnc['display'])) {
							$vncdisplay = $vnc['display'];
							$vncport = 5900 + $vncdisplay;
						} else if (isset($vnc['findunused'])) {
							$vncport = $vncport_unused;
							$vncport_unused++;
						}
						*/
						$console = get_xen_console($domid);
						if (!empty($console) && isset($console['vnc-port'])) {
							$vncport = $console['vnc-port']['value'];
						}

						echo " ";
						echo htmlspecialchars("vnc://{$xen_ipaddr}:{$vncport}/");
					}
					echo "</div></td></tr>\n";
					if (++$index < count($vmlist3))
						echo "<tr><td><hr size='1' /></td></tr>\n";
				}
			?>
			</table></td>
		</tr>
		<?php endif;?>
	<?php endif;?>
	</table></td>
    </tr>
</table>
<?php include("fend.inc");?>
