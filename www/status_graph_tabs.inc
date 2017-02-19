<!-- 
	status_graph_tabs.inc

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
-->
<li class="<?=isset($status_graph) ? "tabact" : "tabinact"?>"><a href="status_graph.php" title="<?=gtext("Reload page");?>"><span><?=gtext("System Load");?></span></a></li>
<li class="<?=isset($status_cpu) ? "tabact" : "tabinact"?>"><a href="status_graph_cpu.php" title="<?=gtext("Reload page");?>"><span><?=gtext("CPU Load");?></span></a></li>
<?php if (isset($config['rrdgraphs']['enable'])):?>
	<?php if (isset($config['rrdgraphs']['cpu_frequency'])):?>
		<li class="<?=isset($rrd_cpu_freq) ? "tabact" : "tabinact"?>"><a href="status_graph_cpu_freq.php" title="<?=gtext("Reload page");?>"><span><?=gtext("CPU Frequency");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['cpu_temperature'])):?>
		<li class="<?=isset($rrd_cpu_temp) ? "tabact" : "tabinact"?>"><a href="status_graph_cpu_temp.php" title="<?=gtext("Reload page");?>"><span><?=gtext("CPU Temperature");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['cpu'])):?>
		<li class="<?=isset($rrd_cpu) ? "tabact" : "tabinact"?>"><a href="status_graph_cpu_usage.php" title="<?=gtext("Reload page");?>"><span><?=gtext("CPU Usage");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['load_averages'])):?>
		<li class="<?=isset($rrd_load_averages) ? "tabact" : "tabinact"?>"><a href="status_graph_load_averages.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Load Averages");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['disk_usage'])):?>
		<li class="<?=isset($rrd_disk_usage) ? "tabact" : "tabinact"?>"><a href="status_graph_disk_usage.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Disk Usage");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['memory_usage'])):?>
		<li class="<?=isset($rrd_memory) ? "tabact" : "tabinact"?>"><a href="status_graph_memory.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Memory Usage");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['latency'])):?>
		<li class="<?=isset($rrd_latency) ? "tabact" : "tabinact"?>"><a href="status_graph_latency.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Network Latency");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['lan_load'])):?>
		<li class="<?=isset($rrd_lan) ? "tabact" : "tabinact"?>"><a href="status_graph_network.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Network Traffic");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['no_processes'])):?>
		<li class="<?=isset($rrd_processes) ? "tabact" : "tabinact"?>"><a href="status_graph_processes.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Processes");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['ups'])):?>
		<li class="<?=isset($rrd_ups) ? "tabact" : "tabinact"?>"><a href="status_graph_ups.php" title="<?=gtext("Reload page");?>"><span><?=gtext("UPS");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['uptime'])):?>
		<li class="<?=isset($rrd_uptime) ? "tabact" : "tabinact"?>"><a href="status_graph_uptime.php" title="<?=gtext("Reload page");?>"><span><?=gtext("Uptime");?></span></a></li>
	<?php endif;?>
	<?php if (isset($config['rrdgraphs']['arc_usage'])):?>
		<li class="<?=isset($rrd_zfs_arc) ? "tabact" : "tabinact"?>"><a href="status_graph_zfs_arc.php" title="<?=gtext("Reload page");?>"><span><?=gtext("ZFS ARC");?></span></a></li>
	<?php endif;?>
<?php endif;?>
