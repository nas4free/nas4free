<?php
/*
	exec.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Exec+ v1.02-000 - Copyright 2001-2003, All rights reserved
	Created by technologEase (http://www.technologEase.com).

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

function exec_get_sphere() {
//	global $config;
	
//	sphere structure
	$sphere = new \stdClass;
//	sphere content
	$sphere->basename = 'exec';
	$sphere->extension = '.php';
	$sphere->scriptname = $sphere->basename . $sphere->extension;
	$sphere->header = 'Location: ' . $sphere->scriptname;
	return $sphere;
}
//	get environment
$sphere = &exec_get_sphere();
//	local variables
$a_message = [];
$a_message[] = gtext('This is a very powerful tool. Use at your own risk!');
//
if($_POST):
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'upload':
				$source = $_FILES['ulfile']['tmp_name'];
				$destination = sprintf('/tmp/%s',$_FILES['ulfile']['name']);
				if(is_uploaded_file($source)):
					move_uploaded_file($source,$destination);
					$a_message[] = gtext('Script has been uploaded.') . sprintf(' [%s]',$destination);
					unset($_POST['txtCommand']);
				endif;
				break;
		endswitch;
	endif;
endif;
$pgtitle = [gtext('Tools'),gtext('Execute Command')];
include 'fbegin.inc';
?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init onsubmit()
	$("#frmExecPlus").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
	$("#txtCommand").click(function () { txtCommand_onKey(event) });
});
<?php
//	Create recall buffer array (of encoded strings).
if(isset($_POST['txtRecallBuffer']) && preg_match('/\S/',$_POST['txtRecallBuffer'])):
	echo 'var arrRecallBuffer = new Array(',"\n";
	$a_in = explode('&',$_POST['txtRecallBuffer']);
	$a_out = [];
	foreach($a_in as $r_in):
		$a_out[] = sprintf("'%s'",$r_in);
	endforeach;
	echo "\t",implode(",\n\t",$a_out),"\n";
	echo ');',"\n";
else:
	echo 'var arrRecallBuffer = new Array;',"\n";
endif;
?>
<?php
	//	Set pointer to end of recall buffer.
?>
	var intRecallPtr = arrRecallBuffer.length;
<?php
	//	Functions to extend String class.
?>
	function str_encode() { return escape( this ) }
	function str_decode() { return unescape( this ) }
<?php
	//	Extend string class to include encode() and decode() functions.
?>
	String.prototype.encode = str_encode
	String.prototype.decode = str_decode
<?php
	//	Function: is Blank
	//	Returns boolean true or false if argument is blank.
?>
	function isBlank( strArg ) { return strArg.match( /^\s*$/ ) }
<?php
	//	Function: frmExecPlus onSubmit (event handler)
	//	Builds the recall buffer from the command string on submit.
?>
	function frmExecPlus_onSubmit( form ) {
		if (!isBlank(form.txtCommand.value)) {
<?php
			//	If this command is repeat of last command, then do not store command.
?>
			if (form.txtCommand.value.encode() == arrRecallBuffer[arrRecallBuffer.length-1]) { return true }
<?php
			//	Stuff encoded command string into the recall buffer.
?>
			if (isBlank(form.txtRecallBuffer.value))
				form.txtRecallBuffer.value = form.txtCommand.value.encode();
			else
				form.txtRecallBuffer.value += '&' + form.txtCommand.value.encode();
		}
		return true;
	}
<?php
	//	Function: btnRecall onClick (event handler)
	//	Recalls command buffer going either up or down.
?>
	function btnRecall_onClick( form, n ) {
<?php
		//	If nothing in recall buffer, then error.
?>
		if (!arrRecallBuffer.length) {
<?php
			//	alert( 'Nothing to recall!' );
?>
			form.txtCommand.focus();
			return;
		}
<?php
		//	Increment recall buffer pointer in positive or negative direction
		//	according to <n>.
?>
		intRecallPtr += n;
<?php
		// Make sure the buffer stays circular.
?>
		if (intRecallPtr < 0) { intRecallPtr = arrRecallBuffer.length - 1 }
		if (intRecallPtr > (arrRecallBuffer.length - 1)) { intRecallPtr = 0 }
<?php
		//	Recall the command.
?>
		form.txtCommand.value = arrRecallBuffer[intRecallPtr].decode();
	}
<?php
	//	Function: Reset onClick (event handler)
	//	Resets form on reset button click event.
?>
	function Reset_onClick( form ) {
<?php
		//	Reset recall buffer pointer.
?>
		intRecallPtr = arrRecallBuffer.length;
<?php
		//	Clear form (could have spaces in it) and return focus ready for cmd.
?>
		form.txtCommand.value = '';
		form.txtCommand.focus();
		return true;
	}
<?php
	//	hansmi, 2005-01-13
?>
	function txtCommand_onKey(e) {
		if(!e) var e = window.event; // IE-Fix
		var code = (e.keyCode?e.keyCode:(e.which?e.which:0));
		if(!code) return;
		var f = document.getElementsByName('frmExecPlus')[0];
		if(!f) return;
		switch(code) {
			case 38: // up
				btnRecall_onClick(f, -1);
				break;
			case 40: // down
				btnRecall_onClick(f, 1);
				break;
		}
	}
//]]>
</script>
<form action="<?=$sphere->scriptname;?>" method="post" enctype="multipart/form-data" name="frmExecPlus" id="frmExecPlus" onsubmit="return frmExecPlus_onSubmit(this);">
	<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
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
				html_titleline2(gtext('Command'));
?>
			</thead>
			<tbody>
<?php
				html_inputbox2('txtCommand',gtext('Command'),'','',false,80,false,false,1024,gtext('Enter Command'));
?>
				<tr>
					<td class="celltag"><?=gtext('Control');?></td>
					<td class="celldata">
						<input type="hidden" name="txtRecallBuffer" value="<?=!empty($_POST['txtRecallBuffer']) ? $_POST['txtRecallBuffer'] : '';?>"/>
						<input type="button" class="formbtn" name="btnRecallPrev" value="&lt;" onclick="btnRecall_onClick( this.form, -1 );"/>
						<input type="submit" class="formbtn" value="<?=gtext('Execute');?>"/>
						<input type="button" class="formbtn" name="btnRecallNext" value="&gt;" onclick="btnRecall_onClick( this.form,  1 );"/>
						<input type="button" class="formbtn" value="<?=gtext('Clear');?>" onclick="return Reset_onClick( this.form );"/>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gtext('Upload Script'));
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('Script');?></td>
					<td class="celldata">
						<input name="ulfile" type="file" class="formbtn" id="ulfile"/>
					</td>
				</tr>
				<tr>
					<td class="celltag"><?=gtext('Control');?></td>
					<td class="celldata">
<?php
						echo html_button('upload',gtext('Upload Script'));
?>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gtext('PHP Command'));
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('PHP Command');?></td>
					<td class="celldata"><textarea id="txtPHPCommand" name="txtPHPCommand" rows="3" cols="49" wrap="off"><?=htmlspecialchars(!empty($_POST['txtPHPCommand']) ? $_POST['txtPHPCommand'] : '');?></textarea></td>
				</tr>
				<tr>
					<td class="celltag"><?=gtext('Control');?></td>
					<td class="celldata">
						<input type="submit" class="formbtn" value="<?=gtext('Execute');?>" />
					</td>
				</tr>
			</tbody>
		</table>
<?php
		if(isset($_POST['txtCommand']) && preg_match('/\S/',$_POST['txtCommand'])):
?>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_separator2();
					html_titleline2(gtext('Command Output'));
?>
				</thead>
				<tbody>
				</tbody>
			</table>
<?php
			echo '<div>','<pre class="celldata">';
			echo "\$ ",htmlspecialchars($_POST['txtCommand']),"\n";
			putenv('PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin');
			putenv('COLUMNS=1024');
			putenv('SCRIPT_FILENAME=' . strtok($_POST['txtCommand'],' ')); /* PHP scripts */
			$ph = popen($_POST['txtCommand'],'r');
			while($line = fgets($ph)):
				echo htmlspecialchars($line);
			endwhile;
			pclose($ph);
			echo '</pre>','</div>';
		endif;
?>
<?php
		if(isset($_POST['txtPHPCommand']) && preg_match('/\S/',$_POST['txtPHPCommand'])):
?>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_separator2();
					html_titleline2(gtext('PHP Command Output'));
?>
				</thead>
				<tbody>
				</tbody>
			</table>
<?php
			echo '<div>','<pre class="celldata">';
			require_once 'config.inc';
			require_once 'functions.inc';
			require_once 'util.inc';
			require_once 'rc.inc';
			require_once 'email.inc';
			require_once 'tui.inc';
			require_once 'array.inc';
			require_once 'services.inc';
			require_once 'zfs.inc';
			echo eval($_POST['txtPHPCommand']);
			echo '</pre>','</div>';
		endif;
?>
	</td></tr></tbody></table>
<?php
	include 'formend.inc';
?>
</form>
<script type="text/javascript">
//<![CDATA[
document.forms[0].txtCommand.focus();
//]]>
</script>
<?php
include 'fend.inc';
?>
