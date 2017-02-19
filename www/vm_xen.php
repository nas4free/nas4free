<?php
/*
	vm_xen.php

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

$pgtitle = array(gtext("VM"), gtext("Xen"));

if (!isset($config['xen']['vms']['param']) || !is_array($config['xen']['vms']['param']))
	$config['xen']['vms']['param'] = array();

if (!isset($config['vinterfaces']['bridge']) || !is_array($config['vinterfaces']['bridge']))
	$config['vinterfaces']['bridge'] = array();

$a_vms = &$config['xen']['vms']['param'];

$a_bridge = &$config['vinterfaces']['bridge'];
array_sort_key($a_bridge, "if");

if (!sizeof($a_bridge)) {
	$errormsg = gtext('No configured bridge interfaces.')
		. ' '
		. '<a href="' . 'interfaces_bridge.php' . '">'
		. gtext('Please add a bridge interface first.')
		. '</a>';
}

// js button handler
if (is_ajax()) {
	$result = array();
	$action = $_GET['action'];
	$uuid = $_GET['uuid'];

	$result['uuid'] = $uuid;
	if ($action == "vmstart") {
		$file = create_vm_config($uuid);
		$result['file'] = $file;
		unset($rawdata, $ret);
		mwexec2("/usr/local/sbin/xl create -q -f $file 2>&1", $rawdata, $ret);
		$result['raw'] = $rawdata;
		$result['ret'] = $ret;
		//unlink($file);
		unset($rawdata, $ret);
	} else if ($action == "vmshutdown") {
		$domid = get_vm_domid($uuid);
		$result['domid'] = $domid;
		unset($rawdata, $ret);
		mwexec2("/usr/local/sbin/xl shutdown $domid 2>&1", $rawdata, $ret);
		$result['raw'] = $rawdata;
		$result['ret'] = $ret;
		$to = 100;
		if ($ret == 0) {
			do {
				sleep(1);
				$to--;
				$domid = get_vm_domid($uuid);
			} while ($domid !== false && $to > 0);
		}
		$result['to'] = $to;
		unset($rawdata, $ret);
	} else if ($action == "vmreboot") {
		$domid = get_vm_domid($uuid);
		$result['domid'] = $domid;
		unset($rawdata, $ret);
		mwexec2("/usr/local/sbin/xl reboot $domid 2>&1", $rawdata, $ret);
		$result['raw'] = $rawdata;
		$result['ret'] = $ret;
		$oldid = $domid;
		$to = 100;
		if ($ret == 0) {
			do {
				sleep(1);
				$to--;
				$domid = get_vm_domid($uuid);
			} while ($domid == $oldid && $to > 0);
			do {
				sleep(1);
				$to--;
				$domid = get_vm_domid($uuid);
			} while ($domid === false && $to > 0);
		}
		$result['to'] = $to;
		unset($rawdata, $ret);
	} else if ($action == "vmstop") {
		$domid = get_vm_domid($uuid);
		$result['domid'] = $domid;
		unset($rawdata, $ret);
		mwexec2("/usr/local/sbin/xl destroy $domid 2>&1", $rawdata, $ret);
		$result['raw'] = $rawdata;
		$result['ret'] = $ret;
		$to = 100;
		if ($ret == 0) {
			do {
				sleep(1);
				$to--;
				$domid = get_vm_domid($uuid);
			} while ($domid !== false && $to > 0);
		}
		$result['to'] = $to;
		unset($rawdata, $ret);
	}
	render_ajax($result);
}

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("vm_xen_pv", "vm_xen_pv_process_updatenotification");
			$retval |= updatenotify_process("vm_xen_hvm", "vm_xen_hvm_process_updatenotification");
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("vm_xen_pv");
			updatenotify_delete("vm_xen_hvm");
		}
		header("Location: vm_xen.php");
		exit;
	}
}

if (isset($_GET['act']) && $_GET['act'] === "del") {
	$index = array_search_ex($_GET['uuid'], $config['xen']['vms']['param'], "uuid");
	if ($index !== false) {
		$type = $config['xen']['vms']['param'][$index]['type'];
		updatenotify_set(($type == "pv") ? "vm_xen_pv" : "vm_xen_hvm", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
		header("Location: vm_xen.php");
		exit;
	}
}

function vm_xen_pv_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = array_search_ex($data, $config['xen']['vms']['param'], "uuid");
			if ($cnid !== false) {
				unset($config['xen']['vms']['param'][$cnid]);
				write_config();
			}
			break;
	}
	return $retval;
}

function vm_xen_hvm_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;
	switch ($mode) {
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = array_search_ex($data, $config['xen']['vms']['param'], "uuid");
			if ($cnid !== false) {
				unset($config['xen']['vms']['param'][$cnid]);
				write_config();
			}
			break;
	}
	return $retval;
}

function get_vm_domid($vmuuid) {
	unset($rawdata);
	mwexec2("/usr/local/sbin/xl vm-list", $rawdata);
	array_shift($rawdata);
	// XXX reverse bytes
	preg_match('/^(..)(..)(..)(..)-(..)(..)-(..)(..)-(.*)$/', $vmuuid, $m);
	$xuuid = $m[4].$m[3].$m[2].$m[1].'-'.$m[6].$m[5].'-'.$m[8].$m[7].'-'.$m[9];
	foreach ($rawdata as $line) {
		if (preg_match("/^(\S+)\s+(\S+)\s+(.+)$/", $line, $match)) {
			$uuid = $match[1];
			$domid = $match[2];
			$name = $match[3];
			if (strcmp(strtolower($vmuuid), strtolower($uuid)) == 0)
				return $domid;
			if (strcmp(strtolower($xuuid), strtolower($uuid)) == 0)
				return $domid;
		}
	}
	return false;
}

function create_vm_config($vmuuid) {
	global $config;

	$index = array_search_ex($vmuuid, $config['xen']['vms']['param'], "uuid");
	if ($index === false)
		return false;
	$vm = $config['xen']['vms']['param'][$index];
	$type = $vm['type'];

	if (!empty($vm['name']))
		$cfgname = "/usr/local/etc/xen/vm-{$vm['name']}.cfg";
	else
		$cfgname = tempnam("/var/tmp", "cfg");
	if (file_exists($cfgname))
		unlink($cfgname);
	$fp = fopen($cfgname, "w");
	if ($vm['type'] == "hvm") {
		fprintf($fp, "builder = \"hvm\"\n");
	}
	fprintf($fp, "name = \"%s\"\n", $vm['name']);
	fprintf($fp, "uuid = \"%s\"\n", $vm['uuid']);
	fprintf($fp, "memory = %s\n", $vm['mem']);
	fprintf($fp, "vcpus = %s\n", $vm['vcpus']);

	if ($vm['type'] == "pv") {
		// Kernel or Loader
		if (!empty($vm['kernel'])) {
			fprintf($fp, "kernel = \"%s\"\n", $vm['kernel']);
			fprintf($fp, "ramdisk = \"%s\"\n", $vm['ramdisk']);
		} else {
			fprintf($fp, "bootloader = \"%s\"\n", $vm['bootloader']);
			if (!empty($vm['bootargs']))
				fprintf($fp, "bootargs = \"%s\"\n", $vm['bootargs']);
		}
	}

	// VIF
	$vif = array();
	$vifmodel = "";
	if ($vm['type'] == "hvm") {
		if (isset($vm['nestedhvm'])) {
			//$vifmodel = ",model=rtl8139";
			$vifmodel = ",model=e1000";
		}
	}
	if ($vm['nic1'] != "none")
		$vif[] = sprintf("'mac=%s,bridge=%s${vifmodel}'", $vm['mac1'], $vm['nic1']);
	if ($vm['nic2'] != "none")
		$vif[] = sprintf("'mac=%s,bridge=%s${vifmodel}'", $vm['mac2'], $vm['nic2']);
	if ($vm['nic3'] != "none")
		$vif[] = sprintf("'mac=%s,bridge=%s${vifmodel}'", $vm['mac3'], $vm['nic3']);
	if ($vm['nic4'] != "none")
		$vif[] = sprintf("'mac=%s,bridge=%s${vifmodel}'", $vm['mac4'], $vm['nic4']);
	fprintf($fp, "vif = [ %s ]\n", implode(',', $vif));

	// DISK
	$disk = array();
	if ($vm['type'] == "pv") {
		if (!empty($vm['disk1']))
			$disk[] = sprintf("'%s,raw,xvda,w'", $vm['disk1']);
		if (!empty($vm['disk2']))
			$disk[] = sprintf("'%s,raw,xvdb,w'", $vm['disk2']);
		if (!empty($vm['cdrom']))
			$disk[] = sprintf("'%s,raw,xvdc:cdrom,r'", $vm['cdrom']);
		if (!empty($vm['disk3']))
			$disk[] = sprintf("'%s,raw,xvdd,w'", $vm['disk3']);

		if (!empty($vm['disk4']))
			$disk[] = sprintf("'%s,raw,xvde,w'", $vm['disk4']);
		if (!empty($vm['disk5']))
			$disk[] = sprintf("'%s,raw,xvdf,w'", $vm['disk5']);
		if (!empty($vm['disk6']))
			$disk[] = sprintf("'%s,raw,xvdg,w'", $vm['disk6']);
		if (!empty($vm['disk7']))
			$disk[] = sprintf("'%s,raw,xvdh,w'", $vm['disk7']);
	} else if ($vm['type'] == "hvm") {
		if (!empty($vm['disk1']))
			$disk[] = sprintf("'%s,raw,hda,rw'", $vm['disk1']);
		if (!empty($vm['disk2']))
			$disk[] = sprintf("'%s,raw,hdb,rw'", $vm['disk2']);
		if (!empty($vm['cdrom']))
			$disk[] = sprintf("'%s,raw,hdc:cdrom,r'", $vm['cdrom']);
		if (!empty($vm['disk3']))
			$disk[] = sprintf("'%s,raw,hdd,rw'", $vm['disk3']);

		if (!empty($vm['disk4']))
			$disk[] = sprintf("'%s,raw,sda,rw'", $vm['disk4']);
		if (!empty($vm['disk5']))
			$disk[] = sprintf("'%s,raw,sdb,rw'", $vm['disk5']);
		if (!empty($vm['disk6']))
			$disk[] = sprintf("'%s,raw,sdc,rw'", $vm['disk6']);
		if (!empty($vm['disk7']))
			$disk[] = sprintf("'%s,raw,sdd,rw'", $vm['disk7']);
	}
	fprintf($fp, "disk = [ %s ]\n", implode(',', $disk));

	$vncip = "0.0.0.0";
	if ($vm['type'] == "pv") {
		// VNC
		if (!empty($vm['vncpassword'])) {
			fprintf($fp, "vfb = [ 'type=vnc,vnclisten=${vncip},vncdisplay=%d,vncpasswd=%s' ]\n", $vm['vncdisplay'], $vm['vncpassword']);
		} else {
			fprintf($fp, "vfb = [ 'type=vnc,vnclisten=${vncip},vncdisplay=%d' ]\n", $vm['vncdisplay']);
		}
	} else if ($vm['type'] == "hvm") {
		// VNC
		$vnc = 1;
		if ($vnc == 0) {
			fprintf($fp, "vnc = 0\n");
		} else {
			fprintf($fp, "vnc = 1\n");
			fprintf($fp, "vnclisten = \"${vncip}\"\n");
			fprintf($fp, "vncconsole = 0\n");
			fprintf($fp, "vncunused = 0\n");
			fprintf($fp, "vncdisplay = %d\n", $vm['vncdisplay']);
			if (!empty($vm['vncpassword'])) {
				fprintf($fp, "vncpasswd = \"%s\"\n", $vm['vncpassword']);
			}
		}
		// Serial
		fprintf($fp, "serial = \"pty\"\n");
		// USB pointer (absolute coordinates)
		fprintf($fp, "usb = 1\n");
		fprintf($fp, "usbdevice = \"tablet\"\n");
		// ACPI
		fprintf($fp, "acpi = 1\n");
		// Nested Virtualization
		if (isset($vm['nestedhvm'])) {
			fprintf($fp, "hap = 1\n");
			fprintf($fp, "nestedhvm = 1\n");
			fprintf($fp, "cpuid = ['0x1:ecx=0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx']\n");
		}
	}

	fclose($fp);
	return $cfgname;
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	$('#CreatePV').click(function(){ location.href='vm_xen_pv.php'; });
	$('#CreateHVM').click(function(){ location.href='vm_xen_hvm.php'; });
	$('.vmstart').click(function(){
		$(this).prop("disabled", true);
		var value = $(this).val();
		var target = this;
		$.ajax({
			url: 'vm_xen.php',
			data: { action: 'vmstart', uuid: value },
			dataType: 'json',
			type: 'GET'
		}).done(function(data){
			$(target).prop("disabled", false);
			location.reload(true);
		}).fail(function(){
			$(target).prop("disabled", false);
			alert("vmstart failed");
		});
	});
	$('.vmshutdown').click(function(){
		$(this).prop("disabled", true);
		$(this).siblings('button').prop("disabled", true);
		var value = $(this).val();
		var target = this;
		$.ajax({
			url: 'vm_xen.php',
			data: { action: 'vmshutdown', uuid: value },
			dataType: 'json',
			type: 'GET'
		}).done(function(data){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			if (data.ret != 0 && data.raw) {
				alert(data.raw);
			} else if (data.to != 0) {
				location.reload(true);
			}
			// timeout
		}).fail(function(){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			alert("vmshutdown failed");
		});
	});
	$('.vmreboot').click(function(){
		$(this).prop("disabled", true);
		$(this).siblings('button').prop("disabled", true);
		var value = $(this).val();
		var target = this;
		$.ajax({
			url: 'vm_xen.php',
			data: { action: 'vmreboot', uuid: value },
			dataType: 'json',
			type: 'GET'
		}).done(function(data){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			if (data.ret != 0 && data.raw) {
				alert(data.raw);
			} else if (data.to != 0) {
				location.reload(true);
			}
			// timeout
		}).fail(function(){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			alert("vmreboot failed");
		});
	});
	$('.vmstop').click(function(){
		$(this).prop("disabled", true);
		$(this).siblings('button').prop("disabled", true);
		var value = $(this).val();
		var target = this;
		$.ajax({
			url: 'vm_xen.php',
			data: { action: 'vmstop', uuid: value },
			dataType: 'json',
			type: 'GET'
		}).done(function(data){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			if (data.ret != 0 && data.raw) {
				alert(data.raw);
			} else if (data.to != 0) {
				location.reload(true);
			}
			// timeout
		}).fail(function(){
			$(target).prop("disabled", false);
			$(target).siblings('button').prop("disabled", false);
			alert("vmstop failed");
		});
	});
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabcont">
      <form action="vm_xen.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<?php if (!empty($errormsg)) print_error_box($errormsg);?>
	<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
	<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	<?php if (updatenotify_exists("vm_xen_pv") || updatenotify_exists("vm_xen_hvm")) print_config_change_box();?>
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<?php html_titleline(gtext("Xen Virtual Machine"), 7);?>
	<tr>
	  <td width="20%" class="listhdrlr"><?=gtext("Name");?></td>
	  <td width="10%" class="listhdrr"><?=gtext("Mem");?></td>
	  <td width="10%" class="listhdrr"><?=gtext("VCPUs");?></td>
	  <td width="10%" class="listhdrr"><?=gtext("VM Type");?></td>
	  <td width="10%" class="listhdrr"><?=gtext("VNC Display");?></td>
	  <td width="10%" class="listhdrr"><?=gtext("DomID");?></td>
	  <td width="25%" class="listhdrr"><?=gtext("Command");?></td>
	  <td width="5%" class="list"></td>
	</tr>
	<?php foreach ($a_vms as $vmv):?>
	<tr>
	  <td class="listlr"><?=htmlspecialchars($vmv['name']);?>&nbsp;</td>
	  <td class="listr"><?=htmlspecialchars($vmv['mem']);?>&nbsp;</td>
	  <td class="listr"><?=htmlspecialchars($vmv['vcpus']);?>&nbsp;</td>
	  <td class="listr"><?=htmlspecialchars($vmv['type']);?>&nbsp;</td>
	  <td class="listr"><?=htmlspecialchars($vmv['vncdisplay']);?>&nbsp;</td>
	  <?php $domid = get_vm_domid($vmv['uuid']);?>
	  <td class="listr"><?=htmlspecialchars(($domid !== false) ? "$domid" : "-")?></td>
	  <td class="listr"><?php
		if ($domid === false) {
			// not running uuid
			echo "<button type=\"button\" value=\"{$vmv['uuid']}\" class=\"formbtn vmstart\">".gtext("Start")."</button>";
			echo "\n";
		} else {
			// running uuid
			echo "<button type=\"button\" value=\"{$vmv['uuid']}\" class=\"formbtn vmshutdown\">".gtext("Shutdown")."</button> ";
			echo "<button type=\"button\" value=\"{$vmv['uuid']}\" class=\"formbtn vmreboot\">".gtext("Reboot")."</button> ";
			echo "<button type=\"button\" value=\"{$vmv['uuid']}\" class=\"formbtn vmstop\">".gtext("Stop")."</button> ";
			echo "\n";
		}
	  ?></td>
	  <?php $notificationmode = updatenotify_get_mode(($vmv['type'] == "pv") ? "vm_xen_pv" : "vm_xen_hvm", $vmv['uuid']);?>
	  <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
	  <td valign="middle" nowrap="nowrap" class="list">
	    <a href="<?=($vmv['type'] == "pv") ? "vm_xen_pv.php" : "vm_xen_hvm.php";?>?uuid=<?=$vmv['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit VM");?>" border="0" alt="<?=gtext("Edit VM");?>" /></a>
	    <a href="vm_xen.php?act=del&amp;uuid=<?=$vmv['uuid'];?>" onclick="return confirm('<?=gtext("Do you really want to delete this VM?");?>')"><img src="images/delete.png" title="<?=gtext("Delete VM");?>" border="0" alt="<?=gtext("Delete VM");?>" /></a>
	  </td>
	  <?php else:?>
	  <td valign="middle" nowrap="nowrap" class="list">
	    <img src="images/delete.png" border="0" alt="" />
	  </td>
	  <?php endif;?>
	</tr>
	<?php endforeach;?>
	<?php html_separator();?>
	</table>
	<div id="vm_new">
	  <button type="button" id="CreatePV" class="formbtn"><?=gtext("Create PV Guest");?></button>
	  <button type="button" id="CreateHVM" class="formbtn"><?=gtext("Create HVM Guest");?></button>
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
