<?php
/*
	diag_infos_netstat.php

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

function diag_infos_netstat_ajax() {
	$cmd = '/usr/bin/netstat -Aa';
	mwexec2($cmd,$rawdata);
	return implode("\n",$rawdata);
}
if(is_ajax()):
	$status['area_refresh'] = diag_infos_netstat_ajax();
	render_ajax($status);
endif;
$pgtitle = [gtext('Diagnostics'),gtext('Information'),gtext('Netstat')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(5000, 5000, 'diag_infos_netstat.php', null, function(data) {
		if ($('#area_refresh').length > 0) {
			$('#area_refresh').text(data.area_refresh);
		}
	});
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="diag_infos_disks.php"><span><?=gtext('Disks');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_disks_info.php"><span><?=gtext('Disks (Info)');?></span></a></li>
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
		<li class="tabact"><a href="diag_infos_netstat.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Netstat');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gtext('Sockets');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ipmi.php"><span><?=gtext('IPMI Stats');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gtext('UPS');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gtext('Netstat'));
?>
		</thead>
		<tbody><tr>
			<td class="celltag"><?=gtext('Information');?></td>
			<td class="celldata">
				<pre><span id="area_refresh"><?=diag_infos_netstat_ajax();?></span></pre>
			</td>
		</tr></tbody>
	</table>
</table>
<?php
include 'fend.inc';
?>
