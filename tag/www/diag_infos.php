#!/usr/local/bin/php
<?php
/*
	diag_infos.php
	
	Part of NAS4Free (http://www.nas4free.org).
	Copyright (C) 2012 by NAS4Free Team <info@nas4free.org>.
	All rights reserved.
	
	Modified for XHTML by Daisuke Aoyama <aoyama@peach.ne.jp>
	Copyright (C) 2010 Daisuke Aoyama <aoyama@peach.ne.jp>.	
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (C) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
	All rights reserved.
	
	Portions of m0n0wall (http://m0n0.ch/wall).
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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
	either expressed or implied, of the FreeBSD Project.
*/
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Diagnostics"), gettext("Information"), gettext("Disks"));
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="diag_infos.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Disks");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ata.php"><span><?=gettext("Disks (ATA)");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_part.php"><span><?=gettext("Partitions");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_smart.php"><span><?=gettext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_space.php"><span><?=gettext("Space Used");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_mount.php"><span><?=gettext("Mounts");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_raid.php"><span><?=gettext("Software RAID");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_iscsi.php"><span><?=gettext("iSCSI Initiator");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ad.php"><span><?=gettext("MS Domain");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_samba.php"><span><?=gettext("CIFS/SMB");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ftpd.php"><span><?=gettext("FTP");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_rsync_client.php"><span><?=gettext("RSYNC Client");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_swap.php"><span><?=gettext("Swap");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gettext("Sockets");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gettext("UPS");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<table width="100%" border="0">
				<?php
				unset($rawdata);
				exec("/sbin/atacontrol list", $rawdata);
				html_titleline(gettext("List of detected ATA disks"));
				$disk_list=null;
				exec("dmesg -N system | grep ata |  egrep ad[0-9]", $disk_list);
				// first clean up dmesg
				$disk_array=array();
				foreach($disk_list as $disk)
				{
					$info = preg_match('=(ad[0-9]+):\s([0-9A-Z]+)\s\<([\w\s\/\-\.\_\:]+)\>\sat\s([\w-]+)=is', $disk, $tr);
					$disk_array[$tr[1]]=array("size"=>$tr[2], "name"=>$tr[3], "port"=>$tr[4]);
				}
				// sort by channel
				unset($disk);
				$channel_array=array();
				foreach($disk_array as $id => $disk)
				{
					preg_match('!([\w]+)\-([\w]+)!', $disk['port'], $channel);
					$channel_array[$channel[1]][]=array(
									'dev' =>$id, 
									'name'=>$disk['name'], 
									'size'=>$disk['size'],
									'port'=>$channel[2]); 
				}
				?>
				<tr>
					<td>
						<?php
						if(count($channel_array) > 0)
						{
							echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
							 echo '<tr>
                                                                        <td width="10%" class="listhdrlr">'.gettext("Port").'</td>
                                                                        <td width="10%" class="listhdrlr">'.gettext("Disk").'</td>
                                                                        <td width="20%" class="listhdrr">'.gettext("Size").'</td>
                                                                        <td class="listhdrr">'.gettext("Description").'</td>
                                                                </tr>';
							foreach($channel_array as $channel => $data)
							{
								echo '<tr><td colspan="4" class="listtopic">'.strtoupper($channel).'</td></tr>';
								foreach( $data as $disk )
								{
									echo '<tr>';
									echo '<td class="vncellt" width="10%">'.ucfirst($disk['port']).'</td>';
									echo '<td class="listr" width="10%" >'.strtoupper($disk['dev']).'</td>';
									echo '<td class="listr" width="20%" >'.$disk['size'].'</td>';
									echo '<td class="listr">'.$disk['name'].'</td>';
									echo '</tr>';
								}
							}
							echo '</table>';
						}
						?>
					</td>
				</tr>
				<?php
				unset($rawdata);
				exec("/sbin/camcontrol devlist", $rawdata);
				html_titleline(gettext("List of detected SCSI disks"));
				?>
				<tr>
					<td>
						<pre><?php if (empty($rawdata)) { echo gettext("n/a"); } else { echo htmlspecialchars(implode("\n", $rawdata)); }?></pre>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
