<?php
/*
	disks_manage_edit.php

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

$pgtitle = [gtext('Disks'),gtext('Management'),gtext('Disk'), isset($uuid) ? gtext('Edit') : gtext('Add')];

// Get all physical disks including CDROM.
$a_phy_disk = array_merge((array)get_physical_disks_list(), (array)get_cdrom_list());
$a_disk = &array_make_branch($config,'disks','disk');
if(empty($a_disk)):
else:
	array_sort_key($a_disk,'name');
endif;

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_disk, "uuid")))) {
	$pconfig['uuid'] = $a_disk[$cnid]['uuid'];
	$pconfig['name'] = $a_disk[$cnid]['name'];
	$pconfig['id'] = $a_disk[$cnid]['id'];
	$pconfig['devicespecialfile'] = $a_disk[$cnid]['devicespecialfile'];
	$pconfig['model'] = $a_disk[$cnid]['model'];
	$pconfig['desc'] = $a_disk[$cnid]['desc'];
	$pconfig['serial'] = $a_disk[$cnid]['serial'];
	$pconfig['harddiskstandby'] = $a_disk[$cnid]['harddiskstandby'];
	$pconfig['acoustic'] = $a_disk[$cnid]['acoustic'];
	$pconfig['apm'] = $a_disk[$cnid]['apm'];
	$pconfig['transfermode'] = $a_disk[$cnid]['transfermode'];
	$pconfig['fstype'] = $a_disk[$cnid]['fstype'];
	$pconfig['controller'] = $a_disk[$cnid]['controller'];
	$pconfig['controller_id'] =  $a_disk[$cnid]['controller_id'];
	$pconfig['controller_desc'] = $a_disk[$cnid]['controller_desc'];
	$pconfig['smart']['devicefilepath'] = $a_disk[$cnid]['smart']['devicefilepath'];
	$pconfig['smart']['devicetype'] = $a_disk[$cnid]['smart']['devicetype'];
	$pconfig['smart']['devicetypearg'] = $a_disk[$cnid]['smart']['devicetypearg'];
	$pconfig['smart_enable'] = isset($a_disk[$cnid]['smart']['enable']);
	$pconfig['smart_extraoptions'] = $a_disk[$cnid]['smart']['extraoptions'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = "";
	$pconfig['model'] = "";
	$pconfig['desc'] = "";
	$pconfig['transfermode'] = "auto";
	$pconfig['harddiskstandby'] = "0";
	$pconfig['apm'] = "0";
	$pconfig['acoustic'] = "0";
	$pconfig['fstype'] = "";
	$pconfig['serial'] = "";
	$pconfig['smart_enable'] = false;
	$pconfig['smart_extraoptions'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: disks_manage.php");
		exit;
	}

	// Input validation.
	foreach ($a_disk as $disk) {
		if (isset($uuid) && (FALSE !== $cnid) && ($disk['uuid'] === $uuid))
			continue;
		if ($disk['name'] === $_POST['name']) {
			$input_errors[] = gtext("This disk already exists in the disk list.");
			break;
		}
	}

	if (empty($input_errors)) {
		$devname = $_POST['name'];

		$disks = [];
		$disks['uuid'] = $_POST['uuid'];
		$disks['name'] = $devname;
		$disks['id'] = $a_phy_disk[$devname]['id'];
		$disks['devicespecialfile'] = $a_phy_disk[$devname]['devicespecialfile'];
		$disks['model'] = (empty($_POST['model'])) ? $a_phy_disk[$devname]['model'] : $_POST['model'];
		$disks['desc'] = (empty($_POST['desc'])) ? $a_phy_disk[$devname]['desc'] : $_POST['desc'];
		$disks['type'] = $a_phy_disk[$devname]['type'];
		if (isset($a_phy_disk[$devname]['serial'])) {
			$serial = $a_phy_disk[$devname]['serial'];
		} else {
			$serial = "";
		}
		if (($serial == "n/a") || ($serial == gtext("n/a"))) {
			$serial = "";
		}
		$disks['serial'] = $serial;
		$disks['size'] = $a_phy_disk[$devname]['size'];
		$disks['harddiskstandby'] = $_POST['harddiskstandby'];
		$disks['acoustic'] = $_POST['acoustic'];
		$disks['apm'] = $_POST['apm'];
		if ($_POST['fstype']) $disks['fstype'] = $_POST['fstype'];
		$disks['transfermode'] = $_POST['transfermode'];
		$disks['controller'] = $a_phy_disk[$devname]['controller'];
		$disks['controller_id'] = $a_phy_disk[$devname]['controller_id'];
		$disks['controller_desc'] = $a_phy_disk[$devname]['controller_desc'];
		$disks['smart']['devicefilepath'] = $a_phy_disk[$devname]['smart']['devicefilepath'];
		$disks['smart']['devicetype'] = $a_phy_disk[$devname]['smart']['devicetype'];
		$disks['smart']['devicetypearg'] = $a_phy_disk[$devname]['smart']['devicetypearg'];
		$disks['smart']['enable'] = isset($_POST['smart_enable']) ? true : false;
		$disks['smart']['extraoptions'] = $_POST['smart_extraoptions'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_disk[$cnid] = $disks;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_disk[] = $disks;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("device", $mode, $disks['uuid']);
		write_config();

		header("Location: disks_manage.php");
		exit;
	}
}
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	document.iform.name.disabled = !enable_change;
	document.iform.fstype.disabled = !enable_change;
}

function smart_enable_change() {
	switch (document.iform.smart_enable.checked) {
		case false:
			showElementById('smart_extraoptions_tr','hide');
			break;
		case true:
			showElementById('smart_extraoptions_tr','show');
			break;
	}
}
// -->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td class="tabnavtbl">
	<ul id="tabnav">
		<li class="tabact"><a href="disks_manage.php" title="<?=gtext('Reload page');?>"><span><?=gtext("HDD Management");?></span></a></li>
		<li class="tabinact"><a href="disks_init.php"><span><?=gtext("HDD Format");?></span></a></li>
		<li class="tabinact"><a href="disks_manage_smart.php"><span><?=gtext("S.M.A.R.T.");?></span></a></li>
		<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext("iSCSI Initiator");?></span></a></li>
	</ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
			<form action="disks_manage_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_titleline(gtext("Disk Settings"));?>
			<tr>
				<td width="22%" valign="top" class="vncellreq"><?=gtext("Disk");?></td>
				<td width="78%" class="vtable">
						<select name="name" class="formfld" id="name">
						<?php foreach ($a_phy_disk as $diskk => $diskv):?>
						<?php // Do not display disks that are already configured. (Create mode);?>
						<?php if (!isset($uuid) && (false !== array_search_ex($diskk, $a_disk, "name"))) continue;?>
						<option value="<?=$diskk;?>" <?php if ($diskk == $pconfig['name']) echo "selected=\"selected\"";?>><?php echo htmlspecialchars($diskk . ": " .$diskv['size'] . " (" . $diskv['model'] . ")");?></option>
						<?php endforeach;?>
						</select>
					  </td>
					</tr>
					<?php
					html_inputbox("desc", gtext("Description"), $pconfig['desc'], gtext("You may enter a description here for your reference."), false, 40);
					$options = ['auto' => 'Automatic','PIO0' => 'PIO0','PIO1' => 'PIO1','PIO2' => 'PIO2','PIO3' => 'PIO3','PIO4' => 'PIO4','WDMA2' => 'WDMA2','UDMA2' => 'UDMA-33','UDMA4' => 'UDMA-66','UDMA5' => 'UDMA-100','UDMA6' => 'UDMA-133'];
					html_combobox("transfermode", gtext("Transfer mode"), $pconfig['transfermode'], $options, gtext("This allows you to set the transfer mode for ATA/IDE disks. You can set 'Automatic' to enable the automatic mode for all SATA/ATA/IDE disks."), false);
					$options = [0 => gtext('Always On')]; foreach([5,10,20,30,60,120,180,240,300,360] as $vsbtime) { $options[$vsbtime] = sprintf("%d %s", $vsbtime, gtext("Minutes")); }
					html_combobox("harddiskstandby", gtext("HDD standby time"), $pconfig['harddiskstandby'], $options, gtext("Puts the disk into standby mode when the selected amount of time after the last disk access has been elapsed."), false);
					$options = [0 => gtext('Disabled'),1 => gtext('Level 1 - Minimum Power Usage with Standby (Spindown)'),64 => gtext('Level 64 - Intermediate Power Usage with Standby'),127 => gtext('Level 127 - Intermediate Power Usage with Standby'),128 => gtext('Level 128 - Minimum Power Usage without Standby (No Spindown)'),192 => gtext('Level 192 - Intermediate Power Usage without Standby'),254 => gtext('Level 254 - Maximum Performance, Maximum Power Usage')];
					html_combobox("apm", gtext("Power management"), $pconfig['apm'], $options, gtext("This allows you to lower the power consumption of the disk, at the expense of performance."), false);
					$options = [0 => gtext('Disabled'),1 => gtext('Minimum Performance, Minimum Acoustic Output'),64 => gtext('Medium Acoustic Output'),127 => gtext('Maximum Performance, Maximum Acoustic Output')];
					html_combobox("acoustic", gtext("Acoustic level"), $pconfig['acoustic'], $options, gtext("This allows you to set how loud the drive is while it's operating."), false);
					html_checkbox("smart_enable", gtext("S.M.A.R.T."), !empty($pconfig['smart_enable']) ? true : false, gtext("Activate S.M.A.R.T. monitoring for this device."), "", false, "smart_enable_change()");
					$helpinghand = gtext('Extra options (usually empty).')
						. ' '
						. '<a href="' . 'http://smartmontools.sourceforge.net/man/smartd.conf.5.html' . '" target="_blank">'
						. gtext('Please check the documentation.')
						. '</a>';
					html_inputbox("smart_extraoptions", gtext("S.M.A.R.T. extra options"), $pconfig['smart_extraoptions'], $helpinghand, false, 40);
					$options = get_fstype_list();
					$helpinghand = gtext('This option allows you to set the file system of already formatted disks containing data.')
						. ' '
						. gtext("Select option 'Unformatted' for unformatted disks and format them with the")
						. ' '
						. '<a <href="' . 'disks_init.php' . '">'
						. gtext('Format Program')
						. '</a>.';
					html_combobox("fstype", gtext("Preformatted file system"), $pconfig['fstype'], $options, $helpinghand, false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" onclick="enable_change(true)" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php if (isset($uuid) && (FALSE !== $cnid)):?>
<script type="text/javascript">
<!-- Disable controls that should not be modified anymore in edit mode. -->
enable_change(false);
smart_enable_change();
</script>
<?php endif;?>
<?php include 'fend.inc';?>
