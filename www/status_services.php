<?php
/*
	services_status.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	3. Products derived from this software may not be called "NAS4Free"
	   nor may "NAS4Free" appear in their names without prior written
	   permission of the NAS4Free Project. For written permission, please
	   contact info@nas4free.org

	4. Redistributions of any form whatsoever must retain the following
	   acknowledgment:

	   "This product includes software developed by the NAS4Free Project
	   for use in the NAS4Free Software Distribution (http://www.nas4free.org)".

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
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gtext("Status"), gtext("Services"));

$ups_script = "nut";
if (isset($config["ups"]["mode"]) && ($config["ups"]["mode"] == "slave")) { $ups_script = "nut_upsmon"; }

if ("dom0" !== $g['arch']) {
$a_service[] = array("desc" => gtext("HAST"), "link" => "services_hast.php", "config" => "hast", "scriptname" => "hastd");
$a_service[] = array("desc" => gtext("CIFS/SMB"), "link" => "services_samba.php", "config" => "samba", "scriptname" => "samba");
$a_service[] = array("desc" => gtext("FTP"), "link" => "services_ftp.php", "config" => "ftpd", "scriptname" => "proftpd");
$a_service[] = array("desc" => gtext("TFTP"), "link" => "services_tftp.php", "config" => "tftpd", "scriptname" => "tftpd");
$a_service[] = array("desc" => gtext("SSH"), "link" => "services_sshd.php", "config" => "sshd", "scriptname" => "sshd");
$a_service[] = array("desc" => gtext("NFS"), "link" => "services_nfs.php", "config" => "nfsd", "scriptname" => "nfsd");
$a_service[] = array("desc" => gtext("AFP"), "link" => "services_afp.php", "config" => "afp", "scriptname" => "netatalk");
$a_service[] = array("desc" => gtext("RSYNC"), "link" => "services_rsyncd.php", "config" => "rsyncd", "scriptname" => "rsyncd");
$a_service[] = array("desc" => gtext("Syncthing"), "link" => "services_syncthing.php", "config" => "syncthing", "scriptname" => "syncthing");
$a_service[] = array("desc" => gtext("Unison"), "link" => "services_unison.php", "config" => "unison", "scriptname" => "unison");
$a_service[] = array("desc" => gtext("iSCSI Target"), "link" => "services_iscsitarget.php", "config" => "iscsitarget", "scriptname" => "iscsi_target");
$a_service[] = array("desc" => gtext("DLNA/UPnP Fuppes"), "link" => "services_fuppes.php", "config" => "upnp", "scriptname" => "fuppes");
$a_service[] = array("desc" => gtext("DLNA/UPnP MiniDLNA"), "link" => "services_minidlna.php", "config" => "minidlna", "scriptname" => "minidlna");
$a_service[] = array("desc" => gtext("iTunes/DAAP"), "link" => "services_daap.php", "config" => "daap", "scriptname" => "mt-daapd");
$a_service[] = array("desc" => gtext("Dynamic DNS"), "link" => "services_dynamicdns.php", "config" => "dynamicdns", "scriptname" => "inadyn");
$a_service[] = array("desc" => gtext("SNMP"), "link" => "services_snmp.php", "config" => "snmpd", "scriptname" => "bsnmpd");
$a_service[] = array("desc" => gtext("UPS"), "link" => "services_ups.php", "config" => "ups", "scriptname" => $ups_script);
$a_service[] = array("desc" => gtext("Webserver"), "link" => "services_websrv.php", "config" => "websrv", "scriptname" => "websrv");
$a_service[] = array("desc" => gtext("BitTorrent"), "link" => "services_bittorrent.php", "config" => "bittorrent", "scriptname" => "transmission");
$a_service[] = array("desc" => gtext("LCDproc"), "link" => "services_lcdproc.php", "config" => "lcdproc", "scriptname" => "LCDd");
} else {
$a_service[] = array("desc" => gtext("SSH"), "link" => "services_sshd.php", "config" => "sshd", "scriptname" => "sshd");
$a_service[] = array("desc" => gtext("NFS"), "link" => "services_nfs.php", "config" => "nfsd", "scriptname" => "nfsd");
$a_service[] = array("desc" => gtext("iSCSI Target"), "link" => "services_iscsitarget.php", "config" => "iscsitarget", "scriptname" => "iscsi_target");
$a_service[] = array("desc" => gtext("UPS"), "link" => "services_ups.php", "config" => "ups", "scriptname" => $ups_script);
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_info.php" method="post">
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php html_titleline(gtext('Overview'), 3);?>
					<tr>
						<td width="90%" class="listhdrlr"><?=gtext("Service");?></td>
						<td width="5%" class="listhdrc"><?=gtext("Enabled");?></td>
						<td width="5%" class="listhdrc"><?=gtext("Status");?></td>
					</tr>
					<?php foreach ($a_service as $servicev):?>
					<tr>
						<?php $enable = isset($config[$servicev['config']]['enable']);?>
						<?php $status = rc_is_service_running($servicev['scriptname']);?>
						<td class="<?=$enable?"listlr":"listlrd";?>"><?=htmlspecialchars($servicev['desc']);?>&nbsp;</td>
						<td class="<?=$enable?"listrc":"listrcd";?>">
							<a href="<?=$servicev['link'];?>">
								<?php if ($enable):?>
								<img src="images/status_enabled.png" border="0" alt="" />
								<?php else:?>
								<img src="images/status_disabled.png" border="0" alt="" />
								<?php endif;?>
							</a>
						</td>
						<td class="<?=$enable?"listrc":"listrcd";?>">
							<?php if (0 === $status):?>
							<a title="<?=gtext("Running");?>"><img src="images/status_enabled.png" border="0" alt="" /></a>
							<?php else:?>
							<a title="<?=gtext("Stopped");?>"><img src="images/status_disabled.png" border="0" alt="" /></a>
							<?php endif;?>
						</td>
					</tr>
					<?php endforeach;?>
				</table>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
