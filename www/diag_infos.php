<?php
/*
	diag_infos.php

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

$pgtitle = array(gtext("Diagnostics"), gtext("Information"), gtext("Disks"));

// Get all physical disks.
$a_phy_disk = array_merge((array)get_physical_disks_list());

?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="diag_infos.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Disks");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ata.php"><span><?=gtext("Disks (ATA)");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_part.php"><span><?=gtext("Partitions");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_smart.php"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_space.php"><span><?=gtext("Space Used");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_mount.php"><span><?=gtext("Mounts");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_raid.php"><span><?=gtext("Software RAID");?></span></a></li>
		  </ul>
	  </td>
	</tr>
  <tr>
		<td class="tabnavtbl">
		  <ul id="tabnav2">
				<li class="tabinact"><a href="diag_infos_iscsi.php"><span><?=gtext("iSCSI Initiator");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ad.php"><span><?=gtext("MS Domain");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_samba.php"><span><?=gtext("CIFS/SMB");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ftpd.php"><span><?=gtext("FTP");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_rsync_client.php"><span><?=gtext("RSYNC Client");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_swap.php"><span><?=gtext("Swap");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gtext("Sockets");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ipmi.php"><span><?=gtext('IPMI Stats');?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gtext("UPS");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php html_titleline2(gtext('List Detected Disks'), 12);?>
				<tr>
					<td width="5%" class="listhdrlr"><?=gtext("Device");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Device Model"); ?></td>
					<td width="11%" class="listhdrr"><?=gtext("Description"); ?></td>
					<td width="8%" class="listhdrr"><?=gtext("Size");?></td>
					<td width="10%" class="listhdrr"><?=gtext("Serial Number"); ?></td>
					<td width="7%" class="listhdrr"><?=gtext("Rotation Rate"); ?></td>
					<td width="7%" class="listhdrr"><?=gtext("Transfer Rate"); ?></td>
					<td width="8%" class="listhdrr"><?=gtext("S.M.A.R.T."); ?></td>
					<td width="5%" class="listhdrr"><?=gtext("Controller"); ?></td>
					<td width="14%" class="listhdrr"><?=gtext("Controller Model"); ?></td>
					<td width="7%" class="listhdrr"><?=gtext("Temperature");?></td>
					<td width="6%" class="listhdrr"><?=gtext("Status");?></td>
				</tr>
				<?php foreach ($a_phy_disk as $disk):?>
				<?php (($temp = system_get_device_temp($disk['devicespecialfile'])) === FALSE) ? $temp = gtext("n/a") : $temp = sprintf("%s &deg;C", htmlspecialchars($temp));?>
				<?php
					if ($disk['type'] == 'HAST') {
						$role = $a_phy_disk[$disk['name']]['role'];
						$status = sprintf("%s (%s)", (0 == disks_exists($disk['devicespecialfile'])) ? gtext("ONLINE") : gtext("MISSING"), $role);
						$disk['size'] = $a_phy_disk[$disk['name']]['size'];
					} else {
						$status = (0 == disks_exists($disk['devicespecialfile'])) ? gtext("ONLINE") : gtext("MISSING");
					}
				?>
				<tr>
					<td class="listlr"><?=htmlspecialchars($disk['name']);?></td>
					<td class="listr"><?=htmlspecialchars($disk['model']);?>&nbsp;</td>
				<?php	global $config_disks;
					$disk['desc'] = $config_disks[$disk['devicespecialfile']]['desc'];
				?>
					<td class="listr"><?=(empty($disk['desc']) ) === FALSE ? htmlspecialchars($disk['desc']) : gtext("n/a");?>&nbsp;</td>
					<td class="listr"><?=htmlspecialchars($disk['size']);?></td>
					<td class="listr"><?=(empty($disk['serial']) ) === FALSE ? htmlspecialchars($disk['serial']) : gtext("n/a");?>&nbsp;</td>
					<td class="listr"><?=(empty($disk['rotation_rate']) ) === FALSE ? htmlspecialchars($disk['rotation_rate']) : gtext("Unknown");?>&nbsp;</td>
					<td class="listr"><?=(empty($disk['transfer_rate']) ) === FALSE ? htmlspecialchars($disk['transfer_rate']) : gtext("n/a");?>&nbsp;</td>
				<?php
					$matches = preg_split("/[\s\,]+/", $disk['smart']['smart_support']);
					$smartsupport = '';
					if (isset($matches[0])) {
						if (0 == strcasecmp($matches[0], 'available')) {
							$smartsupport .= gtext('Available');
							if (isset($matches[1])) {
								if (0 == strcasecmp($matches[1], 'enabled')) {
									$smartsupport .= (', ' . gtext('Enabled'));
								} elseif (0 ==  strcasecmp($matches[1], 'disabled')) {
									$smartsupport .= (', ' . gtext('Disabled'));
								}
							}
						} elseif (0 == strcasecmp($matches[0], 'unavailable')) {
							$smartsupport .= gtext('Unavailable');
						}
					}
				?>
					<td class="listr"><?=htmlspecialchars($smartsupport);?>&nbsp;</td>
					<td class="listr"><?=htmlspecialchars($disk['controller'].$disk['controller_id']);?>&nbsp;</td>
					<td class="listr"><?=htmlspecialchars($disk['controller_desc']);?>&nbsp;</td>
					<td class="listr"><?=$temp;?>&nbsp;</td>
					<td class="listbg"><?=$status;?>&nbsp;</td>
				</tr>
				<?php endforeach;?>
				</table>
			</td>
		</tr>
	  </table>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>