<?php
/*
	interfaces_vlan.php

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

function vlan_inuse($ifn) {
	global $config, $g;
	if(isset($config['interfaces']['lan']['if']) && ($config['interfaces']['lan']['if'] === $ifn)):
		return true;
	endif;
	if(isset($config['interfaces']['wan']['if']) && ($config['interfaces']['wan']['if'] === $ifn)):
		return true;
	endif;
	for($i = 1;isset($config['interfaces']['opt' . $i]);$i++):
		if(isset($config['interfaces']['opt' . $i]['if']) && ($config['interfaces']['opt' . $i]['if'] === $ifn)):
			return true;
		endif;
	endfor;
	return false;
}
function interfaces_vlan_get_sphere() {
	global $config;
	$sphere = new co_sphere_grid('interfaces_vlan','php');
	$sphere->modify->basename($sphere->basename() . '_edit');
	$sphere->notifier('ifvlan');
	$sphere->row_identifier('uuid');
	$sphere->enadis(false);
	$sphere->lock(false);
	$sphere->sym_add(gtext('Add VLAN'));
	$sphere->sym_mod(gtext('Edit VLAN'));
	$sphere->sym_del(gtext('VLAN is marked for deletion'));
	$sphere->sym_loc(gtext('VLAN is protected'));
	$sphere->sym_unl(gtext('VLAN is unlocked'));
	$sphere->cbm_delete(gtext('Delete Selected VLANs'));
	$sphere->cbm_delete_confirm(gtext('Do you want to delete selected VLANs?'));
	$sphere->grid = &array_make_branch($config,'vinterfaces','vlan');
	return $sphere;
}
$sphere = &interfaces_vlan_get_sphere();
array_sort_key($sphere->grid,'if');
if($_POST):
	if(isset($_POST['submit'])):
		switch($_POST['submit']):
			case 'rows.delete':
				$sphere->cbm_grid = $_POST[$sphere->cbm_name] ?? [];
				$updateconfig = false;
				foreach($sphere->cbm_grid as $sphere->cbm_row):
					if(false !== ($sphere->row_id = array_search_ex($sphere->cbm_row,$sphere->grid,$sphere->row_identifier()))):
						$sphere->row = $sphere->grid[$sphere->record_id];
						//	Check if interface is still in use.
						if(vlan_inuse($sphere->row['if'])):
							$input_errors[] = htmlspecialchars($sphere->row['if']) . ': ' . gtext('VLAN cannot be deleted because it is still being used as an interface.');
						else:
							$cmd = sprintf('/usr/local/sbin/rconf attribute remove %s',escapeshellarg('ifconfig_' . $sphere->row['if']));
							mwexec($cmd);
							unset($sphere->grid[$sphere->row_id]);
							$updateconfig = true;
						endif;
					endif;
				endforeach;
				if($updateconfig):
					write_config();
					touch($d_sysrebootreqd_path);
					header($sphere->header());
					exit;
				endif;
				break;
		endswitch;
	endif;
endif;
$pgtitle = [gtext('Network'),gtext('Interface Management'),gtext('VLAN')];
include 'fbegin.inc';
echo $sphere->doj();
?>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"> <ul id="tabnav">
		<li class="tabinact"><a href="interfaces_assign.php"><span><?=gtext('Management');?></span></a></li>
		<li class="tabinact"><a href="interfaces_wlan.php"><span><?=gtext('WLAN');?></span></a></li>
		<li class="tabact"><a href="<?=$sphere->scriptname();?>" title="<?=gtext('Reload page');?>"><span><?=gtext('VLAN');?></span></a></li>
		<li class="tabinact"><a href="interfaces_lagg.php"><span><?=gtext('LAGG');?></span></a></li>
		<li class="tabinact"><a href="interfaces_bridge.php"><span><?=gtext('Bridge');?></span></a></li>
		<li class="tabinact"><a href="interfaces_carp.php"><span><?=gtext('CARP');?></span></a></li>
	</ul></td></tr>
</tbody></table>
<form action="<?=$sphere->scriptname();?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:20%">
			<col style="width:20%">
			<col style="width:10%">
			<col style="width:35%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gtext('Overview'),6);
?>
			<tr>
				<th class="lhelc"><?=$sphere->html_checkbox_toggle_cbm();?></th>
				<th class="lhell"><?=gtext('Virtual Interface');?></th>
				<th class="lhell"><?=gtext('Physical Interface');?></th>
				<th class="lhell"><?=gtext('VLAN Tag');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			$notificationmode = false;
			$notdirty = true;
			foreach($sphere->grid as $sphere->row):
				$enabled = $sphere->enadis() ? isset($sphere->row['enable']) : true;
				$notprotected = $sphere->lock() ? !isset($sphere->row['protected']) : true;
?>
				<tr>
					<td class="<?=$enabled ? "lcelc" : "lcelcd";?>">
<?php
						if($notdirty && $notprotected && !vlan_inuse($sphere->row['if'])):
							echo $sphere->html_checkbox_cbm(false);
						else:
							echo $sphere->html_checkbox_cbm(true);
						endif;
?>
					</td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['if']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['vlandev']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['tag']);?></td>
					<td class="<?=$enabled ? "lcell" : "lcelld";?>"><?=htmlspecialchars($sphere->row['desc']);?></td>
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
	<div id="remarks">
<?php
		html_remark2('note',gtext('Note'),gtext('Not all drivers/NICs support 802.1Q VLAN tagging properly. On cards that do not explicitly support it, VLAN tagging will still work, but the reduced MTU may cause problems.'));
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
?>
