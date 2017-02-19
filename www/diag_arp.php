<?php
/*
	diag_arp.php

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

if (isset($_GET['id']))
  $id = $_GET['id'];
if (isset($_POST['id']))
  $id = $_POST['id'];

if (isset($_GET['act']) && $_GET['act'] == "del") {
	if (isset($id)) {
		/* remove arp entry from arp table */
		mwexec("/usr/sbin/arp -d " . escapeshellarg($id));

		/* redirect to avoid reposting form data on refresh */
		header("Location: diag_arp.php");
		exit;
	} else {
		/* remove all entries from arp table */
		mwexec("/usr/sbin/arp -d -a");

		/* redirect to avoid reposting form data on refresh */
		header("Location: diag_arp.php");
		exit;
	}
}
$resolve = isset($config['syslogd']['resolve']);

$fp = @fopen("{$g['vardb_path']}/dhcpd.leases","r");
if ($fp) {
	$return = [];

	while ($line = fgets($fp)) {
		$matches = "";

		// Sort out comments
		// C-style comments not supported!
		if (preg_match("/^\s*[\r|\n]/", $line, $matches[0]) ||
					preg_match("/^([^\"#]*)#.*$/", $line, $matches[1]) ||
					preg_match("/^([^\"]*)\/\/.*$/", $line, $matches[2]) ||
					preg_match("/\s*#(.*)/", $line, $matches[3]) ||
					preg_match("/\\\"\176/", $line, $matches[4])
			) {
			$line = "";
			continue;
		}

		if (preg_match("/(.*)#(.*)/", $line, $matches))
			$line = $matches[0];

		// Tokenize lines
		do {
			if (preg_match("/^\s*\"([^\"]*)\"(.*)$/", $line, $matches)) {
				$line = $matches[2];
				$return[] = [$matches[1], 0];
			} else if (preg_match("/^\s*([{};])(.*)$/", $line, $matches)) {
				$line = $matches[2];
				$return[] = [$matches[0], 1];
			} else if (preg_match("/^\s*([^{}; \t]+)(.*)$/", $line, $matches)) {
				$line = $matches[2];
				$return[] = [$matches[1], 0];
			} else
				break;

		} while($line);

		$lines++;
	}

	fclose($fp);

	$leases = [];
	$i = 0;

	// Put everything together again
	while ($data = array_shift($return)) {
		if ($data[0] == "next") {
			$d = array_shift($return);
		}
		if ($data[0] == "lease") {
			$d = array_shift($return);
			$leases[$i]['ip'] = $d[0];
		}
		if ($data[0] == "client-hostname") {
			$d = array_shift($return);
			$leases[$i]['hostname'] = $d[0];
		}
		if ($data[0] == "hardware") {
			$d = array_shift($return);
			if ($d[0] == "ethernet") {
				$d = array_shift($return);
				$leases[$i]['mac'] = $d[0];
			}
		} else if ($data[0] == "starts") {
			$d = array_shift($return);
			$d = array_shift($return);
			$leases[$i]['start'] = $d[0];
			$d = array_shift($return);
			$leases[$i]['start'] .= " " . $d[0];
		} else if ($data[0] == "ends") {
			$d = array_shift($return);
			$d = array_shift($return);
			$leases[$i]['end'] = $d[0];
			$d = array_shift($return);
			$leases[$i]['end'] .= " " . $d[0];
		} else if ($data[0] == "binding") {
			$d = array_shift($return);
			if ($d[0] == "state") {
				$d = array_shift($return);
				$leases[$i]['act'] = $d[0];
			}
		} else if (($data[0] == "}") && ($data[1] == 1))		// End of group
			$i++;
	}

	// Put this in an easy to use form
	$dhcpmac = [];
	$dhcpip = [];

	foreach ($leases as $value) {
		$dhcpmac[$value['mac']] = $value['hostname'];
		$dhcpip[$value['ip']] = $value['hostname'];
	}

	unset($data);
}

exec("/usr/sbin/arp -an",$rawdata);

$i = 0;
$ifdescrs = ['lan' => 'LAN'];

for ($j = 1; isset($config['interfaces']['opt' . $j]); $j++) {
	$ifdescrs['opt' . $j] = $config['interfaces']['opt' . $j]['descr'];
}

foreach ($ifdescrs as $key => $interface) {
	$hwif[get_ifname($config['interfaces'][$key]['if'])] = $interface;
}

$data = [];
foreach ($rawdata as $line) {
	$elements = explode(' ',$line);

	if ($elements[3] != "(incomplete)") {
		$arpent = [];
		$arpent['ip'] = trim(str_replace(['(',')'],'',$elements[1]));
		$arpent['mac'] = trim($elements[3]);
		$arpent['interface'] = trim($elements[5]);
		$data[] = $arpent;
	}
}

function get_HostName($mac, $ip) {
	global $dhcpmac, $dhcpip, $resolve;

	if ($dhcpmac[$mac])
		return $dhcpmac[$mac];
	else if ($dhcpip[$ip])
		return $dhcpip[$ip];
	else if ($resolve)
		return get_hostbyaddr($ip);
	else
		return "";
}
$pgtitle = [gtext('Diagnostics'),gtext('ARP Tables')];

?>
<?php include 'fbegin.inc';?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
	<table class="area_data_selection">
		<colgroup>
			<col style="width:20%">
			<col style="width:20%">
			<col style="width:30%">
			<col style="width:20%">
			<col style="width:10%">
		</colgroup>
		<thead>
			<?php html_titleline2(gtext('ARP Tables List'),5);?>
			<tr>
				<th class="lhell"><?=gtext('IP Address');?></th>
				<th class="lhell"><?=gtext('MAC Address');?></th>
				<th class="lhell"><?=gtext('Hostname');?></th>
				<th class="lhell"><?=gtext('Interface');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0; foreach ($data as $entry): ?>
				<tr>
					<td class="lcell"><?=htmlspecialchars($entry['ip']);?></td>
					<td class="lcell"><?=htmlspecialchars($entry['mac']);?></td>
					<td class="lcell"><?=htmlspecialchars(get_HostName($entry['mac'], $entry['ip']));?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($hwif[$entry['interface']]);?>&nbsp;</td>
					<td class="lcebld">
						<table class="area_data_selection_toolbox"><tbody><tr>
							<td>
								<a href="diag_arp.php?act=del&amp;id=<?=$entry['ip'];?>"><img src="images/delete.png" title="<?=gtext("Delete ARP entry");?>" border="0" alt="<?=gtext("Delete ARP entry");?>" /></a>
							</td>
							<td></td>
							<td></td>
						</tr></tbody></table>
					</td>
				</tr>
			<?php $i++; endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td class="lcenl" colspan="4"></td>
				<td class="lceadd"><a href="diag_arp.php?act=del"><img src="images/delete.png" title="<?=gtext('Remove all entries from ARP table');?>" border="0" alt="<?=gtext('Remove all entries from ARP table');?>"/></a></td>
			</tr>
		</tfoot>
	</table>
	<div id="remarks">
		<?php
		$helpinghand =  
			gtext('IP addresses are resolved to hostnames when the following option is enabled:') .
			' ' .
			'<a href="' . 'diag_log_settings.php' . '">' . gtext('Resolve IP addresses to hostnames.') . '</a>';
		html_remark("hint", gtext('Hint'), $helpinghand);
		?>
	</div>
</td></tr></tbody></table>
<?php include 'fend.inc';?>
