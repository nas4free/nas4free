<?php
/*
	diag_infos_rsync_client.php

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

$pgtitle = [gtext('Diagnostics'),gtext('Information'),gtext('RSYNC Client')];
include 'fbegin.inc';
?>
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
		<li class="tabact"><a href="diag_infos_rsync_client.php" title="<?=gtext('Reload page');?>"><span><?=gtext('RSYNC Client');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_netstat.php"><span><?=gtext('Netstat');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gtext('Sockets');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ipmi.php"><span><?=gtext('IPMI Stats');?></span></a></li>
		<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gtext('UPS');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
if(!is_array($config['rsync']) || !is_array($config['rsync']['rsyncclient'])):
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gtext('RSYNC Client Information & Status'));
?>
		</thead>
		<tbody><tr>
			<td class="celltag"><?=gtext('Information');?></td>
			<td class="celldata">
<?php
				echo '<pre>';
				echo gtext('No RSYNC Client configured.');
				echo '</pre>';
?>
			</tr></tbody>
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
			html_titleline2(gtext('RSYNC Client Information & Status'));
?>
		</thead>
		<tbody><tr>
			<td class="celltag"><?=gtext('Information');?></td>
			<td class="celldata">
<?php
				echo '<pre>';
				echo '<strong>',gtext('Detected RSYNC Remote Shares'),':</strong><br /><br />';
				$i = 0;
				foreach($config['rsync']['rsyncclient'] as $rsyncclient): 
					echo '<br />';
					echo '',gtext('RSYNC client number'),' ',$i,':','<br />';
					echo '- ',gtext('Remote server address'),': ',$rsyncclient['rsyncserverip'],'<br />';
					echo '- ',gtext('Remote share name configured'),': ',$rsyncclient['remoteshare'],'<br />';
					echo '- ',gtext('Detected shares on this server'),': ','<br />';
					$cmd = sprintf('/usr/local/bin/rsync %s::',$rsyncclient['rsyncserverip']);
					exec($cmd,$rawdata);
					echo htmlspecialchars(implode("\n",$rawdata));
					unset($rawdata);
				endforeach;
				echo '</pre>';
?>
		</tr></tbody>
	</table>
<?php
endif;
?>
</td></tr></table>
<?php
include 'fend.inc';
?>