<?php
/*
	system_rc_edit.php

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
$sphere_array = [];
$sphere_record = [];
$prerequisites_ok = true;

$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD); // detect page mode
if(PAGE_MODE_POST == $mode_page): // POST is Cancel or not Submit => cleanup
	if((isset($_POST['Cancel']) && $_POST['Cancel']) || !(isset($_POST['Submit']) && $_POST['Submit'])):
		header($sphere_header_parent);
		exit;
	endif;
endif;

if((PAGE_MODE_POST == $mode_page) && isset($_POST['uuid']) && is_uuid_v4($_POST['uuid'])):
	$sphere_record['uuid'] = $_POST['uuid'];
else:
	if((PAGE_MODE_EDIT == $mode_page) && isset($_GET['uuid']) && is_uuid_v4($_GET['uuid'])):
		$sphere_record['uuid'] = $_GET['uuid'];
	else:
		$mode_page = PAGE_MODE_ADD; // Force ADD
		$sphere_record['uuid'] = uuid();
	endif;
endif;

$sphere_array = &array_make_branch($config,'rc','param');
$index = array_search_ex($sphere_record['uuid'], $sphere_array, 'uuid'); // find index of uuid
$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']); // get updatenotify mode for uuid
$mode_record = RECORD_ERROR;
if(false !== $index): // uuid found
	if ((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))): // POST or EDIT
		switch ($mode_updatenotify):
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		endswitch;
	endif;
else: // uuid not found
	if ((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page)): // POST or ADD
		switch ($mode_updatenotify):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		endswitch;
	endif;
endif;
if(RECORD_ERROR == $mode_record): // oops, someone tries to cheat, over and out
	header($sphere_header_parent);
	exit;
endif;
if(PAGE_MODE_POST == $mode_page): // We know POST is "Submit", already checked
	unset($input_errors);
	$sphere_record['enable'] = isset($_POST['enable']) ? true : false;
	$sphere_record['protected'] = isset($_POST['protected']) ? true : false;
	$sphere_record['name'] = $_POST['name'] ?? '';
	$sphere_record['value'] = $_POST['value'] ?? '';
	$sphere_record['comment'] = $_POST['comment'] ?? '';
	$sphere_record['typeid'] = $_POST['typeid'] ?? '';
	// Input validation.
	$reqdfields = ['value', 'typeid'];
	$reqdfieldsn = [gtext('Command'), gtext('Type')];
	$reqdfieldst = ['string', 'string'];
	do_input_validation($sphere_record, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($sphere_record, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	if ($prerequisites_ok && empty($input_errors)):
		if(RECORD_NEW == $mode_record):
			$sphere_array[] = $sphere_record;
			updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_NEW, $sphere_record['uuid']);
		else:
			$sphere_array[$index] = $sphere_record;
			if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
				updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
			endif;
		endif;
		write_config();
		header($sphere_header_parent);
		exit;
	endif;
else: // EDIT / ADD
	switch ($mode_record):
		case RECORD_NEW:
			$sphere_record['enable'] = true;
			$sphere_record['protected'] = false;
			$sphere_record['name'] = '';
			$sphere_record['value'] = '';
			$sphere_record['comment'] = '';
			$sphere_record['typeid'] = '1';
			break;
		case RECORD_NEW_MODIFY:
		case RECORD_MODIFY:
			$sphere_record['enable'] = isset($sphere_array[$index]['enable']);
			$sphere_record['protected'] = isset($sphere_array[$index]['protected']);
			$sphere_record['name'] = trim($sphere_array[$index]['name']);
			$sphere_record['value'] = $sphere_array[$index]['value'];
			$sphere_record['comment'] = trim($sphere_array[$index]['comment']);
			$sphere_record['typeid'] = $sphere_array[$index]['typeid'];
			break;
	endswitch;
endif;
$l_type = [
	'1' => gtext('PreInit'),
	'2' => gtext('PostInit'),
	'3' => gtext('Shutdown')
];
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Command Scripts'),(RECORD_NEW !== $mode_record) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<table id="area_navigator">
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
</table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if (!empty($errormsg)) { print_error_box($errormsg); }
	if (!empty($input_errors)) { print_input_errors($input_errors); }
	if (file_exists($d_sysrebootreqd_path)) { print_info_box(get_std_save_message(0)); }
	?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline_checkbox2('enable', gtext('Settings'), $sphere_record['enable'], gtext('Enable'));?>
		</thead>
		<tbody>
			<?php
			html_inputbox2('name',gtext('Name'),$sphere_record['name'],gtext('Enter a name for the command.'),false, 40,false,false,40,gtext('Enter a name'));
			html_inputbox2('value',gtext('Command'),$sphere_record['value'],gtext('The command to be executed.'),true,60,false,false,256,gtext('Enter the command'));
			html_inputbox2('comment',gtext('Comment'),$sphere_record['comment'],gtext('Enter a description for your reference.'),false,60,false,false,60,gtext('Enter a comment'));
			html_combobox2('typeid',gtext('Type'),$sphere_record['typeid'],$l_type,gtext('Execute command pre or post system initialization (booting) or before system shutdown.'),true,isset($pconfig['type']));
			?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=($isrecordnew) ? gtext('Add') : gtext('Save');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>"/>
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>"/>
	</div>
<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
