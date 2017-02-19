<?php
/*
	vm_xen_hvm.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$a_vms = &array_make_branch($config,'xen','vms','param');
$a_bridge = &array_make_branch($config,'vinterfaces','bridge');
if(empty($a_bridge)):
	$errormsg = gtext('No configured bridge interfaces.')
		. ' '
		. '<a href="' . 'interfaces_bridge.php' . '">'
		. gtext('Please add a bridge interface first.')
		. '</a>';
else:
	array_sort_key($a_bridge, "if");
endif;

function get_vnic_mac_base()
{
	global $config;
	// OUI 24bits + random 20bits + I/F# 4bits
	do {
		$bytes = [0x00, 0x16, 0x3e, // OUI
			mt_rand(0x00, 0x07f),
			mt_rand(0x00, 0x0ff),
			mt_rand(0x00, 0x0f) << 4];
		$mac = implode(':',
			array_map(function ($v) { return sprintf("%02x",$v); },
			$bytes));
		$mac1 = substr($mac, 0, -1)."1";
		$index = array_search_ex($mac1, $config['xen']['vms']['param'], "mac1");
	} while ($index !== false);
	return $mac;
}

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_vms, "uuid")))) {
	$pconfig['uuid'] = $a_vms[$cnid]['uuid'];
	$pconfig['name'] = $a_vms[$cnid]['name'];
	$pconfig['type'] = $a_vms[$cnid]['type'];
	$pconfig['cpus'] = $a_vms[$cnid]['cpus'];
	$pconfig['desc'] = $a_vms[$cnid]['desc'];

	$pconfig['vncdisplay'] = $a_vms[$cnid]['vncdisplay'];
	$pconfig['vncpassword'] = $a_vms[$cnid]['vncpassword'];

	$pconfig['mem'] = $a_vms[$cnid]['mem'];
	$pconfig['vcpus'] = $a_vms[$cnid]['vcpus'];
	$pconfig['nestedhvm'] = isset($a_vms[$cnid]['nestedhvm']);

	$pconfig['kernel'] = $a_vms[$cnid]['kernel'];
	$pconfig['ramdisk'] = $a_vms[$cnid]['ramdisk'];
	$pconfig['bootloader'] = $a_vms[$cnid]['bootloader'];
	$pconfig['bootargs'] = $a_vms[$cnid]['bootargs'];

	$pconfig['nic1'] = $a_vms[$cnid]['nic1'];
	$pconfig['mac1'] = $a_vms[$cnid]['mac1'];
	$pconfig['nic2'] = $a_vms[$cnid]['nic2'];
	$pconfig['mac2'] = $a_vms[$cnid]['mac2'];
	$pconfig['nic3'] = $a_vms[$cnid]['nic3'];
	$pconfig['mac3'] = $a_vms[$cnid]['mac3'];
	$pconfig['nic4'] = $a_vms[$cnid]['nic4'];
	$pconfig['mac4'] = $a_vms[$cnid]['mac4'];

	$pconfig['disk1'] = $a_vms[$cnid]['disk1'];
	$pconfig['disk2'] = $a_vms[$cnid]['disk2'];
	$pconfig['disk3'] = $a_vms[$cnid]['disk3'];
	$pconfig['cdrom'] = $a_vms[$cnid]['cdrom'];

	$pconfig['disk4'] = $a_vms[$cnid]['disk4'];
	$pconfig['disk5'] = $a_vms[$cnid]['disk5'];
	$pconfig['disk6'] = $a_vms[$cnid]['disk6'];
	$pconfig['disk7'] = $a_vms[$cnid]['disk7'];
} else {
	// find next unused display
	$vncdisplay = 0;
	$a_vncdisplay = [];
	foreach($a_vms as $v)
		$a_vncdisplay[] = $v['vncdisplay'];
	while (true === in_array($vncdisplay, $a_vncdisplay))
		$vncdisplay += 1;

	$pconfig['uuid'] = uuid();
	$pconfig['name'] = "";
	$pconfig['type'] = "hvm";
	$pconfig['cpus'] = "";
	$pconfig['desc'] = "";

	$pconfig['vncdisplay'] = $vncdisplay;
	$pconfig['vncpassword'] = "";

	$pconfig['mem'] = "2048";
	$pconfig['vcpus'] = "2";
	$pconfig['nestedhvm'] = false;

	$pconfig['kernel'] = "";
	$pconfig['ramdisk'] = "";
	$pconfig['bootloader'] = "";
	$pconfig['bootargs'] = "";

	$mac = get_vnic_mac_base();
	$pconfig['nic1'] = "bridge0";
	$pconfig['mac1'] = substr($mac, 0, -1)."1";
	$pconfig['nic2'] = "";
	$pconfig['mac2'] = substr($mac, 0, -1)."2";
	$pconfig['nic3'] = "";
	$pconfig['mac3'] = substr($mac, 0, -1)."3";
	$pconfig['nic4'] = "";
	$pconfig['mac4'] = substr($mac, 0, -1)."4";

	$pconfig['disk1'] = "";
	$pconfig['disk2'] = "";
	$pconfig['disk3'] = "";
	$pconfig['cdrom'] = "";

	$pconfig['disk4'] = "";
	$pconfig['disk5'] = "";
	$pconfig['disk6'] = "";
	$pconfig['disk7'] = "";
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);

	$pconfig = $_POST;

	if (isset($_POST['action']) && $_POST['action'] == "Cancel") {
		header("Location: vm_xen.php");
		exit;
	}

	// input validation
	$reqdfields = ['name','mem','vcpus'];
	$reqdfieldsn = [gtext('Name'),gtext('Memory (MiB)'),gtext('VCPUs')];
	$reqdfieldst = ['string','numericint','numericint'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	// VNC display
	if (trim($_POST['vncdisplay']) != "") {
		/*
		$reqdfields = ['vncdisplay','vncpassword'];
		$reqdfieldsn = [gtext('VNC Display'),gtext('VNC Password')];
		$reqdfieldst = ['numericint','string'];
		*/
		$reqdfields = ['vncdisplay'];
		$reqdfieldsn = [gtext('VNC Display')];
		$reqdfieldst = ['numericint'];
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	// mac address
	$reqdfields = ['mac1','mac2','mac3','mac4'];
	$reqdfieldsn = [gtext('MAC Address').' 1', gtext('MAC Address').' 2', gtext('MAC Address').' 3', gtext('MAC Address').' 4'];
	$reqdfieldst = ['macaddr','macaddr','macaddr','macaddr'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	// VM name check
	if (!empty($_POST['name']) && !preg_match("/^[a-zA-Z0-9\-\_\.]+$/", $_POST['name'])) {
		$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Name"));
	}

	// duplicate check
	if (!(isset($uuid) && (FALSE !== $cnid))) {
		$index = array_search_ex($_POST['name'], $config['xen']['vms']['param'], "name");
		if ($index !== false) {
			$input_errors[] = sprintf(gtext("The attribute '%s' already exists."), gtext("Name"));
		}
		$index = array_search_ex($_POST['vncdisplay'], $config['xen']['vms']['param'], "vncdisplay");
		if ($index !== false) {
			$input_errors[] = sprintf(gtext("The attribute '%s' already exists."), gtext("VNC Display"));
		}
	}

	if (empty($input_errors)) {
		$vm = [];
		$vm['uuid'] = $_POST['uuid'];
		$vm['name'] = $_POST['name'];
		$vm['type'] = "hvm";
		$vm['cpus'] = "";
		$vm['desc'] = $_POST['desc'];

		$vm['vncdisplay'] = trim($_POST['vncdisplay']);
		$vm['vncpassword'] = $_POST['vncpassword'];

		$vm['mem'] = $_POST['mem'];
		$vm['vcpus'] = $_POST['vcpus'];
		$vm['nestedhvm'] = isset($_POST['nestedhvm']) ? true : false;

		$vm['kernel'] = "";
		$vm['ramdisk'] = "";
		$vm['bootloader'] = "";
		$vm['bootargs'] = "";

		$vm['nic1'] = $_POST['nic1'];
		$vm['mac1'] = strtolower($_POST['mac1']);
		$vm['nic2'] = $_POST['nic2'];
		$vm['mac2'] = strtolower($_POST['mac2']);
		$vm['nic3'] = $_POST['nic3'];
		$vm['mac3'] = strtolower($_POST['mac3']);
		$vm['nic4'] = $_POST['nic4'];
		$vm['mac4'] = strtolower($_POST['mac4']);

		$vm['disk1'] = $_POST['disk1'];
		$vm['disk2'] = $_POST['disk2'];
		$vm['disk3'] = $_POST['disk3'];
		$vm['cdrom'] = $_POST['cdrom'];

		$vm['disk4'] = $_POST['disk4'];
		$vm['disk5'] = $_POST['disk5'];
		$vm['disk6'] = $_POST['disk6'];
		$vm['disk7'] = $_POST['disk7'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_vms[$cnid] = $vm;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_vms[] = $vm;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("vm_xen_hvm", $mode, $vm['uuid']);
		write_config();

		header("Location: vm_xen.php");
		exit;
	}
}
$pgtitle = [gtext('Virtualization'),gtext('Xen'),gtext('HVM Guest'),isset($uuid) ? gtext("Edit") : gtext("Add")];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
}); 
$(document).ready(function(){
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="vm_xen_hvm.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php
				if(!empty($errormsg)):
					print_error_box($errormsg);
				endif;
				if(!empty($input_errors)):
					print_input_errors($input_errors);
				endif;
				if(!empty($savemsg)):
					print_info_box($savemsg);
				endif;
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline(gtext("Settings"));
					html_inputbox("name", gtext("Name"), $pconfig['name'], "", true, 20);
					html_inputbox("mem", gtext("Memory (MiB)"), $pconfig['mem'], "", true, 10);
					html_inputbox("vcpus", gtext("VCPUs"), $pconfig['vcpus'], "", true, 10);
					html_checkbox("nestedhvm", gtext("Nested HVM"), !empty($pconfig['nestedhvm']) ? true : false, gtext("Enable nested virtualization"), "", false);
					html_inputbox("desc", gtext("Description"), $pconfig['desc'], gtext("You may enter a description here for your reference."), false, 40);
					html_separator();
					html_titleline(gtext("Display"));
					html_inputbox("vncdisplay", gtext("VNC Display"), $pconfig['vncdisplay'], gtext("TCP port is 5900+N, where N is VNC display number."), true, 10);
					html_passwordbox("vncpassword", gtext("VNC Password"), $pconfig['vncpassword'], "", false, 15);
					html_separator();
					html_titleline(gtext("Network"));
					$a_bridgeif = ['none' => gtext('None')];
					foreach($a_bridge as $bridge):
						$a_bridgeif[$bridge['if']] = htmlspecialchars("{$bridge['if']}".(!empty($bridge['desc']) ? " ({$bridge['desc']})" : ""));
					endforeach;
					html_combobox("nic1", gtext("Network Adapter")." 1", $pconfig['nic1'], $a_bridgeif, "", true);
					html_inputbox("mac1", gtext("MAC Address")." 1", $pconfig['mac1'], "", true, 20);
					html_separator();
					html_titleline(gtext("Storage"));
					html_filechooser("disk1", gtext("Hard Disk")." 1", $pconfig['disk1'], sprintf(gtext("File path (e.g. %s) or ZFS volume (e.g. %s) used as Hard disk image."), "/mnt/sharename/disk.img", "/dev/zvol/tank/volume"), $g['media_path'], true);
					html_filechooser("cdrom", gtext("CD/DVD drive"), $pconfig['cdrom'], sprintf(gtext("ISO file path (e.g. %s) used as CD/DVD drive."), "/mnt/sharename/image.iso"), $g['media_path'], false);
					html_filechooser("disk2", gtext("Hard Disk")." 2", $pconfig['disk2'], "", $g['media_path'], false);
					html_filechooser("disk3", gtext("Hard Disk")." 3", $pconfig['disk3'], "", $g['media_path'], false);
					html_separator();
					html_titleline(gtext("Additional Network"));
					html_combobox("nic2", gtext("Network Adapter")." 2", $pconfig['nic2'], $a_bridgeif, "", false);
					html_inputbox("mac2", gtext("MAC Address")." 2", $pconfig['mac2'], "", false, 20);
					html_combobox("nic3", gtext("Network Adapter")." 3", $pconfig['nic3'], $a_bridgeif, "", false);
					html_inputbox("mac3", gtext("MAC Address")." 3", $pconfig['mac3'], "", false, 20);
					html_combobox("nic4", gtext("Network Adapter")." 4", $pconfig['nic4'], $a_bridgeif, "", false);
					html_inputbox("mac4", gtext("MAC Address")." 4", $pconfig['mac4'], "", false, 20);
					html_separator();
					html_titleline(gtext("Additional Storage"));
					html_filechooser("disk4", gtext("Hard Disk")." 4", $pconfig['disk4'], "", $g['media_path'], false);
					html_filechooser("disk5", gtext("Hard Disk")." 5", $pconfig['disk5'], "", $g['media_path'], false);
					html_filechooser("disk6", gtext("Hard Disk")." 6", $pconfig['disk6'], "", $g['media_path'], false);
					html_filechooser("disk7", gtext("Hard Disk")." 7", $pconfig['disk7'], "", $g['media_path'], false);
					?>
				</table>
				<div id="submit">
					<button type="submit" class="formbtn" name="action" value="Submit"><?=((isset($uuid) && (FALSE !== $cnid))) ? gtext("Save") : gtext("Add");?></button>
					<button type="submit" class="formbtn" name="action" value="Cancel"><?=gtext("Cancel");?></button>
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
