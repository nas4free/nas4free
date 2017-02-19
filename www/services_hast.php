<?php
/*
	services_hast.php

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

array_make_branch($config,'hast','auxparam');
//	array_make_branch($config,'hast','hastresource');
array_make_branch($config,'vinterfaces','carp');

$pconfig['enable'] = isset($config['hast']['enable']);
//$pconfig['role'] = $config['hast']['role'];
$pconfig['auxparam'] = implode("\n", $config['hast']['auxparam']);

$nodeid = @exec("/sbin/sysctl -q -n kern.hostuuid");
if (empty($nodeid))
	$nodeid = "unknown";
$nodename = system_get_hostname();
if (empty($nodename))
	$nodename = "unknown";

$a_carp = &$config['vinterfaces']['carp'];
array_sort_key($a_carp, "if");

if (!sizeof($a_carp)) {
	$errormsg = gtext('No configured CARP interfaces.')
		. ' '
		. '<a href="' . 'interfaces_carp.php' . '">'
		. gtext('Please add a new CARP interface first')
		. '</a>.';
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);

	$pconfig = $_POST;

	$preempt = @exec("/sbin/sysctl -q -n net.inet.carp.preempt");
	if (isset($_POST['switch_backup']) && $_POST['switch_backup']) {
		// down all carp
		foreach ($a_carp as $carp) {
			//system("/sbin/ifconfig {$carp['if']} down");
			mwexec("/etc/rc.d/netif stop {$carp['if']}");
			if ($carp['advskew'] <= 1) {
				system("/sbin/ifconfig {$carp['if']} vhid {$carp['vhid']} state backup advskew 240");
			} else {
				system("/sbin/ifconfig {$carp['if']} vhid {$carp['vhid']} state backup");
			}
			//system("/sbin/ifconfig {$carp['if']} up");
			mwexec("/etc/rc.d/netif start {$carp['if']}");
		}
		// waits for the primary disk to disappear
		$retry = 60;
		while ($retry > 0) {
			$result = mwexec("pgrep -lf 'hastd: .* \(primary\)' > /dev/null 2>&1");
			if ($result != 0)
				break;
			$retry--;
			sleep(1);
		}
		if ($retry <= 0) {
			write_log("error: still hasted primary exists!");
		}
		// up and set backup all carp
		if ($preempt == 0 || (isset($a_carp[0]) && $a_carp[0]['advskew'] > 1)) {
			foreach ($a_carp as $carp) {
				//system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state backup");
			}
		}
		header("Location: services_hast.php");
		exit;
	}
	if (isset($_POST['switch_master']) && $_POST['switch_master']) {
		// up and set master all carp
		$role = get_hast_role();
		foreach ($a_carp as $carp) {
			$state = @exec("/sbin/ifconfig {$carp['if']} | grep  'carp:' | awk '{ print tolower($2) }'");
			if ($carp['advskew'] <= 1) {
				system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state master advskew {$carp['advskew']}");
			} else {
				system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state master");
			}
			// if already master, use linkup action
			if ($state == "master" && $role != "primary") {
				$action = $carp['linkup'];
				$result = mwexec($action);
			}
		}
		// waits for the secondary disk to disappear
		$retry = 60;
		while ($retry > 0) {
			$result = mwexec("pgrep -lf 'hastd: .* \(secondary\)' > /dev/null 2>&1");
			if ($result != 0)
				break;
			$retry--;
			sleep(1);
		}
		if ($retry <= 0) {
			write_log("error: still hasted secondary exists!");
		}
		header("Location: services_hast.php");
		exit;
	}

	// Input validation.
/*
	$reqdfields = ['role'];
	$reqdfieldsn = [gtext('HAST role')];
	$reqdfieldst = ['string'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
*/

	if (empty($input_errors)) {
		$old_enable = isset($config['hast']['enable']) ? true : false;
		$config['hast']['enable'] = isset($_POST['enable']) ? true : false;
		//$config['hast']['role'] = $_POST['role'];

		unset($config['hast']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['hast']['auxparam'][] = $auxparam;
		}

		$retval = 0;

		if ($old_enable == false && $config['hast']['enable'] == true) {
			// disable services
			array_make_branch($config,'samba');
			$config['samba']['enable'] = false;
			array_make_branch($config,'ftpd');
			$config['ftpd']['enable'] = false;
			array_make_branch($config,'tftpd');
			$config['tftpd']['enable'] = false;
			//	array_make_branch($config,'sshd');
			//	$config['sshd']['enable'] = false;
			array_make_branch($config,'nfsd');
			$config['nfsd']['enable'] = false;
			array_make_branch($config,'afp');
			$config['afp']['enable'] = false;
			array_make_branch($config,'rsyncd');
			$config['rsyncd']['enable'] = false;
			array_make_branch($config,'unison');
			$config['unison']['enable'] = false;
			array_make_branch($config,'iscsitarget');
			$config['iscsitarget']['enable'] = false;
			array_make_branch($config,'upnp');
			$config['upnp']['enable'] = false;
			array_make_branch($config,'daap');
			$config['daap']['enable'] = false;
			array_make_branch($config,'dynamicdns');
			$config['dynamicdns']['enable'] = false;
			array_make_branch($config,'snmpd');
			$config['snmpd']['enable'] = false;
			array_make_branch($config,'ups');
			$config['ups']['enable'] = false;
			array_make_branch($config,'websrv');
			$config['websrv']['enable'] = false;
			array_make_branch($config,'bittorrent');
			$config['bittorrent']['enable'] = false;
			array_make_branch($config,'lcdproc');
			$config['lcdproc']['enable'] = false;

			// update config
			write_config();

			// stop services
			config_lock();
			$retval |= rc_update_service("samba");
			$retval |= rc_update_service("proftpd");
			$retval |= rc_update_service("tftpd");
			//$retval |= rc_update_service("sshd");
			$retval |= rc_update_service("rpcbind");
			$retval |= rc_update_service("mountd");
			$retval |= rc_update_service("nfsd");
			$retval |= rc_update_service("statd");
			$retval |= rc_update_service("lockd");
			$retval |= rc_update_service("netatalk");
			$retval |= rc_update_service("rsyncd");
			$retval |= rc_update_service("unison");
			$retval |= rc_update_service("iscsi_target");
			$retval |= rc_update_service("fuppes");
			$retval |= rc_update_service("mt-daapd");
			$retval |= rc_update_service("inadyn");
			$retval |= rc_update_service("bsnmpd");
			$retval |= rc_update_service("nut");
			$retval |= rc_update_service("nut_upslog");
			$retval |= rc_update_service("nut_upsmon");
			$retval |= rc_exec_service("websrv_htpasswd");
			$retval |= rc_update_service("websrv");
			$retval |= rc_update_service("transmission");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		} else {
			write_config();
		}

		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("hastd");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}
$pgtitle = [gtext('Services'),gtext('HAST')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var val = !($('#enable').prop('checked') || enable_change);
		$('#auxparam').prop('disabled', val);
	}
	$('#enable').click(function(){
		enable_change(false);
	});
	$('input:submit').click(function(){
		enable_change(true);
	});
	enable_change(false);
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="services_hast.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Settings");?></span></a></li>
				<li class="tabinact"><a href="services_hast_resource.php"><span><?=gtext("Resources");?></span></a></li>
				<li class="tabinact"><a href="services_hast_info.php"><span><?=gtext("Information");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="services_hast.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($errormsg)) print_error_box($errormsg);?>
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("HAST (Highly Available Storage)"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "");?>
					<?php echo html_text("nodeid", gtext("Node ID"), htmlspecialchars($nodeid)); ?>
					<?php echo html_text("nodename", gtext("Node Name"), htmlspecialchars($nodename)); ?>
					<?php
					$a_vipaddrs = [];
					foreach ($a_carp as $carp) {
						$ifinfo = get_carp_info($carp['if']);
						//$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
						$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
					}
					?>
					<?php echo html_text("vipaddr", gtext("Virtual IP Address"), (!empty($a_vipaddrs) ? htmlspecialchars(join(', ', $a_vipaddrs)) : sprintf("<span class='red'>%s</span>", gtext("No configured CARP interfaces.")))); ?>
					<?php //html_combobox("role", gtext("HAST role"), $pconfig['role'], ['primary' => gtext('Primary'),'secondary' => gtext('Secondary')], "", true);?>
					<tr id="control_btn">
						<td colspan="2">
							<input id="switch_backup" name="switch_backup" type="submit" class="formbtn" value="<?php echo gtext("Switch VIP to BACKUP"); ?>" />
							<?php if (isset($a_carp[0]) && $a_carp[0]['advskew'] <= 1) { ?>
							&nbsp;<input id="switch_master" name="switch_master" type="submit" class="formbtn" value="<?php echo gtext("Switch VIP to MASTER"); ?>" />
							<?php } ?>
						</td>
					</tr>
					<?php
					html_separator();
					html_titleline(gtext("Advanced Settings"));
					
					$helpinghand = '<a href="'
					. 'http://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/disks-hast.html'
					. '" target="_blank">'
					. gtext('Please check the documentation')
					. '</a>.';
					html_textarea("auxparam", gtext("Additional Parameters"), $pconfig['auxparam'], sprintf(gtext("These parameters are added to %s."), "hast.conf") . " " . $helpinghand, false, 65, 5, false, false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" />
				</div>
				<div id="remarks">
					<?php html_remark("note", gtext('Note'), sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li><li>%s</li></ul></div>", gtext("When HAST is enabled, the local devices, the local services and the additional packages which do not support HAST volume cannot be used."), gtext("The HAST volumes can not be accessed until HAST node becomes Primary."), gtext("Dynamic IP (DHCP) can not be used for HAST resources.")));?>
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
