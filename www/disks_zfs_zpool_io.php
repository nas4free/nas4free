<?php
/*
	disks_zfs_zpool_io.php

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

function disks_zfs_zpool_get_io_ajax() {
	if (isset($_GET['pool']) && is_string($_GET['pool'])) {
		$cmd = sprintf('zpool iostat -v "%s"', $_GET['pool']);
	} else {
		$cmd = 'zpool iostat -v';
	}
	mwexec2($cmd, $rawdata);
	return htmlspecialchars(implode("\n", $rawdata));
}
if (is_ajax()) {
	$status = disks_zfs_zpool_get_io_ajax();
	render_ajax($status);
}
$pgtitle = [gtext('Disks'), gtext('ZFS'), gtext('Pools'), gtext('I/O Statistics')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(0, 5000, 'disks_zfs_zpool_io.php', null, function(data) {
		$('#ajax_refresh').text(data.data);
	});
});
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="disks_zfs_zpool.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabinact"><a href="disks_zfs_zpool_vdevice.php"><span><?=gtext('Virtual Device');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Management');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool_tools.php"><span><?=gtext('Tools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_zpool_info.php"><span><?=gtext('Information');?></span></a></li>
				<li class="tabact"><a href="disks_zfs_zpool_io.php" title="<?=gtext('Reload page');?>"><span><?=gtext('I/O Statistics');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('ZFS Pool I/O Statistics'));?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Information');?></td>
				<td class="celldata">
					<pre><span id="ajax_refresh"></span></pre>
				</td>
			</tr>
		</tbody>
	</table>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
