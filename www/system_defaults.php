<?php
/*
	system_defaults.php

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
$gt_defaults = gtext('The server is now reset to factory defaults and will reboot.');
$gt_defaults_confirm = gtext('Are you sure you want to reset the server to factory defaults?');
$gt_yes = gtext('Yes');
$gt_no = gtext('No');
$cmd_system_defaults = false;
$gt_note_1 = gtext('The server will be reset to factory defaults and will reboot.');
$gt_note_2 = gtext('The entire system configuration will be overwritten.');
$gt_note_3 = gtext('The LAN IP address will be reset to') . ': <b>' . htmlspecialchars($g['default_ip']) . '</b>.';
$gt_note_4 = gtext('The administrator password will be reset to') . ': "<b>' . htmlspecialchars($g['default_passwd']) . '</b>".';

if($_POST) {
	if($_POST['submit']) {
		switch($_POST['submit']) {
			case 'save':
				$cmd_system_defaults = true;
				break;
			case 'cancel':
				header($sphere_header_parent);
				exit;
				break;
			default:
				header($sphere_header_parent);
				exit;
				break;
		}
	}
}
$pgtitle = [gtext('System'),gtext('Factory Defaults')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner onsubmit().?>
	$("#iform").submit(function() { spinner(); });
});
//]]>
</script>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if($cmd_system_defaults) {
		echo print_info_box($gt_defaults);
	} else {
		echo print_warning_box($gt_defaults_confirm);
	}
	?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Factory Defaults'),2);?>
		</thead>
		<tbody>
			<?php
			html_textinfo2('note1',gtext('Note'),$gt_note_1);
			html_textinfo2('note2',gtext('Warning'),$gt_note_2);
			html_textinfo2('note3',gtext('IP Address'),$gt_note_3);
			html_textinfo2('note4',gtext('Password'),$gt_note_4);
			?>
		</tbody>
	</table>
		<?php if(!$cmd_system_defaults):;?>
			<div id="submit">
			<?php
			echo html_button('save',$gt_yes);
			echo html_button('cancel',$gt_no);
			?>
			</div>
		<?php endif;?>
<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
<?php
if ($cmd_system_defaults) {
	reset_factory_defaults();
	flush();
	sleep(5);
	system_reboot();
}
?>
