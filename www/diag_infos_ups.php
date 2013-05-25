<?php
/*
	diag_infos_ups.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
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

if ($_POST) {
	$upsc_enable = $_POST['raw_upsc_enable'];
}

/* functions */

function tblopen () {
	print('<table width="100%" border="0" cellspacing="0" cellpadding="6">'."\n");
}

function tblclose () {
	print("</table>\n");
}

function tblrow ($name, $value, $symbol = null, $id = null) {
	if(!$value) return;

	if($symbol == '&deg;C')
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
  <td width="25%"class="vncellreq">{$name}</td>
  <td width="75%" class="vtable">{$value}{$symbol}</td>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
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

	print(<<<EOD
<tr>
  <td class="vncellreq" width="100px">{$name}</td>
  <td class="vtable">
    <div style="width: 290px; height: 12px; border-top: thin solid gray; border-bottom: thin solid gray; border-left: thin solid gray; border-right: thin solid gray;">
      <div style="width: {$value}{$symbol}; height: 12px; background-color: {$bgcolor};">
        <div style="text-align: center; color: {$color}">{$value}{$symbol}</div>
      </div>
    </div>
  </td>
</tr>
EOD
	."\n");
}

$pgtitle = array(gettext("Diagnostics"), gettext("Information"), gettext("UPS"));
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function upsc_enable_change() {
        switch (document.getElementById('raw_upsc_enable').checked) {
                case true:
                        showElementById('upsc_raw_command','show');
                        break;

                case false:
                        showElementById('upsc_raw_command','hide');
                        break;
        }
}

//-->
</script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="diag_infos.php"><span><?=gettext("Disks");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ata.php"><span><?=gettext("Disks (ATA)");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_part.php"><span><?=gettext("Partitions");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_smart.php"><span><?=gettext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_space.php"><span><?=gettext("Space Used");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_mount.php"><span><?=gettext("Mounts");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_raid.php"><span><?=gettext("Software RAID");?></span></a></li>
		  </ul>
	  </td>
	</tr>
  <tr>
		<td class="tabnavtbl">
		  <ul id="tabnav2">
				<li class="tabinact"><a href="diag_infos_iscsi.php"><span><?=gettext("iSCSI Initiator");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ad.php"><span><?=gettext("MS Domain");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_samba.php"><span><?=gettext("CIFS/SMB");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ftpd.php"><span><?=gettext("FTP");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_rsync_client.php"><span><?=gettext("RSYNC Client");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_swap.php"><span><?=gettext("Swap");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gettext("Sockets");?></span></a></li>
				<li class="tabact"><a href="diag_infos_ups.php" title="<?=gettext("Reload page");?>"><span><?=gettext("UPS");?></span></a></li>
			</ul>
		</td>
	</tr>
  <tr>
    <td class="tabcont">
    	<!-- <table width="100%" border="0"> -->
		<?php tblopen();?>
				<?php html_titleline(gettext("UPS Status"));?>
				<?php if (!isset($config['ups']['enable'])):?>
				<tr>
					<td>
						<pre><?=gettext("UPS disabled");?></pre>
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

						tblrow(gettext('Manufacturer'), $ups['device.mfr']);
						tblrow(gettext('Model'), $ups['device.model']);
						tblrow(gettext('Type'), $ups['device.type']);
						tblrow(gettext('Serial number'), $ups['device.serial']);
						tblrow(gettext('Firmware version'), $ups['ups.firmware']);

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
						tblrow('Status', $disp_status);

						tblrowbar(gettext('Load'), $ups['ups.load'], '%', '100-80', '79-60', '59-0');
						tblrowbar(gettext('Battery Charge'), $ups['battery.charge'], '%', '0-29' ,'30-79', '80-100');

						// status
						tblrow(gettext('Battery voltage'), $ups['battery.voltage'], 'V');
						tblrow(gettext('Input voltage'), $ups['input.voltage'], 'V');
						tblrow(gettext('Input frequency'), $ups['input.frequency'], 'Hz');
						tblrow(gettext('Output voltage'), $ups['output.voltage'], 'V');
						tblrow(gettext('Temperature'), $ups['ups.temperature'], ' &deg;C');
						tblrow(gettext('Remaining battery runtime'), $ups['battery.runtime'], ' seconds');
						
						html_separator();
						
						// output						
						html_titleline(gettext('General Information UPS Unit'));
						tblrow(gettext('UPS status'), $ups['ups.status']);
						tblrow(gettext('UPS alarms'), $ups['ups.alarm']);
						tblrow(gettext('Internal UPS clock time'), $ups['ups.time']);
						tblrow(gettext('Internal UPS clock date'), $ups['ups.date']);
						tblrow(gettext('UPS model'), $ups['ups.model']);
						tblrow(gettext('Manufacturer'), $ups['ups.mfr']);
						tblrow(gettext('Manufacturing date'), $ups['ups.mfr.date']);
						tblrow(gettext('Serial number'), $ups['ups.serial']);
						tblrow(gettext('Vendor ID'), $ups['ups.vendorid']);
						tblrow(gettext('Product ID'), $ups['ups.productid']);
						tblrow(gettext('UPS firmware'), $ups['ups.firmware']);
						tblrow(gettext('Auxiliary device firmware'), $ups['ups.firmware.aux']);
						tblrow(gettext('UPS temperature'), $ups['ups.temperature'], ' &deg;C');
						tblrow(gettext('Load on UPS'), $ups['ups.load'], '%');
						tblrow(gettext('Load when UPS switches to overload condition ("OVER")'), $ups['ups.load.high'], '%');
						tblrow(gettext('UPS system identifier'), $ups['ups.id']);
						tblrow(gettext('Interval to wait before restarting the load'), $ups['ups.delay.start'], ' seconds');
						tblrow(gettext('Interval to wait before rebooting the UPS)'), $ups['ups.delay.reboot'], ' seconds');
						tblrow(gettext('Interval to wait after shutdown with delay command'), $ups['ups.delay.shutdown'], ' seconds');
						tblrow(gettext('Time before the load will be started'), $ups['ups.timer.start'], ' seconds');
						tblrow(gettext('Time before the load will be rebooted'), $ups['ups.timer.reboot'], ' seconds');
						tblrow(gettext('Time before the load will be shutdown'), $ups['ups.timer.shutdown'], ' seconds');
						tblrow(gettext('Interval between self tests'), $ups['ups.test.interval'], ' seconds');
						tblrow(gettext('Results of last self test'), $ups['ups.test.result']);
						tblrow(gettext('Language to use on front panel'), $ups['ups.display.language']);
						tblrow(gettext('UPS external contact sensors'), $ups['ups.contacts']);
						tblrow(gettext('Efficiency of the UPS (Ratio of the output current on the input current)'), $ups['ups.efficiency'], '%');
						tblrow(gettext('Current value of apparent power (Volt-Amps)'), $ups['ups.power'], 'VA');
						tblrow(gettext('Nominal value of apparent power (Volt-Amps)'), $ups['ups.power.nominal'], 'VA');
						tblrow(gettext('Current value of real power (Watts)'), $ups['ups.realpower'], 'W');
						tblrow(gettext('Nominal value of real power (Watts)'), $ups['ups.realpower.nominal'], 'W');
						tblrow(gettext('UPS beeper status'), $ups['ups.beeper.status']);
						tblrow(gettext('UPS type'), $ups['ups.type']);
						tblrow(gettext('UPS watchdog status'), $ups['ups.watchdog.status']);
						tblrow(gettext('UPS starts when mains is (re)applied'), $ups['ups.start.auto']);
						tblrow(gettext('Allow to start UPS from battery'), $ups['ups.start.battery']);
						tblrow(gettext('UPS coldstarts from battery'), $ups['ups.start.reboot']);
						
						html_separator();
						
						html_titleline(gettext('Incoming Line/Power Information'));
						tblrow(gettext('Input voltage'), $ups['input.voltage'], 'V');
						tblrow(gettext('Maximum incoming voltage seen'), $ups['input.voltage.maximum'], 'V');
						tblrow(gettext('Minimum incoming voltage seen'), $ups['input.voltage.minimum'], 'V');
						tblrow(gettext('Nominal input voltage'), $ups['input.voltage.nominal'], 'V');
						tblrow(gettext('Extended input voltage range'), $ups['input.voltage.extended']);
						tblrow(gettext('Reason for last transfer to battery (* opaque)'), $ups['input.transfer.reason']);
						tblrow(gettext('Low voltage transfer point'), $ups['input.transfer.low'], 'V');
						tblrow(gettext('High voltage transfer point'), $ups['input.transfer.high'], 'V');
						tblrow(gettext('smallest settable low voltage transfer point'), $ups['input.transfer.low.min'], 'V');
						tblrow(gettext('greatest settable low voltage transfer point'), $ups['input.transfer.low.max'], 'V');
						tblrow(gettext('smallest settable high voltage transfer point'), $ups['input.transfer.high.min'], 'V');
						tblrow(gettext('greatest settable high voltage transfer point'), $ups['input.transfer.high.max'], 'V');
						tblrow(gettext('Input power sensitivity'), $ups['input.sensitivity']);
						tblrow(gettext('Input power quality (* opaque)'), $ups['input.quality']);
						tblrow(gettext('Input current (A)'), $ups['input.current'], 'A');
						tblrow(gettext('Nominal input current (A)'), $ups['input.current.nominal'], 'A');
						tblrow(gettext('Input line frequency (Hz)'), $ups['input.frequency'], 'Hz');
						tblrow(gettext('Nominal input line frequency (Hz)'), $ups['input.frequency.nominal'], 'Hz');
						tblrow(gettext('Input line frequency low (Hz)'), $ups['input.frequency.low'], 'Hz');
						tblrow(gettext('Input line frequency high (Hz)'), $ups['input.frequency.high'], 'Hz');
						tblrow(gettext('Extended input frequency range'), $ups['input.frequency.extended']);
						tblrow(gettext('Low voltage boosting transfer point'), $ups['input.transfer.boost.low'], 'V');
						tblrow(gettext('High voltage boosting transfer point'), $ups['input.transfer.boost.high'], 'V');
						tblrow(gettext('Low voltage trimming transfer point'), $ups['input.transfer.trim.low'], 'V');
						tblrow(gettext('High voltage trimming transfer point'), $ups['input.transfer.trim.high'], 'V');
						
						html_separator();
						
						html_titleline(gettext('Outgoing Power/Inverter Information'));
						tblrow(gettext('Output voltage (V)'), $ups['output.voltage'], 'V');
						tblrow(gettext('Nominal output voltage (V)'), $ups['output.voltage.nominal'], 'V');
						tblrow(gettext('Output frequency (Hz)'), $ups['output.frequency'], 'Hz');
						tblrow(gettext('Nominal output frequency (Hz)'), $ups['output.frequency.nominal'], 'Hz');
						tblrow(gettext('Output current (A)'), $ups['output.current'], 'A');
						tblrow(gettext('Nominal output current (A)'), $ups['output.current.nominal'], 'A');
						
						html_separator();
						
						html_titleline(gettext('Battery Details'));
						tblrow(gettext('Battery charge'), $ups['battery.charge'], '%');
						tblrow(gettext('Battery Remaining level when UPS switches to Shutdown mode (Low Battery)'), $ups['battery.charge.low'], '%');
						tblrow(gettext('Minimum battery level for UPS restart after power-off'), $ups['battery.charge.restart'], '%');
						tblrow(gettext('Battery level when UPS switches to "Warning" state'), $ups['battery.charge.warning'], '%');
						tblrow(gettext('Battery voltage'), $ups['battery.voltage'], 'V');
						tblrow(gettext('Battery capacity'), $ups['battery.capacity'], 'Ah');
						tblrow(gettext('Battery current'), $ups['battery.current'], 'A');
						tblrow(gettext('Battery temperature'), $ups['battery.temperature'], ' &deg;C');
						tblrow(gettext('Nominal battery voltage'), $ups['battery.voltage.nominal'], 'V');
						tblrow(gettext('Remaining battery runtime'), $ups['battery.runtime'], ' seconds');
						tblrow(gettext('When UPS switches to Low Battery'), $ups['battery.runtime.low'], ' seconds');
						tblrow(gettext('Battery alarm threshold'), $ups['battery.alarm.threshold']);
						tblrow(gettext('Battery change date'), $ups['battery.date']);
						tblrow(gettext('Battery manufacturing date'), $ups['battery.mfr.date']);
						tblrow(gettext('Number of battery packs'), $ups['battery.packs']);
						tblrow(gettext('Number of bad battery packs'), $ups['battery.packs.bad']);
						tblrow(gettext('Battery chemistry'), $ups['battery.type']);
						tblrow(gettext('Prevent deep discharge of battery'), $ups['battery.protection']);
						tblrow(gettext('Switch off when running on battery and no/low load'), $ups['battery.energysave']);
						
						html_separator();
						
						html_titleline(gettext('Ambient Conditions From External Probe Equipment'));
						tblrow(gettext('Ambient temperature (degrees C)'), $ups['ambient.temperature'], ' &deg;C');
						tblrow(gettext('Temperature alarm (enabled/disabled)'), $ups['ambient.temperature.alarm']);
						tblrow(gettext('Temperature threshold high (degrees C)'), $ups['ambient.temperature.high'], ' &deg;C');
						tblrow(gettext('Temperature threshold low (degrees C)'), $ups['ambient.temperature.low'], ' &deg;C');
						tblrow(gettext('Maximum temperature seen (degrees C)'), $ups['ambient.temperature.maximum'], ' &deg;C');
						tblrow(gettext('Minimum temperature seen (degrees C)'), $ups['ambient.temperature.minimum'], ' &deg;C');
						tblrow(gettext('Ambient relative humidity (percent)'), $ups['ambient.humidity'], '%');
						tblrow(gettext('Relative humidity alarm (enabled/disabled)'), $ups['ambient.humidity.alarm']);
						tblrow(gettext('Relative humidity threshold high (percent)'), $ups['ambient.humidity.high'], '%');
						tblrow(gettext('Relative humidity threshold high (percent)'), $ups['ambient.humidity.low'], '%');
						tblrow(gettext('Maximum relative humidity seen (percent)'), $ups['ambient.humidity.maximum'], '%');
						tblrow(gettext('Minimum relative humidity seen (percent)'), $ups['ambient.humidity.minimum'], '%');
						
						html_separator();
						
						html_titleline('Smart Outlet Management');
						tblrow('[Main] Outlet system identifier', $ups['outlet.id']);
						tblrow('[Main] Outlet description', $ups['outlet.desc']);
						tblrow('[Main] Outlet switch control (on/off)', $ups['outlet.switch']);
						tblrow('[Main] Outlet switch status (on/off)', $ups['outlet.status']);
						tblrow('[Main] Outlet switch ability (yes/no)', $ups['outlet.switchable']);
						tblrow('[Main] Remaining battery level to power off this outlet', $ups['outlet.autoswitch.charge.low'], '%');
						tblrow('[Main] Interval to wait before shutting down this outlet', $ups['outlet.delay.shutdown'], ' seconds');
						tblrow('[Main] Interval to wait before restarting this outlet', $ups['outlet.delay.start'], ' seconds');
						tblrow('[Main] Current (A)', $ups['outlet.current'], 'A');
						tblrow('[Main] Maximum seen current (A)', $ups['outlet.current.maximum'], 'A');
						tblrow('[Main] Current value of real power (W)', $ups['outlet.realpower'], 'W');
						tblrow('[Main] Voltage (V)', $ups['outlet.voltage'], 'V');
						tblrow('[Main] Power Factor (dimensionless value between 0 and 1)', $ups['outlet.powerfactor']);
						tblrow('[Main] Crest Factor (dimensionless, equal to or greater than 1)', $ups['outlet.crestfactor']);
						tblrow('[Main] Apparent power (VA)', $ups['outlet.power'], 'VA');
						
						for ($i = 1; $ups['outlet.'.$i.'.id']; $i++) {
							tblrow('['.$i.'] Outlet system identifier', $ups['outlet.'.$i.'.id']);
							tblrow('['.$i.'] Outlet description', $ups['outlet.'.$i.'.desc']);
							tblrow('['.$i.'] Outlet switch control (on/off)', $ups['outlet.'.$i.'.switch']);
							tblrow('['.$i.'] Outlet switch status (on/off)', $ups['outlet.'.$i.'.status']);
							tblrow('['.$i.'] Outlet switch ability (yes/no)', $ups['outlet.'.$i.'.switchable']);
							tblrow('['.$i.'] Remaining battery level to power off this outlet', $ups['outlet.'.$i.'.autoswitch.charge.low'], '%');
							tblrow('['.$i.'] Interval to wait before shutting down this outlet', $ups['outlet.'.$i.'.delay.shutdown'], ' seconds');
							tblrow('['.$i.'] Interval to wait before restarting this outlet', $ups['outlet.'.$i.'.delay.start'], ' seconds');
							tblrow('['.$i.'] Current (A)', $ups['outlet.'.$i.'.current'], 'A');
							tblrow('['.$i.'] Maximum seen current (A)', $ups['outlet.'.$i.'.current.maximum'], 'A');
							tblrow('['.$i.'] Current value of real power (W)', $ups['outlet.'.$i.'.realpower'], 'W');
							tblrow('['.$i.'] Voltage (V)', $ups['outlet.'.$i.'.voltage'], 'V');
							tblrow('['.$i.'] Power Factor (dimensionless value between 0 and 1)', $ups['outlet.'.$i.'.powerfactor']);
							tblrow('['.$i.'] Crest Factor (dimensionless, equal to or greater than 1)', $ups['outlet.'.$i.'.crestfactor']);
							tblrow('['.$i.'] Apparent power (VA)', $ups['outlet.'.$i.'.power'], 'VA');
						}
						
						html_separator();
						
						html_titleline(gettext('NUT Internal Driver Information'));
						tblrow(gettext('Driver used'), $ups['driver.name']);
						tblrow(gettext('Driver version'), $ups['driver.version']);
						tblrow(gettext('Driver version internal'), $ups['driver.version.internal']);
						tblrow(gettext('Parameter xxx (ups.conf or cmdline -x) setting'), $ups['driver.parameter.xxx']);
						tblrow(gettext('Flag xxx (ups.conf or cmdline -x) status'), $ups['driver.flag.xxx']);
						
						html_separator();
						
						html_titleline(gettext('Internal Server Information'));
						tblrow(gettext('Server information'), $ups['server.info']);
						tblrow(gettext('Server version'), $ups['server.version']);
						
						
						html_separator();
						html_separator();

						html_titleline_checkbox('raw_upsc_enable', 'NUT', $upsc_enable ? true : false, (gettext('Show RAW UPS Info')), 'upsc_enable_change()');
						tblrow('RAW info', htmlspecialchars($read), 'pre', 'upsc_raw_command');

						unset($handle);
						unset($read);
						unset($lines);
						unset($status);
						unset($disp_status);
						unset($ups);
					}
					
					unset($cmd);
				?>
			  <?php endif;?>
    	<!-- </table> -->
		<?php tblclose();?>
    </td>
  </tr>
</table>
<script type="text/javascript">
<!--
upsc_enable_change();
//-->
</script>
<?php include("fend.inc");?>