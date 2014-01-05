<?php
/*
	services_hast_resource.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gettext("Services"), gettext("HAST"), gettext("Resources"));

if ($_POST) {
	$pconfig = $_POST;

	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("hastresource", "hastresource_process_updatenotification");
			config_lock();
			$retval |= rc_update_service("hastd");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("hastresource");
		}
	}
}

if (!isset($config['hast']['hastresource']) || !is_array($config['hast']['hastresource']))
	$config['hast']['hastresource'] = array();

array_sort_key($config['hast']['hastresource'], "name");
$a_resource = &$config['hast']['hastresource'];

if (isset($_GET['act']) && $_GET['act'] === "del") {
	updatenotify_set("hastresource", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	header("Location: services_hast_resource.php");
	exit;
}

function hastresource_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = array_search_ex($data, $config['hast']['hastresource'], "uuid");
			if (FALSE !== $cnid) {
				unset($config['hast']['hastresource'][$cnid]);
				write_config();
			}
			break;
	}

	return $retval;
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
      <form action="services_hast_resource.php" method="post">
	<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	<?php if (updatenotify_exists("hastresource")) print_config_change_box();?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td width="12%" class="listhdrlr"><?=gettext("Resource");?></td>
	    <td width="10%" class="listhdrr"><?=gettext("Role");?></td>
	    <td width="10%" class="listhdrr"><?=gettext("Status");?></td>
	    <td width="20%" class="listhdrr"><?=gettext("Node Name");?></td>
	    <td width="22%" class="listhdrr"><?=gettext("Path");?></td>
	    <td width="20%" class="listhdrr"><?=gettext("IP address");?></td>
	    <td width="6%" class="list"></td>
	  </tr>
	  <?php foreach ($a_resource as $resourcev):?>
	  <?php $hvolinfo = get_hvol_info($resourcev['name']); ?>
	  <?php $notificationmode = updatenotify_get_mode("hastresource", $resourcev['uuid']);?>
	  <tr>
	    <td class="listlr"><?=htmlspecialchars($resourcev['name']);?>&nbsp;</td>
	    <td class="listr"><?=htmlspecialchars($hvolinfo['role']);?>&nbsp;</td>
	    <td class="listr"><?=htmlspecialchars($hvolinfo['status']);?>&nbsp;</td>
	    <td class="listr"><?=htmlspecialchars($resourcev['aname']);?><br /><?=htmlspecialchars($resourcev['bname']);?>&nbsp;</td>
	    <td class="listr"><?=htmlspecialchars($resourcev['apath']);?><br /><?=htmlspecialchars($resourcev['bpath']);?>&nbsp;</td>
	    <td class="listr"><?=htmlspecialchars($resourcev['aremoteaddr']);?><br /><?=htmlspecialchars($resourcev['bremoteaddr']);?>&nbsp;</td>

	    <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
	    <td valign="middle" nowrap="nowrap" class="list">
	      <a href="services_hast_resource_edit.php?uuid=<?=$resourcev['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit resource");?>" border="0" alt="<?=gettext("Edit resource");?>" /></a>
	      <a href="services_hast_resource.php?act=del&amp;uuid=<?=$resourcev['uuid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this resource?");?>')"><img src="x.gif" title="<?=gettext("Delete resource");?>" border="0" alt="<?=gettext("Delete resource");?>" /></a>
	    </td>
	    <?php else:?>
	    <td valign="middle" nowrap="nowrap" class="list">
	      <img src="del.gif" border="0" alt="" />
	    </td>
	    <?php endif;?>
	  </tr>
	  <?php endforeach;?>
	  <tr>
	    <td class="list" colspan="6"></td>
	    <td class="list"><a href="services_hast_resource_edit.php"><img src="plus.gif" title="<?=gettext("Add resource");?>" border="0" alt="<?=gettext("Add resource");?>" /></a></td>
	  </tr>
	</table>
	<div id="submit">
	  <input id="reload" name="reload" type="submit" class="formbtn" value="<?php echo gettext("Reload page"); ?>" />
	</div>
	<?php include("formend.inc");?>
      </form>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
