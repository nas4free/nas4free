<?php
/*
	disks_zfs_dataset_edit.php

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
require("auth.inc");
require("guiconfig.inc");
require("zfs.inc");

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: disks_zfs_dataset.php';
$sphere_notifier = 'zfsdataset';
$sphere_array = [];
$sphere_record = [];
$prerequisites_ok = true;

$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD); // detect page mode
if (PAGE_MODE_POST == $mode_page) { // POST is Cancel or not Submit => cleanup
	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header($sphere_header_parent);
		exit;
	}
	if (!(isset($_POST['Submit']) && $_POST['Submit'])) {
		header($sphere_header_parent);
		exit;
	}
}

if ((PAGE_MODE_POST == $mode_page) && isset($_POST['uuid']) && is_uuid_v4($_POST['uuid'])) {
	$sphere_record['uuid'] = $_POST['uuid'];
} else {
	if ((PAGE_MODE_EDIT == $mode_page) && isset($_GET['uuid']) && is_uuid_v4($_GET['uuid'])) {
		$sphere_record['uuid'] = $_GET['uuid'];
	} else {
		$mode_page = PAGE_MODE_ADD; // Force ADD
		$sphere_record['uuid'] = uuid();
	}
}

if (!(isset($config['zfs']) && is_array($config['zfs']))) {
	$config['zfs'] = [];
}
if (!(isset($config['zfs']['datasets']) && is_array($config['zfs']['datasets']))) {
	$config['zfs']['datasets'] = [];
}
if (!(isset($config['zfs']['datasets']['dataset']) && is_array($config['zfs']['datasets']['dataset']))) {
	$config['zfs']['datasets']['dataset'] = [];
}
array_sort_key($config['zfs']['datasets']['dataset'], 'name');
$sphere_array = &$config['zfs']['datasets']['dataset'];

if (!(isset($config['zfs']['volumes']) && is_array($config['zfs']['volumes']))) {
	$config['zfs']['volumes'] = [];
}
if (!(isset($config['zfs']['volumes']['volume']) && is_array($config['zfs']['volumes']['volume']))) {
	$config['zfs']['volumes']['volume'] = [];
}
array_sort_key($config['zfs']['volumes']['volume'], 'name');
$a_volume = &$config['zfs']['volumes']['volume'];

if (!(isset($config['zfs']['pools']) && is_array($config['zfs']['pools']))) {
	$config['zfs']['pools'] = [];
}
if (!(isset($config['zfs']['pools']['pool']) && is_array($config['zfs']['pools']['pool']))) {
	$config['zfs']['pools']['pool'] = [];
	$errormsg = gtext('No configured pools.') . ' ' . '<a href="' . 'disks_zfs_zpool.php' . '">' . gtext('Please add new pools first.') . '</a>';
	$prerequisites_ok = false;
}
array_sort_key($config['zfs']['pools']['pool'], 'name');
$a_pool = &$config['zfs']['pools']['pool'];

$index = array_search_ex($sphere_record['uuid'], $sphere_array, 'uuid'); // get index from config for dataset by looking up uuid
$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']); // get updatenotify mode for uuid
$mode_record = RECORD_ERROR;
if (false !== $index) { // uuid found
	if ((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))) { // POST or EDIT
		switch ($mode_updatenotify) {
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		}
	}
} else { // uuid not found
	if ((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page)) { // POST or ADD
		switch ($mode_updatenotify) {
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		}
	}
}
if (RECORD_ERROR == $mode_record) { // oops, someone tries to cheat, over and out
	header($sphere_header_parent);
	exit;
}
$isrecordnew = (RECORD_NEW === $mode_record);
$isrecordnewmodify = (RECORD_NEW_MODIFY === $mode_record);
$isrecordmodify = (RECORD_MODIFY === $mode_record);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);

if (PAGE_MODE_POST == $mode_page) { // POST Submit, already confirmed
	unset($input_errors);
	// apply post values that are applicable for all record modes
	$sphere_record['compression'] = $_POST['compression'] ?? '';
	$sphere_record['dedup'] = $_POST['dedup'] ?? '';
	$sphere_record['sync'] = $_POST['sync'] ?? '';
	$sphere_record['atime'] = $_POST['atime'] ?? '';
	$sphere_record['aclinherit'] = $_POST['aclinherit'] ?? '';
	$sphere_record['aclmode'] = $_POST['aclmode'] ?? '';
	$sphere_record['canmount'] = isset($_POST['canmount']);
	$sphere_record['readonly'] = isset($_POST['readonly']);
	$sphere_record['xattr'] = isset($_POST['xattr']);
	$sphere_record['snapdir'] = isset($_POST['snapdir']);
	$sphere_record['quota'] = $_POST['quota'] ?? '';
	$sphere_record['reservation'] = $_POST['reservation'] ?? '';
	$sphere_record['desc'] = $_POST['desc'] ?? '';
	$sphere_record['accessrestrictions']['owner'] = $_POST['owner'] ?? '';
	$sphere_record['accessrestrictions']['group'] = $_POST['group'] ?? '';
	$helpinghand = 0;
	if (isset($_POST['mode_access']) && is_array($_POST['mode_access']) && count($_POST['mode_access'] < 10)) {
		foreach ($_POST['mode_access'] as $r_mode_access) {
			$helpinghand |= (257 > $r_mode_access) ? $r_mode_access : 0;
		}
	}
	$sphere_record['accessrestrictions']['mode'] = sprintf( "%04o", $helpinghand);
	switch ($mode_record) {
		case RECORD_NEW:
		case RECORD_NEW_MODIFY:
			$sphere_record['name'] = $_POST['name'] ?? '';
			$sphere_record['pool'] = $_POST['pool'] ?? '';
			$sphere_record['casesensitivity'] = $_POST['casesensitivity'] ?? '';
			break;
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['pool'] = $sphere_array[$index]['pool'][0];
			break;
	}
	
	// Input validation
	$reqdfields = ['pool', 'name'];
	$reqdfieldsn = [gtext('Pool'), gtext('Name')];
	$reqdfieldst = ['string', 'string'];

	do_input_validation($sphere_record, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($sphere_record, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if ($prerequisites_ok && empty($input_errors)) { // check for a valid name with format name[/name], blanks are excluded.
		if (false === zfs_is_valid_dataset_name($sphere_record['name'])) {
			$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext('Name'));
		}
	}
	
	// 1. RECORD_MODIFY: throw error if posted pool is different from configured pool.
	// 2. RECORD_NEW: posted pool/name must not exist in configuration or live.
	// 3. RECORD_NEW_MODIFY: if posted pool/name is different from configured pool/name: posted pool/name must not exist in configuration or live.
	// 4. RECORD_MODIFY: if posted name is different from configured name: pool/posted name must not exist in configuration or live.
	// 
	// 1.
	if ($prerequisites_ok && empty($input_errors)) {
		if ($isrecordmodify && (0 !== strcmp($sphere_array[$index]['pool'][0], $sphere_record['pool']))) {
			$input_errors[] = gtext('Pool cannot be changed.');
		}
	}
	// 2., 3., 4.
	if ($prerequisites_ok && empty($input_errors)) {
		$poolslashname = escapeshellarg($sphere_record['pool']."/".$sphere_record['name']); // create quoted full dataset name
		if ($isrecordnew || (!$isrecordnew && (0 !== strcmp(escapeshellarg($sphere_array[$index]['pool'][0]."/".$sphere_array[$index]['name']), $poolslashname)))) {
			// throw error when pool/name already exists in live
			if (empty($input_errors)) {
				mwexec2(sprintf("zfs get -H -o value type %s 2>&1", $poolslashname), $retdat, $retval);
				switch ($retval) {
					case 1: // An error occured. => zfs dataset doesn't exist
						break;
					case 0: // Successful completion. => zfs dataset found
						$input_errors[] = sprintf(gtext('%s already exists as a %s.'), $poolslashname, $retdat[0]);
						break;
 					case 2: // Invalid command line options were specified.
						$input_errors[] = gtext('Failed to execute command zfs.');
						break;
				}
			}
			// throw error when pool/name exists in configuration file, zfs->volumes->volume[]
			if (empty($input_errors)) {
				foreach ($a_volume as $r_volume) {
					if (0 === strcmp(escapeshellarg($r_volume['pool'][0]."/".$r_volume['name']), $poolslashname)) {
						$input_errors[] = sprintf(gtext('%s is already configured as a volume.'), $poolslashname);
						break;
					}
				}
			}
			// throw error when  pool/name exists in configuration file, zfs->datasets->dataset[] 
			if (empty($input_errors)) {
				foreach ($sphere_array as $r_dataset) {
					if (0 === strcmp(escapeshellarg($r_dataset['pool'][0]."/".$r_dataset['name']), $poolslashname)) {
						$input_errors[] = sprintf(gtext('%s is already configured as a filesystem.'), $poolslashname);
						break;
					}
				}
			}
		}
	}

	if ($prerequisites_ok && empty($input_errors)) {
		// convert listtags to arrays
		$helpinghand = $sphere_record['pool'];
		$sphere_record['pool'] = [$helpinghand]; 
		$helpinghand = $sphere_record['accessrestrictions']['group'];
		$sphere_record['accessrestrictions']['group'] = [$helpinghand];
		if ($isrecordnew) {
			$sphere_array[] = $sphere_record;
			updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_NEW, $sphere_record['uuid']);
		} else {
			$sphere_array[$index] = $sphere_record;
			// avoid unnecessary notifications, avoid mode modify if mode new already exists
			if (UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify) {
				updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
			}
		}
		write_config();
		header($sphere_header_parent);
		exit;		
	}
} else { // EDIT / ADD
	switch ($mode_record) {
		case RECORD_NEW:
			$sphere_record['name'] = '';
			$sphere_record['pool'] = '';
			$sphere_record['compression'] = 'off';
			$sphere_record['dedup'] = 'off';
			$sphere_record['sync'] = 'standard';
			$sphere_record['atime'] = 'off';
			$sphere_record['aclinherit'] = 'restricted';
			$sphere_record['aclmode'] = 'discard';
			$sphere_record['casesensitivity'] = 'sensitive';
			$sphere_record['canmount'] = true;
			$sphere_record['readonly'] = false;
			$sphere_record['xattr'] = true;
			$sphere_record['snapdir'] = false;
			$sphere_record['quota'] = '';
			$sphere_record['reservation'] = '';
			$sphere_record['desc'] = '';
			$sphere_record['accessrestrictions']['owner'] = 'root';
			$sphere_record['accessrestrictions']['group'] = 'wheel';
			$sphere_record['accessrestrictions']['mode'] = '0777';
			break;
		case RECORD_NEW_MODIFY:
			$sphere_record['casesensitivity'] = $sphere_array[$index]['casesensitivity'] ?? 'sensitive';
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['pool'] = $sphere_array[$index]['pool'][0];
			$sphere_record['compression'] = $sphere_array[$index]['compression'];
			$sphere_record['dedup'] = $sphere_array[$index]['dedup'];
			$sphere_record['sync'] = $sphere_array[$index]['sync'];
			$sphere_record['atime'] = $sphere_array[$index]['atime'];	
			$sphere_record['aclinherit'] = $sphere_array[$index]['aclinherit'];
			$sphere_record['aclmode'] = $sphere_array[$index]['aclmode'];
			$sphere_record['canmount'] = isset($sphere_array[$index]['canmount']);
			$sphere_record['readonly'] = isset($sphere_array[$index]['readonly']);
			$sphere_record['xattr'] = isset($sphere_array[$index]['xattr']);
			$sphere_record['snapdir'] = isset($sphere_array[$index]['snapdir']);
			$sphere_record['quota'] = $sphere_array[$index]['quota'];
			$sphere_record['reservation'] = $sphere_array[$index]['reservation'];
			$sphere_record['desc'] = $sphere_array[$index]['desc'];
			$sphere_record['accessrestrictions']['owner'] = $sphere_array[$index]['accessrestrictions']['owner'];
			$sphere_record['accessrestrictions']['group'] = $sphere_array[$index]['accessrestrictions']['group'][0];
			$sphere_record['accessrestrictions']['mode'] = $sphere_array[$index]['accessrestrictions']['mode'];
			break;
	}
}

$a_poollist = zfs_get_pool_list();
$l_poollist = [];
foreach ($a_pool as $r_pool) {
	$r_poollist = $a_poollist[$r_pool['name']];
	$helpinghand = $r_pool['name'].': '.$r_poollist['size'];
	if (!empty($r_pool['desc'])) {
		$helpinghand .= ' '.$r_pool['desc'];
	}
	$l_poollist[$r_pool['name']] = htmlspecialchars($helpinghand);
}
$l_compressionmode = [
	'on' => gtext('On'),
	'off' => gtext('Off'),
	'lz4' => 'lz4',
	'lzjb' => 'lzjb',
	'gzip' => 'gzip',
	'gzip-1' => 'gzip-1',
	'gzip-2' => 'gzip-2',
	'gzip-3' => 'gzip-3',
	'gzip-4' => 'gzip-4',
	'gzip-5' => 'gzip-5',
	'gzip-6' => 'gzip-6',
	'gzip-7' => 'gzip-7',
	'gzip-8' => 'gzip-8',
	'gzip-9' => 'gzip-9',
	'zle' => 'zle'
];
$l_dedup = [
	'on' => gtext('On'),
	'off' => gtext('Off'),
	'verify' => gtext('Verify'),
	'sha256' => 'SHA256',
	'sha256,verify' => gtext('SHA256, Verify')
];		
$l_sync = [
	'standard' => gtext('Standard'),
	'always' => gtext('Always'),
	'disabled' => gtext('Disabled')
];
$l_atime = [
	'on' => gtext('On'),
	'off' => gtext('Off')
];
$l_aclinherit = [
	'discard' => gtext('Discard - Do not inherit entries'),
	'noallow' => gtext('Noallow - Only inherit deny entries'),
	'restricted' => gtext('Restricted - Inherit all but "write ACL" and "change owner"'),
	'passthrough' => gtext('Passthrough - Inherit all entries'),
	'passthrough-x' => gtext('Passthrough-X - Inherit all but "execute" when not specified')
];
$l_aclmode = [
	'discard' => gtext('Discard - Discard ACL'),
	'groupmask' => gtext('Groupmask - Mask ACL with mode'),
	'passthrough' => gtext('Passthrough - Do not change ACL'),
	'restricted' => gtext('Restricted')
];
$l_casesensitivity = [
	'sensitive' => gtext('Sensitive'),
	'insensitive' => gtext('Insensitive'),
	'mixed' => gtext('Mixed')
];
$l_users = [];
foreach (system_get_user_list() as $r_key => $r_value) {
	$l_users[$r_key] = htmlspecialchars($r_key);
}
$l_groups = [];
foreach (system_get_group_list() as $r_key => $r_value) {
	$l_groups[$r_key] = htmlspecialchars($r_key);
}
// Calculate value of access right checkboxes, contains a) 0 for not checked or b) the required bit mask value
$mode_access = [];
$helpinghand = octdec($sphere_record['accessrestrictions']['mode']);
for ($i = 0; $i < 9; $i++) {
	$mode_access[$i] = $helpinghand & (1 << $i);
}

$pgtitle = [gtext('Disks'), gtext('ZFS'), gtext('Datasets'), gtext('Dataset'), $isrecordnew ? gtext('Add') : gtext('Edit')];
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
//]]>
</script>
<table id="area_navigator"><tbody>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabact"><a href="disks_zfs_dataset.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume.php"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabact"><a href="disks_zfs_dataset.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Dataset');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset_info.php"><span><?=gtext('Information');?></span></a></li>
			</ul>
		</td>
	</tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
		if (!empty($errormsg)) { print_error_box($errormsg); }
		if (!empty($input_errors)) { print_input_errors($input_errors); }
		if (file_exists($d_sysrebootreqd_path)) { print_info_box(get_std_save_message(0)); }
	?>
	<table id="area_data_settings">
		<colgroup>
			<col id="area_data_settings_col_tag">
			<col id="area_data_settings_col_data">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('Settings'));?>
		</thead>
		<tbody>
			<?php
				html_inputbox2('name', gtext('Name'), $sphere_record['name'], '', true, 60, $isrecordmodify, false, 60);
				html_combobox2('pool', gtext('Pool'), $sphere_record['pool'], $l_poollist, '', true, $isrecordmodify);
				html_combobox2('compression', gtext('Compression'), $sphere_record['compression'], $l_compressionmode, gtext("Controls the compression algorithm used for this dataset. The 'lzjb' compression algorithm is optimized for performance while providing decent data compression. Setting compression to 'On' uses the 'lzjb' compression algorithm. You can specify the 'gzip' level by using the value 'gzip-N', where N is an integer from 1 (fastest) to 9 (best compression ratio). Currently, 'gzip' is equivalent to 'gzip-6'."), true);
				$helpinghand = gtext('Controls the dedup method.')
					. ' '
					. '<br><b>'
					. '<font color="red">' . gtext('WARNING') . '</font>'
					. ': '
					. '<a href="http://wiki.nas4free.org/doku.php?id=documentation:setup_and_user_guide:disks_zfs_datasets_dataset" target="_blank">'
					. gtext('See ZFS datasets & deduplication wiki article BEFORE using this feature.')
					. '</a>'
					. '</b></br>';
				html_combobox2('dedup', gtext('Dedup'), $sphere_record['dedup'], $l_dedup, $helpinghand, true);
				html_combobox2('sync', gtext('Sync'), $sphere_record['sync'], $l_sync, gtext('Controls the behavior of synchronous requests.'), true);
				html_combobox2('atime', gtext('Access Time (atime)'), $sphere_record['atime'], $l_atime, gtext('Turn access time on or off for this dataset.'), true);
				html_combobox2('aclinherit', gtext('ACL inherit'), $sphere_record['aclinherit'], $l_aclinherit, gtext('This attribute determines the behavior of Access Control List inheritance.'), true);
				html_combobox2('aclmode', gtext('ACL mode'), $sphere_record['aclmode'], $l_aclmode, gtext('This attribute controls the ACL behavior when a file is created or whenever the mode of a file or a directory is modified.'), true);
				if ($isrecordnewornewmodify) {
					html_combobox2('casesensitivity', gtext('Case Sensitivity'), $sphere_record['casesensitivity'], $l_casesensitivity, gtext('This property indicates whether the file name matching algorithm used by the file system should be casesensitive, caseinsensitive, or allow a combination of both styles of matching'), false);
				}
				html_checkbox2('canmount', gtext('Canmount'), !empty($sphere_record['canmount']) ? true : false, gtext('If this property is disabled, the file system cannot be mounted.'), '', false);
				html_checkbox2('readonly', gtext('Readonly'), !empty($sphere_record['readonly']) ? true : false, gtext('Controls whether this dataset can be modified.'), '', false);
				html_checkbox2('xattr', gtext('Extended attributes'), !empty($sphere_record['xattr']) ? true : false, gtext('Enable extended attributes for this file system.'), '', false);
				html_checkbox2('snapdir', gtext('Snapshot Visibility'), !empty($sphere_record['snapdir']) ? true : false, gtext('If this property is enabled, the snapshots are displayed into .zfs directory.'), '', false);
				html_inputbox2('reservation', gtext('Reservation'), $sphere_record['reservation'], gtext("The minimum amount of space guaranteed to a dataset (usually empty). To specify the size use the following human-readable suffixes (for example, 'k', 'KB', 'M', 'Gb', etc.)."), false, 10);
				html_inputbox2('quota', gtext('Quota'), $sphere_record['quota'], gtext("Limits the amount of space a dataset and its descendants can consume. This property enforces a hard limit on the amount of space used. This includes all space consumed by descendants, including file systems and snapshots. To specify the size use the following human-readable suffixes (for example, 'k', 'KB', 'M', 'Gb', etc.)."), false, 10);
				html_inputbox2('desc', gtext('Description'), $sphere_record['desc'], gtext('You may enter a description here for your reference.'), false, 40);
				html_separator2();
				html_titleline2(gtext('Access Restrictions'));
				html_combobox2('owner', gtext('Owner'), $sphere_record['accessrestrictions']['owner'], $l_users, '', false);
				html_combobox2('group', gtext('Group'), $sphere_record['accessrestrictions']['group'], $l_groups, '', false);
			?>
			<tr>
				<td class="celltag"><?=gtext('Mode');?></td>
				<td class="celldata">
					<table id="area_data_selection">
						<colgroup>
							<col style="width:25%">
							<col style="width:25%">
							<col style="width:25%">
							<col style="width:25%">
						</colgroup>
						<thead>
							<tr>
								<td class="lhell"><?=gtext('Who');?></td>
								<td class="lhelc"><?=gtext('Read');?></td>
								<td class="lhelc"><?=gtext('Write');?></td>
								<td class="lhebc"><?=gtext('Execute');?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="lcell"><?=gtext('Owner');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="owner_r" value="256" <?php if ($mode_access[8] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="owner_w" value="128" <?php if ($mode_access[7] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="owner_x" value= "64" <?php if ($mode_access[6] > 0) echo "checked=\"checked\"";?>/></td>
							</tr>
							<tr>
								<td class="lcell"><?=gtext('Group');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="group_r" value= "32" <?php if ($mode_access[5] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="group_w" value= "16" <?php if ($mode_access[4] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="group_x" value=  "8" <?php if ($mode_access[3] > 0) echo "checked=\"checked\"";?>/></td>
							</tr>
							<tr>
								<td class="lcell"><?=gtext('Others');?>&nbsp;</td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="other_r" value=  "4" <?php if ($mode_access[2] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcelc"><input type="checkbox" name="mode_access[]" id="other_w" value=  "2" <?php if ($mode_access[1] > 0) echo "checked=\"checked\"";?>/></td>
								<td class="lcebc"><input type="checkbox" name="mode_access[]" id="other_x" value=  "1" <?php if ($mode_access[0] > 0) echo "checked=\"checked\"";?>/></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=$isrecordnew ? gtext('Add') : gtext('Save');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>" />
	</div>
	<?php include("formend.inc");?>
</form></td></tr></tbody></table>
<?php include("fend.inc");?>
