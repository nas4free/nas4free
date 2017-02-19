<?php
/*
	disks_zfs_config_current.php

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

array_make_branch($config,'zfs','pools','pool');
array_make_branch($config,'zfs','vdevices','vdevice');
array_make_branch($config,'zfs','datasets','dataset');
array_make_branch($config,'zfs','volumes','volume');
$zfs = $config['zfs'];

foreach ($zfs['pools']['pool'] as $index => $pool):
	$zfs['pools']['pool'][$index]['size'] = gtext('Unknown');
	$zfs['pools']['pool'][$index]['used'] = gtext('Unknown');
	$zfs['pools']['pool'][$index]['avail'] = gtext('Unknown');
	$zfs['pools']['pool'][$index]['cap'] = gtext('Unknown');
	$zfs['pools']['pool'][$index]['health'] = gtext('Unknown');
	foreach ($pool['vdevice'] as $vdevice):
		if (false === ($index = array_search_ex($vdevice, $zfs['vdevices']['vdevice'], 'name'))):
			continue;
		endif;
		$zfs['vdevices']['vdevice'][$index]['pool'] = $pool['name'];
	endforeach;
endforeach;

$rawdata = null;
mwexec2("zfs list -H -t filesystem -o name,used,available", $rawdata);
foreach($rawdata as $line):
	if($line == 'no datasets available'):
		continue;
	endif;
	list($fname, $used, $avail) = explode("\t", $line);
	if(false === ($index = array_search_ex($fname, $zfs['pools']['pool'], 'name'))):
		continue;
	endif;
	if(strpos($fname, '/') === false): // zpool
		$zfs['pools']['pool'][$index]['used'] = $used;
		$zfs['pools']['pool'][$index]['avail'] = $avail;
	endif;
endforeach;

$rawdata = null;
$spa = @exec("sysctl -q -n vfs.zfs.version.spa");
if($spa == ''):
	mwexec2("zpool list -H -o name,root,size,allocated,free,capacity,health", $rawdata);
else:
	if($spa < 21):
		mwexec2("zpool list -H -o name,altroot,size,allocated,free,capacity,health", $rawdata);
	else:
		mwexec2("zpool list -H -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup", $rawdata);
	endif;
endif;
foreach ($rawdata as $line):
	if($line == 'no pools available'):
		continue;
	endif;
	list($pool, $root, $size, $alloc, $free, $cap, $expandsz, $frag, $health, $dedup) = explode("\t", $line);
	if(false === ($index = array_search_ex($pool, $zfs['pools']['pool'], 'name'))):
		continue;
	endif;
	if($root != '-'):
		$zfs['pools']['pool'][$index]['root'] = $root;
	endif;
	$zfs['pools']['pool'][$index]['size'] = $size;
	$zfs['pools']['pool'][$index]['alloc'] = $alloc;
	$zfs['pools']['pool'][$index]['free'] = $free;
	$zfs['pools']['pool'][$index]['expandsz'] = $expandsz;
	$zfs['pools']['pool'][$index]['frag'] = $frag;
	$zfs['pools']['pool'][$index]['cap'] = $cap;
	$zfs['pools']['pool'][$index]['health'] = $health;
	$zfs['pools']['pool'][$index]['dedup'] = $dedup;
endforeach;
if(updatenotify_exists('zfs_import_config')):
	$notifications = updatenotify_get('zfs_import_config');
	$retval = 0;
	foreach ($notifications as $notification):
		$retval |= !($notification['data'] == true);
	endforeach;
	$savemsg = get_std_save_message($retval);
	if ($retval == 0):
		updatenotify_delete("zfs_import_config");
	endif;
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Configuration'),gtext('Current')];
?>
<?php include 'fbegin.inc';?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext("Pools");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext("Datasets");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext("Volumes");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext("Snapshots");?></span></a></li>
		<li class="tabact"><a href="disks_zfs_config.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Configuration");?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabact"><a href="disks_zfs_config_current.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Current");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext("Detected");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_config_sync.php"><span><?=gtext("Synchronize");?></span></a></li>
	</ul></td></tr>
	<tr>
		<td class="tabcont">
			<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php html_titleline(gtext('Pools').' ('.count($zfs['pools']['pool']).')', 10);?>
				<tr>
					<td width="16%" class="listhdrlr"><?=gtext("Name");?></td>
					<td width="10%" class="listhdrr"><?=gtext("Size");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Alloc");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Free");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Expandsz");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Frag");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Dedup");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Health");?></td>
					<td width="10%" class="listhdrr"><?=gtext("Mount Point");?></td>
					<td width="10%" class="listhdrr"><?=gtext("AltRoot");?></td>
				</tr>
				<?php foreach ($zfs['pools']['pool'] as $pool):?>
				<tr>
					<td class="listlr"><?= $pool['name']; ?></td>
					<td class="listr"><?= $pool['size']; ?></td>
					<td class="listr"><?= $pool['alloc']; ?> (<?= $pool['cap']; ?>)</td>
					<td class="listr"><?= $pool['free']; ?></td>
					<td class="listr"><?= $pool['expandsz']; ?></td>
					<td class="listr"><?= $pool['frag']; ?></td>
					<td class="listr"><?= $pool['dedup']; ?></td>
					<td class="listr"><?= $pool['health']; ?></td>
					<td class="listr"><?= empty($pool['mountpoint']) ? "/mnt/{$pool['name']}" : $pool['mountpoint']; ?></td>
					<td class="listr"><?= empty($pool['root']) ? '-' : $pool['root']; ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<br />
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php html_titleline(gtext('Virtual Devices').' ('.count($zfs['vdevices']['vdevice']).')', 4);?>
				<tr>
					<td width="16%" class="listhdrlr"><?=gtext("Name");?></td>
					<td width="21%" class="listhdrr"><?=gtext("Type");?></td>
					<td width="21%" class="listhdrr"><?=gtext("Pool");?></td>
					<td width="42%" class="listhdrr"><?=gtext("Devices");?></td>
				</tr>
				<?php foreach ($zfs['vdevices']['vdevice'] as $vdevice):?>
				<tr>
					<td class="listlr"><?= $vdevice['name']; ?></td>
					<td class="listr"><?= $vdevice['type']; ?></td>
					<td class="listr"><?= !empty($vdevice['pool']) ? $vdevice['pool'] : ""; ?></td>
					<td class="listr"><?= implode(', ', $vdevice['device']); ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<br />
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php html_titleline(gtext('Datasets').' ('.count($zfs['datasets']['dataset']).')', 11);?>
				<tr>
					<td width="14%" class="listhdrlr"><?=gtext("Name");?></td>
					<td width="14%" class="listhdrr"><?=gtext("Pool");?></td>
					<td width="7%" class="listhdrr"><?=gtext("Compression");?></td>
					<td width="7%" class="listhdrr"><?=gtext("Dedup");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Sync");?></td>
					<td width="9%" class="listhdrr"><?=gtext("ACL Inherit");?></td>
					<td width="9%" class="listhdrr"><?=gtext("ACL Mode");?></td>
					<td width="7%" class="listhdrr"><?=gtext("Canmount");?></td>
					<td width="8%" class="listhdrr"><?=gtext("Quota");?></td>
<!--
					<td width="8%" class="listhdrr"><?=gtext("Extended Attributes");?></td>
-->
					<td width="7%" class="listhdrr"><?=gtext("Readonly");?></td>
					<td width="9%" class="listhdrr"><?=gtext("Snapshot Visibility");?></td>
				</tr>
				<?php foreach ($zfs['datasets']['dataset'] as $dataset):?>
				<tr>
					<td class="listlr"><?= $dataset['name']; ?></td>
					<td class="listr"><?= $dataset['pool'][0]; ?></td>
					<td class="listr"><?= $dataset['compression']; ?></td>
					<td class="listr"><?= $dataset['dedup']; ?></td>
					<td class="listr"><?= $dataset['sync']; ?></td>
					<td class="listr"><?= $dataset['aclinherit']; ?></td>
					<td class="listr"><?= $dataset['aclmode']; ?></td>
					<td class="listr"><?= isset($dataset['canmount']) ? 'on' : 'off'; ?></td>
					<td class="listr"><?= empty($dataset['quota']) ? 'none' : $dataset['quota']; ?></td>
<!--
					<td class="listr"><?= isset($dataset['xattr']) ? 'on' : 'off'; ?></td>
-->
					<td class="listr"><?= isset($dataset['readonly']) ? 'on' : 'off'; ?></td>
					<td class="listr"><?= isset($dataset['snapdir']) ? 'visible' : 'hidden'; ?></td>
				</tr>
				<?php endforeach;?>
			</table>
			<br />
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php html_titleline(gtext('Volumes').' ('.count($zfs['volumes']['volume']).')', 8);?>
				<tr>
					<td width="16%" class="listhdrlr"><?=gtext("Name");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Pool");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Size");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Blocksize");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Sparse");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Compression");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Dedup");?></td>
					<td width="12%" class="listhdrr"><?=gtext("Sync");?></td>
				</tr>
				<?php foreach ($zfs['volumes']['volume'] as $volume):?>
				<tr>
					<td class="listlr"><?= $volume['name']; ?></td>
					<td class="listr"><?= $volume['pool'][0]; ?></td>
					<td class="listr"><?= $volume['volsize']; ?></td>
					<td class="listr"><?= !empty($volume['volblocksize']) ?$volume['volblocksize']: '-'; ?></td>
					<td class="listr"><?= !isset($volume['sparse']) ? '-' : 'on'; ?></td>
					<td class="listr"><?= $volume['compression']; ?></td>
					<td class="listr"><?= $volume['dedup']; ?></td>
					<td class="listr"><?= $volume['sync']; ?></td>
				</tr>
				<?php endforeach;?>
			</table>
			<div id="remarks">
				<?php html_remark("note", gtext("Note"), gtext("This page reflects the configuration that has been created with the WebGUI."));?>
			</div>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
