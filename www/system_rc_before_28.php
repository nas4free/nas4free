<?php
/*
	system_rc_before_28.php

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

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_regular = 'Location: system_rc.php';
$sphere_array = [];
$gt_selection_delete = gtext('Delete command');
$gt_selection_delete_confirm = gtext('Do you want to delete this command?');
$img_path = [
	'add' => 'images/add.png',
	'del' => 'images/delete.png',
	'dow' => 'images/down.png',
	'inf' => 'images/info.png',
	'loc' => 'images/locked.png',
	'mai' => 'images/maintain.png',
	'mod' => 'images/edit.png',
	'unl' => 'images/unlocked.png',
	'up' => 'images/up.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png'
];
$oldstufffound = false;
if(!(isset($config['rc']['preinit']['cmd']) && is_array($config['rc']['preinit']['cmd']))):
	$sphere_array['preinit'] = [];
else:
	$sphere_array['preinit'] = &$config['rc']['preinit']['cmd'];
	$oldstufffound = true;
endif;
$sphere_array['postinit'] = [];
if(!(isset($config['rc']['postinit']['cmd']) && is_array($config['rc']['postinit']['cmd']))):
	$sphere_array['postinit'] = [];
else:
	$sphere_array['postinit'] = &$config['rc']['postinit']['cmd'];
	$oldstufffound = true;
endif;
$sphere_array['shutdown'] = [];
if(!(isset($config['rc']['shutdown']['cmd']) && is_array($config['rc']['shutdown']['cmd']))):
	$sphere_array['shutdown'] = [];
else:
	$sphere_array['shutdown'] = &$config['rc']['shutdown']['cmd'];
	$oldstufffound = true;
endif;
if(!$oldstufffound):
	header($sphere_header_regular);
	exit;
endif;

if(isset($_GET['act']) && $_GET['act'] == 'del'):
	switch($_GET['type']):
		case "PREINIT":
			$a_cmd = &$sphere_array['preinit'];
			break;
		case "POSTINIT":
			$a_cmd = &$sphere_array['postinit'];
			break;
		case "SHUTDOWN":
			$a_cmd = &$sphere_array['shutdown'];
			break;
	endswitch;
	if($a_cmd[$_GET['id']]):
		unset($a_cmd[$_GET['id']]);
		write_config();
		header($sphere_header);
		exit;
	endif;
endif;
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Command Scripts')];
?>
<?php include 'fbegin.inc';?>
<table id="area_navigator"><tbody>
	<tr>
	<td class="tabnavtbl">
		<ul id="tabnav">
		<li class="tabinact"><a href="system_advanced.php"><span><?=gtext('Advanced');?></span></a></li>
		<li class="tabinact"><a href="system_email.php"><span><?=gtext('Email');?></span></a></li>
		<li class="tabinact"><a href="system_email_reports.php"><span><?=gtext("Email Reports");?></span></a></li>
		<li class="tabinact"><a href="system_monitoring.php"><span><?=gtext("Monitoring");?></span></a></li>
		<li class="tabinact"><a href="system_swap.php"><span><?=gtext('Swap');?></span></a></li>
		<li class="tabinact"><a href="system_rc.php" ><span><?=gtext('Command Scripts');?></span></a></li>
		<li class="tabact"><a href="system_rc_before_28.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Command Scripts (Maintenance Only)');?></span></a></li>
		<li class="tabinact"><a href="system_cron.php"><span><?=gtext('Cron');?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext('loader.conf');?></span></a></li>
		<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext('rc.conf');?></span></a></li>
		<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext('sysctl.conf');?></span></a></li>
		</ul>
	</td>
</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_selection">
		<colgroup>
			<col style="width:80%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Overview'), 3);?>
			<tr>
				<td class="lhell"><?=gtext("Command");?></td>
				<td class="lhell"><?=gtext("Type");?></td>
				<td class="lhebl"><?=gtext('Toolbox');?></td>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0; foreach($sphere_array['preinit'] as $cmd):?>
				<tr>
					<td class="lcelld"><?=htmlspecialchars($cmd);?>&nbsp;</td>
					<td class="lcelld"><?=gtext("PreInit");?>&nbsp;</td>
					<td class="lcebld">
						<a href="<?=$sphere_scriptname;?>?act=del&amp;id=<?=$i;?>&amp;type=PREINIT" onclick="return confirm('<?=$gt_selection_delete_confirm;?>')"><img src="<?=$img_path['del'];?>" title="<?=$gt_selection_delete;?>" border="0" alt="<?=$gt_selection_delete;?>"/></a>
					</td>
				</tr>
			<?php $i++; endforeach;?>
			<?php $i = 0; foreach($sphere_array['postinit'] as $cmd): ?>
				<tr>
					<td class="lcelld"><?=htmlspecialchars($cmd);?>&nbsp;</td>
					<td class="lcelld"><?=gtext("PostInit");?>&nbsp;</td>
					<td class="lcebld">
						<a href="<?=$sphere_scriptname;?>?act=del&amp;id=<?=$i;?>&amp;type=POSTINIT" onclick="return confirm('<?=$gt_selection_delete_confirm;?>')"><img src="<?=$img_path['del'];?>" title="<?=$gt_selection_delete;?>" border="0" alt="<?=$gt_selection_delete;?>"/></a>
					</td>
				</tr>
			<?php $i++; endforeach;?>
			<?php $i = 0; foreach($sphere_array['shutdown'] as $cmd): ?>
				<tr>
					<td class="lcelld"><?=htmlspecialchars($cmd);?>&nbsp;</td>
					<td class="lcelld"><?=gtext("Shutdown");?>&nbsp;</td>
					<td class="lcebld">
						<a href="<?=$sphere_scriptname;?>?act=del&amp;id=<?=$i;?>&amp;type=SHUTDOWN" onclick="return confirm('<?=$gt_selection_delete_confirm;?>')"><img src="<?=$img_path['del'];?>" title="<?=$gt_selection_delete;?>" border="0" alt="<?=$gt_selection_delete;?>"/></a>
					</td>
				</tr>
			<?php $i++; endforeach;?>
		</tbody>
	</table>
	<div id="remarks">
		<?php html_remark('note', gtext('Note'), gtext('This page is for maintenance only.'));?>
	</div>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
