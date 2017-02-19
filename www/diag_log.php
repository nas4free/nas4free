<?php
/*
	diag_log.php

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
require 'diag_log.inc';

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;

if(isset($_GET['log'])):
	$log = $_GET['log'];
endif;
if(isset($_POST['log'])):
	$log = $_POST['log'];
endif;
if(empty($log)):
	$log = 0;
endif;
if(isset($_POST['clear']) && $_POST['clear']):
	log_clear($loginfo[$log]);
	header(sprintf('Location: diag_log.php?log=%s',$log));
	exit;
endif;
if(isset($_POST['download']) && $_POST['download']):
	log_download($loginfo[$log]);
	exit;
endif;
if(isset($_POST['refresh']) && $_POST['refresh']):
	header(sprintf('Location: diag_log.php?log=%s',$log));
	exit;
endif;
$searchlog = $_POST['searchlog'] ?? '';
$pgtitle = [gtext('Diagnostics'),gtext('Log')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load",function() {
<?php // Init spinner on submit for id form.?>
	$("#iform").submit(function() { spinner(); });
<?php // Init spinner on click for class spin.?>
	$(".spin").click(function() { spinner(); });
}); 
function log_change() {
	// Reload page
	window.document.location.href = 'diag_log.php?log=' + document.iform.log.value;
}
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabact"><a href="diag_log.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Log');?></span></a></li>
		<li class="tabinact"><a href="diag_log_settings.php"><span><?=gtext('Settings');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform">
	<table class="area_data_settings">
		<colgroup>
			<col style="width:100%">
		</colgroup>
		<thead>
			<?php
			html_titleline2(gtext('Log Filter'),1);
			html_separator2(1);
			?>
		</thead>
		<tbody><tr><td>
			<select id="log" class="formfld" onchange="log_change()" name="log">
				<?php 
				foreach($loginfo as $loginfo_key => $loginfo_val):
					if(false === $loginfo_val['visible']):
						continue;
					endif;
					?>
					<option value="<?=$loginfo_key;?>" <?php if ($loginfo_key == $log) echo 'selected="selected"';?>><?=$loginfo_val['desc'];?></option>
				<?php
				endforeach;
				?>
			</select>
			<input name="clear" type="submit" class="formbtn" value="<?=gtext("Clear");?>" />
			<input name="download" type="submit" class="formbtn" value="<?=gtext("Download");?>" />
			<input name="refresh" type="submit" class="formbtn" value="<?=gtext("Refresh");?>" />
			<span class="label">&nbsp;&nbsp;&nbsp;<?=gtext("Search event");?></span>
			<input size="30" id="searchlog" name="searchlog" value="<?=$searchlog;?>" />
			<input name="search" type="submit" class="formbtn" value="<?=gtext("Search");?>" />
		</td></tr></tbody>
	</table>
	<table class="area_data_settings">
		<thead>
			<?php
			$columns = 0;
			$column_header = [];
			if(is_array($loginfo[$log])):
				$columns = count($loginfo[$log]['columns']);
				foreach($loginfo[$log]['columns'] as $column_key => $column_val):
					$column_header[] = sprintf('<td %1$s class="%2$s">%3$s</td>',$column_val['param'],$column_val['hdrclass'],$column_val['title']);
				endforeach;
			endif;
			html_separator2($columns);
			html_titleline2(gtext('Log'),$columns);
			echo '<tr>',implode("\n",$column_header),'</tr>';
			?>
		</thead>
		<tbody>
			<?php
			$content_array = log_get_contents($loginfo[$log]['logfile'],$loginfo[$log]['type']);
			if(!empty($content_array)):
				// Create table data
				foreach ($content_array as $content_record):
					// Skip invalid pattern matches
					$result = preg_match($loginfo[$log]['pattern'],$content_record,$matches);
					if((false === $result) || (0 == $result)):
						continue;
					endif;
					// Skip empty lines
					if(count($loginfo[$log]['columns']) == 1 && empty($matches[1])):
						continue;
					endif;
					echo "<tr>\n";
						foreach ($loginfo[$log]['columns'] as $column_key => $column_val):
							echo sprintf('<td %1$s class="%2$s">%3$s</td>',$column_val['param'],$column_val['class'],htmlspecialchars($matches[$column_val['pmid']]));
						endforeach;
					echo "</tr>\n";
				endforeach;
//				log_display($loginfo[$log]);
			endif;
			?>
		</tbody>
	</table>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
