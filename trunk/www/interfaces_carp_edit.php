<?php
/*
	interfaces_carp_edit.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = array(gettext("Network"), gettext("Interface Management"), gettext("CARP"), isset($uuid) ? gettext("Edit") : gettext("Add"));

if (!isset($config['vinterfaces']['carp']) || !is_array($config['vinterfaces']['carp']))
	$config['vinterfaces']['carp'] = array();

$a_carp = &$config['vinterfaces']['carp'];
array_sort_key($a_carp, "if");

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_carp, "uuid")))) {
	$pconfig['enable'] = isset($a_carp[$cnid]['enable']);
	$pconfig['uuid'] = $a_carp[$cnid]['uuid'];
	$pconfig['if'] = $a_carp[$cnid]['if'];
	$pconfig['vhid'] = $a_carp[$cnid]['vhid'];
	$pconfig['vipaddr'] = $a_carp[$cnid]['vipaddr'];
	$pconfig['vsubnet'] = $a_carp[$cnid]['vsubnet'];
	$pconfig['advskew'] = $a_carp[$cnid]['advskew'];
	$pconfig['password'] = $a_carp[$cnid]['password'];
	$pconfig['extraoptions'] = !empty($a_carp[$cnid]['extraoptions']) ? $a_carp[$cnid]['extraoptions'] : "";
	$pconfig['desc'] = $a_carp[$cnid]['desc'];
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['if'] = "carp" . get_nextcarp_id();
	$pconfig['vhid'] = "";
	$pconfig['vipaddr'] = "";
	$pconfig['vsubnet'] = "24";
	$pconfig['advskew'] = "100";
	$pconfig['password'] = "";
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
	$reqdfields = explode(" ", "vipaddr vsubnet");
	$reqdfieldsn = array(gettext("Virtual IP address"), gettext("Subnet bit count"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (!empty($_POST['vipaddr']) && !is_ipv4addr($_POST['vipaddr']))
		$input_errors[] = gettext("A valid IPv4 address must be specified.");
	if (!empty($_POST['vsubnet']) && !filter_var($_POST['vsubnet'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32))))
		$input_errors[] = gettext("A valid network bit count (1-32) must be specified.");

	$reqdfields = explode(" ", "vhid advskew password");
	$reqdfieldsn = array(gettext("Virtual Host ID"), gettext("Advertisement skew"), gettext("Password"));
	$reqdfieldst = explode(" ", "numericint numericint string");

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (!empty($_POST['password']) && preg_match("/\'|\"/", $_POST['password']))
		$input_errors[] = sprintf(gettext("The attribute '%s' contains invalid characters."), gettext("Password"));

	if (empty($input_errors)) {
		$carp = array();
		$carp['enable'] = $_POST['enable'] ? true : false;
		$carp['uuid'] = $_POST['uuid'];
		$carp['if'] = $_POST['if'];
		$carp['vhid'] = $_POST['vhid'];
		$carp['vipaddr'] = $_POST['vipaddr'];
		$carp['vsubnet'] = $_POST['vsubnet'];
		$carp['advskew'] = $_POST['advskew'];
		$carp['password'] = $_POST['password'];
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
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="interfaces_assign.php"><span><?=gettext("Management");?></span></a></li>
			<li class="tabinact"><a href="interfaces_vlan.php"><span><?=gettext("VLAN");?></span></a></li>
			<li class="tabinact"><a href="interfaces_lagg.php"><span><?=gettext("LAGG");?></span></a></li>
			<li class="tabinact"><a href="interfaces_bridge.php"><span><?=gettext("Bridge");?></span></a></li>
			<li class="tabact"><a href="interfaces_carp.php" title="<?=gettext("Reload page");?>"><span><?=gettext("CARP");?></span></a></li>
		</ul>
	</td>
</tr>
<tr>
	<td class="tabcont">
		<form action="interfaces_carp_edit.php" method="post" name="iform" id="iform">
			<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_inputbox("if", gettext("Interface"), $pconfig['if'], "", true, 5, true);?>
				<?php html_inputbox("vhid", gettext("Virtual Host ID"), $pconfig['vhid'], "", true, 5);?>
				<?php html_ipv4addrbox("vipaddr", "vsubnet", gettext("Virtual IP address"), $pconfig['vipaddr'], $pconfig['vsubnet'], "", true);?>
				<?php html_inputbox("advskew", gettext("Advertisement skew"), $pconfig['advskew'], "", true, 5);?>
				<?php html_inputbox("password", gettext("Password"), $pconfig['password'], "", true, 20);?>
				<?php html_inputbox("extraoptions", gettext("Extra options"), $pconfig['extraoptions'], gettext("Extra options to ifconfig (usually empty)."), false, 40);?>
				<?php html_inputbox("desc", gettext("Description"), $pconfig['desc'], gettext("You may enter a description here for your reference."), false, 40);?>
			</table>
			<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gettext("Save") : gettext("Add")?>" />
				<input name="Cancel" type="submit" class="formbtn" value="<?=gettext("Cancel");?>" />
				<input name="enable" type="hidden" value="<?=$pconfig['enable'];?>" />
				<input name="if" type="hidden" value="<?=$pconfig['if'];?>" />
				<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
			</div>
		<?php include("formend.inc");?>
		</form>
	</td>
</tr>
</table>
<?php include("fend.inc");?>
