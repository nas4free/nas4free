<?php
/*
	system_rc.php

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

$pgtitle = array(gtext("System"),gtext("Advanced"),gtext("Command Scripts"));

if (!isset($config['rc']['preinit']['cmd']) || !is_array($config['rc']['preinit']['cmd']))
	$config['rc']['preinit']['cmd'] = array();

if (!isset($config['rc']['postinit']['cmd']) || !is_array($config['rc']['postinit']['cmd']))
	$config['rc']['postinit']['cmd'] = array();

if (!isset($config['rc']['shutdown']['cmd']) || !is_array($config['rc']['shutdown']['cmd']))
	$config['rc']['shutdown']['cmd'] = array();

if (isset($_GET['act']) && $_GET['act'] == "del")
{
	switch($_GET['type']) {
		case "PREINIT":
			$a_cmd = &$config['rc']['preinit']['cmd'];
			break;
		case "POSTINIT":
			$a_cmd = &$config['rc']['postinit']['cmd'];
			break;
		case "SHUTDOWN":
			$a_cmd = &$config['rc']['shutdown']['cmd'];
			break;
	}

	if ($a_cmd[$_GET['id']]) {
		unset($a_cmd[$_GET['id']]);
		write_config();
		header("Location: system_rc.php");
		exit;
	}
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
      	<li class="tabinact"><a href="system_advanced.php"><span><?=gtext("Advanced");?></span></a></li>
      	<li class="tabinact"><a href="system_email.php"><span><?=gtext("Email");?></span></a></li>
      	<li class="tabinact"><a href="system_swap.php"><span><?=gtext("Swap");?></span></a></li>
        <li class="tabact"><a href="system_rc.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Command Scripts");?></span></a></li>
        <li class="tabinact"><a href="system_cron.php"><span><?=gtext("Cron");?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext("loader.conf");?></span></a></li>
        <li class="tabinact"><a href="system_rcconf.php"><span><?=gtext("rc.conf");?></span></a></li>
        <li class="tabinact"><a href="system_sysctl.php"><span><?=gtext("sysctl.conf");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="80%" class="listhdrlr"><?=gtext("Command");?></td>
          <td width="10%" class="listhdrr"><?=gtext("Type");?></td>
          <td width="10%" class="list"></td>
        </tr>
			  <?php $i = 0; foreach($config['rc']['preinit']['cmd'] as $cmd): ?>
        <tr>
          <td class="listlr"><?=htmlspecialchars($cmd);?>&nbsp;</td>
          <td class="listbg"><?php echo(gtext("PreInit"));?>&nbsp;</td>
          <td valign="middle" nowrap="nowrap" class="list">
            <a href="system_rc_edit.php?id=<?=$i;?>&amp;type=PREINIT"><img src="images/edit.png" title="<?=gtext("Edit command");?>" border="0" alt="<?=gtext("Edit command");?>" /></a>&nbsp;
            <a href="system_rc.php?act=del&amp;id=<?=$i;?>&amp;type=PREINIT" onclick="return confirm('<?=gtext("Do you really want to delete this command?");?>')"><img src="images/delete.png" title="<?=gtext("Delete command");?>" border="0" alt="<?=gtext("Delete command");?>" /></a>
          </td>
        </tr>
        <?php $i++; endforeach;?>
        <?php $i = 0; foreach($config['rc']['postinit']['cmd'] as $cmd): ?>
        <tr>
          <td class="listlr"><?=htmlspecialchars($cmd);?>&nbsp;</td>
          <td class="listbg"><?php echo(gtext("PostInit"));?>&nbsp;</td>
          <td valign="middle" nowrap="nowrap" class="list">
            <a href="system_rc_edit.php?id=<?=$i;?>&amp;type=POSTINIT"><img src="images/edit.png" title="<?=gtext("Edit command");?>" border="0" alt="<?=gtext("Edit command");?>" /></a>&nbsp;
            <a href="system_rc.php?act=del&amp;id=<?=$i;?>&amp;type=POSTINIT" onclick="return confirm('<?=gtext("Do you really want to delete this command?");?>')"><img src="images/delete.png" title="<?=gtext("Delete command");?>" border="0" alt="<?=gtext("Delete command");?>" /></a>
          </td>
        </tr>
        <?php $i++; endforeach;?>
        <?php $i = 0; foreach($config['rc']['shutdown']['cmd'] as $cmd): ?>
        <tr>
          <td class="listlr"><?=htmlspecialchars($cmd);?>&nbsp;</td>
          <td class="listbg"><?php echo(gtext("Shutdown"));?>&nbsp;</td>
          <td valign="middle" nowrap="nowrap" class="list">
            <a href="system_rc_edit.php?id=<?=$i;?>&amp;type=SHUTDOWN"><img src="images/edit.png" title="<?=gtext("Edit command");?>" border="0" alt="<?=gtext("Edit command");?>" /></a>&nbsp;
            <a href="system_rc.php?act=del&amp;id=<?=$i;?>&amp;type=SHUTDOWN" onclick="return confirm('<?=gtext("Do you really want to delete this command?");?>')"><img src="images/delete.png" title="<?=gtext("Delete command");?>" border="0" alt="<?=gtext("Delete command");?>" /></a>
          </td>
        </tr>
        <?php $i++; endforeach;?>
        <tr>
          <td class="list" colspan="2"></td>
          <td class="list"><a href="system_rc_edit.php"><img src="images/add.png" title="<?=gtext("Add command");?>" border="0" alt="<?=gtext("Add command");?>" /></a></td>
        </tr>
      </table>
      <div id="remarks">
      	<?php html_remark("note", gtext("Note"), gtext("These commands will be executed pre or post system initialization (booting) or before system shutdown."));?>
      </div>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
