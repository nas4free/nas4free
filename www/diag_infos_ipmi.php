<?php
/*
	diag_infos_ipmi.php

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
$sphere_header = 'Location: '.$sphere_scriptname;

function get_ipmi_sensor() {
	$a_sensor = [];
	mwexec2('ipmitool sensor',$a_output);
	foreach($a_output as $r_output):
		$r_sensor = explode('|',$r_output);
		$c_sensor = count($r_sensor);
		for($i = 0;$i < $c_sensor;$i++):
			$r_sensor[$i] = trim($r_sensor[$i]);
		endfor;
		$a_sensor[] = $r_sensor;
	endforeach;
	unset($a_output);
	return $a_sensor;
}

function get_ipmi_fru() {
	$a_fru = [];
	mwexec2('ipmitool fru',$a_output);
	foreach($a_output as $r_output):
		$r_fru = explode(': ',$r_output,2); // we need 2 columns only, tag and value
		$c_fru = count($r_fru);
		for($i = 0;$i < $c_fru;$i++):
			$r_fru[$i] = trim($r_fru[$i]);
		endfor;
		$a_fru[] = $r_fru;
	endforeach;
	unset($a_output);
	return $a_fru;
}
function diag_infos_ipmi_ajax() {
	$a_ipmi_sensor = get_ipmi_sensor();
	$body_output = '';
	foreach($a_ipmi_sensor as $r_ipmi_sensor):
		$body_output .= '<tr>';
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[0]) . '</td>';
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[1] . ' ' . $r_ipmi_sensor[2]) . '</td>';
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[3]) . '</td>';
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[4]) . '</td>';
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[9]) . '</td>';
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[6]) . '</td>';
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[7]) . '</td>';
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[5]) . '</td>';
		$body_output .= '<td class="lcebr">' . htmlspecialchars($r_ipmi_sensor[8]) . '</td>';
		$body_output .= '</tr>' . "\n";
	endforeach;
	return $body_output;
}
$a_ipmi_sensor = get_ipmi_sensor();
$a_ipmi_fru = get_ipmi_fru();
if(is_ajax()):
	$status = diag_infos_ipmi_ajax();
	render_ajax($status);
endif;
$pgtitle = [gtext('Diagnostics'),gtext('Information'),gtext('IPMI Stats')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'diag_infos_ipmi.php', null, function(data) {
		if ($('#area_refresh').length > 0) {
			$('#area_refresh').html(data.data);
		}
	});
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="diag_infos_disks.php"><span><?=gtext("Disks");?></span></a></li>
		<li class="tabinact"><a href="diag_infos_disks_info.php"><span><?=gtext("Disks (Info)");?></span></a></li>
		<li class="tabinact"><a href="diag_infos_part.php"><span><?=gtext('Partitions');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_smart.php"><span><?=gtext('S.M.A.R.T.');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_space.php"><span><?=gtext('Space Used');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_swap.php"><span><?=gtext('Swap');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_mount.php"><span><?=gtext('Mounts');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_raid.php"><span><?=gtext('Software RAID');?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabinact"><a href="diag_infos_iscsi.php"><span><?=gtext('iSCSI Initiator');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ad.php"><span><?=gtext('MS Domain');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_samba.php"><span><?=gtext('CIFS/SMB');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ftpd.php"><span><?=gtext('FTP');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_rsync_client.php"><span><?=gtext('RSYNC Client');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_netstat.php"><span><?=gtext('Netstat');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gtext('Sockets');?></span></a></li>
		<li class="tabact"><a href="diag_infos_ipmi.php" title="<?=gtext('Reload page');?>"><span><?=gtext('IPMI Stats');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gtext('UPS');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(empty($a_ipmi_sensor)):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline2(gtext('Sensor Information'),9);
?>
			</thead>
			<tbody>
<?php
				html_text2('sensor',gtext('System Message'),gtext('No IPMI Sensor data available.'));
?>			
			</tbody>
		</table>
<?php
	else:
?>
		<table class="area_data_selection">
			<colgroup>
				<col style="width:11%">
				<col style="width:12%">
				<col style="width:5%">
				<col style="width:12%">
				<col style="width:12%">
				<col style="width:12%">
				<col style="width:12%">
				<col style="width:12%">
				<col style="width:12%">
			</colgroup>
			<thead>
<?php
				html_titleline2(gtext('Sensor Information'),9);
?>
				<tr>
					<td class="lhelc" colspan="3"><?=gtext('Sensor List');?></td>
					<td class="lhelc" colspan="2"><?=gtext('Non-Recoverable');?></td>
					<td class="lhelc" colspan="2"><?=gtext('Non-Critical');?></td>
					<td class="lhebc" colspan="2"><?=gtext('Critical');?></td>
				</tr>
				<tr>
					<td class="lhell"><?=gtext('Sensor');?></td>
					<td class="lhell"><?=gtext('Reading');?></td>
					<td class="lhell"><?=gtext('Status');?></td>
					<td class="lhell"><?=gtext('Lower');?></td>
					<td class="lhell"><?=gtext('Upper');?></td>
					<td class="lhell"><?=gtext('Lower');?></td>
					<td class="lhell"><?=gtext('Upper');?></td>
					<td class="lhell"><?=gtext('Lower');?></td>
					<td class="lhebl"><?=gtext('Upper');?></td>
				</tr>
			</thead>
			<tbody id="area_refresh"><?=diag_infos_ipmi_ajax();?></tbody>
		</table>
<?php
	endif;
?>
<?php
	if(empty($a_ipmi_fru)):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gtext('FRU Information'),2);
?>
			</thead>
			<tbody>
<?php
				html_text2('sensor',gtext('System Message'),gtext('No IPMI FRU data available.'));
?>				
			</tbody>
		</table>
<?php
	else:
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gtext('FRU Information'),2);
?>
				<tr>
					<td class="lhell"><?=gtext('Tag');?></td>
					<td class="lhebl"><?=gtext('Value');?></td>
				</tr>
			</thead>
			<tbody>
<?php
				foreach ($a_ipmi_fru as $r_ipmi_fru):
?>
					<tr>
						<td class="lcell"><?=htmlspecialchars($r_ipmi_fru[0]);?>&nbsp;</td>
						<td class="lcebl"><?=htmlspecialchars($r_ipmi_fru[1]);?>&nbsp;</td>
					</tr>
<?php
				endforeach;
?>
			</tbody>
		</table>
<?php 
	endif;
	include 'formend.inc';
?>
</td></tr></tbody></table>
<?php
include 'fend.inc';
?>
