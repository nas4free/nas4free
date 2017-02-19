<?php
/*
	system_rc_sort.php

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
$sphere_header_parent = 'Location: system_rc.php';
$sphere_notifier = 'rc';
$sphere_notifier_processor = 'rc_process_updatenotification';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];

$gt_record_add = gtext('Add command');
$gt_record_del = gtext('Command is marked for removal');
$gt_record_dow = gtext('Move down');
$gt_record_inf = gtext('Information');
$gt_record_loc = gtext('Command is protected');
$gt_record_mai = gtext('Maintenance');
$gt_record_mod = gtext('Edit command');
$gt_record_unl = gtext('Command is unlocked');
$gt_record_up = gtext('Move up');
$gt_selection_delete = gtext('Delete Selected Commands');
$gt_selection_delete_confirm = gtext('Do you want to delete selected commands?');
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
// sunrise: verify if setting exists, otherwise run init tasks
$sphere_array = &array_make_branch($config,'rc','param');
if($_POST):
	if(isset($_POST['Submit'])):
		if($_POST[$checkbox_member_name] && is_array($_POST[$checkbox_member_name])):
			$a_param =[];
			foreach($_POST[$checkbox_member_name] as $r_member):
				if(is_string($r_member)):
					if(false !== ($index = array_search_ex($r_member, $sphere_array, 'uuid'))):
						$a_param[] = $sphere_array[$index];
					endif;
				endif;
			endforeach;
			$sphere_array = $a_param;
			write_config();
		endif;
		header($sphere_header_parent);
		exit;
	endif;
endif;
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Command Scripts'),gtext('Sort')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	// Init move row capability
	$('#system_rc_list img.move').click(function() {
		var row = $(this).closest('table').closest('tr');
		if ($(this).hasClass('up')) row.prev().before(row);
		if ($(this).hasClass('down')) row.next().after(row);
	});
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="system_advanced.php"><span><?=gtext('Advanced');?></span></a></li>
		<li class="tabinact"><a href="system_email.php"><span><?=gtext('Email');?></span></a></li>
		<li class="tabinact"><a href="system_email_reports.php"><span><?=gtext("Email Reports");?></span></a></li>
		<li class="tabinact"><a href="system_monitoring.php"><span><?=gtext("Monitoring");?></span></a></li>
		<li class="tabinact"><a href="system_swap.php"><span><?=gtext('Swap');?></span></a></li>
		<li class="tabact"><a href="system_rc.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Command Scripts');?></span></a></li>
		<li class="tabinact"><a href="system_cron.php"><span><?=gtext('Cron');?></span></a></li>
		<li class="tabinact"><a href="system_loaderconf.php"><span><?=gtext('loader.conf');?></span></a></li>
		<li class="tabinact"><a href="system_rcconf.php"><span><?=gtext('rc.conf');?></span></a></li>
		<li class="tabinact"><a href="system_sysctl.php"><span><?=gtext('sysctl.conf');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(updatenotify_exists($sphere_notifier)):
		print_config_change_box();
	endif;
	?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:7%">
			<col style="width:18%">
			<col style="width:10%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Reorder Commands'), 7);?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Command');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhell"><?=gtext('Comment');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody id="system_rc_list">
			<?php foreach($sphere_array as $sphere_record):?>
				<?php
				$notificationmode = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$enabled = isset($sphere_record['enable']);
				$notprotected = !isset($sphere_record['protected']);
				switch ($sphere_record['typeid']) {
					case 1:
						$gt_type = gtext('PreInit');
						break;
					case 2:
						$gt_type = gtext('PostInit');
						break;
					case 3:
						$gt_type = gtext('Shutdown');
						break;
					default:
						$gt_type = gtext('Unknown');
						break;
				}
				?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
						<input type="hidden" name="<?=$checkbox_member_name;?>[]" value="<?=$sphere_record['uuid'];?>" id="<?=$sphere_record['uuid'];?>"/>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['name']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['value']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>">
						<?php if ($enabled):?>
							<a title="<?=gtext('Enabled');?>"><center><img src="<?=$img_path['ena'];?>"/></center></a>
						<?php else:?>
							<a title="<?=gtext('Disabled');?>"><center><img src="<?=$img_path['dis'];?>"/></center></a>
						<?php endif;?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere_record['comment']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=$gt_type;?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
								<img src="<?=$img_path['up'];?>" title="<?=$gt_record_up;?>" alt="<?=$gt_record_up;?>" class="move up"/>
								<img src="<?=$img_path['dow'];?>" title="<?=$gt_record_dow;?>" alt="<?=$gt_record_dow;?>" class="move down"/>
							</td>
						</tr></tbody></table>
					</td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" id="reorder_rows" type="submit" class="formbtn" value="<?=gtext('Reorder Commands');?>"/>
	</div>
	<div id="remarks">
		<?php html_remark('note', gtext('Note'), gtext('These commands will be executed pre or post system initialization (booting) or before system shutdown.'));?>
	</div>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
