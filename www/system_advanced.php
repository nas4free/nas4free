<?php
/*
	system_advanced.php

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

$pgtitle = array(gtext("System"), gtext("Advanced Setup"));

$pconfig['disableconsolemenu'] = isset($config['system']['disableconsolemenu']);
$pconfig['disablefm'] = isset($config['system']['disablefm']);
$pconfig['disablefirmwarecheck'] = isset($config['system']['disablefirmwarecheck']);
$pconfig['disablebeep'] = isset($config['system']['disablebeep']);
$pconfig['enabletogglemode'] = isset($config['system']['enabletogglemode']);
$pconfig['tune_enable'] = isset($config['system']['tune']);
$pconfig['zeroconf'] = isset($config['system']['zeroconf']);
$pconfig['powerd'] = isset($config['system']['powerd']);
$pconfig['pwmode'] = $config['system']['pwmode'];
$pconfig['pwmax'] = !empty($config['system']['pwmax']) ? $config['system']['pwmax'] : "";
$pconfig['pwmin'] = !empty($config['system']['pwmin']) ? $config['system']['pwmin'] : "";
$pconfig['motd'] = base64_decode($config['system']['motd']);
$pconfig['sysconsaver'] = isset($config['system']['sysconsaver']['enable']);
$pconfig['sysconsaverblanktime'] = $config['system']['sysconsaver']['blanktime'];
$pconfig['enableserialconsole'] = isset($config['system']['enableserialconsole']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (!isset($pconfig['pwmax']))
		$pconfig['pwmax'] = "";
	if (!isset($pconfig['pwmin']))
		$pconfig['pwmin'] = "";

	// Input validation.
	if (isset($_POST['sysconsaver'])) {
		$reqdfields = explode(" ", "sysconsaverblanktime");
		$reqdfieldsn = array(gtext("Blank time"));
		$reqdfieldst = explode(" ", "numeric");

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}
	if (isset($_POST['powerd'])) {
		$reqdfields = explode(" ", "pwmax pwmin");
		$reqdfieldsn = array(gtext("Maximum frequency"), gtext("Minimum frequency"));
		$reqdfieldst = explode(" ", "numeric numeric");

		//do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	if (empty($input_errors)) {
		// Process system tuning.
		if ($_POST['tune_enable']) {
			sysctl_tune(1);
		} else if (isset($config['system']['tune']) && (!$_POST['tune_enable'])) {
			// Simply force a reboot to reset to default values.
			// This makes programming easy :-) Also we are sure that
			// system will use origin values (maybe default values
			// change from one FreeBSD release to the next. This will
			// reduce maintenance).
			sysctl_tune(0);
			touch($d_sysrebootreqd_path);
		}
		$bootconfig="boot.config";
		if (!isset($_POST['enableserialconsole'])) {
			if (file_exists("/$bootconfig")) {
				unlink("/$bootconfig");
			}
			if (file_exists("{$g['cf_path']}/mfsroot.uzip")
			    && file_exists("{$g['cf_path']}/$bootconfig")) {
				config_lock();
				conf_mount_rw();
				unlink("{$g['cf_path']}/$bootconfig");
				conf_mount_ro();
				config_unlock();
			}
		} else {
			if (file_exists("/$bootconfig")) {
				unlink("/$bootconfig");
			}
			file_put_contents("/$bootconfig", "-Dh\n");
			if (file_exists("{$g['cf_path']}/mfsroot.uzip")) {
				config_lock();
				conf_mount_rw();
				if (file_exists("{$g['cf_path']}/$bootconfig")) {
					unlink("{$g['cf_path']}/$bootconfig");
				}
				file_put_contents("{$g['cf_path']}/$bootconfig", "-Dh\n");
				conf_mount_ro();
				config_unlock();
			}
		}
		if ((isset($config['system']['disablefm']) && (!isset($_POST['disablefm'])))
		    || (!isset($config['system']['disablefm']) && (isset($_POST['disablefm'])))) {
			// need restarting server to export/clear .htusers.php by fmperm.
			touch($d_sysrebootreqd_path);
		}
		if ((isset($config['system']['disableconsolemenu']) && (!isset($_POST['disableconsolemenu'])))
		    || (!isset($config['system']['disableconsolemenu']) && (isset($_POST['disableconsolemenu'])))) {
			// need restarting server to made active.
			touch($d_sysrebootreqd_path);
		}

		$config['system']['disableconsolemenu'] = isset($_POST['disableconsolemenu']) ? true : false;
		$config['system']['disablefm'] = isset($_POST['disablefm']) ? true : false;
		$config['system']['disablefirmwarecheck'] = isset($_POST['disablefirmwarecheck']) ? true : false;
		$config['system']['enabletogglemode'] = isset($_POST['enabletogglemode']) ? true : false;
		$config['system']['webgui']['noantilockout'] = isset($_POST['noantilockout']) ? true : false;
		$config['system']['disablebeep'] = isset($_POST['disablebeep']) ? true : false;
		$config['system']['tune'] = isset($_POST['tune_enable']) ? true : false;
		$config['system']['zeroconf'] = isset($_POST['zeroconf']) ? true : false;
		$config['system']['powerd'] = isset($_POST['powerd']) ? true : false;
		$config['system']['pwmode'] = $_POST['pwmode'];
		$config['system']['pwmax'] = $_POST['pwmax'];
		$config['system']['pwmin'] = $_POST['pwmin'];
		$config['system']['motd'] = base64_encode($_POST['motd']); // Encode string, otherwise line breaks will get lost
		$config['system']['sysconsaver']['enable'] = isset($_POST['sysconsaver']) ? true : false;
		$config['system']['sysconsaver']['blanktime'] = $_POST['sysconsaverblanktime'];
		$config['system']['enableserialconsole'] = isset($_POST['enableserialconsole']) ? true : false;

		// adjust power mode
		$pwmode = $config['system']['pwmode'];
		$pwmax = $config['system']['pwmax'];
		$pwmin = $config['system']['pwmin'];
		$pwopt = "-a {$pwmode} -b {$pwmode} -n {$pwmode}";
		if (!empty($pwmax))
			$pwopt .= " -M {$pwmax}";
		if (!empty($pwmin))
			$pwopt .= " -m {$pwmin}";
		$index = array_search_ex("powerd_flags", $config['system']['rcconf']['param'], "name");
		if ($index !== false) {
			$config['system']['rcconf']['param'][$index]['value'] = $pwopt;
		} else {
			$config['system']['rcconf']['param'][] = array(
				"uuid" => uuid(),
				"name" => "powerd_flags",
				"value" => $pwopt,
				"comment" => "System power control options",
				"enable" => true );
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_exec_service("rcconf");
			$retval |= rc_update_service("powerd");
			$retval |= rc_update_service("mdnsresponder");
			$retval |= rc_exec_service("motd");
			if (isset($config['system']['tune']))
				$retval |= rc_update_service("sysctl");
			$retval |= rc_update_service("syscons");
			$retval |= rc_update_service("fmperm");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}

function sysctl_tune($mode) {
	global $config;

	if (!is_array($config['system']['sysctl']['param']))
		$config['system']['sysctl']['param'] = array();

	array_sort_key($config['system']['sysctl']['param'], "name");
	$a_sysctlvar = &$config['system']['sysctl']['param'];

	$a_mib = array(
	"kern.maxvnodes"                    =>	3339551,
	"kern.maxproc"	                    =>  201940,
	"kern.maxfiles"	                    =>  65536,
	"kern.maxusers"	                    =>  12620,
	"kern.ipc.nmbclusters"	            =>  12255534,
	"kern.ipc.nmbjumbop"	            =>  6127766,
	"kern.ipc.nmbjumbo9"	            =>  5446902,
	"kern.ipc.nmbjumbo16"	            =>  4085176,
	"kern.ipc.maxsockets"	            =>  1042450,
	"kern.ipc.maxsockbuf"	            =>  2097152,
	"kern.ipc.somaxconn"	            =>  2048,
	"net.inet.tcp.sendbuf_auto"	    =>  1,
	"net.inet.tcp.recvbuf_auto"	    =>  1,
	"net.inet.tcp.sendspace"	    =>  32768,
	"net.inet.tcp.recvspace"	    =>  65536,
	"net.inet.tcp.sendbuf_max"	    =>  2097152,
	"net.inet.tcp.recvbuf_max"	    =>  2097152,
	"net.inet.tcp.sendbuf_inc"	    =>  8192,
	"net.inet.tcp.recvbuf_inc"	    =>  16384,
	"net.inet.tcp.tcbhashsize"	    =>  2097152,
	"net.inet.ip.intr_queue_maxlen"	    =>  256,
	"net.route.netisr_maxqlen"	    =>  256,
	"hw.igb.max_interrupt_rate"	    =>  8000,
	"hw.ix.max_interrupt_rate"	    =>  31250,
	"hw.igb.rxd"	                    =>  1024,
	"hw.igb.txd"	                    =>  1024,
	"hw.ix.txd"	                    =>  2048,
	"hw.ix.rxd"	                    =>  2048,
	"hw.igb.num_queues"	            =>  0,
	"hw.ix.num_queues"	            =>  8,

		"net.inet.tcp.delayed_ack" 		=> 1,
		"net.inet.tcp.rfc1323" 			=> 1,
		"net.inet.udp.recvspace" 		=> 65536,
		"net.inet.udp.maxdgram" 		=> 57344,

		"net.local.stream.recvspace" 		=> 65536,
		"net.local.stream.sendspace" 		=> 65536,

		"net.inet.icmp.icmplim" 		=> 300,
		"net.inet.icmp.icmplim_output" 		=> 1,
		"net.inet.tcp.path_mtu_discovery" 	=> 0,
		"hw.intr_storm_threshold" 		=> 9000,
	);

	switch ($mode) {
		case 0:
			// Remove system tune MIB's.
			while (list($name, $value) = each($a_mib)) {
				$id = array_search_ex($name, $a_sysctlvar, "name");
				if (false === $id)
					continue;
				unset($a_sysctlvar[$id]);
			}
			break;

		case 1:
			// Add system tune MIB's.
			while (list($name, $value) = each($a_mib)) {
				$id = array_search_ex($name, $a_sysctlvar, "name");
				if (false !== $id)
					continue;

				$param = array();
				$param['uuid'] = uuid();
				$param['name'] = $name;
				$param['value'] = $value;
				$param['comment'] = gtext("System tuning");
				$param['enable'] = true;

				$a_sysctlvar[] = $param;
			}
			break;
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function sysconsaver_change() {
	switch (document.iform.sysconsaver.checked) {
		case true:
			showElementById('sysconsaverblanktime_tr','show');
			break;

		case false:
			showElementById('sysconsaverblanktime_tr','hide');
			break;
	}
}
function powerd_change() {
	switch (document.iform.powerd.checked) {
		case true:
			showElementById('pwmode_tr','show');
			showElementById('pwmax_tr','show');
			showElementById('pwmin_tr','show');
			break;

		case false:
			showElementById('pwmode_tr','hide');
			showElementById('pwmax_tr','hide');
			showElementById('pwmin_tr','hide');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="system_advanced.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Advanced");?></span></a></li>
				<li class="tabinact"><a href="system_email.php"><span><?=gtext("Email");?></span></a></li>
				<li class="tabinact"><a href="system_swap.php"><span><?=gtext("Swap");?></span></a></li>
				<li class="tabinact"><a href="system_rc.php"><span><?=gtext("Command Scripts");?></span></a></li>
				<li class="tabinact"><a href="system_cron.php"><span><?=gtext("Cron");?></span></a></li>
				<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext("loader.conf");?></span></a></li>
				<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext("rc.conf");?></span></a></li>
				<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext("sysctl.conf");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="system_advanced.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline(gtext("System Settings"));
					html_checkbox("zeroconf", gtext("Zeroconf/Bonjour"), !empty($pconfig['zeroconf']) ? true : false, gtext("Enable Zeroconf/Bonjour to advertise services of this device."));
					html_checkbox("disablefm", gtext("File Manager"), !empty($pconfig['disablefm']) ? true : false, gtext("Disable file manager completely."));
					if ("full" !== $g['platform']) {
						$link = '<a href="' . 'system_firmware.php' . '">' . gtext('System') . ': ' . gtext('Firmware Update') . '</a>';
						$helpinghand = sprintf(gtext('Do not let the server check for newer firmware versions when the %s page gets loaded.'), $link);
						html_checkbox("disablefirmwarecheck", gtext("Firmware Check"), !empty($pconfig['disablefirmwarecheck']) ? true : false, gtext("Disable firmware version check."), $helpinghand);
					}
					html_checkbox("disablebeep", gtext("Internal Speaker"), !empty($pconfig['disablebeep']) ? true : false, gtext("Disable speaker beep on startup and shutdowns."));
					html_checkbox("enabletogglemode", gtext("Toggle Mode"), !empty($pconfig['enabletogglemode']) ? true : false, gtext("Use toggle button instead of enable/disable buttons."));
					html_separator();
					?>
					<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gtext("Performance Settings");?></td>
					</tr>
					<?php html_checkbox("tune_enable", gtext("Tuning"), !empty($pconfig['tune_enable']) ? true : false, gtext("Enable tuning of some kernel variables."));?>
					<?php html_checkbox("powerd", gtext("Power Daemon"), !empty($pconfig['powerd']) ? true : false, gtext("Enable the server power control utility."), gtext("The powerd utility monitors the server state and sets various power control options accordingly."), false, "powerd_change()");?>
					<?php $a_pwmode = array("maximum" => gtext("Maximum (Highest Performance)"), "hiadaptive" => gtext("Hiadaptive (High Performance)"), "adaptive" => gtext("Adaptive (Low Power Consumption)"), "minimum" => gtext("Minimum (Lowest Performance)")); ?>
					<?php html_combobox("pwmode", gtext("Power Mode"), $pconfig['pwmode'], $a_pwmode, gtext("Controls power consumption."), false);?>
					<?php $clocks = @exec("/sbin/sysctl -q -n dev.cpu.0.freq_levels");
						$a_freq = array();
						if (!empty($clocks)) {
							$a_tmp = preg_split("/\s/", $clocks);
						foreach ($a_tmp as $val) {
							list($freq,$tmp) = preg_split("/\//", $val);
							if (!empty($freq))
								$a_freq[] = $freq;
						}
						}
					?>
					<?php html_inputbox("pwmax", gtext("Maximum frequency"), $pconfig['pwmax'], sprintf("%s %s", gtext("CPU frequency:"), join(", ", $a_freq)).".<br />".gtext("Empty as default."), false, 5);?>
					<?php html_inputbox("pwmin", gtext("Minimum frequency"), $pconfig['pwmin'], gtext("Empty as default."), false, 5);?>
					<?php html_separator();?>
			    		<?php html_titleline(gtext("Console Settings"));?>
					<?php html_checkbox("disableconsolemenu", gtext("Console Menu"), !empty($pconfig['disableconsolemenu']) ? true : false, gtext("Disable console menu."), gtext("Changes to this option will take effect after a reboot."));?>
					<?php html_checkbox("enableserialconsole", gtext("Serial Console"), !empty($pconfig['enableserialconsole']) ? true : false, gtext("Enable serial console."), sprintf("<span class='red'><strong>%s</strong></span><br />%s", gtext("The COM port in BIOS has to be enabled before enabling this option."), gtext("Changes to this option will take effect after a reboot.")));?>
					<?php html_checkbox("sysconsaver", gtext("Console screensaver"), !empty($pconfig['sysconsaver']) ? true : false, gtext("Enable console screensaver."), "", false, "sysconsaver_change()");?>
					<?php html_inputbox("sysconsaverblanktime", gtext("Blank time"), $pconfig['sysconsaverblanktime'], gtext("Turn the monitor to standby after N seconds."), true, 5);?>
					<?php html_textarea("motd", gtext("MOTD"), $pconfig['motd'], gtext("Message of the day."), false, 65, 7, false, false);?>

				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save");?>" onclick="enable_change(true)" />
				</div>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
sysconsaver_change();
powerd_change();
//-->
</script>
<?php include("fend.inc");?>
