<?php
/*
	disks_zfs_volume_edit.php

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
require 'zfs.inc';

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: disks_zfs_volume.php';
$sphere_notifier = 'zfsvolume';
$sphere_array = [];
$sphere_record = [];
$prerequisites_ok = true;

$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD); // detect page mode
if(PAGE_MODE_POST == $mode_page): // POST is Cancel or not Submit => cleanup
	if((isset($_POST['Cancel']) && $_POST['Cancel']) || !(isset($_POST['Submit']) && $_POST['Submit'])):
		header($sphere_header_parent);
		exit;
	endif;
endif;

function get_volblocksize($pool, $name) {
	mwexec2('zfs get -H -o value volblocksize '.escapeshellarg($pool.'/'.$name).' 2>&1', $rawdata);
	return $rawdata[0];
}

if((PAGE_MODE_POST == $mode_page) && isset($_POST['uuid']) && is_uuid_v4($_POST['uuid'])):
	$sphere_record['uuid'] = $_POST['uuid'];
else:
	if((PAGE_MODE_EDIT == $mode_page) && isset($_GET['uuid']) && is_uuid_v4($_GET['uuid'])):
		$sphere_record['uuid'] = $_GET['uuid'];
	else:
		$mode_page = PAGE_MODE_ADD; // Force ADD
		$sphere_record['uuid'] = uuid();
	endif;
endif;

$a_dataset = &array_make_branch($config,'zfs','datasets','dataset');
if(empty($a_dataset)):
else:	
	array_sort_key($a_dataset,'name');
endif;
$sphere_array = &array_make_branch($config,'zfs','volumes','volume');
if(empty($sphere_array)):
else:
	array_sort_key($sphere_array,'name');
endif;
$a_pool = &array_make_branch($config,'zfs','pools','pool');
if(empty($a_pool)): // Throw error message if no pool exists
	$errormsg = gtext('No configured pools.') . ' ' . '<a href="' . 'disks_zfs_zpool.php' . '">' . gtext('Please add new pools first.') . '</a>';
	$prerequisites_ok = false;
else:
	array_sort_key($a_pool,'name');
endif;

$index = array_search_ex($sphere_record['uuid'], $sphere_array, 'uuid'); // get index from config for volume by looking up uuid
$mode_updatenotify = updatenotify_get_mode($sphere_notifier, $sphere_record['uuid']); // get updatenotify mode for uuid
$mode_record = RECORD_ERROR;
if(false !== $index): // uuid found
	if((PAGE_MODE_POST == $mode_page || (PAGE_MODE_EDIT == $mode_page))): // POST or EDIT
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		endswitch;
	endif;
else: // uuid not found
	if((PAGE_MODE_POST == $mode_page) || (PAGE_MODE_ADD == $mode_page)): // POST or ADD
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		endswitch;
	endif;
endif;
if(RECORD_ERROR == $mode_record): // oops, someone tries to cheat, over and out
	header($sphere_header_parent);
	exit;
endif;
$isrecordnew = (RECORD_NEW === $mode_record);
$isrecordnewmodify = (RECORD_NEW_MODIFY == $mode_record);
$isrecordmodify = (RECORD_MODIFY === $mode_record);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);

if(PAGE_MODE_POST == $mode_page): // POST Submit, already confirmed
	unset($input_errors);
	// apply post values that are applicable for all record modes
	$sphere_record['volsize'] = isset($_POST['volsize']) ? $_POST['volsize'] : '';
	$sphere_record['volmode'] = isset($_POST['volmode']) ? $_POST['volmode'] : '';
	$sphere_record['compression'] = isset($_POST['compression']) ? $_POST['compression'] : '';
	$sphere_record['dedup'] = isset($_POST['dedup']) ? $_POST['dedup'] : '';
	$sphere_record['sync'] = isset($_POST['sync']) ? $_POST['sync'] : '';
	$sphere_record['sparse'] = isset($_POST['sparse']);
	$sphere_record['desc'] = isset($_POST['desc']) ? $_POST['desc'] : '';
	switch($mode_record):
		case RECORD_NEW:
		case RECORD_NEW_MODIFY:
			$sphere_record['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$sphere_record['pool'] = isset($_POST['pool']) ? $_POST['pool'] : '';
			$sphere_record['volblocksize'] = isset($_POST['volblocksize']) ? $_POST['volblocksize'] : '';
			break;
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['pool'] = $sphere_array[$index]['pool'][0];
			$sphere_record['volblocksize'] = $sphere_array[$index]['volblocksize'];
			break;
	endswitch;

	// Input validation
	$reqdfields = ['pool', 'name', 'volsize'];
	$reqdfieldsn = [gtext('Pool'), gtext('Name'), gtext('Size')];
	$reqdfieldst = ['string', 'string', 'string'];

	do_input_validation($sphere_record, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($sphere_record, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if(empty($input_errors)):
		// check for a valid name with the format name[/name], blanks are not supported.
		$helpinghand = preg_quote('.:-_', '/');
		if(!(preg_match('/^[a-z\d][a-z\d'.$helpinghand.']*(?:\/[a-z\d][a-z\d'.$helpinghand.']*)*$/i', $sphere_record['name']))):
			$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext('Name'));
		endif;
	endif;
	
	// 1. RECORD_MODIFY: throw error if posted pool is different from configured pool.
	// 2. RECORD_NEW: posted pool/name must not exist in configuration or live.
	// 3. RECORD_NEW_MODIFY: if posted pool/name is different from configured pool/name: posted pool/name must not exist in configuration or live.
	// 4. RECORD_MODIFY: if posted name is different from configured name: pool/posted name must not exist in configuration or live.
	// 
	// 1.
	if(empty($input_errors)):
		if($isrecordmodify && (0 !== strcmp($sphere_array[$index]['pool'][0], $sphere_record['pool']))):
			$input_errors[] = gtext('Pool cannot be changed.');
		endif;
	endif;
	// 2., 3., 4.
	if(empty($input_errors)):
		$poolslashname = escapeshellarg($sphere_record['pool']."/".$sphere_record['name']); // create quoted full dataset name
		if($isrecordnew || (!$isrecordnew && (0 !== strcmp(escapeshellarg($sphere_array[$index]['pool'][0]."/".$sphere_array[$index]['name']), $poolslashname)))):
			// throw error when pool/name already exists in live
			if(empty($input_errors)):
				mwexec2(sprintf("zfs get -H -o value type %s 2>&1", $poolslashname), $retdat, $retval);
				switch($retval):
					case 1: // An error occured. => zfs dataset doesn't exist
						break;
					case 0: // Successful completion. => zfs dataset found
						$input_errors[] = sprintf(gtext('%s already exists as a %s.'), $poolslashname, $retdat[0]);
						break;
 					case 2: // Invalid command line options were specified.
						$input_errors[] = gtext('Failed to execute command zfs.');
						break;
				endswitch;
			endif;
			// throw error when pool/name exists in configuration file, zfs->volumes->volume[]
			if(empty($input_errors)):
				foreach ($sphere_array as $r_volume):
					if(0 === strcmp(escapeshellarg($r_volume['pool'][0].'/'.$r_volume['name']), $poolslashname)):
						$input_errors[] = sprintf(gtext('%s is already configured as a volume.'), $poolslashname);
						break;
					endif;
				endforeach;
			endif;
			// throw error when pool/name exists in configuration file, zfs->datasets->dataset[] 
			if(empty($input_errors)):
				foreach($a_dataset as $r_dataset):
					if(0 === strcmp(escapeshellarg($r_dataset['pool'][0].'/'.$r_dataset['name']), $poolslashname)):
						$input_errors[] = sprintf(gtext('%s is already configured as a filesystem.'), $poolslashname);
						break;
					endif;
				endforeach;
			endif;
		endif;
	endif;
	
	if($prerequisites_ok && empty($input_errors)):
		// convert listtags to arrays
		$helpinghand = $sphere_record['pool'];
		$sphere_record['pool'] = [$helpinghand];
		if($isrecordnew):
			$sphere_array[] = $sphere_record;
			updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_NEW, $sphere_record['uuid']);
		else:
			$sphere_array[$index] = $sphere_record;
			if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
				updatenotify_set($sphere_notifier, UPDATENOTIFY_MODE_MODIFIED, $sphere_record['uuid']);
			endif;
		endif;
		write_config();
		header($sphere_header_parent); // cleanup
		exit;
	endif;
else:
	switch($mode_record):
		case RECORD_NEW:
			$sphere_record['name'] = '';
			$sphere_record['pool'] = '';
			$sphere_record['volsize'] = '';
			$sphere_record['volmode'] = 'default';
			$sphere_record['volblocksize'] = '';
			$sphere_record['compression'] = 'off';
			$sphere_record['dedup'] = 'off';
			$sphere_record['sync'] = 'standard';
			$sphere_record['sparse'] = false;
			$sphere_record['desc'] = '';
			break;
		case RECORD_NEW_MODIFY: // get from config only
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['pool'] = $sphere_array[$index]['pool'][0];
			$sphere_record['volsize'] = $sphere_array[$index]['volsize'];
			$sphere_record['volmode'] = $sphere_array[$index]['volmode'];
			$sphere_record['volblocksize'] = $sphere_array[$index]['volblocksize'];
			$sphere_record['compression'] = $sphere_array[$index]['compression'];
			$sphere_record['dedup'] = $sphere_array[$index]['dedup'];
			$sphere_record['sync'] = $sphere_array[$index]['sync'];
			$sphere_record['sparse'] = isset($sphere_array[$index]['sparse']);
			$sphere_record['desc'] = $sphere_array[$index]['desc'];
			break;
		case RECORD_MODIFY: // get from config or system
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['pool'] = $sphere_array[$index]['pool'][0];
			$sphere_record['volsize'] = $sphere_array[$index]['volsize'];
			$sphere_record['volmode'] = $sphere_array[$index]['volmode'];
			$sphere_record['volblocksize'] = get_volblocksize($sphere_record['pool'], $sphere_record['name']);
			$sphere_record['compression'] = $sphere_array[$index]['compression'];
			$sphere_record['dedup'] = $sphere_array[$index]['dedup'];
			$sphere_record['sync'] = $sphere_array[$index]['sync'];
			$sphere_record['sparse'] = isset($sphere_array[$index]['sparse']);
			$sphere_record['desc'] = $sphere_array[$index]['desc'];
			break;
	endswitch;
endif;
$a_poollist = zfs_get_pool_list();
$l_poollist = [];
foreach ($a_pool as $r_pool):
	$r_poollist = $a_poollist[$r_pool['name']];
	$helpinghand = $r_pool['name'].': '.$r_poollist['size']; 
	if(!empty($r_pool['desc'])):
		$helpinghand .= ' '.$r_pool['desc'];
	endif;
	$l_poollist[$r_pool['name']] = htmlspecialchars($helpinghand);
endforeach;
$l_volmode = [
	'default' => gtext('Default'),
	'geom' => 'geom',
	'dev' => 'dev',
	'none' => 'none'
];
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
$l_volblocksize = [
	'' => gtext('Default'),
	'512B' => '512B',
	'1K' => '1K',
	'2K' => '2K',
	'4K' => '4K',
	'8K' => '8K',
	'16K' => '16K',
	'32K' => '32K',
	'64K' => '64K',
	'128K' => '128K'
];

$pgtitle = [gtext('Disks'), gtext('ZFS'), gtext('Volumes'), gtext('Volume'), ($isrecordnew) ? gtext('Add') : gtext('Edit')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
//]]>
</script>
<table id="area_navigator">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="disks_zfs_zpool.php"><span><?=gtext('Pools');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_dataset.php"><span><?=gtext('Datasets');?></span></a></li>
				<li class="tabact"><a href="disks_zfs_volume.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Volumes');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_snapshot.php"><span><?=gtext('Snapshots');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_config.php"><span><?=gtext('Configuration');?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabact"><a href="disks_zfs_volume.php" title="<?=gtext('Reload page');?>"><span><?=gtext('Volume');?></span></a></li>
				<li class="tabinact"><a href="disks_zfs_volume_info.php"><span><?=gtext('Information');?></span></a></li>
			</ul>
		</td>
	</tr>
</table>
<table id="area_data"><tbody><tr><td id="area_data_frame"><form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform">
	<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
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
			<?php html_titleline2(gtext('Settings'));?>
		</thead>
		<tbody>
			<?php
			html_inputbox2('name',gtext('Name'),$sphere_record['name'],'',true,60,$isrecordmodify,false,128,gtext('Enter a name for this volume'));
			html_combobox2('pool',gtext('Pool'),$sphere_record['pool'],$l_poollist,'',true,$isrecordmodify);
			html_inputbox2('volsize',gtext('Size'),$sphere_record['volsize'],gtext("ZFS volume size. To specify the size use the following human-readable suffixes (for example, 'K', 'KB', 'M', 'GB', etc.)."),true,10);
			html_combobox2('volmode',gtext('Volume Mode'),$sphere_record['volmode'],$l_volmode, gtext('Specifies how the volume should be exposed to the OS.'),true);
			html_combobox2('compression',gtext('Compression'),$sphere_record['compression'],$l_compressionmode,gtext("Controls the compression algorithm used for this volume. 'LZ4' is now the recommended compression algorithm. Setting compression to 'On' uses the LZ4 compression algorithm if the feature flag lz4_compress is active, otherwise LZJB is used. You can specify the 'GZIP' level by using the value 'GZIP-N', where N is an integer from 1 (fastest) to 9 (best compression ratio). Currently, 'GZIP' is equivalent to 'GZIP-6'."),true);
			$helpinghand = gtext('Controls the dedup method.')
				. ' '
				. '<br><b>'
				. '<font color="red">' . gtext('WARNING') . '</font>'
				. ': '
				. '<a href="http://wiki.nas4free.org/doku.php?id=documentation:setup_and_user_guide:disks-zfs-volumes-volume" target="_blank">'
				. gtext('See ZFS volumes & deduplication wiki article BEFORE using this feature.')
				. '</a>'
				. '</b></br>';
			html_combobox2('dedup',gtext('Dedup'),$sphere_record['dedup'],$l_dedup,$helpinghand,true);
			html_combobox2('sync',gtext('Sync'),$sphere_record['sync'],$l_sync,gtext('Controls the behavior of synchronous requests.'),true);
			html_checkbox2('sparse',gtext('Sparse Volume'),!empty($sphere_record['sparse']) ? true : false,gtext('Use as sparse volume. (thin provisioning)'),'',false);
			html_combobox2('volblocksize',gtext('Block Size'),$sphere_record['volblocksize'],$l_volblocksize,gtext('ZFS volume block size. This value can not be changed after creation.'),false,$isrecordmodify);
			html_inputbox2('desc',gtext('Description'),$sphere_record['desc'],gtext('You may enter a description here for your reference.'),false,40,false,false,40,gtext('Enter a description'));
			?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=($isrecordnew) ? gtext('Add') : gtext('Save');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>"/>
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>"/>
	</div>
	<?php include 'formend.inc';?>
</form></td></tr></tbody></table>
<?php include 'fend.inc';?>
