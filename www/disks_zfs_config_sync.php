<?php
/*
	disks_zfs_config_sync.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
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
require("auth.inc");
require("guiconfig.inc");

$zfs = [
	'vdevices' => ['vdevice' => []],
	'pools' => ['pool' => []],
	'datasets' => ['dataset' => []],
	'volumes' => ['volume' => []]
];

$rawdata = null;
$spa = @exec("sysctl -q -n vfs.zfs.version.spa");
if ($spa == '' || $spa < 21) {
	mwexec2('zfs list -H -t filesystem -o name,mountpoint,compression,canmount,quota,used,available,xattr,snapdir,readonly,origin', $rawdata);
} else {
	mwexec2('zfs list -H -t filesystem -o name,mountpoint,compression,canmount,quota,used,available,xattr,snapdir,readonly,origin,reservation,dedup,sync,atime,aclinherit,aclmode,primarycache,secondarycache', $rawdata);
}
foreach($rawdata as $line) {
	if ($line == 'no datasets available') {
		continue;
	}
	list($fname, $mpoint, $compress, $canmount, $quota, $used, $avail, $xattr, $snapdir, $readonly, $origin, $reservation, $dedup, $sync, $atime, $aclinherit, $aclmode, $primarycache, $secondarycache) = explode("\t", $line);
	if (strpos($fname, '/') !== false) { // dataset
		if (empty($origin) || $origin != '-') {
			continue;
		}
		list($pool, $name) = explode('/', $fname, 2);
		$zfs['datasets']['dataset'][$fname] = [
			'identifier' => $fname,
			'uuid' => uuid(),
			'name' => $name,
			'pool' => $pool,
			'compression' => $compress,
			'canmount' => ($canmount == 'on') ? null : $canmount,
			'quota' => ($quota == 'none') ? null : $quota,
			'reservation' => ($reservation == 'none') ? null : $reservation,
			'xattr' => ($xattr == 'on'),
			'snapdir' => ($snapdir == 'visible'),
			'readonly' => ($readonly == 'on'),
			'dedup' => $dedup,
			'sync' => $sync,
			'atime' => $atime,
			'aclinherit' => $aclinherit,
			'aclmode' => $aclmode,
			'primarycache' => $primarycache,
			'secondarycache' => $secondarycache,
			'desc' => '',
		];
		list($mp_owner, $mp_group, $mp_mode) = ['root', 'wheel', 0777];
		if ($canmount == 'on' && !empty($mpoint) && file_exists($mpoint)) {
			$mp_uid = fileowner($mpoint);
			$mp_gid = filegroup($mpoint);
			$mp_perm = (fileperms($mpoint) & 0777);
			$tmp = posix_getpwuid($mp_uid);
			if (!empty($tmp) && !empty($tmp['name'])) {
				$mp_owner = $tmp['name'];
			}
			$tmp = posix_getgrgid($mp_gid);
			if (!empty($tmp) && !empty($tmp['name'])) {
				$mp_group = $tmp['name'];
			}
			$mp_mode = sprintf("0%o", $mp_perm);
		}
		$zfs['datasets']['dataset'][$fname]['accessrestrictions'] = [
			'owner' => $mp_owner,
			'group' => $mp_group,
			'mode' => $mp_mode,
		];
	}
	else { // zpool
		$zfs['pools']['pool'][$fname] = [
			'uuid' => uuid(),
			'name' => $fname,
			'vdevice' => [],
			'root' => null,
			'mountpoint' => ($mpoint == "/mnt/{$fname}") ? null : $mpoint,
			'desc' => '',
		];
		$zfs['extra']['pools']['pool'][$fname] = [
			'size' => null,
			'used' => $used,
			'avail' => $avail,
			'cap' => null,
			'health' => null,
		];
	}
}

$rawdata = null;
$spa = @exec("sysctl -q -n vfs.zfs.version.spa");
if ($spa == '' || $spa < 21) {
	mwexec2('zfs list -H -t volume -o name,volsize,volblocksize,compression,origin', $rawdata);
} else {
	mwexec2('zfs list -H -t volume -o name,volsize,volblocksize,compression,origin,dedup,sync,refreservation', $rawdata);
}
foreach($rawdata as $line) {
	if ($line == 'no datasets available') {
		continue;
	}
	list($fname, $volsize, $volblocksize, $compress, $origin, $dedup, $sync, $refreservation) = explode("\t", $line);
	if (strpos($fname, '/') !== false) { // volume
		if (empty($origin) || $origin != '-') {
			continue;
		}
		list($pool, $name) = explode('/', $fname, 2);
		$zfs['volumes']['volume'][$fname] = [
			'identifier' => $fname,
			'uuid' => uuid(),
			'name' => $name,
			'pool' => $pool,
			'volsize' => $volsize,
			'volblocksize' => $volblocksize,
			'compression' => $compress,
			'dedup' => $dedup,
			'sync' => $sync,
			'sparse' => ($refreservation == "none") ? true : false,
			'desc' => '',
		];
	}
}

$rawdata = null;
$spa = @exec("sysctl -q -n vfs.zfs.version.spa");
if ($spa == '') {
	mwexec2('zpool list -H -o name,root,size,allocated,free,capacity,health', $rawdata);
} else if ($spa < 21) {
	mwexec2("zpool list -H -o name,altroot,size,allocated,free,capacity,health", $rawdata);
} else {
	mwexec2("zpool list -H -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup", $rawdata);
}
foreach ($rawdata as $line) {
	if ($line == 'no pools available') {
		continue;
	}
	list($pool, $root, $size, $alloc, $free, $cap, $expandsz, $frag, $health, $dedup) = explode("\t", $line);
	if ($root != '-') {
		$zfs['pools']['pool'][$pool]['root'] = $root;
	}
	$zfs['extra']['pools']['pool'][$pool]['size'] = $size;
	$zfs['extra']['pools']['pool'][$pool]['alloc'] = $alloc;
	$zfs['extra']['pools']['pool'][$pool]['free'] = $free;
	$zfs['extra']['pools']['pool'][$pool]['expandsz'] = $expandsz;
	$zfs['extra']['pools']['pool'][$pool]['frag'] = $frag;
	$zfs['extra']['pools']['pool'][$pool]['cap'] = $cap;
	$zfs['extra']['pools']['pool'][$pool]['health'] = $health;
	$zfs['extra']['pools']['pool'][$pool]['dedup'] = $dedup;
}

$pool = null;
$vdev = null;
$type = null;
$i = 0;
$vdev_type = array('mirror', 'raidz1', 'raidz2', 'raidz3');

$rawdata = null;
mwexec2('zpool status', $rawdata);
foreach ($rawdata as $line) {
	if (empty($line[0]) || $line[0] != "\t") {
		continue;
	}
	if (!is_null($vdev) && preg_match('/^\t    (\S+)/', $line, $m)) { // dev
		$dev = $m[1];
		if (preg_match("/^(.+)\.nop$/", $dev, $m)) {
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
		} else if (preg_match("/^(.+)\.eli$/", $dev, $m)) {
			//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		} else {
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		}
	}
	else if (!is_null($pool) && preg_match('/^\t  (\S+)/', $line, $m)) { // vdev or dev (type disk)
		$is_vdev_type = true;
		if ($type == 'spare') { // disk in vdev type spares
			$dev = $m[1];
		} else if ($type == 'cache') {
			$dev = $m[1];
		} else if ($type == 'log') {
			$dev = $m[1];
			if (preg_match("/^mirror-([0-9]+)$/", $dev, $m)) {
				$type = "log-mirror";
			}
		}
		else { // vdev or dev (type disk)
			$type = $m[1];
			if (preg_match("/^(.*)\-\d+$/", $type, $m)) {
				$tmp = $m[1];
				$is_vdev_type = in_array($tmp, $vdev_type);
				if ($is_vdev_type)
					$type = $tmp;
			} else {
				$is_vdev_type = in_array($type, $vdev_type);
			}
			if (!$is_vdev_type) { // type disk
				$dev = $type;
				$type = 'disk';
				$vdev = sprintf("%s_%s_%d", $pool, $type, $i++);
			} else { // vdev
				$vdev = sprintf("%s_%s_%d", $pool, $type, $i++);
			}
		}
		if (!array_key_exists($vdev, $zfs['vdevices']['vdevice'])) {
			$zfs['vdevices']['vdevice'][$vdev] = [
				'uuid' => uuid(),
				'name' => $vdev,
				'type' => $type,
				'device' => [],
				'desc' => ''
			];
			$zfs['extra']['vdevices']['vdevice'][$vdev]['pool'] = $pool;
			$zfs['pools']['pool'][$pool]['vdevice'][] = $vdev;
		}
		if ($type == 'spare' || $type == 'cache' || $type == 'log' || $type == 'disk') {
			if (preg_match("/^(.+)\.nop$/", $dev, $m)) {
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
			} else if (preg_match("/^(.+)\.eli$/", $dev, $m)) {
				//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			} else {
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			}
		}
	}
	else if (preg_match('/^\t(\S+)/', $line, $m)) { // zpool or spares
		$vdev = null;
		$type = null;
		if ($m[1] == 'spares') {
			$type = 'spare';
			$vdev = sprintf("%s_%s_%d", $pool, $type, $i++);
		} else if ($m[1] == 'cache') {
			$type = 'cache';
			$vdev = sprintf("%s_%s_%d", $pool, $type, $i++);
		} else if ($m[1] == 'logs') {
			$type = 'log';
			$vdev = sprintf("%s_%s_%d", $pool, $type, $i++);
		} else {
			$pool = $m[1];
		}
	}
}

function get_geli_info($device) {
	$result = array();
	exec("/sbin/geli dump {$device}", $rawdata);
	array_shift($rawdata);
	foreach($rawdata as $line) {
		$a = preg_split("/:\s+/", $line);
		$key = trim($a[0]);
		$val = trim($a[1]);
		$result[$key] = $val;
	}
	return $result;
}

if (isset($_POST['import_config'])) {
	$import = false;
	$cfg['zfs'] = [
		'vdevices' => [],
		'pools' => [],
		'datasets' => [],
		'volumes' => [],
		'autosnapshots' => [],
	];
	if (!isset($_POST['vol'])) { $_POST['vol'] = []; }
	if (!isset($_POST['dset'])) { $_POST['dset'] = []; }
	if (!isset($_POST['vdev'])) { $_POST['vdev'] = []; }
	if (!isset($_POST['pool'])) { $_POST['pool'] = []; }
	foreach ($_POST['vol'] as $vol) {
		$import |= true;
		$tmp = $zfs['volumes']['volume'][$vol];
		unset($tmp['volblocksize']); // not yet supported.
		unset($tmp['identifier']); // no longer required
		$cfg['zfs']['volumes']['volume'][] = $tmp;
		if (!in_array($zfs['volumes']['volume'][$vol]['pool'], $_POST['pool'])) {
			$_POST['pool'][] = $zfs['volumes']['volume'][$vol]['pool'];
		}
	}
	foreach ($_POST['dset'] as $dset) {
		$import |= true;
		$tmp = $zfs['datasets']['dataset'][$dset];
		unset($tmp['identifier']); // no longer required
		$cfg['zfs']['datasets']['dataset'][] = $tmp;
		if (!in_array($zfs['datasets']['dataset'][$dset]['pool'], $_POST['pool'])) {
			$_POST['pool'][] = $zfs['datasets']['dataset'][$dset]['pool'];
		}
	}
	foreach ($_POST['pool'] as $pool) {
		$import |= true;
		$hastpool = false;
		foreach ($zfs['pools']['pool'][$pool]['vdevice'] as $vdev) {
			if (!in_array($vdev, $_POST['vdev'])) {
				$_POST['vdev'][] = $vdev;
			}
			foreach ($zfs['vdevices']['vdevice'][$vdev]['device'] as $device) {
				if (preg_match('/^\/dev\/hast\//', $device)) {
					$hastpool = true;
				}
			}
		}
		$zfs['pools']['pool'][$pool]['hastpool'] = $hastpool;
		$cfg['zfs']['pools']['pool'][] = $zfs['pools']['pool'][$pool];
	}
	foreach ($_POST['vdev'] as $vdev) {
		$import |= true;
		$cfg['zfs']['vdevices']['vdevice'][] = $zfs['vdevices']['vdevice'][$vdev];
	}
	
	if ($import) {
		$cfg['disks'] = $config['disks'];
		$cfg['geli'] = $config['geli'];
		$disks = get_physical_disks_list();
		foreach ($cfg['zfs']['vdevices']['vdevice'] as $vdev) {
			foreach ($vdev['device'] as $device) {
				$encrypted = false;
				$device = disks_label_to_device($device);
				if (preg_match("/^(.+)\.eli$/", $device, $m)) {
					$device = $m[1];
					$encrypted = true;
				}
				if (preg_match("/^(.*)p\d+$/", $device, $m)) {
					$device = $m[1];
				}
				$index = false;
				if (!empty($cfg['disks']['disk'])) {
					$index = array_search_ex($device, $cfg['disks']['disk'], 'devicespecialfile');
				}
				if ($index === false && isset($_POST['import_disks'])) {
					$disk = array_search_ex($device, $disks, 'devicespecialfile');
					$disk = $disks[$disk];
					$serial = "";
					if (!empty($disk['serial'])) {
						$serial = $disk['serial'];
					}
					if (($serial == "n/a") || ($serial == gtext("n/a"))) {
						$serial = "";
					}
					$cfg['disks']['disk'][] = [
						'uuid' => uuid(),
						'name' => $disk['name'],
						'id' => $disk['id'],
						'devicespecialfile' => $disk['devicespecialfile'],
						'model' => !empty($disk['model']) ? $disk['model'] : "",
						'desc' => !empty($disk['desc']) ? $disk['desc'] : "",
						'type' => $disk['type'],
						'serial' => $serial,
						'size' => $disk['size'],
						'harddiskstandby' => 0,
						'acoustic' => 0,
						'apm' => 0,
						'transfermode' => 'auto',
						'fstype' => $encrypted ? 'geli' : 'zfs',
						'controller' => $disk['controller'],
						'controller_id' => $disk['controller_id'],
						'controller_desc' => $disk['controller_desc'],
						'smart' => [
							'devicefilepath' => $disk['smart']['devicefilepath'],
							'devicetype' => $disk['smart']['devicetype'],
							'devicetypearg' => $disk['smart']['devicetypearg'],
							'enable' => false,
							'extraoptions' => "",
						],
					];
				}
				else if ($index !== false && isset($_POST['import_disks_overwrite'])) {
					if ($encrypted) {
						$cfg['disks']['disk'][$index]['fstype'] = 'geli';
					} else {
						$cfg['disks']['disk'][$index]['fstype'] = 'zfs';
					}
				}
				if ($encrypted) {
					$index = array_search_ex($device, $cfg['geli']['vdisk'], 'device');
					$geli_info = get_geli_info($device);
					if ($index === false && !empty($geli_info) && isset($_POST['import_disks'])) {
						$disk = array_search_ex($device, $disks, 'devicespecialfile');
						$disk = $disks[$disk];
						$cfg['geli']['vdisk'][] = [
							'uuid' => uuid(),
							'name' => $disk['name'],
							'device' => $disk['devicespecialfile'],
							'devicespecialfile' => $disk['devicespecialfile'].".eli",
							'desc' => "Encrypted disk",
							'size' => $disk['size'],
							'aalgo' => "none",
							'ealgo' => $geli_info['ealgo'],
							'fstype' => 'zfs',
						];
					} else if ($index !== false && isset($_POST['import_disks_overwrite'])) {
						$cfg['geli']['vdisk'][$index]['fstype'] = 'zfs';
					}
				}
			}
		}
		
		if (isset($_GET['zfs']['autosnapshots'])) {
			$pconfig['zfs']['autosnapshots'] = $_GET['zfs']['autosnapshots'];
		}
		if (isset($_POST['leave_autosnapshots'])) {
			$cfg['zfs']['autosnapshots'] = !empty($config['zfs']['autosnapshots']) ? $config['zfs']['autosnapshots'] : array();
		}
		$config['zfs'] = $cfg['zfs'];
		$config['disks'] = $cfg['disks'];
		$config['geli'] = $cfg['geli'];
		updatenotify_set('zfs_import_config', UPDATENOTIFY_MODE_UNKNOWN, true);
		write_config();
		// remove existing pool cache
		conf_mount_rw();
		unlink_if_exists("{$g['cf_path']}/boot/zfs/zpool.cache");
		conf_mount_ro();
		header('Location: disks_zfs_config_current.php');
		exit();
	}
}

$health = true;
if (!empty($zfs['extra']) && !empty($zfs['extra']['pools']) && !empty($zfs['extra']['pools']['pool'])) {
	$health &= (bool)!array_search_ex('DEGRADED', $zfs['extra']['pools']['pool'], 'health');
	$health &= (bool)!array_search_ex('FAULTED', $zfs['extra']['pools']['pool'], 'health');
}

if (!$health) {
	$message_box_type = 'warning';
	$message_box_text = gtext('Your ZFS system is not healthy.');
	$message_box_text .= ' ';
	$message_box_text .= gtext('It is not recommanded to import non healthy pools nor virtual devices that are part of a non healthy pool.');
}

$pgtitle = array(gtext('Disks'), gtext('ZFS'), gtext('Configuration'), gtext('Synchronize'));
?>
<?php include("fbegin.inc");?>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext("Pools");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext("Datasets");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext("Volumes");?></span></a></li>
		<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext("Snapshots");?></span></a></li>
		<li class="tabact"><a href="disks_zfs_config.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Configuration");?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabinact"><a href="disks_zfs_config_current.php"><span><?=gtext("Current");?></span></a></li>
		<li class="tabinact" title="<?=gtext('Reload page');?>"><a href="disks_zfs_config.php"><span><?=gtext("Detected");?></span></a></li>
		<li class="tabact"><a href="disks_zfs_config_sync.php"><span><?=gtext("Synchronize");?></span></a></li>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
		if (!empty($message_box_text)) {
			print_core_box($message_box_type, $message_box_text);
		}
		if (isset($import) && $import === false) {
			print_error_box(gtext('Nothing to synchronize'));
		}
	?>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:14%"><!-- // Name -->
			<col style="width:9%"><!-- // Size -->
			<col style="width:9%"><!-- // Alloc -->
			<col style="width:9%"><!-- // Free -->
			<col style="width:9%"><!-- // Expandsz -->
			<col style="width:9%"><!-- // Frag -->
			<col style="width:9%"><!-- // Dedup -->
			<col style="width:9%"><!-- // Health -->
			<col style="width:9%"><!-- // Mount Point -->
			<col style="width:9%"><!-- // AltRoot -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Pools').' ('.count($zfs['pools']['pool']).')', 11);?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Alloc');?></th>
				<th class="lhell"><?=gtext('Free');?></th>
				<th class="lhell"><?=gtext('Expandsz');?></th>
				<th class="lhell"><?=gtext('Frag');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhell"><?=gtext('Health');?></th>
				<th class="lhell"><?=gtext('Mount Point');?></th>
				<th class="lhebl"><?=gtext('AltRoot');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="11"></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($zfs['pools']['pool'] as $key => $pool):?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="pool[]" value="<?=$pool['name'];?>" id="pool_<?=$pool['uuid'];?>" /></td>
					<td class="lcell"><label for="pool_<?=$pool['uuid'];?>"><?=$pool['name'];?></label></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['size'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['alloc'];?> (<?=$zfs['extra']['pools']['pool'][$key]['cap'];?>)</td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['free'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['expandsz'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['frag'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['dedup'];?></td>
					<td class="lcell"><?=$zfs['extra']['pools']['pool'][$key]['health'];?></td>
					<td class="lcell"><?=empty($pool['mountpoint']) ? "/mnt/{$pool['name']}" : $pool['mountpoint'];?></td>
					<td class="lcebl"><?=empty($pool['root']) ? '-' : $pool['root'];?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>

	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:15%"><!-- // Name -->
			<col style="width:20%"><!-- // Type -->
			<col style="width:20%"><!-- // Pool -->
			<col style="width:40%"><!-- // Devices -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Virtual Devices').' ('.count($zfs['vdevices']['vdevice']).')', 5);?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Type');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhebl"><?=gtext('Devices');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="5"></th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($zfs['vdevices']['vdevice'] as $key => $vdevice):?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="vdev[]" value="<?=$vdevice['name'];?>" id="vdev_<?=$vdevice['uuid'];?>" /></td>
					<td class="lcell"><?=$vdevice['name'];?></td>
					<td class="lcell"><?=$vdevice['type'];?></td>
					<td class="lcell"><?=$zfs['extra']['vdevices']['vdevice'][$key]['pool'];?></td>
					<td class="lcebl"><?=implode(', ', $vdevice['device']);?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
					
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:15%"><!-- // Name -->
			<col style="width:8%"><!-- // Pool -->
			<col style="width:8%"><!-- // Compression -->
			<col style="width:8%"><!-- // Dedup -->
			<col style="width:8%"><!-- // Sync -->
			<col style="width:8%"><!-- // ACL Inherit -->
			<col style="width:8%"><!-- // ACL Mode -->
			<col style="width:8%"><!-- // Canmount -->
			<col style="width:8%"><!-- // Quota -->
<!--
			<col style="width:8%">
-->
			<col style="width:8%"><!-- // Readonly -->
			<col style="width:8%"><!-- // Snapshot Visibility -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Datasets').' ('.count($zfs['datasets']['dataset']).')', 12);?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhell"><?=gtext('Compression');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhell"><?=gtext('Sync');?></th>
				<th class="lhell"><?=gtext('ACL Inherit');?></th>
				<th class="lhell"><?=gtext('ACL Mode');?></th>
				<th class="lhell"><?=gtext('Canmount');?></th>
				<th class="lhell"><?=gtext('Quota');?></th>
<!--
				<th class="lhell"><?=gtext('Extended Attributes');?></th>
-->
				<th class="lhell"><?=gtext('Readonly');?></th>
				<th class="lhebl"><?=gtext('Snapshot Visibility');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="12"></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($zfs['datasets']['dataset'] as $dataset):?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="dset[]" value="<?=$dataset['identifier'];?>" id="ds_<?=$dataset['uuid'];?>"/></td>
					<td class="lcell"><?=$dataset['name'];?></td>
					<td class="lcell"><?=$dataset['pool'];?></td>
					<td class="lcell"><?=$dataset['compression'];?></td>
					<td class="lcell"><?=$dataset['dedup'];?></td>
					<td class="lcell"><?=$dataset['sync'];?></td>
					<td class="lcell"><?=$dataset['aclinherit'];?></td>
					<td class="lcell"><?=$dataset['aclmode'];?></td>
					<td class="lcell"><?=empty($dataset['canmount']) ? 'on' : $dataset['canmount'];?></td>
					<td class="lcell"><?=empty($dataset['quota']) ? 'none' : $dataset['quota'];?></td>
<!--
					<td class="lcell"><?=empty($dataset['xattr']) ? 'off' : 'on';?></td>
-->
					<td class="lcell"><?=empty($dataset['readonly']) ? 'off' : 'on';?></td>
					<td class="lcebl"><?=empty($dataset['snapdir']) ? 'hidden' : 'visible';?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<table id="area_data_selection">
		<colgroup>
			<col style="width:5%"><!-- // Checkbox -->
			<col style="width:15%"><!-- // Name -->
			<col style="width:14%"><!-- // Pool -->
			<col style="width:11%"><!-- // Size -->
			<col style="width:11%"><!-- // Blocksize -->
			<col style="width:11%"><!-- // Sparse -->
			<col style="width:11%"><!-- // Compression -->
			<col style="width:11%"><!-- // Dedup -->
			<col style="width:11%"><!-- // Sync -->
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Volumes').' ('.count($zfs['volumes']['volume']).')', 9);?>
			<tr>
				<th class="lhelc">&nbsp;</th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Pool');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Blocksize');?></th>
				<th class="lhell"><?=gtext('Sparse');?></th>
				<th class="lhell"><?=gtext('Compression');?></th>
				<th class="lhell"><?=gtext('Dedup');?></th>
				<th class="lhebl"><?=gtext('Sync');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="8"></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($zfs['volumes']['volume'] as $volume):?>
				<tr>
					<td class="lcelc"><input type="checkbox" checked="checked" name="vol[]" value="<?=$volume['identifier'];?>" id="vol_<?=$volume['uuid'];?>"/></td>
					<td class="lcell"><?=$volume['name'];?></td>
					<td class="lcell"><?=$volume['pool'];?></td>
					<td class="lcell"><?=$volume['volsize'];?></td>
					<td class="lcell"><?=$volume['volblocksize'];?></td>
					<td class="lcell"><?=empty($volume['sparse']) ? '-' : 'on';?></td>
					<td class="lcell"><?=$volume['compression'];?></td>
					<td class="lcell"><?=$volume['dedup'];?></td>
					<td class="lcebl"><?=$volume['sync'];?></td>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Options'));?>
		</thead>
		<tbody>
			<?php
				html_checkbox2('leave_autosnapshots', gtext('Leave auto snapshot configuration'), true, gtext('Leave already configured auto snapshots.'), '', false);
				html_checkbox2('import_disks', gtext('Import disks'), true, gtext('Import disks used in configuration.'), '', false);
				html_checkbox2('import_disks_overwrite', gtext('Overwrite disks configuration'), false, gtext('Overwrite already configured disks (only affects filesystem value).'), '', false);
			?>
		</tbody>
	</table>
	<div id="submit">
		<input type="submit" name="import_config" value="<?=gtext('Synchronize');?>"/>
	</div>
	<?php include("formend.inc");?>
</form></td></tr></tbody></table>
<?php include("fend.inc");?>
