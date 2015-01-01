<?php
/*
	interfaces_bridge_edit.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gettext("Network"), gettext("Interface Management"), gettext("Bridge"), isset($uuid) ? gettext("Edit") : gettext("Add"));

if (!isset($config['vinterfaces']['bridge']) || !is_array($config['vinterfaces']['bridge']))
	$config['vinterfaces']['bridge'] = array();

$a_bridge = &$config['vinterfaces']['bridge'];
array_sort_key($a_bridge, "if");

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_bridge, "uuid")))) {
	$pconfig['enable'] = isset($a_bridge[$cnid]['enable']);
	$pconfig['uuid'] = $a_bridge[$cnid]['uuid'];
	$pconfig['if'] = $a_bridge[$cnid]['if'];
	$pconfig['bridgeif'] = $a_bridge[$cnid]['bridgeif'];
	$pconfig['mtu'] = !empty($a_bridge[$cnid]['mtu']) ? $a_bridge[$cnid]['mtu'] : "";
	$pconfig['extraoptions'] = !empty($a_bridge[$cnid]['extraoptions']) ? $a_bridge[$cnid]['extraoptions'] : "";
	$pconfig['desc'] = $a_bridge[$cnid]['desc'];
} else {
	$pconfig['enable'] = true;
	$pconfig['uuid'] = uuid();
	$pconfig['if'] = "bridge" . get_nextbridge_id();
	$pconfig['bridgeif'] = array();
	$pconfig['mtu'] = "";
	$pconfig['extraoptions'] = "";
	$pconfig['desc'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: interfaces_bridge.php");
		exit;
	}

	if (count($_POST['bridgeif']) < 1)
		$input_errors[] = gettext("There must be selected a minimum of 1 interface.");
	if (!empty($_POST['mtu']) && !is_numericint($_POST['mtu']))
		$input_errors[] = sprintf(gettext("The attribute '%s' must be a number."), gettext("MTU"));

	if (empty($input_errors)) {
		$bridge = array();
		$bridge['enable'] = $_POST['enable'] ? true : false;
		$bridge['uuid'] = $_POST['uuid'];
		$bridge['if'] = $_POST['if'];
		$bridge['bridgeif'] = $_POST['bridgeif'];
		$bridge['mtu'] = $_POST['mtu'];
		$bridge['extraoptions'] = $_POST['extraoptions'];
		$bridge['desc'] = $_POST['desc'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_bridge[$cnid] = $bridge;
		} else {
			$a_bridge[] = $bridge;
		}

		write_config();
		touch($d_sysrebootreqd_path);

		header("Location: interfaces_bridge.php");
		exit;
	}
}

function get_nextbridge_id() {
	global $config;

	$id = 0;
	$a_bridge = $config['vinterfaces']['bridge'];

	if (false !== array_search_ex("bridge" . strval($id), $a_bridge, "if")) {
		do {
			$id++; // Increase ID until a unused one is found.
		} while (false !== array_search_ex("bridge" . strval($id), $a_bridge, "if"));
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
			<li class="tabact"><a href="interfaces_bridge.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Bridge");?></span></a></li>
			<li class="tabinact"><a href="interfaces_carp.php"><span><?=gettext("CARP");?></span></a></li>
		</ul>
	</td>
</tr>
<tr>
	<td class="tabcont">
		<form action="interfaces_bridge_edit.php" method="post" name="iform" id="iform">
			<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_inputbox("if", gettext("Interface"), $pconfig['if'], "", true, 5, true);?>
				<?php $a_bridgeif = array(); foreach (get_interface_list() as $ifk => $ifv) { if (preg_match('/bridge/i', $ifk)) { continue; } if (!(isset($uuid) && (FALSE !== $cnid)) && false !== array_search_ex($ifk, $a_bridge, "bridgeif")) { continue; } $a_bridgeif[$ifk] = htmlspecialchars("{$ifk} ({$ifv['mac']})"); } ?>
				<?php html_listbox("bridgeif", gettext("Member Interface"), $pconfig['bridgeif'], $a_bridgeif, gettext("Note: Ctrl-click (or command-click on the Mac) to select multiple entries."), true);?>
				<?php html_inputbox("mtu", gettext("MTU"), $pconfig['mtu'], gettext("Set the maximum transmission unit of the interface to n, default is interface specific. The MTU is used to limit the size of packets that are transmitted on an interface. Not all interfaces support setting the MTU, and some interfaces have range restrictions."), false, 5);?>
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
