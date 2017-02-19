<?php
/*
	disks_mount.php

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

$pgtitle = [gtext('Disks'),gtext('Mount Point'),gtext('Management')];

if ($_POST) {
	$pconfig = $_POST;

	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			// Process notifications
			updatenotify_process("mountpoint", "mountmanagement_process_updatenotification");

			// Restart services
			config_lock();
			$retval |= rc_update_service("samba");
			$retval |= rc_update_service("rsyncd");
			$retval |= rc_update_service("netatalk");
			$retval |= rc_update_service("rpcbind"); // !!! Do
			$retval |= rc_update_service("mountd");  // !!! not
			$retval |= rc_update_service("nfsd");    // !!! change
			$retval |= rc_update_service("statd");   // !!! this
			$retval |= rc_update_service("lockd");   // !!! order
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("mountpoint");
		}
		header("Location: disks_mount.php");
		exit;
	}
}
$a_mount = &array_make_branch($config,'mounts','mount');
if(empty($a_mount)):
else:
	array_sort_key($a_mount,'devicespecialfile');
endif;
if (isset($_GET['act']) && $_GET['act'] === "del") {
	$index = array_search_ex($_GET['uuid'], $config['mounts']['mount'], "uuid");
	if (false !== $index) {
		// MUST check if mount point is used by swap.
		if ((isset($config['system']['swap']['enable'])) &&
			($config['system']['swap']['type'] === "file") &&
			($config['system']['swap']['mountpoint'] === $_GET['uuid'])) {
			$errormsg[] = gtext(sprintf("A swap file is located on the mounted device %s.",
				$config['mounts']['mount'][$index]['devicespecialfile']));
		} else {
			updatenotify_set("mountpoint", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
			header("Location: disks_mount.php");
			exit;
		}
	}
}

if (isset($_GET['act']) && $_GET['act'] === "retry") {
	$index = array_search_ex($_GET['uuid'], $config['mounts']['mount'], "uuid");
	if (false !== $index) {
		if (0 == disks_mount($config['mounts']['mount'][$index])) {
			rc_update_service("samba");
			rc_update_service("rsyncd");
			rc_update_service("netatalk");
			rc_update_service("rpcbind"); // !!! Do
			rc_update_service("mountd");  // !!! not
			rc_update_service("nfsd");    // !!! change
			rc_update_service("statd");   // !!! this
			rc_update_service("lockd");   // !!! order
		}
		header("Location: disks_mount.php");
		exit;
	}
}

function mountmanagement_process_updatenotification($mode, $data) {
	global $config;

	if (empty($config['mounts']['mount']) || !is_array($config['mounts']['mount']))
		return 1;

	$index = array_search_ex($data, $config['mounts']['mount'], "uuid");
	if (false === $index)
		return 1;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
			disks_mount($config['mounts']['mount'][$index]);
			break;

		case UPDATENOTIFY_MODE_MODIFIED:
			disks_umount_ex($config['mounts']['mount'][$index]);
			disks_mount($config['mounts']['mount'][$index]);
			break;

		case UPDATENOTIFY_MODE_DIRTY:
			disks_umount($config['mounts']['mount'][$index]);
			unset($config['mounts']['mount'][$index]);
			write_config();
			break;
	}
}
?>
<?php include 'fbegin.inc';?>
<?php if(!empty($errormsg)) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
        <li class="tabact"><a href="disks_mount.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Management");?></span></a></li>
        <li class="tabinact"><a href="disks_mount_tools.php"><span><?=gtext("Tools");?></span></a></li>
        <li class="tabinact"><a href="disks_mount_fsck.php"><span><?=gtext("Fsck");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <form action="disks_mount.php" method="post">
        <?php if ($savemsg) print_info_box($savemsg);?>
        <?php if (updatenotify_exists("mountpoint")) print_config_change_box();?>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?php html_titleline2(gtext('Mount Point Management'), 6);?>
          <tr>
            <td width="30%" class="listhdrlr"><?=gtext('Disk');?></td>
            <td width="15%" class="listhdrr"><?=gtext('File System');?></td>
            <td width="15%" class="listhdrr"><?=gtext('Name');?></td>
            <td width="20%" class="listhdrr"><?=gtext('Description');?></td>
            <td width="13%" class="listhdrr"><?=gtext('Status');?></td>
            <td width="7%" class="list"></td>
          </tr>
					<?php foreach($a_mount as $mount):?>
					<?php
					$notificationmode = updatenotify_get_mode("mountpoint", $mount['uuid']);
					switch ($notificationmode) {
						case UPDATENOTIFY_MODE_NEW:
							$status = gtext("Initializing");
							break;
						case UPDATENOTIFY_MODE_MODIFIED:
							$status = gtext("Modifying");
							break;
						case UPDATENOTIFY_MODE_DIRTY:
							$status = gtext("Deleting");
							break;
						default:
							if(disks_ismounted_ex($mount['sharename'],"sharename")) {
								$status = gtext("OK");
							} else {
								$status = gtext("Error") . " - <a href=\"disks_mount.php?act=retry&uuid={$mount['uuid']}\">" . gtext("Retry") . "</a>";
							}
							break;
					}
					?>
          <tr>
          	<?php if ("disk" === $mount['type']):?>
            <td class="listlr"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;<?php if ($mount['fstype'] == "ufs" && preg_match('/^\/dev\/(.+)$/', $mount['mdisk'], $match)) { echo "({$match[1]}{$mount['partition']})"; } ?></td>
            <?php elseif ("hvol" === $mount['type']):?>
            <td class="listlr"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;<?php if ($mount['fstype'] == "ufs" && preg_match('/^\/dev\/(.+)$/', $mount['mdisk'], $match)) { echo "({$match[1]}{$mount['partition']})"; } ?></td>
            <?php elseif ("custom" === $mount['type']):?>
            <td class="listlr"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;</td>
            <?php else:?>
            <td class="listlr"><?=htmlspecialchars($mount['filename']);?>&nbsp;</td>
            <?php endif;?>
            <td class="listr"><?=htmlspecialchars($mount['fstype']);?>&nbsp;</td>
            <td class="listr"><?=htmlspecialchars($mount['sharename']);?>&nbsp;</td>
            <td class="listr"><?=htmlspecialchars($mount['desc']);?>&nbsp;</td>
            <td class="listbg"><?=$status;?>&nbsp;</td>
            <?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
            <td valign="middle" nowrap="nowrap" class="list">
              <a href="disks_mount_edit.php?uuid=<?=$mount['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit mount point");?>" border="0" alt="<?=gtext("Edit mount point");?>" /></a>&nbsp;
              <a href="disks_mount.php?act=del&amp;uuid=<?=$mount['uuid'];?>" onclick="return confirm('<?=gtext("Do you really want to delete this mount point? All elements that still use it will become invalid (e.g. share)!");?>')"><img src="images/delete.png" title="<?=gtext("Delete mount point");?>" border="0" alt="<?=gtext("Delete mount point");?>" /></a>
            </td>
            <?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="images/delete.png" border="0" alt="" />
						</td>
						<?php endif;?>
          </tr>
          <?php endforeach;?>
          <tr>
            <td class="list" colspan="5"></td>
            <td class="list"><a href="disks_mount_edit.php"><img src="images/add.png" title="<?=gtext("Add mount point");?>" border="0" alt="<?=gtext("Add mount point");?>" /></a></td>
          </tr>
        </table>
        <div id="remarks">
		<?php html_remark("Warning", gtext("Warning"), sprintf(gtext("UFS and ZFS are NATIVE filesystems of %s. Attempting to use other filesystems such as EXT2, EXT3, EXT4, FAT, FAT32, or NTFS can result in unpredictable results, file corruption and the loss of data!"), get_product_name()));?>
        </div>
<?php include 'formend.inc';?>
</form>
</td>
</tr>
</table>
<?php include 'fend.inc';?>
