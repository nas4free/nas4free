<?php
/*
	access_users.php

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

$pgtitle = array(gtext("Access"), gtext("Users"));

if ($_POST) {
	$pconfig = $_POST;

	if (isset($_POST['apply']) && $_POST['apply']) {
		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval |= updatenotify_process("userdb_user", "userdbuser_process_updatenotification");
			config_lock();
			$retval |= rc_exec_service("userdb");
			$retval |= rc_exec_service("websrv_htpasswd");
			$retval |= rc_exec_service("fmperm");
			if (isset($config['samba']['enable'])) {
				$retval |= rc_exec_service("passdb");
				$retval |= rc_update_service("samba");
			}
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
		if ($retval == 0) {
			updatenotify_delete("userdb_user");
		}
	}
}

if (!isset($config['access']['user']) || !is_array($config['access']['user']))
	$config['access']['user'] = array();

array_sort_key($config['access']['user'], "login");
$a_user = &$config['access']['user'];
$a_group = system_get_group_list();

if (isset($_GET['act']) && $_GET['act'] === "del") {
	updatenotify_set("userdb_user", UPDATENOTIFY_MODE_DIRTY, $_GET['uuid']);
	header("Location: access_users.php");
	exit;
}

function userdbuser_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$index = array_search_ex($data, $config['access']['user'], "uuid");
			if (false !== $index) {
				unset($config['access']['user'][$index]);
				write_config();
			}
			break;
	}

	return $retval;
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="access_users.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Users");?></span></a></li>
				<li class="tabinact"><a href="access_users_groups.php"><span><?=gtext("Groups");?></span></a></li>
			</ul>
		</td>
	</tr>
  <tr>
    <td class="tabcont">
			<form action="access_users.php" method="post">
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<?php if (updatenotify_exists("userdb_user")) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<?php html_titleline2(gtext('User Setup'), 5);?>
					<tr>
						<td width="20%" class="listhdrlr"><?=gtext("User");?></td>
						<td width="25%" class="listhdrr"><?=gtext("Full Name");?></td>
						<td width="5%" class="listhdrr"><?=gtext("UID");?></td>
						<td width="40%" class="listhdrr"><?=gtext("Group");?></td>
						<td width="10%" class="list"></td>
					</tr>
					<?php foreach ($a_user as $userv):?>
					<?php $notificationmode = updatenotify_get_mode("userdb_user", $userv['uuid']);?>
					<tr>
						<td class="listlr"><?=htmlspecialchars($userv['login']);?>&nbsp;</td>
						<td class="listr"><?=htmlspecialchars($userv['fullname']);?>&nbsp;</td>
						<td class="listr"><?=htmlspecialchars($userv['id']);?>&nbsp;</td>
						<td class="listr"><?=array_search($userv['primarygroup'], $a_group); 
							if (is_array($userv['group'])) {
								for ($i = 0; $i < count($userv['group']); ) {
									echo ", ".array_search($userv['group'][$i], $a_group); ++$i; 
								}
							}?>&nbsp;</td>
						<?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
						<td valign="middle" nowrap="nowrap" class="list">
							<a href="access_users_edit.php?uuid=<?=$userv['uuid'];?>"><img src="images/edit.png" title="<?=gtext("Edit user");?>" border="0" alt="<?=gtext("Edit user");?>" /></a>&nbsp;
							<a href="access_users.php?act=del&amp;uuid=<?=$userv['uuid'];?>" onclick="return confirm('<?=gtext("Do you really want to delete this user?");?>')"><img src="images/delete.png" title="<?=gtext("Delete user");?>" border="0" alt="<?=gtext("Delete user");?>" /></a>
						</td>
						<?php else:?>
						<td valign="middle" nowrap="nowrap" class="list">
							<img src="images/delete.png" border="0" alt="" />
						</td>
						<?php endif;?>
					</tr>
					<?php endforeach;?>
					<tr>
						<td class="list" colspan="4"></td>
						<td class="list">
							<a href="access_users_edit.php"><img src="images/add.png" title="<?=gtext("Add user");?>" border="0" alt="<?=gtext("Add user");?>" /></a>
						</td>
					</tr>
				</table>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
