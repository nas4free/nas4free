<?php
/* 
	system_monitoring.php

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

$sphere_scriptname = basename(__FILE__);
$sphere_notifier = 'rrdgraphs';

array_make_branch($config,'rrdgraphs');

$upsname = !empty($config['ups']['upsname']) ? $config['ups']['upsname'] : "identifier";
$upsip = !empty($config['ups']['ip']) ? $config['ups']['ip'] : "host-ip-address";

/* Check if the directory exists, the mountpoint has at least o=rx permissions and
 * set the permission to 775 for the last directory in the path.
 */
function change_perms($dir) {
	global $input_errors;

	$path = rtrim($dir,'/');	// remove trailing slash
	if (strlen($path) > 1) {
		if (!is_dir($path)) {	// check if directory exists
			$input_errors[] = sprintf(gtext("Directory %s doesn't exist!"), $path);
		} else {
			$path_check = explode('/', $path);	// split path to get directory names
			$path_elements = count($path_check);	// get path depth
			$fp = substr(sprintf('%o', fileperms("/$path_check[1]/$path_check[2]")), -1);	// get mountpoint permissions for others
			if ($fp >= 5) {							// transmission needs at least read & search permission at the mountpoint
				$directory = "/$path_check[1]/$path_check[2]";		// set to the mountpoint
				for ($i = 3; $i < $path_elements - 1; $i++) {		// traverse the path and set permissions to rx
					$directory = $directory."/$path_check[$i]";	// add next level
					exec("chmod o=+r+x \"$directory\"");		// set permissions to o=+r+x
				}
				$path_elements = $path_elements - 1;
				$directory = $directory."/$path_check[$path_elements]";	// add last level
				exec("chmod 775 {$directory}");				// set permissions to 775
				exec("chown {$_POST['who']} {$directory}*");
			} else {
				$input_errors[] = sprintf(gtext("RRDGraphs needs at least read & execute permissions at the mount point for directory %s! Set the Read and Execute bits for Others (Access Restrictions | Mode) for the mount point %s (in <a href='disks_mount.php'>Disks | Mount Point | Management</a> or <a href='disks_zfs_dataset.php'>Disks | ZFS | Datasets</a>) and hit Save in order to take them effect."), $path, "/{$path_check[1]}/{$path_check[2]}");
			}
		}
	}
}
if (isset($_POST['save']) && $_POST['save']) {
	unset($input_errors);
	$pconfig = $_POST;
	if (isset($_POST['ups']) && empty($_POST['ups_at'])) {
		$input_errors[] = gtext('UPS Identifier and IP address')." ".sprintf(gtext('must be in the format: %s.'), "identifier@host-ip-address");
	}
	if (isset($_POST['latency']) && empty($_POST['latency_host'])) {
		$input_errors[] = gtext('Network Latency') . ': ' . gtext('Destination host name or IP address.') . ' ' . gtext('Host') . ' ' . gtext('must be defined!');
	}
	if (isset($_POST['storage_path']) && (($_POST['storage_path'] == "") || ($_POST['storage_path'] == $g['media_path']))) { $input_errors[] = gtext("The attribute 'Data directory' is required."); }
	if (empty($input_errors)) {
		if (isset($_POST['enable'])) {
			$config['rrdgraphs']['enable'] = isset($_POST['enable']);
			if (empty($_POST['storage_path'])) {
				$config['rrdgraphs']['storage_path'] = $g['media_path'];
			} else {
				$_POST['storage_path'] = rtrim($_POST['storage_path'],'/');	// ensure to have no trailing slash
			}
			if (!is_dir("{$_POST['storage_path']}/rrd")) { 
				mkdir("{$_POST['storage_path']}/rrd", 0775, true);	// new destination or first install
				change_perms("{$_POST['storage_path']}/rrd");	// check/set permissions
			}
			$config['rrdgraphs']['storage_path'] = $_POST['storage_path'];
			$_POST['graph_h'] = trim($_POST['graph_h']);  
			$config['rrdgraphs']['graph_h'] = !empty($_POST['graph_h']) ? $_POST['graph_h'] : 200;
			$config['rrdgraphs']['refresh_time'] = !empty($_POST['refresh_time']) ? $_POST['refresh_time'] : 300;
			$config['rrdgraphs']['autoscale'] = isset($_POST['autoscale']);
			$config['rrdgraphs']['background_white'] = isset($_POST['background_white']) ? true : false;
			$config['rrdgraphs']['bytes_per_second'] = isset($_POST['bytes_per_second']);
			$config['rrdgraphs']['logarithmic'] = isset($_POST['logarithmic']);
			$config['rrdgraphs']['axis'] = isset($_POST['axis']);
			if ($config['rrdgraphs']['axis']) {
				$config['rrdgraphs']['logarithmic'] = false;
			}
			$config['rrdgraphs']['load_averages'] = isset($_POST['load_averages']);
			$config['rrdgraphs']['cpu_frequency'] = isset($_POST['cpu_frequency']);
			$config['rrdgraphs']['cpu_temperature'] = isset($_POST['cpu_temperature']);
			$config['rrdgraphs']['disk_usage'] = isset($_POST['disk_usage']);
			$config['rrdgraphs']['lan_load'] = isset($_POST['lan_load']);
			$config['rrdgraphs']['lan_if'] = get_ifname($config['interfaces']['lan']['if']);	// for 'auto' if name
			$config['rrdgraphs']['no_processes'] = isset($_POST['no_processes']);
			$config['rrdgraphs']['cpu'] = isset($_POST['cpu']);
			$config['rrdgraphs']['memory_usage'] = isset($_POST['memory_usage']);
			$config['rrdgraphs']['arc_usage'] = isset($_POST['arc_usage']);
			$config['rrdgraphs']['latency'] = isset($_POST['latency']);
			$config['rrdgraphs']['latency_host'] = !empty($_POST['latency_host']) ? $_POST['latency_host'] : "127.0.0.1";
			$config['rrdgraphs']['latency_interface'] = $_POST['latency_interface'];
			$config['rrdgraphs']['latency_count'] = $_POST['latency_count'];
			$config['rrdgraphs']['latency_parameters'] = !empty($_POST['latency_parameters']) ? $_POST['latency_parameters'] : "";
			$config['rrdgraphs']['ups'] = isset($_POST['ups']);
			$config['rrdgraphs']['ups_at'] = !empty($_POST['ups_at']) ? $_POST['ups_at'] : "identifier@host-ip-address";
			$config['rrdgraphs']['uptime'] = isset($_POST['uptime']);

			$savemsg = get_std_save_message(write_config());
			$retval = 0;
			if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("cron");
			config_unlock();
		}
			require_once '/usr/local/share/rrdgraphs/rrd-start.php';
		} else {
			$config['rrdgraphs']['enable'] = isset($_POST['enable']) ? true : false;

			$savemsg = get_std_save_message(write_config());
			exec("logger rrdgraphs service stopped");
			if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("cron");
			config_unlock();
			}	
		}
	}
}

if (isset($_POST['reset_graphs']) && $_POST['reset_graphs']) {
	exec("logger rrdgraphs service execute delete statistical data ...");
	$savemsg = gtext("All data from the following statistics have been deleted:");
	if (isset($_POST['cpu_frequency']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/cpu_freq.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/cpu_freq.rrd");
		exec("logger rrdgraphs service deleted cpu frequency statistics");
		$savemsg .= "<br />- ".gtext("CPU Frequency");
	}
	if (isset($_POST['cpu_temperature']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/cpu_temp.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/cpu_temp.rrd");
		exec("logger rrdgraphs service deleted cpu temperature statistics");
		$savemsg .= "<br />- ".gtext("CPU Temperature");
	}
	if (isset($_POST['cpu']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/cpu.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/cpu.rrd");
		exec("logger rrdgraphs service deleted cpu usage statistics");
		$savemsg .= "<br />- ".gtext("CPU Usage");
	}
	if (isset($_POST['load_averages']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/load_averages.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/load_averages.rrd");
		exec("logger rrdgraphs service deleted load averages statistics");
		$savemsg .= "<br />- ".gtext("Load averages");
	}
	if (isset($_POST['disk_usage'])) {
		mwexec("rm {$config['rrdgraphs']['storage_path']}/rrd/mnt_*.rrd", true);
		exec("logger rrdgraphs service deleted disk usage statistics");
		$savemsg .= "<br />- ".gtext("Disk Usage");
	}
	if (isset($_POST['memory_usage']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/memory.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/memory.rrd");
		exec("logger rrdgraphs service deleted memory usage statistics");
		$savemsg .= "<br />- ".gtext("Memory Usage");
	}
	if (isset($_POST['latency']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/latency.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/latency.rrd");
		exec("logger rrdgraphs service deleted network latency statistics");
		$savemsg .= "<br />- ".gtext("Network Latency");
	}
	$rrd_name = "{$config['rrdgraphs']['lan_if']}.rrd";
	if (isset($_POST['lan_load']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}");
		exec("logger rrdgraphs service deleted  network traffic statistics");
		$savemsg .= "<br />- ".gtext("Network Traffic");
	}
	if (isset($_POST['no_processes']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/processes.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/processes.rrd");
		exec("logger rrdgraphs service deleted sytem processes statistics");
		$savemsg .= "<br />- ".gtext("System Processes");
	}
	if (isset($_POST['ups']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/ups.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/ups.rrd");
		exec("logger rrdgraphs service deleted ups statistics");
		$savemsg .= "<br />- ".gtext("UPS Statistics");
	}
	if (isset($_POST['uptime']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/uptime.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/uptime.rrd");
		exec("logger rrdgraphs service deleted uptime statistics");
		$savemsg .= "<br />- ".gtext("Uptime Statistics");
	}
	$rrd_name = "{$config['rrdgraphs']['arc_usage']}.rrd";
	if (isset($_POST['arc_usage']) && is_file("{$config['rrdgraphs']['storage_path']}/rrd/zfs_arc.rrd")) {
		unlink("{$config['rrdgraphs']['storage_path']}/rrd/zfs_arc.rrd");
		exec("logger rrdgraphs service deleted zfs arc usage statistics");
		$savemsg .= "<br />- ".gtext("ZFS ARC Usage");
	}
	require_once '/usr/local/share/rrdgraphs/rrd-start.php';
}

$pconfig['enable'] = isset($config['rrdgraphs']['enable']) ? true : false;
$pconfig['storage_path'] = !empty($config['rrdgraphs']['storage_path']) ? $config['rrdgraphs']['storage_path'] : $g['media_path'];
$pconfig['graph_h'] = !empty($config['rrdgraphs']['graph_h']) ? $config['rrdgraphs']['graph_h'] : 200;
$pconfig['refresh_time'] = !empty($config['rrdgraphs']['refresh_time']) ? $config['rrdgraphs']['refresh_time'] : 300;
$pconfig['autoscale'] = isset($config['rrdgraphs']['autoscale']) ? true : false;
$pconfig['background_white'] = isset($config['rrdgraphs']['background_white']) ? true : false;

// available graphs
$pconfig['cpu_frequency'] = isset($config['rrdgraphs']['cpu_frequency']) ? true : false;
$pconfig['cpu_temperature'] = isset($config['rrdgraphs']['cpu_temperature']) ? true : false;
$pconfig['cpu'] = isset($config['rrdgraphs']['cpu']) ? true : false;
$pconfig['load_averages'] = isset($config['rrdgraphs']['load_averages']) ? true : false;
$pconfig['disk_usage'] = isset($config['rrdgraphs']['disk_usage']) ? true : false;
$pconfig['memory_usage'] = isset($config['rrdgraphs']['memory_usage']) ? true : false;
$pconfig['latency'] = isset($config['rrdgraphs']['latency']) ? true : false;
$pconfig['latency_host'] = !empty($config['rrdgraphs']['latency_host']) ? $config['rrdgraphs']['latency_host'] : "127.0.0.1";
$pconfig['latency_interface'] = !empty($config['rrdgraphs']['latency_interface']) ? $config['rrdgraphs']['latency_interface'] : "identifier@host-ip-address";
$pconfig['latency_count'] = !empty($config['rrdgraphs']['latency_count']) ? $config['rrdgraphs']['latency_count'] : "3";
$pconfig['latency_parameters'] = !empty($config['rrdgraphs']['latency_parameters']) ? $config['rrdgraphs']['latency_parameters'] : "";
$pconfig['lan_load'] = isset($config['rrdgraphs']['lan_load']) ? true : false;
$pconfig['bytes_per_second'] = isset($config['rrdgraphs']['bytes_per_second']) ? true : false;
$pconfig['logarithmic'] = isset($config['rrdgraphs']['logarithmic']) ? true : false;
$pconfig['axis'] = isset($config['rrdgraphs']['axis']) ? true : false;
$pconfig['no_processes'] = isset($config['rrdgraphs']['no_processes']) ? true : false;
$pconfig['ups'] = isset($config['rrdgraphs']['ups']) ? true : false;
$pconfig['ups_at'] = !empty($config['rrdgraphs']['ups_at']) ? $config['rrdgraphs']['ups_at'] : "identifier@host-ip-address";
$pconfig['uptime'] = isset($config['rrdgraphs']['uptime']) ? true : false;
$pconfig['arc_usage'] = isset($config['rrdgraphs']['arc_usage']) ? true : false;

$a_interface = get_interface_list();
// Add VLAN interfaces
array_make_branch($config,'vinterfaces','vlan');
if(!empty($config['vinterfaces']['vlan'])) {
	foreach ($config['vinterfaces']['vlan'] as $vlanv) {
		$a_interface[$vlanv['if']] = $vlanv;
		$a_interface[$vlanv['if']]['isvirtual'] = true;
	}
}
// Add LAGG interfaces
array_make_branch($config,'vfinterfaces','lagg');
if(!empty($config['vinterfaces']['lagg'])) {
	foreach ($config['vinterfaces']['lagg'] as $laggv) {
		$a_interface[$laggv['if']] = $laggv;
		$a_interface[$laggv['if']]['isvirtual'] = true;
	}
}
// Use first interface as default if it is not set.
if(empty($pconfig['latency_interface']) && is_array($a_interface)):
	$pconfig['latency_interface'] = key($a_interface);
endif;
$pgtitle = [gtext('System'), gtext('Advanced'), gtext('Monitoring Setup')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function axis_change() {
	switch(document.iform.axis.checked) {
		case true:
			document.getElementById('logarithmic').checked = false;
			showElementById('logarithmic_tr','hide');
			break;
		case false:
			showElementById('logarithmic_tr','show');
			break;
	}
}
function logarithmic_change() {
	switch(document.iform.logarithmic.checked) {
		case true:
			document.getElementById('axis').checked = false;
			showElementById('axis_tr','hide');
			break;
		case false:
			showElementById('axis_tr','show');
			break;
	}
}
function lan_change() {
	switch(document.iform.lan_load.checked) {
		case true:
			showElementById('bytes_per_second_tr','show');
			showElementById('axis_tr','show');
			axis_change();
			logarithmic_change();
			break;
		case false:
			showElementById('bytes_per_second_tr','hide');
			showElementById('logarithmic_tr','hide');
			showElementById('axis_tr','hide');
			break;
	}
}
function latency_change() {
	switch(document.iform.latency.checked) {
		case true:
			showElementById('latency_host_tr','show');
			showElementById('latency_interface_tr','show');
			showElementById('latency_count_tr','show');
			showElementById('latency_parameters_tr','show');
			showElementById('latency_interface_cell','show');
			showElementById('latency_interface_table','show');
			break;
		case false:
			showElementById('latency_host_tr','hide');
			showElementById('latency_interface_tr','hide');
			showElementById('latency_count_tr','hide');
			showElementById('latency_parameters_tr','hide');
			showElementById('latency_interface_cell','hide');
			showElementById('latency_interface_table','hide');
			break;
	}
}
function ups_change() {
	switch(document.iform.ups.checked) {
		case true:
			showElementById('ups_at_tr','show');
			break;
		case false:
			showElementById('ups_at_tr','hide');
			break;
	}
}
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.storage_path.disabled = endis;
	document.iform.storage_pathbrowsebtn.disabled = endis;
	document.iform.graph_h.disabled = endis;
	document.iform.refresh_time.disabled = endis;
	document.iform.autoscale.disabled = endis;
	document.iform.background_white.disabled = endis;
	document.iform.bytes_per_second.disabled = endis;
	document.iform.logarithmic.disabled = endis;
	document.iform.axis.disabled = endis;
	document.iform.reset_graphs.disabled = endis;
	document.iform.uptime.disabled = endis;
	document.iform.load_averages.disabled = endis;
	document.iform.no_processes.disabled = endis;
	document.iform.cpu.disabled = endis;
	document.iform.cpu_frequency.disabled = endis;
	document.iform.cpu_temperature.disabled = endis;
	document.iform.memory_usage.disabled = endis;
	document.iform.arc_usage.disabled = endis;
	document.iform.disk_usage.disabled = endis;
	document.iform.lan_load.disabled = endis;
	document.iform.latency.disabled = endis;
	document.iform.latency_host.disabled = endis;
	document.iform.latency_interface.disabled = endis;
	document.iform.latency_count.disabled = endis;
	document.iform.latency_parameters.disabled = endis;
	document.iform.ups.disabled = endis;
	document.iform.ups_at.disabled = endis;
}
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="system_advanced.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Advanced");?></span></a></li>
		<li class="tabinact"><a href="system_email.php"><span><?=gtext("Email");?></span></a></li>
		<li class="tabinact"><a href="system_email_reports.php"><span><?=gtext("Email Reports");?></span></a></li>
		<li class="tabact"><a href="system_monitoring.php"><span><?=gtext("Monitoring");?></span></a></li>
		<li class="tabinact"><a href="system_swap.php"><span><?=gtext("Swap");?></span></a></li>
		<li class="tabinact"><a href="system_rc.php"><span><?=gtext("Command Scripts");?></span></a></li>
		<li class="tabinact"><a href="system_cron.php"><span><?=gtext("Cron");?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext("loader.conf");?></span></a></li>
		<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext("rc.conf");?></span></a></li>
		<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext("sysctl.conf");?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform">
	<?php
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline_checkbox2('enable', gtext('System Monitoring Settings'), $pconfig['enable'], gtext('Enable'), "enable_change(false)");?>
		</thead>
		<tbody>
			<?php
			html_filechooser2('storage_path', gtext('Home Directory'), $pconfig['storage_path'], gtext('Enter the path to the home directory. This directory will store the statistical data and gets updated every 5 minutes!'), $g['media_path'], true, 60);
			html_inputbox2('refresh_time', gtext('Page Refresh'), $pconfig['refresh_time'], gtext('Auto page refresh.')." ".sprintf(gtext('(default %s %s'), 300, gtext('seconds)')), false, 5);
			html_inputbox2('graph_h', gtext('Graphs Height'), $pconfig['graph_h'], sprintf(gtext('Height of the graphs. (default %s pixels)'), 200), false, 5);
			html_checkbox2('autoscale', gtext('Autoscale'), $pconfig['autoscale'], gtext('Autoscale for graphs.'), "", false);
			html_checkbox2('background_white', gettext('Background'), $pconfig['background_white'], gettext('Enable white background graphs. (black as default)'), '', false);
			html_separator2();
			html_titleline2(gtext('Available Graphs'));
			html_checkbox2('cpu_frequency', gtext('CPU Frequency'), $pconfig['cpu_frequency'], gtext('Enable collecting CPU frequency statistics.'), '', false);
			html_checkbox2('cpu_temperature', gtext('CPU Temperature'), $pconfig['cpu_temperature'], gtext('Enable collecting CPU temperature statistics.'), '', false);
			html_checkbox2('cpu', gtext('CPU Usage'), $pconfig['cpu'], gtext('Enable collecting CPU usage statistics.'), '', false);
			html_checkbox2('disk_usage', gtext('Disk Usage'), $pconfig['disk_usage'], gtext('Enable collecting disk space usage statistics.'), '', false);
			html_checkbox2('load_averages', gtext('Load Averages'), $pconfig['load_averages'], gtext('Enable collecting average system load statistics.'), '', false);
			html_checkbox2('memory_usage', gtext('Memory Usage'), $pconfig['memory_usage'], gtext('Enable collecting memory usage statistics.'), '', false);
			html_checkbox2('latency', gtext('Network Latency'), $pconfig['latency'], gtext('Enable collecting network latency statistics.'), '', false, false, 'latency_change()');
			html_inputbox2('latency_host', gtext('Host'), $pconfig['latency_host'], gtext('Destination host name or IP address.'), false, 20);
			$a_option = [];
			foreach($a_interface as $if => $ifinfo) {
				$ifinfo = get_interface_info($if);
				if (('up' == $ifinfo['status']) || ('associated' == $ifinfo['status'])) {
					$a_option[] = $if;
					if ($if == $pconfig['latency_interface']) {
						$s_option = $if;
					}
				}
			}
			html_combobox2('latency_interface', gtext('Interface Selection'), $s_option, $a_option, gtext('Select the interface (only selectable if your server has more than one) to use for the source IP address in outgoing packets.'));
			$latency_a_count = [];
			for ($i = 1; $i <= 20; $i++) {
				$latency_a_count[$i] = $i;
			}
			html_combobox2('latency_count', gtext('Count'), $pconfig['latency_count'], $latency_a_count, gtext('Stop after sending (and receiving) N packets.'), false);
			html_inputbox2('latency_parameters', gtext('Additional Parameters'), $pconfig['latency_parameters'], gtext('These parameters will be added to the ping command.')." ".sprintf(gtext('Please check the %s documentation%s.'), "<a href=http://www.freebsd.org/cgi/man.cgi?query=ping&amp;apropos=0&amp;sektion=0&amp;format=html target='_blank'>", "</a>"), false, 60);
			html_checkbox2('lan_load', gtext('Network Traffic'), $pconfig['lan_load'], gtext('Enable collecting network traffic statistics.'), '', false, false, 'lan_change()');
			html_checkbox2('bytes_per_second', gtext('Bytes/sec'), $pconfig['bytes_per_second'], gtext('Use Bytes/sec instead of Bits/sec for network throughput display.'), "", false);
			html_checkbox2('logarithmic', gtext('Logarithmic Scaling'), $pconfig['logarithmic'], sprintf(gtext('Use logarithmic y-axis scaling for %s graphs. (can not be used together with positive/negative y-axis range)'), gtext('network traffic')), "", false, false, 'logarithmic_change()');
			html_checkbox2('axis', gtext('Y-axis Range'), $pconfig['axis'], sprintf(gtext('Show positive/negative values for %s graphs. (can not be used together with logarithmic scaling)'), gtext('network traffic')), '', false, false, 'axis_change()');
			html_checkbox2('no_processes', gtext('System Processes'), $pconfig['no_processes'], gtext('Enable collecting system process statistics.'), '', false);
			html_checkbox2('ups', gtext('UPS Statistics'), $pconfig['ups'], gtext('Enable collecting UPS statistics.'), '', false, false, 'ups_change()');
			html_inputbox2('ups_at', gtext('UPS Identifier'), $pconfig['ups_at'], gtext('Enter the UPS identifier and host IP address of the machine where the UPS is connected to. (this also can be a remote host)')."<br> ".gtext('The UPS identifier and IP address')." ".sprintf(gtext('must be in the format: %s.'), 'identifier@host-ip-address or identifier@localhost'), false, 60);
			html_checkbox2('uptime', gtext('Uptime Statistics'), $pconfig['uptime'], gtext('Enable collecting uptime statistics.'), '', false);
			html_checkbox2('arc_usage', gtext('ZFS ARC Usage'), $pconfig['arc_usage'], gtext('Enable collecting ZFS ARC usage statistics.'), '', false);
			?>
		</tbody>
	</table>
	<div id="submit">
		<input id="save" name="save" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>"/>
		<input id="reset_graphs" name="reset_graphs" type="submit" class="formbtn" value="<?=gtext('Reset Graphs');?>" onclick="return confirm('<?=gtext('Do you really want to delete all data from the selected statistics?');?>')" />
	</div>
	<div id="remarks">
		<?php
		$helpinghand = sprintf(gtext("'%s' deletes all statistical data from the graphs!"), gtext('Reset Graphs'))
			. '<div id="enumeration"><ul>'
			. '<li>' . sprintf(gtext("If only specific statistics needs to be reset, clear all other check boxes before performing '%s'."), gtext('Reset Graphs')) . '</li>'
			. '</ul></div>';
		html_remark("warning", gtext('Warning'), $helpinghand );
		?>
	</div>
	<?php require 'formend.inc';?>
</form></td></tr></tbody></table>
<script type="text/javascript">
//<![CDATA[
lan_change();
latency_change();
ups_change();
enable_change(false);
//]]>
</script>
<?php include 'fend.inc';?>
