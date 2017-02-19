<?php
/*
	status_graph.php

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

$status_graph = true;
$graph_gap = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
$graph_width = 397;
$graph_height = 220;

$curif = "lan";
if (isset($_GET['if']) && $_GET['if']):
	$curif = $_GET['if'];
endif;
$ifnum = get_ifname($config['interfaces'][$curif]['if']);
$ifdescrs = ['lan' => 'LAN'];
for($j = 1;isset($config['interfaces']['opt' . $j]);$j++):
	$ifdescrs['opt' . $j] = $config['interfaces']['opt' . $j]['descr'];
endfor;

$a_object = [];
$a_object['type'] = 'type="image/svg+xml"';
$a_object['width'] = sprintf('width="%s"',$graph_width);
$a_object['height'] = sprintf('height="%s"',$graph_height);
$a_param = [];
$a_param['name'] = 'name="src"';

$gt_notsupported = gtext('Your browser does not support this svg object type.') .
		'<br />' .
		gtext('You need to update your browser or use Internet Explorer 10 or higher.') .
		'<br/>';

$pgtitle = [gtext('Status'),gtext('Monitoring'),gtext('System Load')];
?>
<?php include 'fbegin.inc';?>
<table id="area_navigator"><tbody>
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<?php require 'status_graph_tabs.inc';?>
	</ul></td></tr>
</tbody></table>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_settings">
		<colgroup>
			<col style="width:100%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('System Load'),1);?>
		</thead>
		<tbody>
			<tr><td><?=gtext('Graph shows last 120 seconds');?></td></tr>
			<tr><td>
				<div align="center" style="min-width:840px;">
					<br />
					<?php
					$a_object['id'] = 'id="graph"';
					$a_object['data'] = sprintf('data="status_graph2.php?ifnum=%1$s&amp;ifname=%2$s"',$ifnum,rawurlencode($ifdescrs[$curif]));
					$a_param['value'] = sprintf('value="graph.php?ifnum=%1$s&amp;ifname=%2$s"',$ifnum,rawurlencode($ifdescrs[$curif]));
					echo sprintf('<object %s>',implode(' ',$a_object));
					echo sprintf('<param %s/>',implode(' ',$a_param));
					echo $gt_notsupported;
					echo '</object>',"\n";
					echo $graph_gap;
					for($j = 1;isset($config['interfaces']['opt' . $j]);$j++):
						$ifdescrs = $config['interfaces']['opt' . $j]['descr'];
						$ifnum = $config['interfaces']['opt' . $j]['if'];
						$a_object['id'] = 'id="graph1"';
						$a_object['data'] = sprintf('data="status_graph2.php?ifnum=%1$s&amp;ifname=%2$s"',$ifnum,rawurlencode($ifdescrs));
						$a_param['value'] = sprintf('value="status_graph2.php?ifnum=%1$s&amp;ifname=%2$s"',$ifnum,rawurlencode($ifdescrs));
						echo sprintf('<object %s>',implode(' ',$a_object));
						echo sprintf('<param %s/>',implode(' ',$a_param));
						echo $gt_notsupported;
						echo '</object>',"\n";
						$test = $j % 2;
						if($test != 0):
							echo '<br /><br /><br />'; // add line breaks after second graph ...
						else:
							echo $graph_gap; // or the gap between two graphs
						endif;
					endfor;
					$a_object['id'] = 'id="graph1"';
					$a_object['data'] = 'data="status_graph_cpu2.php"';
					$a_param['value'] = 'value="status_graph_cpu2.php"';
					echo sprintf('<object %s>',implode(' ',$a_object));
					echo sprintf('<param %s/>',implode(' ',$a_param));
					echo $gt_notsupported;
					echo '</object>',"\n";
					?>
				</div>
			</td></tr>
		</tbody>
	</table>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
