<?php
/*
	services_hast.php
	
	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gettext("Services"), gettext("HAST"));

if (!isset($config['hast']['auxparam']) || !is_array($config['hast']['auxparam']))
	$config['hast']['auxparam'] = array();
//if (!isset($config['hast']['hastresource']) || !is_array($config['hast']['hastresource']))
//	$config['hast']['hastresource'] = array();
if (!isset($config['vinterfaces']['carp']) || !is_array($config['vinterfaces']['carp']))
	$config['vinterfaces']['carp'] = array();

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
	$errormsg = sprintf(gettext("No configured CARP interfaces. Please add new <a href='%s'>CARP interface</a> first."), "interfaces_carp.php");
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);

	$pconfig = $_POST;

	// Input validation.
/*
	$reqdfields = explode(" ", "role");
	$reqdfieldsn = array(gettext("HAST role"));
	$reqdfieldst = explode(" ", "string");

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
			$config['samba']['enable'] = false;
			$config['ftpd']['enable'] = false;
			$config['tftpd']['enable'] = false;
			//$config['sshd']['enable'] = false;
			$config['nfsd']['enable'] = false;
			$config['afp']['enable'] = false;
			$config['rsyncd']['enable'] = false;
			$config['unison']['enable'] = false;
			$config['iscsitarget']['enable'] = false;
			$config['upnp']['enable'] = false;
			$config['daap']['enable'] = false;
			$config['dynamicdns']['enable'] = false;
			$config['snmpd']['enable'] = false;
			$config['ups']['enable'] = false;
			$config['websrv']['enable'] = false;
			$config['bittorrent']['enable'] = false;
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
			$retval |= rc_update_service("afpd");
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
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var val = !($('#enable').attr('checked') || enable_change);
		$('#auxparam').attr('disabled', val);
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
	<li class="tabact"><a href="services_hast.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Settings");?></span></a></li>
	<li class="tabinact"><a href="services_hast_resource.php"><span><?=gettext("Resources");?></span></a></li>
	<li class="tabinact"><a href="services_hast_info.php"><span><?=gettext("Information");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <form action="services_hast.php" method="post" name="iform" id="iform">
	<?php if (!empty($errormsg)) print_error_box($errormsg);?>
	<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
	<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<?php html_titleline_checkbox("enable", gettext("HAST (Highly Available Storage)"), !empty($pconfig['enable']) ? true : false, gettext("Enable"), "");?>
	<?php echo html_text("nodeid", gettext("Node ID"), htmlspecialchars($nodeid)); ?>
	<?php echo html_text("nodename", gettext("Node Name"), htmlspecialchars($nodename)); ?>
	<?php
		$a_vipaddrs = array();
		foreach ($a_carp as $carp) {
			$ifinfo = get_carp_info($carp['if']);
			//$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
			$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
		}
	?>
	<?php echo html_text("vipaddr", gettext("Virtual IP address"), (!empty($a_vipaddrs) ? htmlspecialchars(join(', ', $a_vipaddrs)) : sprintf("<span class='red'>%s</span>", htmlspecialchars(gettext("No configured CARP interfaces."))))); ?>
	<?php //html_combobox("role", gettext("HAST role"), $pconfig['role'], array("primary" => gettext("Primary"), "secondary" => gettext("Secondary")), "", true);?>
	<?php html_separator();?>
	<?php html_titleline(gettext("Advanced settings"));?>
	<?php html_textarea("auxparam", gettext("Auxiliary parameters"), $pconfig['auxparam'], sprintf(gettext("These parameters are added to %s."), "hast.conf") . " " . sprintf(gettext("Please check the <a href='%s' target='_blank'>documentation</a>."), "http://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/disks-hast.html"), false, 65, 5, false, false);?>
	</table>
	<div id="submit">
	  <input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save and Restart");?>" />
	</div>
	<div id="remarks">
	  <?php html_remark("note", gettext("Note"), sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li><li>%s</li></ul></div>", gettext("When HAST is enabled, the local devices, the local services and the additional packages which do not support HAST volume cannot be used."), gettext("The HAST volumes can not be accessed until HAST node becomes Primary."), gettext("Dynamic IP (DHCP) can not be used for HAST resources.")));?>
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
