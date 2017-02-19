<?php
/*
	status_disks.php

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

function status_disks_ajax() {
	global $config;
	
	$body_output = '';
	$pconfig['temp_info'] = $config['smartd']['temp']['info'] ?? 0;
	$pconfig['temp_crit'] = $config['smartd']['temp']['crit'] ?? 0;
	$a_phy_hast = array_merge((array)get_hast_disks_list());
	$a_disk_conf = &array_make_branch($config,'disks','disk');
	if(empty($a_disk_conf)):
	else:
		array_sort_key($a_disk_conf,'name');
	endif;
	$raidstatus = get_sraid_disks_list();
	foreach($a_disk_conf as $disk):
		$iostat_value = system_get_device_iostat($disk['name']);
		$iostat_available = (false !== $iostat_value);
		if($iostat_available):
			$gt_iostat = htmlspecialchars(sprintf("%s KiB/t, %s tps, %s MiB/s",$iostat_value['kpt'],$iostat_value['tps'],$iostat_value['mps']));
		else:
			$gt_iostat = gtext('n/a');
		endif;
		$temp_value = system_get_device_temp($disk['devicespecialfile']);
		$temp_available = (false !== $temp_value);
		if($temp_available):
			$gt_temp = htmlspecialchars(sprintf("%s °C",$temp_value));
		endif;
		$gt_name = htmlspecialchars($disk['name']);
		if($disk['type'] == 'HAST'):
			$role = $a_phy_hast[$disk['name']]['role'];
			$gt_size = htmlspecialchars($a_phy_hast[$disk['name']]['size']);
			$gt_status = htmlspecialchars(sprintf("%s (%s)", (0 == disks_exists($disk['devicespecialfile'])) ? gtext('ONLINE') : gtext('MISSING'),$role));
		else:
			$gt_size = htmlspecialchars($disk['size']);
			$gt_status = (0 == disks_exists($disk['devicespecialfile'])) ? gtext('ONLINE') : gtext('MISSING');
		endif;
		$gt_model = htmlspecialchars($disk['model']);
		$gt_description = empty($disk['desc']) ? gtext('n/a') : htmlspecialchars($disk['desc']);
		$gt_serial = empty($disk['serial']) ? gtext('n/a') : htmlspecialchars($disk['serial']);
		$gt_fstype = empty($disk['fstype']) ? gtext('Unknown or unformatted') : htmlspecialchars(get_fstype_shortdesc($disk['fstype']));

		$body_output .= '<tr>';
		$body_output .= '<td class="lcell">' . $gt_name . '</td>';
		$body_output .= '<td class="lcell">' . $gt_size . '</td>';
		$body_output .= '<td class="lcell">' . $gt_model . '</td>';
		$body_output .= '<td class="lcell">' . $gt_description . '</td>';
		$body_output .= '<td class="lcell">' . $gt_serial . '</td>';
		$body_output .= '<td class="lcell">' . $gt_fstype . '</td>';
		$body_output .= '<td class="lcell">' . $gt_iostat . '</td>';
		$body_output .= '<td class="lcell">';
		if($temp_available):
			if(!empty($pconfig['temp_crit']) && $temp_value >= $pconfig['temp_crit']):
				$body_output .= '<div class="errortext">' . $gt_temp . '</div>';
			elseif(!empty($pconfig['temp_info']) && $gt_temp >= $pconfig['temp_info']):
				$body_output .= '<div class="warningtext">' . $gt_temp . '</div>';
			else:
				$body_output .= $gt_temp;
			endif;  
		else:
			$body_output .= gtext('n/a');
		endif;
		$body_output .= '</td>';
		$body_output .= '<td class="lcebld">' . $gt_status . '</td>';
		$body_output .= '</tr>';
	endforeach;
	foreach($raidstatus as $diskk => $diskv):
		$iostat_value = system_get_device_iostat($diskk);
		$iostat_available = (false !== $iostat_value);
		if($iostat_available):
			$gt_iostat = htmlspecialchars(sprintf("%s KiB/t, %s tps, %s MiB/s",$iostat_value['kpt'],$iostat_value['tps'],$iostat_value['mps']));
		else:
			$gt_iostat = gtext('n/a');
		endif;
		$temp_value = system_get_device_temp($disk['devicespecialfile']);
		$temp_available = (false !== $temp_value);
		if($temp_available):
			$gt_temp = htmlspecialchars(sprintf("%s °C",$temp_value));
		endif;
		$gt_name = htmlspecialchars($diskk);
		$gt_size = htmlspecialchars($diskv['size']);
		$gt_model = gtext('n/a');
		$gt_description = gtext('Software RAID');
		$gt_serial = gtext('n/a');
		$gt_fstype = empty($diskv['fstype']) ? gtext('UFS') : htmlspecialchars(get_fstype_shortdesc($diskv['fstype']));
		$gt_status = htmlspecialchars($diskv['state']);
		$body_output .= '<tr>';
		$body_output .= '<td class="lcell">' . $gt_name . '</td>';
		$body_output .= '<td class="lcell">' . $gt_size . '</td>';
		$body_output .= '<td class="lcell">' . $gt_model . '</td>';
		$body_output .= '<td class="lcell">' . $gt_description . '</td>';
		$body_output .= '<td class="lcell">' . $gt_serial . '</td>';
		$body_output .= '<td class="lcell">' . $gt_fstype . '</td>';
		$body_output .= '<td class="lcell">' . $gt_iostat . '</td>';
		$body_output .= '<td class="lcell">';
		if($temp_available):
			if(!empty($pconfig['temp_crit']) && $temp_value >= $pconfig['temp_crit']):
				$body_output .= '<div class="errortext">' . $gt_temp . '</div>';
			elseif(!empty($pconfig['temp_info']) && $gt_temp >= $pconfig['temp_info']):
				$body_output .= '<div class="warningtext">' . $gt_temp . '</div>';
			else:
				$body_output .= $gt_temp;
			endif;  
		else:
			$body_output .= gtext('n/a');
		endif;
		$body_output .= '</td>';
		$body_output .= '<td class="lcebld">' . $gt_status . '</td>';
		$body_output .= '</tr>';
	endforeach;
	return $body_output;
}
if(is_ajax()):
	$status = status_disks_ajax();
	render_ajax($status);
endif;
$pgtitle = [gtext('Status'),gtext('Disks')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'status_disks.php', null, function(data) {
		if ($('#area_refresh').length > 0) {
			$('#area_refresh').html(data.data);
		}
	});
});
//]]>
</script>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%"> 
			<col style="width:7%">
			<col style="width:15%">
			<col style="width:17%">
			<col style="width:13%">
			<col style="width:10%">
			<col style="width:18%">
			<col style="width:8%">
			<col style="width:7%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gtext('Status & Information'),9);
?>
			<tr>
				<th class="lhell"><?=gtext('Device');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Device Model');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhell"><?=gtext('Serial Number');?></th>
				<th class="lhell"><?=gtext('Filesystem'); ?></th>
				<th class="lhell"><?=gtext('I/O Statistics');?></th>
				<th class="lhell"><?=gtext('Temperature');?></th>
				<th class="lhebl"><?=gtext('Status');?></th>
			</tr>
		</thead>
		<tbody id="area_refresh"><?=status_disks_ajax();?></tbody>
	</table>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
