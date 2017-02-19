<?php
/*
	services_status.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE NAS4FREE PROJECT ``AS IS'' AND ANY
	EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE NAS4FREE PROJECT OR ITS CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
	THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies,
	either expressed or implied, of the NAS4Free Project.
*/
require 'auth.inc';
require 'guiconfig.inc';

$sphere_scriptname = basename(__FILE__);

$ups_script = 'nut';
if(isset($config['ups']['mode']) && ($config['ups']['mode'] == 'slave')):
	$ups_script = 'nut_upsmon';
endif;

if('dom0' !== $g['arch']):
	$a_service[] = ['desc' => gtext('HAST'),'link' => 'services_hast.php','config' => 'hast','scriptname' => 'hastd'];
	$a_service[] = ['desc' => gtext('CIFS/SMB'),'link' => 'services_samba.php','config' => 'samba','scriptname' => 'samba'];
	$a_service[] = ['desc' => gtext('FTP'),'link' => 'services_ftp.php','config' => 'ftpd','scriptname' => 'proftpd'];
	$a_service[] = ['desc' => gtext('TFTP'),'link' => 'services_tftp.php','config' => 'tftpd','scriptname' => 'tftpd'];
	$a_service[] = ['desc' => gtext('SSH'),'link' => 'services_sshd.php','config' => 'sshd','scriptname' => 'sshd'];
	$a_service[] = ['desc' => gtext('NFS'),'link' => 'services_nfs.php','config' => 'nfsd','scriptname' => 'nfsd'];
	$a_service[] = ['desc' => gtext('AFP'),'link' => 'services_afp.php','config' => 'afp','scriptname' => 'netatalk'];
	$a_service[] = ['desc' => gtext('RSYNC'),'link' => 'services_rsyncd.php','config' => 'rsyncd','scriptname' => 'rsyncd'];
	$a_service[] = ['desc' => gtext('Syncthing'),'link' => 'services_syncthing.php','config' => 'syncthing','scriptname' => 'syncthing'];
	$a_service[] = ['desc' => gtext('Unison'),'link' => 'services_unison.php','config' => 'unison','scriptname' => 'unison'];
	$a_service[] = ['desc' => gtext('iSCSI Target'),'link' => 'services_iscsitarget.php','config' => 'iscsitarget','scriptname' => 'iscsi_target'];
	$a_service[] = ['desc' => gtext('DLNA/UPnP Fuppes'),'link' => 'services_fuppes.php','config' => 'upnp','scriptname' => 'fuppes'];
	$a_service[] = ['desc' => gtext('DLNA/UPnP MiniDLNA'),'link' => 'services_minidlna.php','config' => 'minidlna','scriptname' => 'minidlna'];
	$a_service[] = ['desc' => gtext('iTunes/DAAP'),'link' => 'services_daap.php','config' => 'daap','scriptname' => 'mt-daapd'];
	$a_service[] = ['desc' => gtext('Dynamic DNS'),'link' => 'services_dynamicdns.php','config' => 'dynamicdns','scriptname' => 'inadyn'];
	$a_service[] = ['desc' => gtext('SNMP'),'link' => 'services_snmp.php','config' => 'snmpd','scriptname' => 'bsnmpd'];
	$a_service[] = ['desc' => gtext('UPS'),'link' => 'services_ups.php','config' => 'ups','scriptname' => $ups_script];
	$a_service[] = ['desc' => gtext('Webserver'),'link' => 'services_websrv.php','config' => 'websrv','scriptname' => 'websrv'];
	$a_service[] = ['desc' => gtext('BitTorrent'),'link' => 'services_bittorrent.php','config' => 'bittorrent','scriptname' => 'transmission'];
	$a_service[] = ['desc' => gtext('LCDproc'),'link' => 'services_lcdproc.php','config' => 'lcdproc','scriptname' => 'LCDd'];
else:
	$a_service[] = ['desc' => gtext('SSH'),'link' => 'services_sshd.php','config' => 'sshd','scriptname' => 'sshd'];
	$a_service[] = ['desc' => gtext('NFS'),'link' => 'services_nfs.php','config' => 'nfsd','scriptname' => 'nfsd'];
	$a_service[] = ['desc' => gtext('iSCSI Target'),'link' => 'services_iscsitarget.php','config' => 'iscsitarget','scriptname' => 'iscsi_target'];
	$a_service[] = ['desc' => gtext('UPS'),'link' => 'services_ups.php','config' => 'ups','scriptname' => $ups_script];
endif;
$pgtitle = [gtext('Status'),gtext('Services')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner on submit for id form.?>
	$("#iform").submit(function() { spinner(); });
}); 
//]]>
</script>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform">
	<table class="area_data_selection">
		<colgroup>
			<col style="width:70%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'),4);?>
			<tr>
				<th class="lhell"><?=gtext('Service');?></th>
				<th class="lhell"><?=gtext('Enabled');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($a_service as $r_service):?>
				<tr>
					<?php
					$enable = isset($config[$r_service['config']]['enable']);
					$status = rc_is_service_running($r_service['scriptname']);
					?>
					<td class="<?=$enable ? 'lcell' : 'lcelld';?>"><?=htmlspecialchars($r_service['desc']);?>&nbsp;</td>
					<td class="<?=$enable ? 'lcelc' : 'lcelcd';?>">
						<?php if($enable):?>
							<a title="<?=gtext('Enabled');?>"><img src="<?=$g_img['ena'];?>" alt=""/></a>
						<?php else:?>
							<a title="<?=gtext('Disabled');?>"><img src="<?=$g_img['dis'];?>" alt=""/></a>
						<?php endif;?>
					</td>
					<td class="<?=$enable ? 'lcelc' : 'lcelcd';?>">
						<?php if(0 === $status):?>
							<a title="<?=gtext('Running');?>"><img src="<?=$g_img['ena'];?>" alt=""/></a>
						<?php else:?>
							<a title="<?=gtext('Stopped');?>"><img src="<?=$g_img['dis'];?>" alt=""/></a>
						<?php endif;?>
					</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<?php
							echo html_row_toolbox($r_service['link'],gtext('Modify Service'),'','',true,true);
							?>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
					
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
