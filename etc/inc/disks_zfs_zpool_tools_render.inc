<?php
/*
	disks_zfs_zpool_tools_render.inc

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
require_once 'config.inc';
require_once 'util.inc';

function render_set_start() {
	$gt_title = gtext('ZFS Pool Tools');
	echo '<table id="area_data_settings">',"\n";
		echo '<colgroup>',"\n";
			echo '<col id="area_data_settings_col_tag">',"\n";
			echo '<col id="area_data_settings_col_data">',"\n";
		echo '</colgroup>',"\n";
		echo '<thead>',html_titleline2($gt_title),'</thead>',"\n";
		echo '<tbody>',"\n";
	return true;
}
function render_command_and_execute($subcommand,$a_param = [],$b_exec = true) {
	$param = implode(' ',$a_param);
	$output = sprintf('%1$s %2$s %3$s','zpool',$subcommand,$param);
	html_text2('syscommand',gtext('Command'),$output);
	echo '<tr>',"\n",'<td class="celltag">',gtext('Output'),'</td><td class="celldata"><pre>';
	if($b_exec):
		$result = zfs_zpool_cmd($subcommand,$param,true);
	else:
		$result = 7;
	endif;
	echo '</pre></td></tr>';
	return $result;
}
function render_zpool_status($param,$b_exec = true) {
	echo '<tr>',"\n",'<td class="celltag">',gtext('Pool Status'),'</td><td class="celldata"><pre>';
	if($b_exec):
		$result = zfs_zpool_cmd('status',escapeshellarg($param),true);
	else:
		$result = 7;
	endif;
	echo '</pre></td></tr>';
	return $result;
}
function render_command_result($result = 0,$render_success = true,$render_error = true) {
	switch($result):
		case 0:
			if($render_success):
				html_text2('success',gtext('Success'),gtext('Command execution was successful'));
			endif;
			break;
		case 2:
			if($render_error):
				html_text2('error',gtext('Error'),gtext('Invalid command line option'));
			endif;
			break;
		case 7: // test mode
			if($render_success):
				html_text2('success',gtext('Success'),gtext('Test mode, no command execution'));
			endif;
			break;
		case 15: // rendering problem, rendering function reported no items
			if($render_error):
				html_text2('error',gtext('Error'),gtext('Missing parameter'));
			endif;
			break;
//		case 1:
		default:
			if($render_error):
				html_text2('error',gtext('Error'),gtext('An error occured.'));
			endif;
			break;
	endswitch;
}
function render_set_end() {
/*
 * End of setting table
 */
		echo '</tbody>',"\n";
	echo '</table>',"\n";
	return true;
}
function render_submit($pageindex = 1,$activity = '',$option = '',$a_pool = [],$a_flag = []) {
/*
 * Displays the submit area of the page
 */
	echo '<div id="submit">',"\n";
	if(1 === $pageindex):
		echo '<input name="reload" type="submit" value="',gtext('Return to Command page'),'" class="formbtn"/>',"\n";
	else:
		echo '<input type="submit" name="submit" value="',gtext('Next'),'" class="formbtn"/>',"\n";
	endif;
	echo '<input type="hidden" name="pageindex" value="',$pageindex,'"/>',"\n";
	if(!empty($activity)):
		echo '<input type="hidden" name="activity[]" value="',$activity,'"/>',"\n";
	endif;
	if(!empty($option)):
		echo '<input type="hidden" name="option[]" value="',$option,'"/>',"\n";
	endif;
	if(is_array($a_flag) && (0 < count($a_flag))):
		foreach($a_flag as $r_flag):
			echo '<input type="hidden" name="flag[]" value="',$r_flag,'"/>',"\n";
		endforeach;
	endif;
	if(is_array($a_pool) && (0 < count($a_pool))):
		foreach($a_pool as $r_pool):
			echo '<input type="hidden" name="pool[]" value="',$r_pool,'"/>',"\n";
		endforeach;
	endif;
	echo '</div>',"\n";
	return true;
}
function render_activity_view($gt_activity_longname) {
/*
 * Displays the activity
 * Embedded in the body of a table with 2 columns
 */
	html_text2('sysactivity',gtext('Activity'),$gt_activity_longname);
	return true;
}
function render_option_view($gt_option_name) {
/*
 * Displays the name of the selected option
 * Embedded in the body of a table with 2 columns
 */
	html_text2('sysoption',gtext('Option'),$gt_option_name);
	return true;
}
function render_pool_edit($a_items = [],$format = '0N',$a_selected_items = []) {
/*
 * Displays a list of radio or checkbox input fields for available pools
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="pool[]"',
		'value' => NULL,
		'checked' => '',
		'required' => ''
	];
	switch ($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">',gtext('Pools'),'</td>'; // left column
		echo '<td class="celldata">'; // right column start
			echo '<table id="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:35%">',"\n"; // Name
					echo '<col style="width:30%">',"\n"; // Version
					echo '<col style="width:35%">',"\n"; // Pool Status Name
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglepool" name="togglepool" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhell">',gtext('Pool'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Pool Version'),'</th>',"\n";
						echo '<th class="lhebl">',gtext('Pool State'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$value = $r_item['pool.name'];
							$attribute['value'] = sprintf(' value="%s"',$value);
							if(0 < count($a_selected_items)):
								$attribute['checked'] = (false === array_search($value,$a_selected_items) ? '' : 'checked="checked"');
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($value),'</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($r_item['pool.version']),'</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($r_item['pool.state_name']),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gtext('No suitable pools found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcell">',$value,'</td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcebl"></td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_pool_view($selected_items = []) { // $item can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($selected_items)):
		if(1 < count($selected_items)):
			$last = array_pop($selected_items);
			$content = sprintf('%s %s %s',implode(', ',$selected_items),gtext('and'),$last);
		else:
			$content = implode(' ',$selected_items);
		endif;
	elseif(is_string($selected_items)):
		$content = $selected_items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = htmlspecialchars($content);
	else:
		$b_ret = false;
		$output = gtext('No pools have been selected');
	endif;
	html_text2('syspool',gtext('Pools'),$output);
	return $b_ret;
}
function render_pooldev_edit($a_items = [],$format = '1',$a_selected_items = []) {
/*
 * Displays a list of radio or checkbox input fields for pool devices
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="pooldev[]"',
		'value' => NULL,
		'checked' => '',
		'required' => ''
	];
	switch ($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Pool Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table id="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:45%">',"\n"; // // Device Name
					echo '<col style="width:10%">',"\n"; // // Device Type
					echo '<col style="width:20%">',"\n"; // // Virtual Device
					echo '<col style="width:20%">',"\n"; // // Pool
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglepooldev" name="togglepooldev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhell">',gtext('Device'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Type'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Virtual Device'),'</th>',"\n";
						echo '<th class="lhebl">',gtext('Pool'),'</th>',"\n";						
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$value = $r_item['device.path'];
							$attribute['value'] = sprintf('value="%s"',$value);
							if(0 < count($a_selected_items)):
								$attribute['checked'] = (false === array_search($value,$a_selected_items) ? '' : 'checked="checked"');
							endif;
							$vdevice = '';
							if(preg_match('/\S/',$r_item['vdev.type'])):
								$vdevice = $r_item['vdev.type'];
								if(preg_match('/\S/',$r_item['vdev.id'])):
									$vdevice .= '-' . $r_item['vdev.id'];
								endif;
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($value),'</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($r_item['device.type']),'</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($vdevice),'</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($r_item['pool.name']),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gtext('No suitable devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcell">',$value,'</td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcebl"></td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_pooldev_view($items = []) { // $items can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($items)):
		if(1 < count($items)):
			$last = array_pop($items);
			$content = sprintf('%s %s %s',implode(', ',$items),gtext('and'),$last);
		else:
			$content = implode(' ',$items);
		endif;
	elseif(is_string($items)):
		$content = $items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = htmlspecialchars($content);
	else:
		$b_ret = false;
		$output = gtext('No pool devices have been selected');
	endif;
	html_text2('syspooldev',gtext('Pool Devices'),$output);
	return $b_ret;
}
function render_newvdev_edit($a_items = [],$format = '0N',$a_selected_items = []) {
/*
 * Displays a list of radio checkbox input fields for available virtual devices
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="newvdev[]"',
		'value' => NULL,
		'checked' => '',
		'required' => ''
	];
	switch ($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Virtual Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table id="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:95%">',"\n"; // Name
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglenewvdev" name="togglenewvdev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhebl">',gtext('Virtual Device'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$value = $r_item['name'];
							$attribute['value'] = sprintf(' value="%s"',$value);
							if(0 < count($a_selected_items)):
								$attribute['checked'] = (false === array_search($value,$a_selected_items) ? '' : 'checked="checked"');
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($value),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gtext('No suitable virtual devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcebl">',$value,'</td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_newvdev_view($items = []) { // $items can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($items)):
		if(1 < count($items)):
			$last = array_pop($items);
			$content = sprintf('%s %s %s',implode(', ',$items),gtext('and'),$last);
		else:
			$content = implode(' ',$items);
		endif;
	elseif(is_string($items)):
		$content = $items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = htmlspecialchars($content);
	else:
		$b_ret = false;
		$output = gtext('No virtual devices have been selected');
	endif;
	html_text2('sysvdev',gtext('Virtual Devices'),$output);
	return $b_ret;
}
function render_newdev_edit($a_items = [],$format = '1',$a_selected_items = []) {
/*
 * Displays a list of radio or checkbox input fields for available devices
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="newdev[]"',
		'value' => NULL,
		'checked' => '',
		'required' => ''
	];
	switch ($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table id="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:95%">',"\n"; // Device Name
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglenewdev" name="togglenewdev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhebl">',gtext('Device'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$value = $r_item['name'];
							$attribute['value'] = sprintf(' value="%s"',$value);
							if(0 < count($a_selected_items)):
								$attribute['checked'] = (false === array_search($value,$a_selected_items) ? '' : 'checked="checked"');
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($value),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gtext('No suitable devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcebl">',$value,'</td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_newdev_view($items = []) { // $items can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($items)):
		if(1 < count($items)):
			$last = array_pop($items);
			$content = sprintf('%s %s %s',implode(', ',$items),gtext('and'),$last);
		else:
			$content = implode(' ',$items);
		endif;
	elseif(is_string($items)):
		$content = $items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = htmlspecialchars($content);
	else:
		$b_ret = false;
		$output = gtext('No devices have been selected');
	endif;
	html_text2('sysvdevice',gtext('Devices'),$output);
	return $b_ret;
}
function render_selector_radio($gt_title,$a_option,$r_option_selected = '') {
/*
 * Displays a list of radio input fields for available options
 * Embedded in the body of a table with 2 columns
 * fields:
 *	name - text - the name of the radio button
 *	value - text - the return value of the radio button
 *	show - - bool - the radio button won't be shown if set to false
 *	longtext - text - Explanation of the option
 *
 * An option gets preselected when it matches $r_option_selected 
 */
	$b_check_default = (false === array_search_ex($r_option_selected,$a_option,'value'));
	if(0 < count($a_option)):
		echo '<tr>';
			echo '<td class="celltag">';  // left hand column start
				echo $gt_title;
			echo '</td>'; // left hand column end
			echo '<td class="celldata">'; // right hand column start
				echo '<table id="area_data_selection">',"\n";
					echo '<colgroup>',"\n";
						echo '<col style="width:5%">',"\n"; // Radio
						echo '<col style="width:95%">',"\n"; // // Name
					echo '</colgroup>',"\n";
					echo '<thead>',"\n";
//						html_titleline2($gt_title,2);
						echo '<tr>',"\n";
							echo '<th class="lhelc"></th>',"\n";
							echo '<th class="lhebl">',$gt_title,'</th>',"\n";
						echo '</tr>',"\n";
					echo '</thead>',"\n";
					echo '<tbody>',"\n";
						foreach($a_option as $r_option):
							if($r_option['show']):
								$attribute = [
									'name' => sprintf(' name="%s[]"',$r_option['name']),
									'value' => sprintf(' value="%s"',$r_option['value']),
									'type' => ' type="radio"',
									'checked' => '',
									'longname' => $r_option['longname']
								];
								if($b_check_default):
									if($r_option['default']):
										$attribute['checked'] = ' checked="checked"';
									endif;
								else:
									if($r_option_selected === $r_option['value']):
										$attribute['checked'] = ' checked="checked"';
									endif;
								endif;
								echo '<tr>';
									echo '<td class="lcelc">';
										echo '<input',$attribute['type'],$attribute['name'],$attribute['value'],$attribute['checked'],'/>';
									echo '</td>',"\n";
									echo '<td class="lcebl">',$attribute['longname'],'</td>',"\n";
								echo '</tr>';
							endif;
						endforeach;
					echo '</tbody>',"\n";
				echo '</table>',"\n";
			echo '</td>',"\n"; // right hand column end
		echo '</tr>',"\n";
	endif;
	return true;
}
class co_zpool_flag_base {
	public $a_pool_keys = [];
	public function __construct() {
		$this->init_pool_keys();
		return true;
	}
	public function init_pool_keys() {
		$this->a_pool_keys = [];
		$this->load_pool_keys();
	}
	public function load_pool_keys() {
		$this->a_pool_keys['force'] = ['name' => 'flag','value' => 'force','show' => true,'default' => false,'longname' => gtext('Force Operation')];
		$this->a_pool_keys['gptlabel'] = ['name' => 'flag','value' => 'gptlabel','show' => true,'default' => false,'longname' => gtext('Import using GPT labels')];
		$this->a_pool_keys['gptid'] = ['name' => 'flag','value' => 'gptid','show' => true,'default' => false,'longname' => gtext('Import using GPT ID')];
		$this->a_pool_keys['sfaiapf'] = ['name' => 'flag','value' => 'sfaiapf','show' => true,'default' => false,'longname' => gtext('Search for and import all disks found')];
		$this->a_pool_keys['test'] = ['name' => 'flag','value' => 'test','show' => true,'default' => false,'longname' => gtext('Test Mode')];
		return true;
	}
}
class co_zpool_flags extends co_zpool_flag_base {
	public $a_available_keys = [];
	public $a_selected_keys = [];
	public $c_tag_header;
	public $c_val_header_name;
	public $c_val_none;
	public function __construct($a_available_keys = [],$a_selected_keys = []) {
		parent::__construct();
		$this->c_tag_header = gtext('Parameter');
		$this->c_val_header_name = gtext('Parameter');
		$this->c_val_none = gtext('None');
		$this->init_available_keys($a_available_keys);
		$this->init_selected_keys($a_selected_keys);
	}
	public function init_available_keys($a_keynames = []) {
/*
 *	Resets the list of available keynames and loads a new list 
 */
		$this->a_available_keys = [];
		$this->load_available_keys($a_keynames);
		return true;
	}
	public function load_available_keys($a_keynames = []) {
/*
 *	loads an array of keynames into the list of available keynames. The provided keynames must be valid pool keys
 */
		$a_temp = array_combine($a_keynames,$a_keynames); // create keys
		$a_intersect = array_intersect_key($a_temp,$this->a_pool_keys); // calculate matching keynames
		$this->a_available_keys = $this->a_available_keys + $a_intersect; // add keys to available optons
		return true;
	}
	public function show_available_keys($a_keynames = []) {
/*
 *	render the given keynames
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['show'] = true;
			endif;
		endforeach;
		return true;
	}
	public function hide_available_keys($a_keynames = []) {
/*
 *	do not render the given keynames
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['show'] = false;
			endif;
		endforeach;
		return true;
	}
	public function set_default_available_keys($a_keynames = []) {
/*
 *	checks the given keynames by default if no options have been selected before
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['dafault'] = true;
			endif;
		endforeach;
		return true;
	}
	public function clr_default_available_keys($a_keynames = []) {
/*
 *	unchecks the given keynames by default if no options have been selected before
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['default'] = false;
			endif;
		endforeach;
		return true;
	}
	public function render_available_keys() {
/*		
 *	Displays a list of checkbox input fields for available keys
 *	Embedded in the body of a table with 2 columns
 *
 *	A key is checked by default when it is in the list of selected keys 
 *	Default option of keys are used when no key has been previously selected
 */
		if(0 === count($this->a_available_keys)):
			return true; // nothing to render
		endif;
		$b_use_default = (0 === count(array_intersect_key($this->a_available_keys,$this->a_selected_keys)));
		$attribute = [
			'name' => '',
			'value' => '',
			'type' => 'type="checkbox"',
			'checked' => ''
		];
		echo '<tr>';
			echo '<td class="celltag">';  // left hand column start
				echo $this->c_tag_header;
			echo '</td>',"\n"; // left hand column end
			echo '<td class="celldata">',"\n"; // right hand column start
				echo '<table id="area_data_selection">',"\n";
					echo '<colgroup>',"\n";
						echo '<col style="width:5%">',"\n"; // Checkbox
						echo '<col style="width:95%">',"\n"; // // Name
					echo '</colgroup>',"\n";
					echo '<thead>',"\n";
						echo '<tr>',"\n";
							echo '<th class="lhelc"></th>',"\n";
							echo '<th class="lhebl">',$this->c_val_header_name,'</th>',"\n";
						echo '</tr>',"\n";
					echo '</thead>',"\n";
					echo '<tbody>',"\n";
						foreach($this->a_available_keys as $key):
							if($this->a_pool_keys[$key]['show']):
								$value = $this->a_pool_keys[$key]['longname'];
								$attribute['name'] = sprintf('name="%s[]"',$this->a_pool_keys[$key]['name']);
								$attribute['value'] = sprintf('value="%s"',$this->a_pool_keys[$key]['value']);
								$attribute['checked'] = '';
								if($b_use_default):
									if ($this->a_pool_keys[$key]['default']):
										$attribute['checked'] = 'checked="checked"';
									endif;
								else:
									if(isset($this->a_selected_keys[$key])):
										$attribute['checked'] = 'checked="checked"';
									endif;
								endif;
								echo '<tr>',"\n";
									echo '<td class="lcelc">';
										echo '<input ',implode(' ',$attribute),'/>';
									echo '</td>',"\n";
									echo '<td class="lcebl">',$value,'</td>',"\n";
								echo '</tr>',"\n";
							endif;
						endforeach;
					echo '</tbody>',"\n";
				echo '</table>',"\n";
			echo '</td>',"\n"; // right hand column end
		echo '</tr>',"\n";
		return true;
	}
	public function init_selected_keys($a_keynames = []) {
/*
 *	Resets the list of selected keynames and loads a new list 
 */
		$this->a_selected_keys = [];
		$this->load_selected_keys($a_keynames);
		return true;
	}
	public function load_selected_keys($a_keynames = []) {
/*
 *	loads an array of keynames into the list of selected options
 *	the provided keynames must be valid available keys
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_available_keys)):
				$this->a_selected_keys[$r_keyname] = $r_keyname;
			endif;
		endforeach;
		return true;
	}
	public function render_selected_keys() {
/*
 *	Displays the long name of each selected flag
 *	Embedded in the body of a table with 2 columns
 */
		$a_output = [];
		if(empty($this->a_selected_keys) || empty($this->a_available_keys)):
			$a_output[] = $this->c_val_none;
		else:
			foreach ($this->a_selected_keys as $r_selected_option):
				if($this->a_pool_keys[$r_selected_option]['show']):
					$a_output[] = $this->a_pool_keys[$r_selected_option]['longname'];
				endif;
			endforeach;
		endif;
		if(1 < count($a_output)):
			$last = array_pop($a_output);
			$output = sprintf('%s %s %s',implode(', ',$a_output),gtext('and'),$last);
		else:
			$output = implode(', ',$a_output);
		endif;
		html_text2('sysflag',$this->c_tag_header,$output);
		return true;
	}
}
