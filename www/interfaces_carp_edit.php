<?php
/*
	interfaces_carp_edit.php

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

$pgtitle = [gtext('Network'),gtext('Interface Management'),gtext('CARP'), isset($uuid) ? gtext('Edit') : gtext('Add')];

$a_carp = &array_make_branch($config,'vinterfaces','carp');
if(empty($a_carp)):
else:
	array_sort_key($a_carp,'if');
endif;

$default_linkup = "/usr/local/sbin/carp-hast-switch master";
$default_linkdown = "/usr/local/sbin/carp-hast-switch slave";

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_carp, "uuid")))) {
	$pconfig['enable'] = isset($a_carp[$cnid]['enable']);
	$pconfig['uuid'] = $a_carp[$cnid]['uuid'];
	$pconfig['if'] = $a_carp[$cnid]['if'];
	$pconfig['vhid'] = $a_carp[$cnid]['vhid'];
	$pconfig['vipaddr'] = $a_carp[$cnid]['vipaddr'];
	$pconfig['vsubnet'] = $a_carp[$cnid]['vsubnet'];
	$pconfig['advskew'] = $a_carp[$cnid]['advskew'];
	$pconfig['password'] = $a_carp[$cnid]['password'];
	$pconfig['linkup'] = !empty($a_carp[$cnid]['linkup']) ? $a_carp[$cnid]['linkup'] : "";
	$pconfig['linkdown'] = !empty($a_carp[$cnid]['linkdown']) ? $a_carp[$cnid]['linkdown'] : "";
	$pconfig['extraoptions'] = !empty($a_carp[$cnid]['extraoptions']) ? $a_carp[$cnid]['extraoptions'] : "";
	$pconfig['desc'] = $a_carp[$cnid]['desc'];
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['if'] = "carp" . get_nextcarp_id();
	$pconfig['vhid'] = "";
	$pconfig['vipaddr'] = "";
	$pconfig['vsubnet'] = "32";
	$pconfig['advskew'] = "100";
	$pconfig['password'] = "";
	$pconfig['linkup'] = "";
	$pconfig['linkdown'] = "";
	$pconfig['extraoptions'] = "";
	$pconfig['desc'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: interfaces_carp.php");
		exit;
	}

	// Input validation.
	$reqdfields = ['vipaddr','vsubnet'];
	$reqdfieldsn = [gtext('Virtual IP Address'),gtext('Subnet Bit Count')];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (!empty($_POST['vipaddr']) && !is_ipv4addr($_POST['vipaddr']))
		$input_errors[] = gtext("A valid IPv4 address must be specified.");
	if (!empty($_POST['vsubnet']) && !filter_var($_POST['vsubnet'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 32]]))
		$input_errors[] = gtext("A valid network bit count (1-32) must be specified.");

	$reqdfields = ['vhid','advskew','password'];
	$reqdfieldsn = [gtext('Virtual Host ID'),gtext('Advertisement Skew'),gtext('Password')];
	$reqdfieldst = ['numericint','numericint','string'];

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (!empty($_POST['password']) && preg_match("/\'|\"/", $_POST['password']))
		$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Password"));

	if (empty($input_errors)) {
		$carp = [];
		$carp['enable'] = $_POST['enable'] ? true : false;
		$carp['uuid'] = $_POST['uuid'];
		$carp['if'] = $_POST['if'];
		$carp['vhid'] = $_POST['vhid'];
		$carp['vipaddr'] = $_POST['vipaddr'];
		$carp['vsubnet'] = $_POST['vsubnet'];
		$carp['advskew'] = $_POST['advskew'];
		$carp['password'] = $_POST['password'];
		$carp['linkup'] = $_POST['linkup'];
		$carp['linkdown'] = $_POST['linkdown'];
		$carp['extraoptions'] = $_POST['extraoptions'];
		$carp['desc'] = $_POST['desc'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_carp[$cnid] = $carp;
		} else {
			$a_carp[] = $carp;
		}

		write_config();
		touch($d_sysrebootreqd_path);

		header("Location: interfaces_carp.php");
		exit;
	}
}

function get_nextcarp_id() {
	global $config;

	$id = 0;
	$a_carp = $config['vinterfaces']['carp'];

	if (false !== array_search_ex("carp" . strval($id), $a_carp, "if")) {
		do {
			$id++; // Increase ID until a unused one is found.
		} while (false !== array_search_ex("carp" . strval($id), $a_carp, "if"));
	}

	return $id;
}
?>
<?php include 'fbegin.inc';?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="interfaces_assign.php"><span><?=gtext("Management");?></span></a></li>
			<li class="tabinact"><a href="interfaces_wlan.php"><span><?=gtext("WLAN");?></span></a></li>
			<li class="tabinact"><a href="interfaces_vlan.php"><span><?=gtext("VLAN");?></span></a></li>
			<li class="tabinact"><a href="interfaces_lagg.php"><span><?=gtext("LAGG");?></span></a></li>
			<li class="tabinact"><a href="interfaces_bridge.php"><span><?=gtext("Bridge");?></span></a></li>
			<li class="tabact"><a href="interfaces_carp.php" title="<?=gtext('Reload page');?>"><span><?=gtext("CARP");?></span></a></li>
		</ul>
	</td>
</tr>
<tr>
	<td class="tabcont">
		<form action="interfaces_carp_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
			<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline(gtext("Carp Settings"));?>
				<?php $a_if = []; foreach (get_interface_list() as $ifk => $ifv) { $a_if[$ifk] = htmlspecialchars("{$ifk} ({$ifv['mac']})"); };?>
				<?php html_combobox("if", gtext("Interface"), $pconfig['if'], $a_if, "", true);?>
				<?php html_inputbox("vhid", gtext("Virtual Host ID"), $pconfig['vhid'], "", true, 5);?>
				<?php html_ipv4addrbox("vipaddr", "vsubnet", gtext("Virtual IP Address"), $pconfig['vipaddr'], $pconfig['vsubnet'], "", true);?>
				<?php html_inputbox("advskew", gtext("Advertisement Skew"), $pconfig['advskew'], gtext("Lowest value is higher priority. For master node, use 0 or 1. If preempt is enabled, it is adjusted to 240 on failure."), true, 5);?>
				<?php html_inputbox("password", gtext("Password"), $pconfig['password'], "", true, 20);?>
				<?php html_inputbox("linkup", gtext("Link Up Action"), $pconfig['linkup'], sprintf(gtext("Command for LINK_UP event (e.g. %s)."), $default_linkup), false, 60);?>
				<?php html_inputbox("linkdown", gtext("Link Down Action"), $pconfig['linkdown'], sprintf(gtext("Command for LINK_DOWN event (e.g. %s)."), $default_linkdown), false, 60);?>
				<?php html_inputbox("extraoptions", gtext("Extra Options"), $pconfig['extraoptions'], gtext("Extra options to ifconfig (usually empty)."), false, 40);?>
				<?php html_inputbox("desc", gtext("Description"), $pconfig['desc'], gtext("You may enter a description here for your reference."), false, 40);?>
			</table>
			<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
				<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
				<input name="enable" type="hidden" value="<?=$pconfig['enable'];?>" />
				<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
			</div>
		<?php include 'formend.inc';?>
		</form>
	</td>
</tr>
</table>
<?php include 'fend.inc';?>
