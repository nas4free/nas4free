<?php
/* 
	rrd-start.php

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
$runtime_dir = "/usr/local/share/rrdgraphs";

if(isset($config['rrdgraphs']['enable'])):
	exec('logger rrdgraphs service started');
	//	create config file - for booleans we need the variable $txt
	$rrdconfig = fopen("{$config['rrdgraphs']['storage_path']}/rrd_config", "w");
	fwrite($rrdconfig, "STORAGE_PATH={$config['rrdgraphs']['storage_path']}"."\n");
	fwrite($rrdconfig, "GRAPH_H={$config['rrdgraphs']['graph_h']}"."\n");
	fwrite($rrdconfig, "REFRESH_TIME={$config['rrdgraphs']['refresh_time']}"."\n");
	$txt = isset($config['rrdgraphs']['autoscale']) ? "--alt-autoscale" : "";
	fwrite($rrdconfig, "AUTOSCALE=".$txt."\n");
	$txt = isset($config['rrdgraphs']['background_white']) ? "1" : "0";
	fwrite($rrdconfig, "BACKGROUND_WHITE=".$txt."\n");
	$txt = isset($config['rrdgraphs']['bytes_per_second']) ? "1" : "0";
	fwrite($rrdconfig, "BYTE_SWITCH=".$txt."\n");
	$txt = isset($config['rrdgraphs']['logarithmic']) ? "1" : "0";
	fwrite($rrdconfig, "LOGARITHMIC=".$txt."\n");
	$txt = isset($config['rrdgraphs']['axis']) ? "1" : "0";
	fwrite($rrdconfig, "AXIS=".$txt."\n");
	fwrite($rrdconfig, "INTERFACE0={$config['rrdgraphs']['lan_if']}"."\n");
	fwrite($rrdconfig, "LATENCY_HOST={$config['rrdgraphs']['latency_host']}"."\n");
	fwrite($rrdconfig, "LATENCY_INTERFACE={$config['rrdgraphs']['latency_interface']}"."\n");
	fwrite($rrdconfig, "LATENCY_INTERFACE_IP=".get_ipaddr($config['rrdgraphs']['lan_if'])."\n");
	fwrite($rrdconfig, "LATENCY_COUNT={$config['rrdgraphs']['latency_count']}"."\n");
	fwrite($rrdconfig, "LATENCY_PARAMETERS='{$config['rrdgraphs']['latency_parameters']}'"."\n");
	fwrite($rrdconfig, "UPS_AT={$config['rrdgraphs']['ups_at']}"."\n");
	$txt = isset($config['rrdgraphs']['cpu_frequency']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_FRQ=".$txt."\n");
	$txt = isset($config['rrdgraphs']['cpu_temperature']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_TMP=".$txt."\n");
	$txt = isset($config['rrdgraphs']['cpu']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_CPU=".$txt."\n");
	$txt = isset($config['rrdgraphs']['disk_usage']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_DUS=".$txt."\n");
	$txt = isset($config['rrdgraphs']['load_averages']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_AVG=".$txt."\n");
	$txt = isset($config['rrdgraphs']['memory_usage']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_MEM=".$txt."\n");
	$txt = isset($config['rrdgraphs']['latency']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_LAT=".$txt."\n");
	$txt = isset($config['rrdgraphs']['lan_load']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_LAN=".$txt."\n");
	$txt = isset($config['rrdgraphs']['no_processes']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_PRO=".$txt."\n");
	$txt = isset($config['rrdgraphs']['ups']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_UPS=".$txt."\n");
	$txt = isset($config['rrdgraphs']['uptime']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_UPT=".$txt."\n");
	$txt = isset($config['rrdgraphs']['arc_usage']) ? "1" : "0";
	fwrite($rrdconfig, "RUN_ARC=".$txt."\n");
	if(isset($config['rrdgraphs']['disk_usage'])):
		if(isset($config["rrdgraphs"]["mounts"])):
			unset($config["rrdgraphs"]["mounts"]);
		endif;
		array_make_branch($config,'rrdgraphs','mounts');
		if(isset($config["rrdgraphs"]["pools"])):
			unset($config["rrdgraphs"]["pools"]);
		endif;
		array_make_branch($config,'rrdgraphs','pools');
		if(is_array($config['mounts']) && is_array($config['mounts']['mount'])):
			for($i = 0; $i < count($config['mounts']['mount']); ++$i):
				$config["rrdgraphs"]["mounts"]["mount{$i}"] = $config['mounts']['mount'][$i]['sharename'];
				fwrite($rrdconfig, "MOUNT{$i}={$config['mounts']['mount'][$i]['sharename']}"."\n");
			endfor;
		endif;
		if(is_array($config['zfs']['pools']) && is_array($config['zfs']['pools']['pool'])):
			unset($pools);
			// get ZFS pools and datasets
			exec("zfs list -H -t filesystem -o name", $pools, $retval);
			for($i = 0; $i < count($pools); ++$i):
				$config['rrdgraphs']['pools']["pool{$i}"] = $pools[$i];
				fwrite($rrdconfig, "POOL{$i}={$pools[$i]}"."\n");
			endfor;
		endif;
		$temp_array = array_merge($config['rrdgraphs']['mounts'],$config['rrdgraphs']['pools']);
		foreach($temp_array as $retval):
			$clean_name = str_replace('/', '-', $retval);
			if(!is_file("{$config['rrdgraphs']['storage_path']}/rrd/mnt_{$clean_name}.rrd")):
				$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/mnt_{$clean_name}.rrd" .
					" -s 300" .
					" 'DS:Used:GAUGE:600:U:U'" .
					" 'DS:Free:GAUGE:600:U:U'" .
					" 'RRA:AVERAGE:0.5:1:576'" .
					" 'RRA:AVERAGE:0.5:6:672'" .
					" 'RRA:AVERAGE:0.5:24:732'" .
					" 'RRA:AVERAGE:0.5:144:1460'",
					true);
				exec("logger rrdgraphs service start collecting mnt_{$clean_name} statistics");
			endif;
		endforeach;
	endif;
	fclose($rrdconfig);
	//	create new .rrds if necessary
	$rrd_name = 'cpu_freq.rrd';
	if(isset($config['rrdgraphs']['cpu_frequency']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:core0:GAUGE:600:0:U'" .
			" 'DS:core1:GAUGE:600:0:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting cpu frequency statistics');
	endif;
	$rrd_name = 'cpu_temp.rrd';
	if(isset($config['rrdgraphs']['cpu_temperature']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:core0:GAUGE:600:0:U'" .
			" 'DS:core1:GAUGE:600:0:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting cpu temperature statistics');
	endif;
	$rrd_name = 'cpu.rrd';
	if(isset($config['rrdgraphs']['cpu']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:user:GAUGE:600:U:U'" .
			" 'DS:nice:GAUGE:600:U:U'" .
			" 'DS:system:GAUGE:600:U:U'" .
			" 'DS:interrupt:GAUGE:600:U:U'" .
			" 'DS:idle:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting cpu usage statistics');
	endif;
	$rrd_name = "load_averages.rrd";
	if(isset($config['rrdgraphs']['load_averages']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:CPU:GAUGE:600:0:100'" .
			" 'DS:CPU5:GAUGE:600:0:100'" .
			" 'DS:CPU15:GAUGE:600:0:100'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting load averages statistics');
	endif;
	$rrd_name = 'memory.rrd';
	if(isset($config['rrdgraphs']['memory_usage']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:active:GAUGE:600:U:U'" .
			" 'DS:inact:GAUGE:600:U:U'" .
			" 'DS:wired:GAUGE:600:U:U'" .
			" 'DS:cache:GAUGE:600:U:U'" .
			" 'DS:buf:GAUGE:600:U:U'" .
			" 'DS:free:GAUGE:600:U:U'" .
			" 'DS:total:GAUGE:600:U:U'" .
			" 'DS:used:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting memory usage statistics');
	endif;
	$rrd_name = 'latency.rrd';
	if(isset($config['rrdgraphs']['latency']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:min:GAUGE:600:U:U'" .
			" 'DS:avg:GAUGE:600:U:U'" .
			" 'DS:max:GAUGE:600:U:U'" .
			" 'DS:stddev:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting network latency stastistics');
	endif;
	if(isset($config['rrdgraphs']['lan_load'])):
		$rrd_name = "{$config['rrdgraphs']['lan_if']}.rrd";
		if(!is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
			$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
				" -s 300" .
				" 'DS:in:COUNTER:600:0:U'" .
				" 'DS:out:COUNTER:600:0:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			exec("logger rrdgraphs service start collecting network {$rrd_name} traffic stastistics");
		endif;
		for($j = 1;isset($config['interfaces']['opt' . $j]);$j++):
			$if = $config['interfaces']['opt' . $j]['if'];
			$rrd_name = "{$if}.rrd";
			if(!is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
				$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
					" -s 300" .
					" 'DS:in:COUNTER:600:0:U'" .
					" 'DS:out:COUNTER:600:0:U'" .
					" 'RRA:AVERAGE:0.5:1:576'" .
					" 'RRA:AVERAGE:0.5:6:672'" .
					" 'RRA:AVERAGE:0.5:24:732'" .
					" 'RRA:AVERAGE:0.5:144:1460'",
					true);
				exec("logger rrdgraphs service start collecting network opt-{$rrd_name} traffic statistics");
			endif;
		endfor;
	endif;
	$rrd_name = 'processes.rrd';
	if(isset($config['rrdgraphs']['no_processes']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:total:GAUGE:600:U:U'" .
			" 'DS:running:GAUGE:600:U:U'" .
			" 'DS:sleeping:GAUGE:600:U:U'" .
			" 'DS:waiting:GAUGE:600:U:U'" .
			" 'DS:starting:GAUGE:600:U:U'" .
			" 'DS:stopped:GAUGE:600:U:U'" .
			" 'DS:zombie:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting system processes statistics');
	endif;
	$rrd_name = 'ups.rrd';
	if(isset($config['rrdgraphs']['ups']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:charge:GAUGE:600:U:U'" .
			" 'DS:load:GAUGE:600:U:U'" .
			" 'DS:bvoltage:GAUGE:600:U:U'" .
			" 'DS:ivoltage:GAUGE:600:U:U'" .
			" 'DS:runtime:GAUGE:600:U:U'" .
			" 'DS:OL:GAUGE:600:U:U'" .
			" 'DS:OF:GAUGE:600:U:U'" .
			" 'DS:OB:GAUGE:600:U:U'" .
			" 'DS:CG:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting ups statistics');
	endif;
	$rrd_name = 'uptime.rrd';
	if(isset($config['rrdgraphs']['uptime']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:uptime:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'". 
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting uptime statistics');
	endif;
	$rrd_name = 'zfs_arc.rrd';
	if(isset($config['rrdgraphs']['arc_usage']) && !is_file("{$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}")):
		$ret_val = mwexec("/usr/local/bin/rrdtool create {$config['rrdgraphs']['storage_path']}/rrd/{$rrd_name}" .
			" -s 300" .
			" 'DS:Total:GAUGE:600:U:U'" .
			" 'DS:MFU:GAUGE:600:U:U'" .
			" 'DS:MRU:GAUGE:600:U:U'" .
			" 'DS:Anon:GAUGE:600:U:U'" .
			" 'DS:Header:GAUGE:600:U:U'" .
			" 'DS:Other:GAUGE:600:U:U'" .
			" 'RRA:AVERAGE:0.5:1:576'" .
			" 'RRA:AVERAGE:0.5:6:672'" .
			" 'RRA:AVERAGE:0.5:24:732'" .
			" 'RRA:AVERAGE:0.5:144:1460'",
			true);
		exec('logger rrdgraphs service start collecting zfs arc statistics');
	endif;
	//	create graphs
	$ret_val = mwexec("{$runtime_dir}/rrd-graph.sh",true);
endif;
write_config();
?>
