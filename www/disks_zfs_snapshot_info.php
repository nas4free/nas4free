<?php
/*
	disks_zfs_snapshot_info.php

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

function zfs_snapshot_display_list() {
	mwexec2("zfs list -t snapshot 2>&1", $rawdata);
	return implode("\n", $rawdata);
}
function zfs_snapshot_display_properties() {
	mwexec2("zfs list -H -o name -t snapshot 2>&1", $rawdata);
	$snaps = implode(" ", $rawdata);
	$rawdata2 = [];
	if (!empty($snaps)) {
		mwexec2("zfs get all $snaps 2>&1", $rawdata2);
	}
	return implode("\n", $rawdata2);
}
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Snapshots'),gtext('Information')];
?>
<?php include 'fbegin.inc';?>
<table id="area_navigator"><tbody>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabact"><a href="disks_zfs_snapshot.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshot');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot_clone.php"><span><?=gtext('Clone');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot_auto.php"><span><?=gtext('Auto Snapshot');?></span></a></li>
				<li class="tabact"><a href="disks_zfs_snapshot_info.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Information');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('ZFS Snapshot Information & Status'));?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Information & Status');?></td>
				<td class="celldata">
					<pre><span id="zfs_snapshot_list"><?=zfs_snapshot_display_list();?></span></pre>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<?php html_separator2();?>
		</tfoot>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('ZFS Snapshot Properties'));?>
		</thead>
		<tbody>
			<tr>
				<td class="celltag"><?=gtext('Properties');?></td>
				<td class="celldata">
					<pre><span id="zfs_snapshot_properties"><?=zfs_snapshot_display_properties();?></span></pre>
				</td>
			</tr>
		<tbody>
	</table>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
