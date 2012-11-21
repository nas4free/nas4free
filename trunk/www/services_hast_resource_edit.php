<?php
/*
	services_hast_resource_edit.php

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

$pgtitle = array(gettext("Services"), gettext("HAST"), isset($uuid) ? gettext("Edit") : gettext("Add"));

if (!isset($config['hast']['hastresource']) || !is_array($config['hast']['hastresource']))
	$config['hast']['hastresource'] = array();

array_sort_key($config['hast']['hastresource'], "name");
$a_resource = &$config['hast']['hastresource'];

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_resource, "uuid")))) {
	$pconfig['uuid'] = $a_resource[$cnid]['uuid'];
	$pconfig['name'] = $a_resource[$cnid]['name'];
	$pconfig['auxparam'] = "";
	if (isset($a_resource[$cnid]['auxparam']) && is_array($a_resource[$cnid]['auxparam']))
		$pconfig['auxparam'] = implode("\n", $a_resource[$cnid]['auxparam']);
	$pconfig['aname'] = $a_resource[$cnid]['aname'];
	$pconfig['apath'] = $a_resource[$cnid]['apath'];
	$pconfig['aremoteaddr'] = $a_resource[$cnid]['aremoteaddr'];
	$pconfig['bname'] = $a_resource[$cnid]['bname'];
	$pconfig['bpath'] = $a_resource[$cnid]['bpath'];
	$pconfig['bremoteaddr'] = $a_resource[$cnid]['bremoteaddr'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = "";
	$pconfig['auxparam'] = "";
	$pconfig['aname'] = "";
	$pconfig['apath'] = "";
	$pconfig['aremoteaddr'] = "";
	$pconfig['bname'] = "";
	$pconfig['bpath'] = "";
	$pconfig['bremoteaddr'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: services_hast_resource.php");
		exit;
	}

	// Input validation.
	$reqdfields = explode(" ", "name aname bname apath bpath aremoteaddr bremoteaddr");
	$reqdfieldsn = array(gettext("Resource name"),
				gettext("Node Name"), gettext("Node Name"),
				gettext("Path"), gettext("Path"),
				gettext("Node B IP address"), gettext("Node A IP address"));
	$reqdfieldst = explode(" ", "alias string string string string string string");
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (empty($input_errors)) {
		$resource = array();
		$resource['uuid'] = $_POST['uuid'];
		$resource['name'] = $_POST['name'];
		$resource['aname'] = $_POST['aname'];
		$resource['apath'] = $_POST['apath'];
		$resource['aremoteaddr'] = $_POST['aremoteaddr'];
		$resource['bname'] = $_POST['bname'];
		$resource['bpath'] = $_POST['bpath'];
		$resource['bremoteaddr'] = $_POST['bremoteaddr'];

		unset($resource['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$resource['auxparam'][] = $auxparam;
		}

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_resource[$cnid] = $resource;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_resource[] = $resource;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("hastresource", $mode, $resource['uuid']);
		write_config();

		header("Location: services_hast_resource.php");
		exit;
	}
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
	<li class="tabinact"><a href="services_hast.php"><span><?=gettext("Settings");?></span></a></li>
	<li class="tabact"><a href="services_hast_resource.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Resources");?></span></a></li>
	<li class="tabinact"><a href="services_hast_info.php"><span><?=gettext("Information");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <form action="services_hast_resource_edit.php" method="post" name="iform" id="iform">
        <?php if ($input_errors) print_input_errors($input_errors);?>
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
	<?php html_titleline(gettext("HAST resource"));?>
	<?php html_inputbox("name", gettext("Resource name"), $pconfig['name'], "", false, 30);?>
	<?php html_textarea("auxparam", gettext("Auxiliary parameters"), $pconfig['auxparam'], sprintf(gettext("These parameters are added to %s."), "hast.conf") . " " . sprintf(gettext("Please check the <a href='%s' target='_blank'>documentation</a>."), "http://www.freebsd.org/cgi/man.cgi?query=hast.conf&sektion=5"), false, 65, 5, false, false);?>
	<?php html_separator();?>
	<?php html_titleline(gettext("Node A settings"));?>
	<?php html_inputbox("aname", gettext("Node Name"), $pconfig['aname'], "", false, 40);?>
	<?php html_inputbox("apath", gettext("Path"), $pconfig['apath'], sprintf(gettext("Path to the local device. (e.g. %s)"), "/dev/da1"), false, 40);?>
	<?php html_inputbox("aremoteaddr", gettext("Node B IP address"), $pconfig['aremoteaddr'], gettext("Address of the remote hastd daemon. It must be a static IP address."), false, 40);?>
	<?php html_separator();?>
	<?php html_titleline(gettext("Node B settings"));?>
	<?php html_inputbox("bname", gettext("Node Name"), $pconfig['bname'], "", false, 40);?>
	<?php html_inputbox("bpath", gettext("Path"), $pconfig['bpath'], sprintf(gettext("Path to the local device. (e.g. %s)"), "/dev/da1"), false, 40);?>
	<?php html_inputbox("bremoteaddr", gettext("Node A IP address"), $pconfig['bremoteaddr'], gettext("Address of the remote hastd daemon. It must be a static IP address."), false, 40);?>
	</table>
	<div id="submit">
	  <input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gettext("Save") : gettext("Add")?>" />
	  <input name="Cancel" type="submit" class="formbtn" value="<?=gettext("Cancel");?>" />
	  <input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
