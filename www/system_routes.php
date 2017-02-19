<?php
/*
	system_routes.php

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
require 'co_sphere.php';

function routes_process_updatenotification($mode,$data) {
	global $config;
	$retval = 0;
	$sphere = &system_routes_get_sphere();
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			if(false !== ($sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->row_identifier()))):
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if(false !== ($sphere->row_id = array_search_ex($data,$sphere->grid,$sphere->row_identifier()))):
				rc_exec_service('routing delete conf_' . strtr($sphere->grid[$sphere->row_id][$sphere->row_identifier()],'-','_'));
				unset($sphere->grid[$sphere->row_id]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
function system_routes_get_sphere() {
	global $config;
	$sphere = new co_sphere_grid('system_routes','php');
	$sphere->modify->basename($sphere->basename() . '_edit');
	$sphere->notifier('routes');
	$sphere->row_identifier('uuid');
	$sphere->enadis(false);
	$sphere->lock(false);
	$sphere->sym_add(gtext('Add Route'));
	$sphere->sym_mod(gtext('Edit Route'));
	$sphere->sym_del(gtext('Route is marked for deletion'));
	$sphere->sym_loc(gtext('Route is protected'));
	$sphere->sym_unl(gtext('Route is unlocked'));
	$sphere->cbm_delete(gtext('Delete Selected Routes'));
	$sphere->cbm_delete_confirm(gtext('Do you want to delete selected routes?'));
	$sphere->grid = &array_make_branch($config,'staticroutes','route');
	return $sphere;
}
$sphere = &system_routes_get_sphere();
if($_POST):
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process($sphere->notifier(),$sphere->notifier_processor());
			$retval |= rc_start_service('routing');
		endif;
		$savemsg = get_std_save_message($retval);
		if ($retval == 0):
			updatenotify_delete($sphere->notifier());
		endif;
		header($sphere->header());
		exit;
	endif;
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'rows.delete':
				$sphere->cbm_array = $_POST[$sphere->cbm_name] ?? [];
				foreach($sphere->cbm_array as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->row_identifier()))):
						$mode_updatenotify = updatenotify_get_mode($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
						switch ($mode_updatenotify):
							case UPDATENOTIFY_MODE_NEW:  
								updatenotify_clear($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
								updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_DIRTY_CONFIG,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_MODIFIED:
								updatenotify_clear($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
								updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
								break;
							case UPDATENOTIFY_MODE_UNKNOWN:
								updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_DIRTY,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
								break;
						endswitch;
					endif;
				endforeach;
//				header($sphere->header());
//				exit;
				break;
			case 'rows.disable':
				$sphere->cbm_grid = $_POST[$sphere->cbm_name] ?? [];
				$updateconfig = false;
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->row_identifier()))):
						if(isset($sphere->grid[$sphere->row_id]['enable'])):
							unset($sphere->grid[$sphere->row_id]['enable']);
							$updateconfig = true;
							$mode_updatenotify = updatenotify_get_mode($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
							if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
								updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
							endif;
						endif;
					endif;
				endforeach;
				if($updateconfig):
					write_config();
					$updateconfig = false;
				endif;
				header($sphere->header());
				exit;
				break;
			case 'rows.enable':
				$sphere->cbm_grid = $_POST[$sphere->cbm_name] ?? [];
				$updateconfig = false;
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->row_identifier()))):
						if(!(isset($sphere->grid[$sphere->row_id]['enable']))):
							$sphere->grid[$sphere->row_id]['enable'] = true;
							$updateconfig = true;
							$mode_updatenotify = updatenotify_get_mode($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
							if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
								updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
							endif;
						endif;
					endif;
				endforeach;
				if($updateconfig):
					write_config();
					$updateconfig = false;
				endif;
				header($sphere->header());
				exit;
				break;
			case 'rows.toggle':
				$sphere->cbm_grid = $_POST[$sphere->cbm_name] ?? [];
				$updateconfig = false;
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->row_identifier()))):
						if(isset($sphere->grid[$sphere->row_id]['enable'])):
							unset($sphere->grid[$sphere->row_id]['enable']);
						else:
							$sphere->grid[$sphere->row_id]['enable'] = true;					
						endif;
						$updateconfig = true;
						$mode_updatenotify = updatenotify_get_mode($sphere->notifier(),$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
						if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
							updatenotify_set($sphere->notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->grid[$sphere->row_id][$sphere->row_identifier()]);
						endif;
					endif;
				endforeach;
				if($updateconfig):
					write_config();
					$updateconfig = false;
				endif;
				header($sphere->header());
				exit;
				break;
		endswitch;
	endif;
endif;
array_sort_key($sphere->grid,'network');
$a_iflabel = ['lan' => 'LAN', 'wan' => 'WAN', 'pptp' => 'PPTP'];
for($j = 1;;$j++):
	$key = sprintf('opt%s',$j);
	if(isset($config['interfaces'][$key])):
		$a_iflabel[$key] = $config['interfaces'][$key]['descr'] ?? $key;
	else:
		break;
	endif;
endfor;
$pgtitle = [gtext('Network'),gtext('Static Routes')];
include 'fbegin.inc';
echo $sphere->doj();
?>
<form action="<?=$sphere->scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists($sphere->notifier())):
		print_config_change_box();
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:20%">
			<col style="width:20%">
			<col style="width:30%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gtext('Overview'),6);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Interface');?></th>
				<th class="lhell"><?=gtext('Network');?></th>
				<th class="lhell"><?=gtext('Gateway');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			foreach ($sphere->grid as $sphere->row):
				$notificationmode = updatenotify_get_mode($sphere->notifier(),$sphere->row[$sphere->row_identifier()]);
				$notdirty = (UPDATENOTIFY_MODE_DIRTY != $notificationmode) && (UPDATENOTIFY_MODE_DIRTY_CONFIG != $notificationmode);
				$enabled = $sphere->enadis() ? isset($sphere->row['enable']) : true;
				$notprotected = $sphere->lock() ? !isset($sphere->row['protected']) : true;
?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
<?php
						if($notdirty && $notprotected):
							echo $sphere->html_checkbox_cbm(false);
						else:
							echo $sphere->html_checkbox_cbm(true);
						endif;
?>
					</td>
					<td class="lcell">
<?php
						if(isset($a_iflabel[$sphere->row['interface']])):
							echo htmlspecialchars($a_iflabel[$sphere->row['interface']]);
						else:
							echo gtext('Unknown');
						endif;
?>
					</td>
					<td class="lcell"><?=htmlspecialchars(strtolower($sphere->row['network']));?></td>
					<td class="lcell"><?=htmlspecialchars(strtolower($sphere->row['gateway']));?></td>
					<td class="lcell"><?=htmlspecialchars($sphere->row['descr']);?></td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><colgroup><col style="width:33%"><col style="width:34%"><col style="width:33%"></colgroup><tbody><tr>
<?php
							echo $sphere->html_toolbox($notprotected,$notdirty);
?>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
<?php
			echo $sphere->html_footer_add(6);
?>
		</tfoot>
	</table>
	<div id="submit">
<?php
		if($sphere->enadis()):
			if($sphere->toggle()):
				echo $sphere->html_button_toggle_rows();
			else:
				echo $sphere->html_button_enable_rows();
				echo $sphere->html_button_disable_rows();
			endif;
		endif;
		echo $sphere->html_button_delete_rows();
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
