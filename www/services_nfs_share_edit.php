<?php
/*
	services_nfs_share_edit.php

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

if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$a_share = &array_make_branch($config,'nfsd','share');
if(empty($a_share)):
else:
	array_sort_key($a_share,'path');
endif;

function ismounted_or_dataset($path)
{
	if (disks_ismounted_ex($path, "mp"))
		return true;

	mwexec2("/sbin/zfs list -H -o mountpoint", $rawdata);
	foreach ($rawdata as $line) {
		$mp = trim($line);
		if ($mp == "-")
			conitnue;
		if ($path == $mp)
			return true;
	}

	return false;
}

if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_share, "uuid")))) {
	$pconfig['uuid'] = $a_share[$cnid]['uuid'];
	$pconfig['path'] = $a_share[$cnid]['path'];
	$pconfig['mapall'] = $a_share[$cnid]['mapall'];
	list($pconfig['network'], $pconfig['mask']) = explode('/', $a_share[$cnid]['network']);
	$pconfig['comment'] = $a_share[$cnid]['comment'];
	$pconfig['v4rootdir'] = isset($a_share[$cnid]['v4rootdir']);
	$pconfig['alldirs'] = isset($a_share[$cnid]['options']['alldirs']);
	$pconfig['readonly'] = isset($a_share[$cnid]['options']['ro']);
	$pconfig['quiet'] = isset($a_share[$cnid]['options']['quiet']);
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['path'] = "";
	$pconfig['mapall'] = "yes";
	$pconfig['network'] = "";
	$pconfig['mask'] = "24";
	$pconfig['comment'] = "";
	$pconfig['v4rootdir'] = false;
	$pconfig['alldirs'] = false;
	$pconfig['readonly'] = false;
	$pconfig['quiet'] = false;
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: services_nfs_share.php");
		exit;
	}

	// Input validation.
	$reqdfields = ['path','network','mask'];
	$reqdfieldsn = [gtext('Share'),gtext('Authorised network'),gtext('Network mask')];
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	// remove last slash and check alldirs option
	$path = $_POST['path'];
	if (strlen($path) > 1 && $path[strlen($path)-1] == "/") {
		$path = substr($path, 0, strlen($path)-1);
	}
	if ($path == "/") {
		// allow alldirs
	} else if (isset($_POST['quiet'])) {
		// might be delayed mount
	} else if (isset($_POST['alldirs']) && !ismounted_or_dataset($path)) {
	   $input_errors[] = sprintf(gtext("All dirs requires mounted path, but Path %s is not mounted."), $path);
	}

	if (empty($input_errors)) {
		$share = [];
		$share['uuid'] = $_POST['uuid'];
		$share['path'] = $path;
		$share['mapall'] = $_POST['mapall'];
		$share['network'] = gen_subnet($_POST['network'], $_POST['mask']) . "/" . $_POST['mask'];
		$share['comment'] = $_POST['comment'];
		$share['v4rootdir'] = isset($_POST['v4rootdir']) ? true : false;
		$share['options']['alldirs'] = isset($_POST['alldirs']) ? true : false;
		$share['options']['ro'] = isset($_POST['readonly']) ? true : false;
		$share['options']['quiet'] = isset($_POST['quiet']) ? true : false;

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_share[$cnid] = $share;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_share[] = $share;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("nfsshare", $mode, $share['uuid']);
		write_config();

		header("Location: services_nfs_share.php");
		exit;
	}
}
$pgtitle = [gtext('Services'),gtext('NFS'),isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_nfs.php"><span><?=gtext("Settings");?></span></a></li>
				<li class="tabact"><a href="services_nfs_share.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Shares");?></span></a></li>
				</ul>
			</td>
	</tr>
		<tr>
		<td class="tabcont">
			<form action="services_nfs_share_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if ($input_errors) print_input_errors($input_errors);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline(gtext("Share Settings"));?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Share");?></td>
						<td width="78%" class="vtable">
							<input name="path" type="text" class="formfld" id="path" size="60" value="<?=htmlspecialchars($pconfig['path']);?>" />
							<input name="browse" type="button" class="formbtn" id="Browse" onclick='ifield = form.path; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value)+"&amp;sd=<?=$g['media_path'];?>", "filechooser", "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield;' value="..." /><br />
							<span class="vexpl"><?=gtext("Path to be shared.");?> <?=gtext("Please note that blanks in path names are not allowed.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Map to Root"); ?></td>
						<td width="78%" class="vtable">
							<select name="mapall" class="formfld" id="mapall">
								<?php $types = [gtext('Yes'),gtext('No')];?>
								<?php $vals = explode(" ", "yes no");?>
								<?php $j = 0; for ($j = 0; $j < count($vals); $j++): ?>
								<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['mapall']) echo "selected=\"selected\"";?>>
									<?=htmlspecialchars($types[$j]);?>
								</option>
								<?php endfor; ?>
							</select><br />
							<span class="vexpl"><?=gtext("Map all users to root, all users will have root privileges.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Authorised Network");?></td>
						<td width="78%" class="vtable">
							<input name="network" type="text" class="formfld" id="network" size="20" value="<?=htmlspecialchars($pconfig['network']);?>" /> /
							<select name="mask" class="formfld" id="mask">
								<?php for ($i = 32; $i >= 1; $i--):?>
									<option value="<?=$i;?>" <?php if ($i == $pconfig['mask']) echo "selected=\"selected\"";?>><?=$i;?></option>
								<?php endfor;?>
							</select><br />
							<span class="vexpl"><?=gtext("Network that is authorised to access the NFS share.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Comment");?></td>
						<td width="78%" class="vtable">
							<input name="comment" type="text" class="formfld" id="comment" size="30" value="<?=htmlspecialchars($pconfig['comment']);?>" />
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("NFSv4");?></td>
						<td width="78%" class="vtable">
							<input name="v4rootdir" type="checkbox" id="v4rootdir" value="yes" <?php if (!empty($pconfig['v4rootdir'])) echo "checked=\"checked\"";?> />
							<span class="vexpl"><?=gtext("Specified path is NFSv4 root directory.");?></span><br />
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("All Dirs");?></td>
						<td width="78%" class="vtable">
							<input name="alldirs" type="checkbox" id="alldirs" value="yes" <?php if (!empty($pconfig['alldirs'])) echo "checked=\"checked\"";?> />
							<span class="vexpl"><?=gtext("Export all the directories in the specified path.");?></span><br />
							<?=sprintf(gtext("To use subdirectories, you must mount each directories. (e.g. %s)"), "mount -t nfs host:/mnt/path/subdir /path/to/mount");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Read Only");?></td>
						<td width="78%" class="vtable">
							<input name="readonly" type="checkbox" id="readonly" value="yes" <?php if (!empty($pconfig['readonly'])) echo "checked=\"checked\"";?> />
							<span class="vexpl"><?=gtext("Specifies that the file system should be exported read-only.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Quiet");?></td>
						<td width="78%" class="vtable">
							<input name="quiet" type="checkbox" id="quiet" value="yes" <?php if (!empty($pconfig['quiet'])) echo "checked=\"checked\"";?> />
							<span class="vexpl"><?=gtext("Inhibit some of the syslog diagnostics for bad lines in /etc/exports.");?></span>
						</td>
					</tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
