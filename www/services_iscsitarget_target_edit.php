<?php
/*
	services_iscsitarget_target_edit.php

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

array_make_branch($config,'iscsitarget','portalgroup');
array_make_branch($config,'iscsitarget','initiatorgroup');
array_make_branch($config,'iscsitarget','authgroup');
array_make_branch($config,'iscsitarget','extent');
array_make_branch($config,'iscsitarget','device');
array_make_branch($config,'iscsitarget','target');

/* currently support LUN0 only */
$MAX_LUNS = 4;
/* supported block length */
$MAX_BLOCKLEN = 4096;
//$MAX_BLOCKLEN = 128 * 1024;

function cmp_tag($a, $b) {
	if ($a['tag'] == $b['tag'])
		return 0;
	return ($a['tag'] > $b['tag']) ? 1 : -1;
}
usort($config['iscsitarget']['portalgroup'], "cmp_tag");
usort($config['iscsitarget']['initiatorgroup'], "cmp_tag");
usort($config['iscsitarget']['authgroup'], "cmp_tag");
array_sort_key($config['iscsitarget']['extent'], "name");
array_sort_key($config['iscsitarget']['device'], "name");
//array_sort_key($config['iscsitarget']['target'], "name");

function get_fulliqn($name) {
	global $config;
	$fullname = $name;
	$basename = $config['iscsitarget']['nodebase'];
	if (strncasecmp("iqn.", $name, 4) != 0
		&& strncasecmp("eui.", $name, 4) != 0
		&& strncasecmp("naa.", $name, 4) != 0) {
		if (strlen($basename) != 0) {
			$fullname = $basename.":".$name;
		}
	}
	return $fullname;
}

function cmp_target($a, $b) {
	$aname = get_fulliqn($a['name']);
	$bname = get_fulliqn($b['name']);
	return strcasecmp($aname, $bname);
}
usort($config['iscsitarget']['target'], "cmp_target");

$a_iscsitarget_extent = &$config['iscsitarget']['extent'];
$a_iscsitarget_device = &$config['iscsitarget']['device'];
$a_iscsitarget_target = &$config['iscsitarget']['target'];

function get_random_iscsi_sn($length = 8){
	global $config;
	$xstr0 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$xstr = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$zero = str_repeat("0", $length);
	$result = "";
	while (1) {
		$tmp = mt_rand(0, strlen($xstr0) - 1);
		$result .= substr($xstr0, $tmp, 1);
		for ($i = 1; $i < $length; $i++) {
			$tmp = mt_rand(0, strlen($xstr) - 1);
			$result .= substr($xstr, $tmp, 1);
		}
		if (strcmp($result, $zero) == 0)
			continue;
		$index = array_search_ex($result, $config['iscsitarget']['target'], "inqserial");
		if (false === $index)
			break;
	}
	return $result;
}

$errormsg = "";
if (count($config['iscsitarget']['portalgroup']) == 0) {
	$errormsg .= gtext('No Portal Group has been configured.')
		. ' '
		. '<a href="' . 'services_iscsitarget_pg.php' . '">'
		. gtext('Please add a new Portal Group first.')
		. '</a>'
		. '<br />'
		. "\n";
}
if (count($config['iscsitarget']['initiatorgroup']) == 0) {
	$errormsg .= gtext('No Initiator Group has been configured.')
		. ' '
		. '<a href="' . 'services_iscsitarget_ig.php' . '">'
		. gtext('Please add a new Initiator Group first.')
		. '</a>'
		. '<br />'
		. "\n";
}
if (count($config['iscsitarget']['extent']) == 0) {
	$errormsg .= gtext('No Extent has been configured.')
		. ' '
		. '<a href="' . 'services_iscsitarget_target.php' . '">'
		. gtext('Please add a new Extent first.')
		. '</a>'
		. '<br />'
		. "\n";
}

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_iscsitarget_target, "uuid")))) {
	$pconfig['uuid'] = $a_iscsitarget_target[$cnid]['uuid'];
	$pconfig['enable'] = isset($a_iscsitarget_target[$cnid]['enable']) ? true : false;
	$pconfig['name'] = $a_iscsitarget_target[$cnid]['name'];
	$pconfig['alias'] = $a_iscsitarget_target[$cnid]['alias'];
	$pconfig['type'] = $a_iscsitarget_target[$cnid]['type'];
	$pconfig['flags'] = $a_iscsitarget_target[$cnid]['flags'];
	$pconfig['comment'] = $a_iscsitarget_target[$cnid]['comment'];
	// OLD format
	//$pconfig['storage'] = $a_iscsitarget_target[$cnid]['storage'];
	//if (is_array($pconfig['storage']))
	//	$pconfig['storage'] = $pconfig['storage'][0];
	$pconfig['storage'] = "";
	$pconfig['pgigmap'] = $a_iscsitarget_target[$cnid]['pgigmap'];
	$pconfig['agmap'] = $a_iscsitarget_target[$cnid]['agmap'];
	$pconfig['lunmap'] = $a_iscsitarget_target[$cnid]['lunmap'];
	$pconfig['portalgroup'] = $pconfig['pgigmap'][0]['pgtag'];
	$pconfig['initiatorgroup'] = $pconfig['pgigmap'][0]['igtag'];
	if (!empty($pconfig['pgigmap'][1])) {
		$pconfig['portalgroup2'] = $pconfig['pgigmap'][1]['pgtag'];
		$pconfig['initiatorgroup2'] = $pconfig['pgigmap'][1]['igtag'];
	} else {
		$pconfig['portalgroup2'] = 0;
		$pconfig['initiatorgroup2'] = 0;
	}
	$pconfig['authgroup'] = $pconfig['agmap'][0]['agtag'];
	$pconfig['authmethod'] = $a_iscsitarget_target[$cnid]['authmethod'];
	$pconfig['digest'] = $a_iscsitarget_target[$cnid]['digest'];
	$pconfig['queuedepth'] = $a_iscsitarget_target[$cnid]['queuedepth'];
	$pconfig['writecache'] = !isset($a_iscsitarget_target[$cnid]['disablewritecache']);
	$pconfig['inqvendor'] = $a_iscsitarget_target[$cnid]['inqvendor'];
	$pconfig['inqproduct'] = $a_iscsitarget_target[$cnid]['inqproduct'];
	$pconfig['inqrevision'] = $a_iscsitarget_target[$cnid]['inqrevision'];
	$pconfig['inqserial'] = $a_iscsitarget_target[$cnid]['inqserial'];
	$pconfig['blocklen'] = $a_iscsitarget_target[$cnid]['blocklen'];

	if (!isset($pconfig['type'])){
		$pconfig['type'] = "Disk";
	}
	if (!isset($pconfig['queuedepth'])){
		$pconfig['queuedepth'] = 32;
	}
	$type = $pconfig['type'];
	if ($type == "Disk"){
		$stype = "Storage";
	}elseif ($type == "Pass"){
		$stype = "Device";
	}else{
		$stype = "Removable";
	}
	if (!is_array($pconfig['lunmap'])) {
		$pconfig['lunmap'] = [];
		$pconfig['lunmap'][0]['lun'] = "0";
		$pconfig['lunmap'][0]['type'] = "$stype";
		$pconfig['lunmap'][0]['extentname'] = $pconfig['storage'];
		for ($i = 1; $i < $MAX_LUNS; $i++) {
			$pconfig['lunmap'][$i]['lun'] = "$i";
			$pconfig['lunmap'][$i]['type'] = "$stype";
			$pconfig['lunmap'][$i]['extentname'] = "-";
		}
	}
} else {
	$type = "Disk";
	$stype = "Storage";
	// Find next unused ID.
	$targetid = 0;
	$a_id = [];
	foreach($a_iscsitarget_target as $target) {
		$tmpa = explode(":", $target['name']);
		$name = $tmpa[count($tmpa)-1];
		$tmp = str_replace(strtolower($type), "", $name); // Extract ID.
		if (is_numeric($tmp))
			$a_id[] = (int)$tmp;
	}
	while (true === in_array($targetid, $a_id))
		$targetid += 1;

	$pconfig['uuid'] = uuid();
	$pconfig['enable'] = true;
	$pconfig['name'] = strtolower($type)."$targetid";
	$pconfig['alias'] = "";
	$pconfig['type'] = "$type";
	$pconfig['flags'] = "rw";
	$pconfig['comment'] = "";
	$pconfig['storage'] = "";
	$pconfig['pgigmap'] = [];
	$pconfig['pgigmap'][0]['pgtag'] = 0;
	$pconfig['pgigmap'][0]['igtag'] = 0;
	$pconfig['pgigmap'][1]['pgtag'] = 0;
	$pconfig['pgigmap'][1]['igtag'] = 0;
	$pconfig['agmap'] = [];
	$pconfig['agmap'][0]['agtag'] = 0;
	$pconfig['lunmap'] = [];
	$pconfig['lunmap'][0]['lun'] = "0";
	$pconfig['lunmap'][0]['type'] = "$stype";
	$pconfig['lunmap'][0]['extentname'] = "";
	for ($i = 1; $i < $MAX_LUNS; $i++) {
		$pconfig['lunmap'][$i]['lun'] = "$i";
		$pconfig['lunmap'][$i]['type'] = "$stype";
		$pconfig['lunmap'][$i]['extentname'] = "-";
	}
	$pconfig['portalgroup'] = $pconfig['pgigmap'][0]['pgtag'];
	$pconfig['initiatorgroup'] = $pconfig['pgigmap'][0]['igtag'];
	if (!empty($pconfig['pgigmap'][1])) {
		$pconfig['portalgroup2'] = $pconfig['pgigmap'][1]['pgtag'];
		$pconfig['initiatorgroup2'] = $pconfig['pgigmap'][1]['igtag'];
	} else {
		$pconfig['portalgroup2'] = 0;
		$pconfig['initiatorgroup2'] = 0;
	}
	$pconfig['authgroup'] = $pconfig['agmap'][0]['agtag'];
	$pconfig['authmethod'] = "Auto";
	$pconfig['digest'] = "Auto";
	$pconfig['queuedepth'] = 32;
	$pconfig['writecache'] = true;
	$pconfig['inqvendor'] = "";
	$pconfig['inqproduct'] = "";
	$pconfig['inqrevision'] = "";
	$sid = "00";
	//$serial = "NFSN{$sid}".substr(preg_replace("/\-/", "", $pconfig['uuid']), -8);
	$serial = "NFSN{$sid}".get_random_iscsi_sn(8);
	$pconfig['inqserial'] = $serial;
	$pconfig['blocklen'] = "512";
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: services_iscsitarget_target.php");
		exit;
	}
	if (!isset($_POST['storage']))
		$_POST['storage'] = $pconfig['storage'] = "";
	$type = $_POST['type'];
	$blocklen = 0;
	if ($type == "Disk"){
		$stype = "Storage";
		$blocklen = $_POST['blocklen'];
	}elseif ($type == "Pass"){
		$stype = "Device";
	}else{
		$stype = "Removable";
	}
	
	$tgtname = $_POST['name'];
	$tgtname = preg_replace('/\s/', '', $tgtname);
	$pconfig['name'] = $tgtname;
	$pgigmap = [];
	$pgigmap[0]['pgtag'] = $_POST['portalgroup'];
	$pgigmap[0]['igtag'] = $_POST['initiatorgroup'];
	if (!empty($_POST['portalgroup2']) || !empty($_POST['initiatorgroup2'])) {
		$pgigmap[1]['pgtag'] = $_POST['portalgroup2'];
		$pgigmap[1]['igtag'] = $_POST['initiatorgroup2'];
	}
	$pconfig['pgigmap'] = $pgigmap;
	$agmap = [];
	$agmap[0]['agtag'] = $_POST['authgroup'];
	$pconfig['agmap'] = $agmap;
	$lunmap = [];
	$lunmap[0]['lun'] = "0";
	$lunmap[0]['type'] = "$stype";
	if ($stype == "Removable") {
		if ($_POST['removable'] == "-") {
			$lunmap[0]['extentname'] = "-";
		} else {
			$lunmap[0]['extentname'] = $_POST['removable'];
		}
		$_POST['storage'] = "-";
	} else {
		$lunmap[0]['extentname'] = $_POST['storage'];
	}
	for ($i = 1; $i < $MAX_LUNS; $i++) {
		if (isset($_POST['enable'.$i])
			&& $_POST['storage'.$i] !== "-") {
			$lunmap[$i]['lun'] = "$i";
			$lunmap[$i]['type'] = "$stype";
			$lunmap[$i]['extentname'] = $_POST['storage'.$i];
		}
	}
	$pconfig['lunmap'] = $lunmap;
	if ($_POST['queuedepth'] === "") {
		$queuedepth = 0;
	} else {
		$queuedepth = $_POST['queuedepth'];
	}

	// Input validation.
	$reqdfields = ['name'];
	$reqdfieldsn = [gtext('Target Name')];
	$reqdfieldst = ['string'];

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	$reqdfields = ['type','flags','portalgroup','initiatorgroup','portalgroup','initiatorgroup','storage'];
	$reqdfieldsn = [gtext('Type'),gtext('Flags'),gtext('Portal Group'),gtext('Initiator Group'),gtext('Portal Group'),gtext('Initiator Group'),gtext('Storage')];
	$reqdfieldst = ['string','string','numericint','numericint','numericint','numericint','string'];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	$reqdfields = ['authmethod','authgroup','digest','queuedepth','blocklen'];
	$reqdfieldsn = [gtext('Auth Method'),gtext('Auth Group'),gtext('Initial Digest'),gtext('Queue Depth'),gtext('Logical Block')];
	$reqdfieldst = ['string','numericint','string','numericint','numericint'];
	//do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if ((!empty($_POST['portalgroup2']) && empty($_POST['initiatorgroup2']))
	   || (empty($_POST['portalgroup2']) && !empty($_POST['initiatorgroup2']))) {

		if (empty($_POST['portalgroup2']))
			$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Portal Group"));
		if (empty($_POST['initiatorgroup2']))
			$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Initiator Group"));
	}

	if ((strcasecmp("Auto", $pconfig['authmethod']) != 0
	   && strcasecmp("None", $pconfig['authmethod']) != 0)
		&& $pconfig['authgroup'] == 0) {
		$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Auth Group"));
	}

	if ($pconfig['queuedepth'] < 0 || $pconfig['queuedepth'] > 255) {
		$input_errors[] = gtext("The queuedepth range is invalid.");
	}
	if (strlen($pconfig['inqvendor']) > 8) {
		$input_errors[] = sprintf(gtext("%s is too long."), gtext("Inquiry Vendor"));
	}
	if (strlen($pconfig['inqproduct']) > 16) {
		$input_errors[] = sprintf(gtext("%s is too long."), gtext("Inquiry Product"));
	}
	if (strlen($pconfig['inqrevision']) > 4) {
		$input_errors[] = sprintf(gtext("%s is too long."), gtext("Inquiry Revision"));
	}
	if (strlen($pconfig['inqserial']) > 16) {
		$input_errors[] = sprintf(gtext("%s is too long."), gtext("Inquiry Serial"));
	}

	// Check for duplicates.
	if (!(isset($uuid) && (FALSE !== $cnid))) {
		$fullname = get_fulliqn($pconfig['name']);
		foreach ($a_iscsitarget_target as $target) {
			if (strcasecmp($fullname, get_fulliqn($target['name'])) == 0) {
				$input_errors[] = gtext("The target name already exists.");
				break;
			}
		}
	}

	// optional LUNs
	for ($i = 1; $i < $MAX_LUNS; $i++) {
		if (!isset($lunmap[$i]['extentname'])
			|| $lunmap[$i]['extentname'] === "-")
			continue;
		for ($j = 0; $j < $i; $j++) {
			if (!isset($lunmap[$j]['extentname'])
				|| $lunmap[$j]['extentname'] === "-")
				continue;
			if ($lunmap[$j]['extentname'] === $lunmap[$i]['extentname']) {
				$input_errors[] = sprintf(gtext("%s%d %s is already used by %s%d."), gtext("LUN"), $i, $lunmap[$i]['extentname'], gtext("LUN"), $j);
			}
		}
	}

	if (empty($input_errors)) {
		$iscsitarget_target = [];
		$iscsitarget_target['uuid'] = $_POST['uuid'];
		$iscsitarget_target['enable'] = isset($_POST['enable']) ? true : false;
		$iscsitarget_target['name'] = $tgtname;
		$iscsitarget_target['alias'] = $_POST['alias'];
		$iscsitarget_target['type'] = $_POST['type'];
		$iscsitarget_target['flags'] = $_POST['flags'];
		$iscsitarget_target['comment'] = $_POST['comment'];

		//$iscsitarget_target['storage'] = $_POST['storage'];

		$iscsitarget_target['authmethod'] = $_POST['authmethod'];
		$iscsitarget_target['digest'] = $_POST['digest'];
		$iscsitarget_target['queuedepth'] = $queuedepth;
		$iscsitarget_target['disablewritecache'] = !isset($_POST['writecache']);
		$iscsitarget_target['inqvendor'] = $_POST['inqvendor'];
		$iscsitarget_target['inqproduct'] = $_POST['inqproduct'];
		$iscsitarget_target['inqrevision'] = $_POST['inqrevision'];
		$iscsitarget_target['inqserial'] = $_POST['inqserial'];
		$iscsitarget_target['blocklen'] = $blocklen;

		$iscsitarget_target['pgigmap'] = $pgigmap;
		$iscsitarget_target['agmap'] = $agmap;
		$iscsitarget_target['lunmap'] = $lunmap;

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_iscsitarget_target[$cnid] = $iscsitarget_target;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_iscsitarget_target[] = $iscsitarget_target;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("iscsitarget_target", $mode, $iscsitarget_target['uuid']);
		write_config();

		header("Location: services_iscsitarget_target.php");
		exit;
	}
}
$pgtitle = [gtext('Services'),gtext('iSCSI Target'),gtext('Target'),isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function type_change() {
	var addedit = document.iform.addedit.value;
	//if (addedit == "edit") return;
	switch (document.iform.type.value) {
	case "Disk":
		if (addedit != "edit") {
			document.iform.flags.value = "rw";
		}
		showElementById("storage_tr", 'show');
		showElementById("removable_tr", 'hide');
<?php if ($MAX_BLOCKLEN > 512): ?>
		showElementById("blocklen_tr", 'show');
<?php endif; ?>
<?php if ($MAX_LUNS > 1): ?>
		for (var idx = 1; idx < <?php echo "$MAX_LUNS"; ?>; idx++) {
			var sw_name = "enable" + idx;
			var tr_name = "storage" + idx + "_tr";
			//eval("document.iform." + sw_name + ".checked = true");
			eval("document.iform." + sw_name + ".disabled = false");
			showElementById("lun"+idx+"_separator", 'show');
			showElementById(sw_name+"_tr", 'show');
			showElementById(tr_name, 'hide');
		}
<?php endif; ?>
		break;
	case "DVD":
		if (addedit != "edit") {
			document.iform.flags.value = "ro";
		}
		showElementById("storage_tr", 'hide');
		showElementById("removable_tr", 'show');
<?php if ($MAX_BLOCKLEN > 512): ?>
		showElementById("blocklen_tr", 'hide');
<?php endif; ?>
<?php if ($MAX_LUNS > 1): ?>
		for (var idx = 1; idx < <?php echo "$MAX_LUNS"; ?>; idx++) {
			var sw_name = "enable" + idx;
			var tr_name = "storage" + idx + "_tr";
			eval("document.iform." + sw_name + ".checked = false");
			eval("document.iform." + sw_name + ".disabled = true");
			showElementById("lun"+idx+"_separator", 'hide');
			showElementById(sw_name+"_tr", 'hide');
			showElementById(tr_name, 'hide');
		}
<?php endif; ?>
		break;
	case "Tape":
		if (addedit != "edit") {
			document.iform.flags.value = "rw";
		}
		showElementById("storage_tr", 'hide');
		showElementById("removable_tr", 'show');
<?php if ($MAX_BLOCKLEN > 512): ?>
		showElementById("blocklen_tr", 'hide');
<?php endif; ?>
<?php if ($MAX_LUNS > 1): ?>
		for (var idx = 1; idx < <?php echo "$MAX_LUNS"; ?>; idx++) {
			var sw_name = "enable" + idx;
			var tr_name = "storage" + idx + "_tr";
			eval("document.iform." + sw_name + ".checked = false");
			eval("document.iform." + sw_name + ".disabled = true");
			showElementById("lun"+idx+"_separator", 'hide');
			showElementById(sw_name+"_tr", 'hide');
			showElementById(tr_name, 'hide');
		}
<?php endif; ?>
		break;
	default:
		if (addedit != "edit") {
			document.iform.flags.value = "rw";
		}
		showElementById("storage_tr", 'show');
		showElementById("removable_tr", 'hide');
<?php if ($MAX_BLOCKLEN > 512): ?>
		showElementById("blocklen_tr", 'hide');
<?php endif; ?>
<?php if ($MAX_LUNS > 1): ?>
		for (var idx = 1; idx < <?php echo "$MAX_LUNS"; ?>; idx++) {
			var sw_name = "enable" + idx;
			var tr_name = "storage" + idx + "_tr";
			eval("document.iform." + sw_name + ".checked = false");
			eval("document.iform." + sw_name + ".disabled = true");
			showElementById("lun"+idx+"_separator", 'hide');
			showElementById(sw_name+"_tr", 'hide');
			showElementById(tr_name, 'hide');
		}
<?php endif; ?>
		break;
	}
}

function lun_change(idx) {
	var sw_name = "enable" + idx;
	var tr_name = "storage" + idx + "_tr";
	var endis = eval("document.iform." + sw_name + ".checked");

	if (endis) {
		showElementById(tr_name, 'show');
	} else {
		showElementById(tr_name, 'hide');
	}
}

function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.name.disabled = endis;
	document.iform.alias.disabled = endis;
	document.iform.type.disabled = endis;
	document.iform.flags.disabled = endis;
	document.iform.portalgroup.disabled = endis;
	document.iform.initiatorgroup.disabled = endis;
	document.iform.comment.disabled = endis;
	document.iform.storage.disabled = endis;
	document.iform.removable.disabled = endis;
<?php if ($MAX_LUNS > 1): ?>
	for (var idx = 1; idx < <?php echo "$MAX_LUNS"; ?>; idx++) {
		var sw_name = "enable" + idx;
		var tr_name = "storage" + idx + "_tr";
		var name = "storage" + idx;
		var endis_str = endis ? "true" : "false";
		eval("document.iform." + sw_name + ".disabled = " + endis_str);
		eval("document.iform." + name + ".disabled = " + endis_str);
	}
<?php endif; ?>
	document.iform.authmethod.disabled = endis;
	document.iform.authgroup.disabled = endis;
	document.iform.digest.disabled = endis;
	document.iform.queuedepth.disabled = endis;
	document.iform.inqvendor.disabled = endis;
	document.iform.inqproduct.disabled = endis;
	document.iform.inqrevision.disabled = endis;
	document.iform.inqserial.disabled = endis;
<?php if ($MAX_BLOCKLEN > 512): ?>
	document.iform.blocklen.disabled = endis;
<?php endif; ?>
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="services_iscsitarget.php"><span><?=gtext("Settings");?></span></a></li>
		<li class="tabact"><a href="services_iscsitarget_target.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Targets");?></span></a></li>
		<li class="tabinact"><a href="services_iscsitarget_pg.php"><span><?=gtext("Portals");?></span></a></li>
		<li class="tabinact"><a href="services_iscsitarget_ig.php"><span><?=gtext("Initiators");?></span></a></li>
		<li class="tabinact"><a href="services_iscsitarget_ag.php"><span><?=gtext("Auths");?></span></a></li>
		<li class="tabinact"><a href="services_iscsitarget_media.php"><span><?=gtext("Media");?></span></a></li>
	</ul></td></tr>
	<tr>
		<td class="tabcont">
			<form action="services_iscsitarget_target_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php
				if(!empty($errormsg)):
					 print_error_box($errormsg);
				endif;
				if(!empty($input_errors)):
					print_input_errors($input_errors);
				endif;
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("iSCSI Target"), $pconfig['enable'] ? true : false, gtext("Enable"), "enable_change(false)");
					html_inputbox("name", gtext("Target Name"), $pconfig['name'], gtext("Base Name will be appended automatically when starting without 'iqn.'."), true, 70, false);
					html_inputbox("alias", gtext("Target Alias"), $pconfig['alias'], gtext("Optional user-friendly string of the target."), false, 70, false);
					html_combobox("type", gtext("Type"), $pconfig['type'], ['Disk' => gtext('Disk'),'DVD' => gtext('DVD'),'Tape' => gtext('Tape'),'Pass' => gtext('Device Pass-through')], gtext("Logical Unit Type mapped to LUN."), true, false, "type_change()");
					html_combobox("flags", gtext("Flags"), $pconfig['flags'], ['rw' => gtext('Read/Write (rw)'),'rw,dynamic' => gtext('Read/Write (rw,dynamic) for removable types file size grow and shrink automatically by EOF (ignore specified size)'),'rw,extend' => gtext('Read/Write (rw,extend) for removable types extend file size if EOM reached'), 'ro' => gtext('Read Only (ro)')], "", true);
					$pg_list = [];
					//$pg_list['0'] = gtext("None");
					foreach($config['iscsitarget']['portalgroup'] as $pg):
						if ($pg['comment']):
							$l = sprintf(gtext("Tag%d (%s)"), $pg['tag'], $pg['comment']);
						else:
							$l = sprintf(gtext("Tag%d"), $pg['tag']);
						endif;
						$pg_list[$pg['tag']] = htmlspecialchars($l);
					endforeach;
					html_combobox("portalgroup", sprintf("%s (%s)", gtext("Portal Group"), gtext("Primary")), $pconfig['portalgroup'], $pg_list, gtext("The initiator can connect to the portals in specific Portal Group."), true);
					$ig_list = [];
					//$ig_list['0'] = gtext("None");
					foreach($config['iscsitarget']['initiatorgroup'] as $ig):
						if ($ig['comment']):
							$l = sprintf(gtext("Tag%d (%s)"), $ig['tag'], $ig['comment']);
						else:
							$l = sprintf(gtext("Tag%d"), $ig['tag']);
						endif;
						$ig_list[$ig['tag']] = htmlspecialchars($l);
					endforeach;
					html_combobox("initiatorgroup", sprintf("%s (%s)", gtext("Initiator Group"), gtext("Primary")), $pconfig['initiatorgroup'], $ig_list, gtext("The initiator can access to the target via the portals by authorised initiator names and networks in specific Initiator Group."), true);
					html_combobox("portalgroup2", sprintf("%s (%s)", gtext("Portal Group"), gtext("Secondary")), $pconfig['portalgroup2'], array_merge(array("0" => gtext("None")), $pg_list), "", true);
					html_combobox("initiatorgroup2", sprintf("%s (%s)", gtext("Initiator Group"), gtext("Secondary")), $pconfig['initiatorgroup2'], array_merge(array("0" => gtext("None")), $ig_list), "", true);
					html_inputbox("comment", gtext("Comment"), $pconfig['comment'], gtext("You may enter a description here for your reference."), false, 40);
					$a_storage_add = [];
					$a_storage_edit = [];
					foreach ($a_iscsitarget_extent as $extent):
						$index = array_search_ex($extent['name'], $a_iscsitarget_target, "storage");
						if (false !== $index):
							continue;
						endif;
						$index = array_search_ex($extent['name'], $a_iscsitarget_device, "storage");
						if (false !== $index):
							continue;
						endif;
						foreach ($a_iscsitarget_target as $target):
							if (isset($target['lunmap'])):
								$index = array_search_ex($extent['name'], $target['lunmap'], "extentname");
								if (false !== $index):
									continue 2;
								endif;
							endif;
						endforeach;
						$a_storage_add[$extent['name']] = htmlspecialchars(sprintf("%s (%s)", $extent['name'], $extent['path']));
					endforeach;
					if (isset($uuid) && (FALSE !== $cnid)):
						// reload lunmap
						$pconfig['lunmap'] = $a_iscsitarget_target[$cnid]['lunmap'];
					endif;
					foreach($pconfig['lunmap'] as $lunmap):
						$index = array_search_ex($lunmap['extentname'], $a_iscsitarget_extent, "name");
						if(false !== $index):
							$extent = $a_iscsitarget_extent[$index];
							$a_storage_edit[$extent['name']] = htmlspecialchars(sprintf("%s (%s)", $extent['name'], $extent['path']));
						endif;
					endforeach;
					if(!(isset($uuid) && (FALSE !== $cnid))):
						// Add
						$a_storage = &$a_storage_add;
						$a_storage_opt=array_merge(array("-" => gtext("None")), $a_storage_add);
					else:
						// Edit
						$a_storage = &$a_storage_edit;
						$a_storage = array_merge($a_storage, $a_storage_add);
						$a_storage_opt = array_merge(array("-" => gtext("None")), $a_storage_edit);
					endif;
					html_separator();
					html_titleline(sprintf("%s%d", gtext("LUN"), 0));
					$index = array_search_ex("0", $pconfig['lunmap'], "lun");
					if(false !== $index):
						html_combobox("storage", gtext("Storage"), $pconfig['lunmap'][$index]['extentname'], $a_storage, sprintf(gtext("The storage area mapped to LUN%d."), 0), true);
						html_combobox("removable", gtext("Removable"), $pconfig['lunmap'][$index]['extentname'], $a_storage_opt, sprintf(gtext("The removable area mapped to LUN%d."), 0), true);
					endif;
					for($i = 1; $i < $MAX_LUNS; $i++):
						$lenable=sprintf("enable%d", $i);
						$lstorage=sprintf("storage%d", $i);
						$a_storage_opt_add=array_merge(array("-" => gtext("None")), $a_storage_add);
						$enabled = 0;
						$index = array_search_ex("$i", $pconfig['lunmap'], "lun");
						if(false !== $index):
							if ($pconfig['lunmap'][$index]['extentname'] !== "-"):
								$enabled = 1;
							endif;
						endif;
						if(!(isset($uuid) && (FALSE !== $cnid))):
							$a_storage_opt=array_merge(array("-" => gtext("None")), $a_storage_add);
						else:
							$a_storage_opt=array_merge(array("-" => gtext("None")), $a_storage_edit);
						endif;
						html_separator(2, sprintf("lun%d_separator", $i));
						html_titleline_checkbox("$lenable", sprintf("%s%d", gtext("LUN"), $i), $enabled ? true : false, gtext("Enable"), "lun_change($i)");
						$index = array_search_ex("$i", $pconfig['lunmap'], "lun");
						if(false !== $index):
							html_combobox("$lstorage", gtext("Storage"), $pconfig['lunmap'][$index]['extentname'], $a_storage_opt, sprintf(gtext("The storage area mapped to LUN%d."), $i), true);
						else:
							html_combobox("$lstorage", gtext("Storage"), "-", $a_storage_opt_add, sprintf(gtext("The storage area mapped to LUN%d."), $i), true);
						endif;
					endfor;
					html_separator();
					html_titleline(gtext("Advanced settings"));
					html_combobox("authmethod", gtext("Auth Method"), $pconfig['authmethod'], array("Auto" => gtext("Auto"), "CHAP" => gtext("CHAP"), "CHAP Mutual" => gtext("Mutual CHAP"), "None" => gtext("None")), gtext("The method can be accepted by the target. Auto means both none and authentication."), false);
					$ag_list = [];
					$ag_list['0'] = gtext("None");
					foreach($config['iscsitarget']['authgroup'] as $ag):
						if($ag['comment']):
							$l = sprintf(gtext("Tag%d (%s)"), $ag['tag'], $ag['comment']);
						else:
							$l = sprintf(gtext("Tag%d"), $ag['tag']);
						endif;
						$ag_list[$ag['tag']] = htmlspecialchars($l);
					endforeach;
					html_combobox("authgroup", gtext("Auth Group"), $pconfig['authgroup'], $ag_list, gtext("The initiator can access to the target with correct user and secret in specific Auth Group."), false);
					html_combobox("digest", gtext("Initial Digest"), $pconfig['digest'], array("Auto" => gtext("Auto"), "Header" => gtext("Header digest"), "Data" => gtext("Data digest"), "Header Data" => gtext("Header and Data digest")), gtext("The initial digest mode negotiated with the initiator."), false);
					html_inputbox("queuedepth", gtext("Queue Depth"), $pconfig['queuedepth'], gtext("0=disabled, 1-255=enabled command queuing with specified depth.")." ".sprintf(gtext("The recommended queue depth is %d."), 32), false, 10);
					html_checkbox("writecache", gtext("Write Cache"), !empty($pconfig['writecache']) ? true : false, gtext("Enable write cache mode."), gtext("It can be changed from the client side by standard SCSI mode page at any time."), false);
					html_inputbox("inqvendor", gtext("Inquiry Vendor"), $pconfig['inqvendor'], sprintf(gtext("You may specify as SCSI INQUIRY data. Empty as default. (up to %d ASCII chars)"), 8), false, 20);
					html_inputbox("inqproduct", gtext("Inquiry Product"), $pconfig['inqproduct'], sprintf(gtext("You may specify as SCSI INQUIRY data. Empty as default. (up to %d ASCII chars)"), 16), false, 20);
					html_inputbox("inqrevision", gtext("Inquiry Revision"), $pconfig['inqrevision'], sprintf(gtext("You may specify as SCSI INQUIRY data. Empty as default. (up to %d ASCII chars)"), 4), false, 20);
					html_inputbox("inqserial", gtext("Inquiry Serial"), $pconfig['inqserial'], sprintf(gtext("You may specify as SCSI INQUIRY data. Empty as default. (up to %d ASCII chars)"), 16), false, 20);
					if($MAX_BLOCKLEN > 512):
						$a_blocklen = [];
						for($x = 0; (512 << $x) <= $MAX_BLOCKLEN; $x++):
							$a_blocklen[(512 << $x)] = sprintf(gtext("%dB / block"), (512 << $x));
						endfor;
						?>
						<?php
						html_combobox("blocklen", gtext("Block Size"), $pconfig['blocklen'], $a_blocklen, sprintf(gtext("You may specify logical block size. (default is %dB for compatibility)."), 512), false, "");
					else:?>
						<input name="blocklen" type="hidden" value="512" />
					<?php endif; ?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" onclick="enable_change(true)" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
					<input name="addedit" type="hidden" value="<?=isset($uuid) ? 'edit' : 'add';?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
	type_change();
	enable_change();
<?php
	for ($i = 1; $i < $MAX_LUNS; $i++) {
		echo "lun_change($i);\n";
	}
?>
//-->
</script>
<?php include 'fend.inc';?>
