<?php
/*
	services_afp_share_edit.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
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

$pgtitle = array(gettext("Services"), gettext("AFP"), gettext("Share"), isset($uuid) ? gettext("Edit") : gettext("Add"));

if (!isset($config['mounts']['mount']) || !is_array($config['mounts']['mount']))
	$config['mounts']['mount'] = array();

if (!isset($config['afp']['share']) || !is_array($config['afp']['share']))
	$config['afp']['share'] = array();

array_sort_key($config['mounts']['mount'], "devicespecialfile");
$a_mount = &$config['mounts']['mount'];

array_sort_key($config['afp']['share'], "name");
$a_share = &$config['afp']['share'];

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_share, "uuid")))) {
	$pconfig['uuid'] = $a_share[$cnid]['uuid'];
	$pconfig['name'] = $a_share[$cnid]['name'];
	$pconfig['path'] = $a_share[$cnid]['path'];
	$pconfig['comment'] = $a_share[$cnid]['comment'];
	$pconfig['volpasswd'] = $a_share[$cnid]['volpasswd'];
	$pconfig['volcharset'] = $a_share[$cnid]['volcharset'];
	$pconfig['allow'] = $a_share[$cnid]['allow'];
	$pconfig['deny'] = $a_share[$cnid]['deny'];
	$pconfig['rolist'] = $a_share[$cnid]['rolist'];
	$pconfig['rwlist'] = $a_share[$cnid]['rwlist'];
        if (isset($a_share[$cnid]['auxparam']) && is_array($a_share[$cnid]['auxparam']))
		$pconfig['auxparam'] = implode("\n", $a_share[$cnid]['auxparam']);

} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = "";
	$pconfig['path'] = "";
	$pconfig['comment'] = "";
	$pconfig['volpasswd'] = '';
	$pconfig['volcharset'] = 'UTF8';
	$pconfig['allow'] = '';
	$pconfig['deny'] = '';
	$pconfig['rolist'] = '';
	$pconfig['rwlist'] = '';
        $pconfig['auxparam'] = "";
	
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: services_afp_share.php");
		exit;
	}

	// Input validation.
	$reqdfields = explode(" ", "name comment");
	$reqdfieldsn = array(gettext("Name"), gettext("Comment"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	$reqdfieldst = explode(" ", "string string");
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	// Verify that the share password is not more than 8 characters.
	if (strlen($_POST['volpasswd']) > 8) {
	    $input_errors[] = gettext("Share passwords can not be more than 8 characters.");
	}

	// Check for duplicates.
	$index = array_search_ex($_POST['name'], $a_share, "name");
	if (FALSE !== $index) {
		if (!((FALSE !== $cnid) && ($a_share[$cnid]['uuid'] === $a_share[$index]['uuid']))) {
			$input_errors[] = gettext("The share name is already used.");
		}
	}

	if (empty($input_errors)) {
		$share = array();
		$share['uuid'] = $_POST['uuid'];
		$share['name'] = $_POST['name'];
		$share['path'] = $_POST['path'];
		$share['comment'] = $_POST['comment'];
		$share['volpasswd'] = $_POST['volpasswd'];
		$share['volcharset'] = $_POST['volcharset'];
		$share['allow'] = $_POST['allow'];
		$share['deny'] = $_POST['deny'];
		$share['rolist'] = $_POST['rolist'];
		$share['rwlist'] = $_POST['rwlist'];		

# Write additional parameters.
		unset($share['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$share['auxparam'][] = $auxparam;
		}

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_share[$cnid] = $share;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_share[] = $share;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("afpshare", $mode, $share['uuid']);
		write_config();

		header("Location: services_afp_share.php");
		exit;
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function adisk_change() {
	switch (document.iform.adisk_enable.checked) {
		case false:
			showElementById('adisk_advf_tr','hide');
			break;

		case true:
			showElementById('adisk_advf_tr','show');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
        <li class="tabinact"><a href="services_afp.php"><span><?=gettext("Settings");?></span></a></li>
        <li class="tabact"><a href="services_afp_share.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Shares");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
			<form action="services_afp_share_edit.php" method="post" name="iform" id="iform">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			  <table width="100%" border="0" cellpadding="6" cellspacing="0">
			  	<tr>
			      <td width="22%" valign="top" class="vncellreq"><?=gettext("Name");?></td>
			      <td width="78%" class="vtable">
			        <input name="name" type="text" class="formfld" id="name" size="30" value="<?=htmlspecialchars($pconfig['name']);?>" />
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncellreq"><?=gettext("Comment");?></td>
			      <td width="78%" class="vtable">
			        <input name="comment" type="text" class="formfld" id="comment" size="30" value="<?=htmlspecialchars($pconfig['comment']);?>" />
			      </td>
			    </tr>
			    <tr>
					  <td width="22%" valign="top" class="vncellreq"><?=gettext("Path");?></td>
					  <td width="78%" class="vtable">
					  	<input name="path" type="text" class="formfld" id="path" size="60" value="<?=htmlspecialchars($pconfig['path']);?>" />
					  	<input name="browse" type="button" class="formbtn" id="Browse" onclick='ifield = form.path; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield;' value="..." /><br />
					  	<span class="vexpl"><?=gettext("Path to be shared.");?></span>
					  </td>
					</tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Share Password");?></td>
			      <td width="78%" class="vtable">
			        <input name="volpasswd" type="text" class="formfld" id="volpasswd" size="16" value="<?=htmlspecialchars($pconfig['volpasswd']);?>" />
			        <?=gettext("Set share password.");?><br />
			        <span class="vexpl"><?=gettext("This controls the access to this share with an access password.");?></span>
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Share Character Set");?></td>
			      <td width="78%" class="vtable">
			        <input name="volcharset" type="text" class="formfld" id="volcharset" size="16" value="<?=htmlspecialchars($pconfig['volcharset']);?>" /><br />
			        <span class="vexpl"><?=gettext("Specifies the share character set. For example UTF8, UTF8-MAC, ISO-8859-15, etc.");?></span>
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Allow");?></td>
			      <td width="78%" class="vtable">
			        <input name="allow" type="text" class="formfld" id="allow" size="60" value="<?=htmlspecialchars($pconfig['allow']);?>" /><br />
			        <?=gettext("This option allows the users and groups that access a share to be specified. Users and groups are specified, delimited by commas. Groups are designated by a @ prefix.");?>
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Deny");?></td>
			      <td width="78%" class="vtable">
			        <input name="deny" type="text" class="formfld" id="deny" size="60" value="<?=htmlspecialchars($pconfig['deny']);?>" /><br />
			        <?=gettext("The  deny  option specifies users and groups who are not allowed access to the share. It follows the same  format  as  the  allow option.");?>
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Read Only Access");?></td>
			      <td width="78%" class="vtable">
			        <input name="rolist" type="text" class="formfld" id="rolist" size="60" value="<?=htmlspecialchars($pconfig['rolist']);?>" /><br />
			        <?=gettext("Allows certain users and groups to have read-only  access  to  a share. This follows the allow option format.");?>
			      </td>
			    </tr>
			    <tr>
			      <td width="22%" valign="top" class="vncell"><?=gettext("Read/Write Access");?></td>
			      <td width="78%" class="vtable">
			        <input name="rwlist" type="text" class="formfld" id="rwlist" size="60" value="<?=htmlspecialchars($pconfig['rwlist']);?>" /><br />
			        <?=gettext("Allows  certain  users and groups to have read/write access to a share. This follows the allow option format.");?>
			      </td>
			    </tr>
				<tr>
					<?php html_textarea("auxparam", gettext("Auxiliary parameters"), $pconfig['auxparam'],sprintf(gettext("add any supplemental parameters")), false, 65, 5, false, false);?>
                                        </tr>
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
<script type="text/javascript">
<!--
adisk_change();
//-->
</script>
<?php include("fend.inc");?>
