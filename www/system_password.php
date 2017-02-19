<?php
/*
	system_password.php

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
$sphere_header_parent = 'Location: index.php';
$savemsg = '';

array_make_branch($config,'system');
array_make_branch($config,'websrv','authentication');

$mode_page = ($_POST) ? PAGE_MODE_POST : PAGE_MODE_EDIT; // detect page mode
if(PAGE_MODE_POST === $mode_page): // POST is Cancel or not Submit => cleanup
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'save':
				break;
			case 'cancel':
				header($sphere_header_parent);
				exit;
				break;
			default:
				header($sphere_header_parent);
				exit;
				break;
		endswitch;
	endif;
endif;
switch($mode_page):
	case PAGE_MODE_POST:
		unset($input_errors);
		$sphere_record = [];
		$sphere_record['password_old'] = $_POST['password_old'] ?? '';
		$sphere_record['password_new'] = $_POST['password_new'] ?? '';
		$sphere_record['password_confirm'] = $_POST['password_confirm'] ?? '';
		$reqdfields = ['password_old','password_new','password_confirm'];
		$reqdfieldsn = [gtext('Current Password'),gtext('New Password'),gtext('Confirm New Password')];
		$reqdfieldst = ['password','password','password'];
		do_input_validation($sphere_record,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($sphere_record,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		//	Validate current password.
		if(!password_verify($sphere_record['password_old'],$config['system']['password'])):
			$input_errors[] = gtext('Current password is incorrectly entered.');
		endif;
		//	Validate new password.
		if($sphere_record['password_new'] !== $sphere_record['password_confirm']):
			$input_errors[] = gtext('New Password does not match the confirmation password. Please ensure both passwords are the same.');
		endif;
		//	Check Webserver document root if auth is required
		if(isset($config['websrv']['enable']) &&
				isset($config['websrv']['authentication']['enable']) &&
				!is_dir($config['websrv']['documentroot'])):
			$input_errors[] = gtext('Webserver document root is missing.');
		endif;
		//	apply settings, no errors found
		if(empty($input_errors)):
			$config['system']['password'] = mkpasswd($sphere_record['password_new']);
			write_config();
			$retval = 0;
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval |= rc_exec_service('userdb');
				$retval |= rc_exec_service('htpasswd');
				$retval |= rc_exec_service('websrv_htpasswd');
				$retval |= rc_exec_service('fmperm');
				config_unlock();
			endif;
			$savemsg = get_std_save_message($retval);
		endif;
		break;
endswitch;
$pgtitle = [gtext('System'),gtext('General'),gtext('Password')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="system.php"><span><?=gtext('General');?></span></a></li>
		<li class="tabact"><a href="system_password.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Password');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('WebGUI and Root Password'));?>
		</thead>
		<tbody>
			<?php
			html_passwordbox2('password_old', gtext('Current Password'),'','',true);
			html_passwordbox2('password_new', gtext('New Password'),'','',true);
			html_passwordbox2('password_confirm', gtext('Confirm New Password'),'','',true);
			?>
		</tbody>
	</table>
	<div id="submit">
		<?php
		echo html_button('save',gtext('Save'));
		echo html_button('cancel',gtext('Cancel'));
		?>
	</div>
	<div id="remarks">
		<?php
		$helpinghand = '<div id="enumeration"><ul>' .
				'<li>' . gtext('This password is required to access the admin web interface.') . '</li>' .
				'<li>' . gtext('This password is the root password of the system.') . '</li>' .
				'</ul></div>';
		html_remark2('note',gtext('Note'),$helpinghand);
		?>
	</div>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
