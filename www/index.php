<?php
/*
	index.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
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

$pgtitle = array(gettext("System information"));
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

if (is_ajax()) {
	$sysinfo = system_get_sysinfo();
	$vipstatus = get_vip_status();
	$sysinfo['vipstatus'] = $vipstatus;
	render_ajax($sysinfo);
}

function tblrow ($name, $value, $symbol = null, $id = null) {
	if(!$value) return;

	if($symbol == '&deg;')
		$value = sprintf("%.1f", $value);

	if($symbol == 'Hz')
		$value = sprintf("%d", $value);
		
	if ($symbol == ' seconds'
			&& $value > 60) {
		$minutes = (int) ($value / 60);
		$seconds = $value % 60;
		
		if ($minutes > 60) {
			$hours = (int) ($minutes / 60);
			$minutes = $minutes % 60;
			$value = $hours;
			$symbol = ' hours '.$minutes.' minutes '.$seconds.$symbol;
		} else {
			$value = $minutes;
			$symbol = ' minutes '.$seconds.$symbol;
		}
	}
	
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

function tblrowbar ($name, $value, $symbol, $red, $yellow, $green) {
	if(!$value) return;

	$value = sprintf("%.1f", $value);

	$red = explode('-', $red);
	$yellow = explode('-', $yellow);
	$green = explode('-', $green);

	sort($red);
	sort($yellow);
	sort($green);

	if($value >= $red[0] && $value <= ($red[0]+9)) {
		$color = 'black';
		$bgcolor = 'red';
	}
	if($value >= ($red[0]+10) && $value <= $red[1]) {
		$color = 'white';
		$bgcolor = 'red';
	}
	if($value >= $yellow[0] && $value <= $yellow[1]) {
		$color = 'black';
		$bgcolor = 'yellow';
	}
	if($value >= $green[0] && $value <= ($green[0]+9)) {
		$color = 'black';
		$bgcolor = 'green';
	}	
	if($value >= ($green[0]+10) && $value <= $green[1]) {
		$color = 'white';
		$bgcolor = 'green';
	}

	$available = 100 - $value;
	$tooltip_used = sprintf("%s%%", $value);
	$tooltip_available = sprintf(gettext("%s%% available"), $available);
	$span_used = sprintf("%s%%", "<span name='ups_status_used' id='ups_status_used' class='capacity'>".$value."</span>");
	
	print(<<<EOD
<tr>
  <td>
	<div id='ups_status'>
		<span name='ups_status_name' id='ups_status_name' class='name'><b>{$name}</b></span><br />
		<img src="bar_left.gif" class="progbarl" alt="" /><img src="bar_blue.gif" name="ups_status_bar_used" id="ups_status_bar_used" width="{$value}" class="progbarcf" title="{$tooltip_used}" alt="" /><img src="bar_gray.gif" name="ups_status_bar_free" id="ups_status_bar_free" width="{$available}" class="progbarc" title="{$tooltip_available}" alt="" /><img src="bar_right.gif" class="progbarr" alt="" />
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
		if ($('#vipstatus').size() > 0)
			$('#vipstatus').text(data.vipstatus);
		if ($('#uptime').size() > 0)
			$('#uptime').text(data.uptime);
		if ($('#date').size() > 0)
			$('#date').val(data.date);
		if ($('#memusage').size() > 0) {
			$('#memusage').val(data.memusage.caption);
			$('#memusageu').attr('width', data.memusage.percentage + 'px');
			$('#memusagef').attr('width', (100 - data.memusage.percentage) + 'px');
		}
		if ($('#loadaverage').size() > 0)
			$('#loadaverage').val(data.loadaverage);
		if (typeof(data.cputemp) != 'undefined')
			if ($('#cputemp').size() > 0)
				$('#cputemp').val(data.cputemp);
		if (typeof(data.cputemp2) != 'undefined') {
			for (var idx = 0; idx < data.cputemp2.length; idx++) {
				if ($('#cputemp'+idx).size() > 0)
					$('#cputemp'+idx).val(data.cputemp2[idx]);
			}
		}
		if (typeof(data.cpufreq) != 'undefined')
			if ($('#cpufreq').size() > 0)
				$('#cpufreq').val(data.cpufreq + 'MHz');
		if (typeof(data.cpuusage) != 'undefined') {
			if ($('#cpuusage').size() > 0) {
				$('#cpuusage').val(data.cpuusage + '%');
				$('#cpuusageu').attr('width', data.cpuusage + 'px');
				$('#cpuusagef').attr('width', (100 - data.cpuusage) + 'px');
			}
		}
		if (typeof(data.cpuusage2) != 'undefined') {
			for (var idx = 0; idx < data.cpuusage2.length; idx++) {
				if ($('#cpuusage'+idx).size() > 0) {
					$('#cpuusage'+idx).val(data.cpuusage2[idx] + '%');
					$('#cpuusageu'+idx).attr('width', data.cpuusage2[idx] + 'px');
					$('#cpuusagef'+idx).attr('width', (100 - data.cpuusage2[idx]) + 'px');
				}
			}
		}

		if (typeof(data.diskusage) != 'undefined') {
			for (var idx = 0; idx < data.diskusage.length; idx++) {
				var du = data.diskusage[idx];
				if ($('#diskusage_'+du.id+'_bar_used').size() > 0) {
					$('#diskusage_'+du.id+'_name').text(du.name);
					$('#diskusage_'+du.id+'_bar_used').attr('width', du.percentage + 'px');
					$('#diskusage_'+du.id+'_bar_used').attr('title', du['tooltip'].used);
					$('#diskusage_'+du.id+'_bar_free').attr('width', (100 - du.percentage) + 'px');
					$('#diskusage_'+du.id+'_bar_free').attr('title', du['tooltip'].available);
					$('#diskusage_'+du.id+'_capacity').text(du.capacity);
					$('#diskusage_'+du.id+'_total').text(du.size);
					$('#diskusage_'+du.id+'_used').text(du.used);
					$('#diskusage_'+du.id+'_free').text(du.avail);
				}
			}
		}
		if (typeof(data.swapusage) != 'undefined') {
			for (var idx = 0; idx < data.swapusage.length; idx++) {
				var su = data.swapusage[idx];
				if ($('#swapusage_'+su.id+'_bar_used').size() > 0) {
					$('#swapusage_'+su.id+'_bar_used').attr('width', su.percentage + 'px');
					$('#swapusage_'+su.id+'_bar_used').attr('title', su['tooltip'].used);
					$('#swapusage_'+su.id+'_bar_free').attr('width', (100 - su.percentage) + 'px');
					$('#swapusage_'+su.id+'_bar_free').attr('title', su['tooltip'].available);
					$('#swapusage_'+su.id+'_capacity').text(su.capacity);
					$('#swapusage_'+su.id+'_total').text(su.total);
					$('#swapusage_'+su.id+'_used').text(su.used);
					$('#swapusage_'+su.id+'_free').text(su.avail);
				}
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
		$errormsg .= sprintf(gettext("Wrong permission on %s."), "/tmp");
		$errormsg .= "<br />\n";
	}
	$perms = fileperms("/var/tmp");
	if (($perms & 01777) != 01777) {
		$errormsg .= sprintf(gettext("Wrong permission on %s."), "/var/tmp");
		$errormsg .= "<br />\n";
	}
	// check DNS
	list($v4dns1,$v4dns2) = get_ipv4dnsserver();
	list($v6dns1,$v6dns2) = get_ipv6dnsserver();
	if (empty($v4dns1) && empty($v4dns2) && empty($v6dns1) && empty($v6dns2)) {
		// need by service/firmware check?
		if (!isset($config['system']['disablefirmwarecheck'])
		   || isset($config['ftpd']['enable'])) {
			$errormsg .= gettext("No DNS setting found.");
			$errormsg .= "<br />\n";
		}
	}
	if (!empty($errormsg)) print_error_box($errormsg);
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabcont">
    	<table width="100%" border="0" cellspacing="0" cellpadding="0">
 			  <tr>
			    <td colspan="2" class="listtopic"><?=gettext("System information");?></td>
			  </tr>
			  <?php if (!empty($config['vinterfaces']['carp'])):?>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("Virtual IP address");?></td>
			    <td width="75%" class="listr"><span id="vipstatus"><?php echo htmlspecialchars(get_vip_status()); ?></vip></td>
			  </tr>
			  <?php endif;?>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("Hostname");?></td>
			    <td width="75%" class="listr"><?=system_get_hostname();?></td>
			  </tr>
			  <tr>
			    <td width="25%" valign="top" class="vncellt"><?=gettext("Version");?></td>
			    <td width="75%" class="listr"><strong><?=get_product_version();?> <?=get_product_versionname();?></strong> (<?=gettext("revision");?> <?=get_product_revision();?>)</td>
			  </tr>
			  <tr>
			    <td width="25%" valign="top" class="vncellt"><?=gettext("Build date");?></td>
			    <td width="75%" class="listr"><?=get_product_buildtime();?>
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" valign="top" class="vncellt"><?=gettext("Platform OS");?></td>
			    <td width="75%" class="listr">
			      <?
			        exec("/sbin/sysctl -n kern.ostype", $ostype);
			        exec("/sbin/sysctl -n kern.osrelease", $osrelease);
			        exec("/sbin/sysctl -n kern.osreldate", $osreldate);
			        echo("$ostype[0] $osrelease[0] (reldate $osreldate[0])");
			      ?>
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("Platform");?></td>
			    <td width="75%" class="listr">
			    	<?=sprintf(gettext("%s on %s"), $g['fullplatform'], $cpuinfo['model']);?>
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("System");?></td>
			    <td width="75%" class="listr"><?=htmlspecialchars($smbios['planar']['maker']);?> <?=htmlspecialchars($smbios['planar']['product']);?></td>
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("System bios");?></td>
			    <td width="75%" class="listr"><?=htmlspecialchars($smbios['bios']['vendor']);?> <?=sprintf(gettext("version:"));?> <?=htmlspecialchars($smbios['bios']['version']);?> <?=htmlspecialchars($smbios['bios']['reldate']);?></td>
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("System time");?></td>
			    <td width="75%" class="listr">
			      <input style="padding: 0; border: 0;" size="30" name="date" id="date" value="<?=htmlspecialchars(shell_exec("date"));?>" />
			    </td>
			  </tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("System uptime");?></td>
			    <td width="75%" class="listr">
						<?php $uptime = system_get_uptime();?>
						<span name="uptime" id="uptime"><?=htmlspecialchars($uptime);?></span>
			    </td>
			  </tr>
			  <?php if (Session::isAdmin()):?>
			  <?php if ($config['lastchange']):?>
		    <tr>
		      <td width="25%" class="vncellt"><?=gettext("Last config change");?></td>
		      <td width="75%" class="listr">
						<input style="padding: 0; border: 0;" size="30" name="lastchange" id="lastchange" value="<?=htmlspecialchars(date("D M j G:i:s T Y", $config['lastchange']));?>" />
		      </td>
		    </tr>
				<?php endif;?>
				<?php if (!empty($cpuinfo['temperature2'])):
					echo "<tr>";
					echo "<td width='25%' class='vncellt'>".gettext("CPU temperature")."</td>";
					echo "<td width='75%' class='listr'>";
					echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td>\n";
					$cpus = system_get_cpus();
					for ($idx = 0; $idx < $cpus; $idx++) {
						echo "<tr><td>";
						echo "<input style='padding: 0; border: 0;' size='2' name='cputemp${idx}' id='cputemp${idx}' value='".htmlspecialchars($cpuinfo['temperature2'][$idx])."' />";
					echo $idx['temperature2']."&#176;C";	
					echo "</td></tr>";
					}
					echo "</table></td>";
					echo "</tr>\n";
				?>
				<?php elseif (!empty($cpuinfo['temperature'])):?>
				<tr>
					<td width="25%" class="vncellt"><?=gettext("CPU temperature");?></td>
					<td width="75%" class="listr">
						<input style="padding: 0; border: 0;" size="30" name="cputemp" id="cputemp" value="<?=htmlspecialchars($cpuinfo['temperature']);?>" />
					</td>
				</tr>
				<?php endif;?>
				<?php if (!empty($cpuinfo['freq'])):?>
				<tr>
					<td width="25%" class="vncellt"><?=gettext("CPU frequency");?></td>
					<td width="75%" class="listr">
						<input style="padding: 0; border: 0;" size="30" name="cpufreq" id="cpufreq" value="<?=htmlspecialchars($cpuinfo['freq']);?>MHz" title="<?=sprintf(gettext("Levels (MHz/mW): %s"), $cpuinfo['freqlevels']);?>" />
					</td>
				</tr>
				<?php endif;?>
				<tr>
					<td width="25%" class="vncellt"><?=gettext("CPU usage");?></td>
					<td width="75%" class="listr">
				    	<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>
						<?php
						$percentage = 0;
						echo "<img src='bar_left.gif' class='progbarl' alt='' />";
						echo "<img src='bar_blue.gif' name='cpuusageu' id='cpuusageu' width='" . $percentage . "' class='progbarcf' alt='' />";
						echo "<img src='bar_gray.gif' name='cpuusagef' id='cpuusagef' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
						echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
						?>
						<input style="padding: 0; border: 0;" size="30" name="cpuusage" id="cpuusage" value="<?=gettext("Updating in 5 seconds.");?>" />
					</td></tr>
						<?php
						$cpus = system_get_cpus();
						if ($cpus > 1) {
							echo "<tr><td><hr size='1' /></td></tr>";
							for ($idx = 0; $idx < $cpus; $idx++) {
								$percentage = 0;
								echo "<tr><td>";
								echo "<img src='bar_left.gif' class='progbarl' alt='' />";
								echo "<img src='bar_blue.gif' name='cpuusageu${idx}' id='cpuusageu${idx}' width='" . $percentage . "' class='progbarcf' alt='' />";
								echo "<img src='bar_gray.gif' name='cpuusagef${idx}' id='cpuusagef${idx}' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
								echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
								echo "<input style='padding: 0; border: 0;' size='30' name='cpuusage${idx}' id='cpuusage${idx}' value=\"".gettext("Updating in 5 seconds.")."\" />";
								echo "</td></tr>";
							}
						}
						?>
					</table>
					</td>
				</tr>
			  <tr>
			    <td width="25%" class="vncellt"><?=gettext("Memory usage");?></td>
			    <td width="75%" class="listr">
						<?php
						$raminfo = system_get_ram_info();
						$percentage = round(($raminfo['used'] * 100) / $raminfo['total'], 0);
						echo "<img src='bar_left.gif' class='progbarl' alt='' />";
						echo "<img src='bar_blue.gif' name='memusageu' id='memusageu' width='" . $percentage . "' class='progbarcf' alt='' />";
						echo "<img src='bar_gray.gif' name='memusagef' id='memusagef' width='" . (100 - $percentage) . "' class='progbarc' alt='' />";
						echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
						?>
						<input style="padding: 0; border: 0;" size="30" name="memusage" id="memusage" value="<?=sprintf(gettext("%d%% of %dMiB"), $percentage, round($raminfo['physical'] / 1024 / 1024));?>" />
			    </td>
			  </tr>
				<?php $swapinfo = system_get_swap_info(); if (!empty($swapinfo)):?>
				<tr>
					<td width="25%" class="vncellt"><?=gettext("Swap usage");?></td>
					<td width="75%" class="listr">
						<table width="100%" border="0" cellspacing="0" cellpadding="1">
							<?php
							array_sort_key($swapinfo, "device");
							$ctrlid = 0;
							foreach ($swapinfo as $swapk => $swapv) {
								$percent_used = rtrim($swapv['capacity'], "%");
								$tooltip_used = sprintf(gettext("%sB used of %sB"), $swapv['used'], $swapv['total']);
								$tooltip_available = sprintf(gettext("%sB available of %sB"), $swapv['avail'], $swapv['total']);

								echo "<tr><td><div id='swapusage'>";
								echo "<img src='bar_left.gif' class='progbarl' alt='' />";
								echo "<img src='bar_blue.gif' name='swapusage_{$ctrlid}_bar_used' id='swapusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
								echo "<img src='bar_gray.gif' name='swapusage_{$ctrlid}_bar_free' id='swapusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_available}' alt='' />";
								echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
								echo sprintf(gettext("%s of %sB"),
									"<span name='swapusage_{$ctrlid}_capacity' id='swapusage_{$ctrlid}_capacity' class='capacity'>{$swapv['capacity']}</span>",
									$swapv['total']);
								echo "<br />";
								echo sprintf(gettext("Device: %s | Total: %s | Used: %s | Free: %s"),
									"<span name='swapusage_{$ctrlid}_device' id='swapusage_{$ctrlid}_device' class='device'>{$swapv['device']}</span>",
									"<span name='swapusage_{$ctrlid}_total' id='swapusage_{$ctrlid}_total' class='total'>{$swapv['total']}</span>",
									"<span name='swapusage_{$ctrlid}_used' id='swapusage_{$ctrlid}_used' class='used'>{$swapv['used']}</span>",
									"<span name='swapusage_{$ctrlid}_free' id='swapusage_{$ctrlid}_free' class='free'>{$swapv['avail']}</span>");
								echo "</div></td></tr>";

								$ctrlid++;
								if ($ctrlid < count($swapinfo))
										echo "<tr><td><hr size='1' /></td></tr>";
							}?>
						</table>
					</td>
				</tr>
				<?php endif;?>
				<tr>
			  	<td width="25%" class="vncellt"><?=gettext("Load averages");?></td>
					<td width="75%" class="listr">
						<?php
						exec("uptime", $result);
						$loadaverage = substr(strrchr($result[0], "load averages:"), 15);
						?>
						<input style="padding: 0; border: 0;" size="14" name="loadaverage" id="loadaverage" value="<?=$loadaverage;?>" />
						<?="<small>[<a href='status_process.php'>".gettext("Show process information")."</a></small>]";?>
			    </td>
			  </tr>
				<tr>
			    <td width="25%" class="vncellt"><?=gettext("Disk space usage");?></td>
			    <td width="75%" class="listr">
				    <table width="100%" border="0" cellspacing="0" cellpadding="1">
				      <?php
				      $diskusage = system_get_mount_usage();
				      if (!empty($diskusage)) {
				      	array_sort_key($diskusage, "name");
				      	$index = 0;
								foreach ($diskusage as $diskusagek => $diskusagev) {
									$ctrlid = get_mount_fsid($diskusagev['filesystem'], $diskusagek);
									$percent_used = rtrim($diskusagev['capacity'],"%");
									$tooltip_used = sprintf(gettext("%sB used of %sB"), $diskusagev['used'], $diskusagev['size']);
									$tooltip_available = sprintf(gettext("%sB available of %sB"), $diskusagev['avail'], $diskusagev['size']);

									echo "<tr><td><div id='diskusage'>";
									echo "<span name='diskusage_{$ctrlid}_name' id='diskusage_{$ctrlid}_name' class='name'>{$diskusagev['name']}</span><br />";
									echo "<img src='bar_left.gif' class='progbarl' alt='' />";
									echo "<img src='bar_blue.gif' name='diskusage_{$ctrlid}_bar_used' id='diskusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
									echo "<img src='bar_gray.gif' name='diskusage_{$ctrlid}_bar_free' id='diskusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_available}' alt='' />";
									echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
									echo sprintf(gettext("%s of %sB"),
										"<span name='diskusage_{$ctrlid}_capacity' id='diskusage_{$ctrlid}_capacity' class='capacity'>{$diskusagev['capacity']}</span>",
										$diskusagev['size']);
									echo "<br />";
									echo sprintf(gettext("Total: %s | Used: %s | Free: %s"),
										"<span name='diskusage_{$ctrlid}_total' id='diskusage_{$ctrlid}_total' class='total'>{$diskusagev['size']}</span>",
										"<span name='diskusage_{$ctrlid}_used' id='diskusage_{$ctrlid}_used' class='used'>{$diskusagev['used']}</span>",
										"<span name='diskusage_{$ctrlid}_free' id='diskusage_{$ctrlid}_free' class='free'>{$diskusagev['avail']}</span>");
									echo "</div></td></tr>";

									if (++$index < count($diskusage))
										echo "<tr><td><hr size='1' /></td></tr>";
								}
							}

							$zfspools = zfs_get_pool_list();
							if (!empty($zfspools)) {
								array_sort_key($zfspools, "name");
								$index = 0;

								if (!empty($diskusage))
										echo "<tr><td><hr size='1' /></td></tr>";

								foreach ($zfspools as $poolk => $poolv) {
									$ctrlid = $poolv['name'];
									$percent_used = rtrim($poolv['cap'],"%");
									$tooltip_used = sprintf(gettext("%sB used of %sB"), $poolv['used'], $poolv['size']);
									$tooltip_available = sprintf(gettext("%sB available of %sB"), $poolv['avail'], $poolv['size']);

									echo "<tr><td><div id='diskusage'>";
									echo "<span name='diskusage_{$ctrlid}_name' id='diskusage_{$ctrlid}_name' class='name'>{$poolv['name']}</span><br />";
									echo "<img src='bar_left.gif' class='progbarl' alt='' />";
									echo "<img src='bar_blue.gif' name='diskusage_{$ctrlid}_bar_used' id='diskusage_{$ctrlid}_bar_used' width='{$percent_used}' class='progbarcf' title='{$tooltip_used}' alt='' />";
									echo "<img src='bar_gray.gif' name='diskusage_{$ctrlid}_bar_free' id='diskusage_{$ctrlid}_bar_free' width='" . (100 - $percent_used) . "' class='progbarc' title='{$tooltip_available}' alt='' />";
									echo "<img src='bar_right.gif' class='progbarr' alt='' /> ";
									echo sprintf(gettext("%s of %sB"),
										"<span name='diskusage_{$ctrlid}_capacity' id='diskusage_{$ctrlid}_capacity' class='capacity'>{$poolv['cap']}</span>",
										$poolv['size']);
									echo "<br />";
									echo sprintf(gettext("Total: %s | Used: %s | Free: %s | State: %s"),
										"<span name='diskusage_{$ctrlid}_total' id='diskusage_{$ctrlid}_total' class='total'>{$poolv['size']}</span>",
										"<span name='diskusage_{$ctrlid}_used' id='diskusage_{$ctrlid}_used' class='used'>{$poolv['used']}</span>",
										"<span name='diskusage_{$ctrlid}_free' id='diskusage_{$ctrlid}_free' class='free'>{$poolv['avail']}</span>",
										"<span name='diskusage_{$ctrlid}_state' id='diskusage_{$ctrlid}_state' class='state'><a href='disks_zfs_zpool_info.php?pool={$poolv['name']}'>{$poolv['health']}</a></span>");
									echo "</div></td></tr>";

									if (++$index < count($zfspools))
										echo "<tr><td><hr size='1' /></td></tr>";
								}
							}

							if (empty($diskusage) && empty($zfspools)) {
								echo "<tr><td>";
								echo gettext("No disk configured");
								echo "</td></tr>";
							}
							?>
						</table>
					</td>
				</tr>
				<tr>
					<td width="25%" class="vncellt"><?=gettext("UPS Status");?></td>
					<td width="75%" class="listr">
						<table width="100%" border="0" cellspacing="0" cellpadding="2">
							<?php if (!isset($config['ups']['enable'])):?>
								<tr>
									<td>
										<pre><?=gettext("UPS disabled");?><?=" <small> [<a href='diag_infos_ups.php'>".gettext("Show ups information")."</a></small>]";?></pre>
									</td>
								</tr>
							<?php else:?>
								<?php
								$cmd = "/usr/local/bin/upsc {$config['ups']['upsname']}@localhost";
								$handle = popen($cmd, 'r');
								
								if($handle) {
									$read = fread($handle, 4096);
									pclose($handle);

									$lines = explode("\n", $read);
									$ups = array();
									foreach($lines as $line) {
										$line = explode(':', $line);
										$ups[$line[0]] = trim($line[1]);
									}

									if(count($lines) == 1)
										tblrow('ERROR:', 'Data stale!');

									$status = explode(' ', $ups['ups.status']);
									foreach($status as $condition) {
										if($disp_status) $disp_status .= ', ';
										switch ($condition) {
											case 'WAIT':
												$disp_status .= gettext('UPS Waiting');
												break;
										case 'OFF':
												$disp_status .= gettext('UPS Off Line');
												break;
										case 'OL':
												$disp_status .= gettext('UPS On Line');
												break;
										case 'OB':
												$disp_status .= gettext('UPS On Battery');
												break;
										case 'TRIM':
												$disp_status .= gettext('SmartTrim');
												break;
										case 'BOOST':
												$disp_status .= gettext('SmartBoost');
												break;
										case 'OVER':
												$disp_status .= gettext('Overload');
												break;
										case 'LB':
												$disp_status .= gettext('Battery Low');
												break;
										case 'RB':
												$disp_status .= gettext('Replace Battery UPS');
												break;
										case 'CAL':
												$disp_status .= gettext('Calibration Battery');
												break;
										case 'CHRG':
												$disp_status .= gettext('Charging Battery');
												break;
										default:
												$disp_status .= $condition;
												break;
									}
								}
									tblrow(gettext('Status'), $disp_status. " <small>[<a href='diag_infos_ups.php'>".gettext("Show ups information")."</a></small>]");
									tblrowbar(gettext('Load'), $ups['ups.load'], '%', '100-80', '79-60', '59-0');
									tblrowbar(gettext('Battery Charge'), $ups['battery.charge'], '%', '0-29' ,'30-79', '80-100');

									// status
									tblrow(gettext('Battery Remain Time'), $ups['battery.runtime'], ' seconds');
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
						</table>
					</td>
				</tr>
				<?php endif;?>
			</table>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>