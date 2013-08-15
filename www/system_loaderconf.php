<?php
/*
	system_loaderconf.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gettext("System"), gettext("Advanced"), gettext("loader.conf"));

if ($_POST) {
	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;

		if (!file_exists($d_sysrebootreqd_path)) {
			touch($d_sysrebootreqd_path);
		}

		$retval |= updatenotify_process("loaderconf", "loaderconf_process_updatenotification");
		$savemsg = get_std_save_message($retval);

		if ($retval == 0) {
			updatenotify_delete("loaderconf");
		}
	}
}

if (!isset($config['system']['loaderconf']['param']) || !is_array($config['system']['loaderconf']['param']))
	$config['system']['loaderconf']['param'] = array();

array_sort_key($config['system']['loaderconf']['param'], "name");
$loader_param_list = &$config['system']['loaderconf']['param'];

if (isset($_GET['act']) && $_GET['act'] === "del") {
	if ($_GET['id'] === "all") {
		foreach ($loader_param_list as $param_key => $param_value) {
			updatenotify_set("loaderconf", UPDATENOTIFY_MODE_DIRTY, $loader_param_list[$param_key]['uuid']);
		}
	} else {
		updatenotify_set("loaderconf", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	}
	header("Location: system_loaderconf.php");
	exit;
}

function loaderconf_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			write_loader_config();
			write_config();
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['system']['loaderconf']['param'])) {
				$index = array_search_ex($data, $config['system']['loaderconf']['param'], "uuid");
				if (false !== $index) {
					unset($config['system']['loaderconf']['param'][$index]);
					write_loader_config();
					write_config();
				}
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
      	<li class="tabinact"><a href="system_advanced.php"><span><?=gettext("Advanced");?></span></a></li>
      	<li class="tabinact"><a href="system_email.php"><span><?=gettext("Email");?></span></a></li>
      	<li class="tabinact"><a href="system_proxy.php"><span><?=gettext("Proxy");?></span></a></li>
      	<li class="tabinact"><a href="system_swap.php"><span><?=gettext("Swap");?></span></a></li>
        <li class="tabinact"><a href="system_rc.php"><span><?=gettext("Command scripts");?></span></a></li>
        <li class="tabinact"><a href="system_cron.php"><span><?=gettext("Cron");?></span></a></li>
		<li class="tabact"><a href="system_loaderconf.php" title="<?=gettext("Reload page");?>"><span><?=gettext("loader.conf");?></span></a></li>
        <li class="tabinact"><a href="system_rcconf.php"><span><?=gettext("rc.conf");?></span></a></li>
        <li class="tabinact"><a href="system_sysctl.php"><span><?=gettext("sysctl.conf");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
    	<form action="system_loaderconf.php" method="post">
    		<?php if (!empty($savemsg)) print_info_box($savemsg);?>
	    	<?php if (updatenotify_exists("loaderconf")) print_config_change_box();?>
	      <table width="100%" border="0" cellpadding="0" cellspacing="0">
	        <tr>
	          <td width="40%" class="listhdrlr"><?=gettext("Variable");?></td>
	          <td width="20%" class="listhdrr"><?=gettext("Value");?></td>
	          <td width="30%" class="listhdrr"><?=gettext("Comment");?></td>
	          <td width="10%" class="list"></td>
	        </tr>
				  <?php foreach($loader_param_list as $param):?>
				  <?php $notificationmode = updatenotify_get_mode("loaderconf", $param['uuid']);?>
	        <tr>
	        	<?php $enable = isset($param['enable']);?>
	          <td class="<?=$enable?"listlr":"listlrd";?>"><?=htmlspecialchars($param['name']);?>&nbsp;</td>
	          <td class="<?=$enable?"listr":"listrd";?>"><?=htmlspecialchars($param['value']);?>&nbsp;</td>
	          <td class="listbg"><?=htmlspecialchars($param['comment']);?>&nbsp;</td>
	          <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
	          <td valign="middle" nowrap="nowrap" class="list">
	            <a href="system_loaderconf_edit.php?uuid=<?=$param['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit option");?>" border="0" alt="<?=gettext("Edit option");?>" /></a>
	            <a href="system_loaderconf.php?act=del&amp;uuid=<?=$param['uuid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this option?");?>')"><img src="x.gif" title="<?=gettext("Delete option");?>" border="0" alt="<?=gettext("Delete option");?>" /></a>
	          </td>
	          <?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="del.gif" border="0" alt="" />
						</td>
						<?php endif;?>
	        </tr>
	        <?php endforeach;?>
	        <tr>
	          <td class="list" colspan="3"></td>
	          <td class="list">
							<a href="system_loaderconf_edit.php"><img src="plus.gif" title="<?=gettext("Add option");?>" border="0" alt="<?=gettext("Add option");?>" /></a>
	          	<?php if (!empty($loader_param_list)):?>
							<a href="system_loaderconf.php?act=del&amp;id=all" onclick="return confirm('<?=gettext("Do you really want to delete all options?");?>')"><img src="x.gif" title="<?=gettext("Delete all options");?>" border="0" alt="<?=gettext("Delete all options");?>" /></a>
							<?php endif;?>
						</td>
	        </tr>
	      </table>
	      <div id="remarks">
	      	<?php html_remark("note", gettext("Note"), gettext("These option(s) will be added to /boot/loader.conf.local. This allows you to specify parameters to be passed to kernel, and additional modules to be loaded."));?>
	      </div>
	      <?php include("formend.inc");?>
			</form>
	  </td>
  </tr>
</table>
<?php include("fend.inc");?>
