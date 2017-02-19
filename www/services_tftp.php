<?php
/*
	services_tftp.php

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
require 'co_sphere.php';

function services_tftp_get_sphere() {
	global $config;
	$sphere = new co_sphere_settings('services_tftp','php');
	$sphere->row_default = [
		'enable' => false,
		'dir' => $g['media_path'],
		'allowfilecreation' => true,
		'port' => 69,
		'username' => 'nobody',
		'umask' => 0,
		'timeout' => 1000000,
		'maxblocksize' => 16384,
		'extraoptions' => ''
	];
	$sphere->grid = &array_make_branch($config,'tftpd');
	if(empty($sphere->grid)):
		$sphere->grid = $sphere->row_default;
		write_config();
		header($sphere->header());
		exit;
	endif;
	return $sphere;
}
$sphere = &services_tftp_get_sphere();
$gt_button_apply_confirm = gtext('Do you want to apply these settings?');
$input_errors = [];
$a_message = [];
//	identify page mode
$mode_page = ($_POST) ? PAGE_MODE_POST : PAGE_MODE_VIEW;
switch($mode_page):
	case PAGE_MODE_POST:
		if(isset($_POST['submit'])):
			$page_action = $_POST['submit'];
			switch($page_action):
				case 'edit':
					$mode_page = PAGE_MODE_EDIT;
					break;
				case 'save':
					break;
				case 'enable':
					break;
				case 'disable':
					break;
				default:
					$mode_page = PAGE_MODE_VIEW;
					$page_action = 'view';
					break;
			endswitch;
		else:
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		endif;
		break;
	case PAGE_MODE_VIEW:
		$page_action = 'view';
		break;
endswitch;
//	get configuration data, depending on the source
switch($page_action):
	case 'save':
		$source = $_POST;
		break;
	default:
		$source = $sphere->grid;
		break;
endswitch;
$sphere->row['enable'] = isset($source['enable']);
$sphere->row['dir'] = $source['dir'] ?? $sphere->row_default['dir'];
$sphere->row['allowfilecreation'] = isset($source['allowfilecreation']);
$sphere->row['port'] = $source['port'] ?? $sphere->row_default['port'];
$sphere->row['username'] = $source['username'] ?? $sphere->row_default['username'];
$sphere->row['umask'] = $source['umask'] ?? $sphere->row_default['umask'];
$sphere->row['timeout'] = $source['timeout'] ?? $sphere->row_default['timeout'];
$sphere->row['maxblocksize'] = $source['maxblocksize'] ?? $sphere->row_default['maxblocksize'];
$sphere->row['extraoptions'] = $source['extraoptions'] ?? $sphere->row_default['extraoptions'];
//	set defaults
if(preg_match('/\S/',$sphere->row['username'])):
else:
	$sphere->row['username'] = $sphere->row_default['username'];
endif;
//	process enable
switch($page_action):
	case 'enable':
		if($sphere->row['enable']):
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		else: // enable and run a full validation
			$sphere->row['enable'] = true;
			$page_action = 'save'; // continue with save procedure
		endif;
		break;
endswitch;
//	process save and disable
switch($page_action):
	case 'save':
		// Input validation.
		$reqdfields = ['dir'];
		$reqdfieldsn = [gtext('Directory')];
		$reqdfieldst = ['string'];
		do_input_validation($sphere->row,$reqdfields,$reqdfieldsn,$input_errors);
		$reqdfields = array_merge($reqdfields,['port','umask','timeout','maxblocksize']);
		$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Port'),gtext('Umask'),gtext('Timeout'),gtext('Max. Block Size')]);
		$reqdfieldst = array_merge($reqdfieldst,['port','numeric','numeric','numeric']);
		do_input_validation_type($sphere->row,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if((512 > $sphere->row['maxblocksize']) || (65464 < $sphere->row['maxblocksize'])):
			$input_errors[] = sprintf(gtext('Invalid maximum block size! It must be in the range from %d to %d.'),512,65464);
		endif;
		if(empty($input_errors)):
			$sphere->grid = $sphere->row;
			write_config();
			$retval = 0;
			config_lock();
			$retval |= rc_update_service('tftpd');
			config_unlock();
			header($sphere->header());
			exit;
		else:
			$mode_page = PAGE_MODE_EDIT;
			$page_action = 'edit';
		endif;
		break;
	case 'disable':
		if($sphere->row['enable']): // if enabled, disable it
			$sphere->row['enable'] = false;
			$sphere->grid = $sphere->row;
			write_config();
			$retval = 0;
			config_lock();
			$retval |= rc_update_service('tftpd');
			config_unlock();
			header($sphere->header());
			exit;
		endif;
		$mode_page = PAGE_MODE_VIEW;
		$page_action = 'view';
		break;
endswitch;
//	determine final page mode
switch($mode_page):
	case PAGE_MODE_EDIT:
		break;
	default:
		if(isset($config['system']['skipviewmode'])):
			$mode_page = PAGE_MODE_EDIT;
			$page_action = 'edit';
		else:
			$mode_page = PAGE_MODE_VIEW;
			$page_action = 'view';
		endif;
		break;
endswitch;
//  prepare lookups
switch($mode_page):
	case PAGE_MODE_EDIT:
		$l_user = [];
		foreach(system_get_user_list() as $key => $val):
			$l_user[$key] = htmlspecialchars($key);
		endforeach;
		break;
endswitch;
$pgtitle = [gtext('Services'),gtext('TFTP')];
include 'fbegin.inc';
switch($mode_page):
	case PAGE_MODE_VIEW:
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
//]]>
</script>
<?php
		break;
	case PAGE_MODE_EDIT:
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	$("#iform").submit(function() {	spinner(); });
	$(".spin").click(function() { spinner(); });
	$("#button_save").click(function () {
		return confirm("<?=$gt_button_apply_confirm;?>");
	});
});
//]]>
</script>
<?php
		break;
endswitch;	
?>
<form action="<?=$sphere->scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	foreach($a_message as $r_message):
		print_info_box($r_message);
	endforeach;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_titleline2(gtext('Trivial File Transfer Protocol'));
					break;
				case PAGE_MODE_EDIT:
					html_titleline_checkbox2('enable',gtext('Trivial File Transfer Protocol'),$sphere->row['enable'],gtext('Enable'));
					break;
			endswitch;
?>
		</thead>
		<tbody>
<?php
			switch($mode_page):
				case PAGE_MODE_VIEW:
					html_text2('enable',gtext('Service Enabled'),$sphere->row['enable'] ? gtext('Yes') : gtext('No'));
					html_text2('dir',gtext('Directory'),htmlspecialchars($sphere->row['dir']));
					html_checkbox2('allowfilecreation',gtext('Allow New Files'),$sphere->row['allowfilecreation'],'','',false,true);
					html_separator2();
					html_titleline2(gtext('Advanced Settings'));
					html_text2('port',gtext('Port'),htmlspecialchars($sphere->row['port']));
					html_text2('username',gtext('Username'),htmlspecialchars($sphere->row['username']));
					html_text2('umask',gtext('Umask'),htmlspecialchars($sphere->row['umask']));
					html_text2('timeout',gtext('Timeout'),htmlspecialchars($sphere->row['timeout']));
					html_text2('maxblocksize',gtext('Max. Block Size'),htmlspecialchars($sphere->row['maxblocksize']));
					html_text2('extraoptions',gtext('Extra Options'),htmlspecialchars($sphere->row['extraoptions']));
					break;
				case PAGE_MODE_EDIT:
					html_filechooser2('dir',gtext('Directory'),htmlspecialchars($sphere->row['dir']),gtext('The directory containing the files you want to publish. The remote host does not need to pass along the directory as part of the transfer.'),$g['media_path'],true,60);
					html_checkbox2('allowfilecreation',gtext('Allow New Files'),$sphere->row['allowfilecreation'],gtext('Allow new files to be created.'),gtext('By default, only already existing files can be uploaded.'),false);
					html_separator2();
					html_titleline2(gtext('Advanced Settings'));
					html_inputbox2('port',gtext('Port'),htmlspecialchars($sphere->row['port']),gtext('Enter a custom port number if you want to override the default port (default is 69).'),false,5);
					html_combobox2('username',gtext('Username'),htmlspecialchars($sphere->row['username']),$l_user,gtext('Specifies the username which the service will run as.'),false);
					html_inputbox2('umask',gtext('Umask'),htmlspecialchars($sphere->row['umask']),gtext('Sets the umask for newly created files to the specified value. The default is zero (anyone can read or write).'),false,4);
					html_inputbox2('timeout',gtext('Timeout'),htmlspecialchars($sphere->row['timeout']),gtext('Determine the default timeout, in microseconds, before the first packet is retransmitted. The default is 1000000 (1 second).'),false,10);
					html_inputbox2('maxblocksize',gtext('Max. Block Size'),htmlspecialchars($sphere->row['maxblocksize']),gtext('Specifies the maximum permitted block size. The permitted range for this parameter is from 512 to 65464.'),false,5);
					html_inputbox2('extraoptions',gtext('Extra Options'),htmlspecialchars($sphere->row['extraoptions']),gtext('Extra options (usually empty).'),false,40);
					break;
			endswitch;
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		switch($mode_page):
			case PAGE_MODE_VIEW;
				echo html_button('edit',gtext('Edit'));
				if($sphere->row['enable']):
					echo html_button('disable',gtext('Disable'));
				else:
					echo html_button('enable',gtext('Enable'));
				endif;
				break;
			case PAGE_MODE_EDIT:
				echo html_button('save',gtext('Apply'));
				echo html_button('cancel',gtext('Cancel'));
				break;
		endswitch;
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></table></form>
<?php
include 'fend.inc';
?>
