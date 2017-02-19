<?php
/*
	system_firewall_edit.php

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

$a_rule = &array_make_branch($config,'system','firewall','rule');
if(empty($a_rule)):
else:
	array_sort_key($a_rule,'ruleno');
endif;
if (isset($uuid) && (FALSE !== ($cnid = array_search_ex($uuid, $a_rule, "uuid")))) {
	$pconfig['uuid'] = $a_rule[$cnid]['uuid'];
	$pconfig['enable'] = isset($a_rule[$cnid]['enable']);
	$pconfig['ruleno'] = $a_rule[$cnid]['ruleno'];
	$pconfig['action'] = $a_rule[$cnid]['action'];
	$pconfig['log'] = isset($a_rule[$cnid]['log']);
	$pconfig['protocol'] = $a_rule[$cnid]['protocol'];
	$pconfig['src'] = $a_rule[$cnid]['src'];
	$pconfig['srcport'] = $a_rule[$cnid]['srcport'];
	$pconfig['dst'] = $a_rule[$cnid]['dst'];
	$pconfig['dstport'] = $a_rule[$cnid]['dstport'];
	$pconfig['direction'] = $a_rule[$cnid]['direction'];
	$pconfig['if'] = $a_rule[$cnid]['if'];
	$pconfig['extraoptions'] = $a_rule[$cnid]['extraoptions'];
	$pconfig['desc'] = $a_rule[$cnid]['desc'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['enable'] = true;
	$pconfig['ruleno'] = get_next_rulenumber();
	$pconfig['action'] = "";
	$pconfig['log'] = false;
	$pconfig['protocol'] = "all";
	$pconfig['src'] = "";
	$pconfig['srcport'] = "";
	$pconfig['dst'] = "";
	$pconfig['dstport'] = "";
	$pconfig['direction'] = "";
	$pconfig['if'] = "";
	$pconfig['extraoptions'] = "";
	$pconfig['desc'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['Cancel']) && $_POST['Cancel']) {
		header("Location: system_firewall.php");
		exit;
	}

	// Input validation.
	// Validate if rule number is unique.
	$index = array_search_ex($_POST['ruleno'], $a_rule, "ruleno");
	if (FALSE !== $index) {
		if (!((FALSE !== $cnid) && ($a_rule[$cnid]['uuid'] === $a_rule[$index]['uuid']))) {
			$input_errors[] = gtext("The unique rule number is already used.");
		}
	}

	if (empty($input_errors)) {
		$rule = [];
		$rule['uuid'] = $_POST['uuid'];
		$rule['enable'] = isset($_POST['enable']) ? true : false;
		$rule['ruleno'] = $_POST['ruleno'];
		$rule['action'] = $_POST['action'];
		$rule['log'] = isset($_POST['log']) ? true : false;
		$rule['protocol'] = $_POST['protocol'];
		$rule['src'] = $_POST['src'];
		$rule['srcport'] = $_POST['srcport'];
		$rule['dst'] = $_POST['dst'];
		$rule['dstport'] = $_POST['dstport'];
		$rule['direction'] = $_POST['direction'];
		$rule['if'] = $_POST['if'];
		$rule['extraoptions'] = $_POST['extraoptions'];
		$rule['desc'] = $_POST['desc'];

		if (isset($uuid) && (FALSE !== $cnid)) {
			$a_rule[$cnid] = $rule;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		} else {
			$a_rule[] = $rule;
			$mode = UPDATENOTIFY_MODE_NEW;
		}

		updatenotify_set("firewall", $mode, $rule['uuid']);
		write_config();

		header("Location: system_firewall.php");
		exit;
	}
}

// Get next rule number.
function get_next_rulenumber() {
	global $config;

	// Set starting rule number
	$ruleno = 10100;

	$a_rules = $config['system']['firewall']['rule'];
	if (false !== array_search_ex(strval($ruleno), $a_rules, "ruleno")) {
		do {
			$ruleno += 100; // Increase rule number until a unused one is found.
		} while (false !== array_search_ex(strval($ruleno), $a_rules, "ruleno"));
	}

	return $ruleno;
}
$pgtitle = [gtext('Network'),gtext('Firewall'),gtext('Rule'),isset($uuid) ? gtext('Edit') : gtext('Add')];
?>
<?php include 'fbegin.inc';?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="system_firewall_edit.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("Firewall Rule Settings"), !empty($pconfig['enable']) ? true : false, gtext("Enable"));
					html_inputbox("ruleno", gtext("Rule number"), $pconfig['ruleno'], gtext("The rule number determines the order of the rule."), true, 10);
					html_combobox("action", gtext("Action"), $pconfig['action'],['allow' => gtext('Allow'),'deny' => gtext('Deny'),'unreach host' => gtext('Reject')], gtext("The action which will be executed when the packet match the criteria specified below."), true);
					$a_interface = array("" => gtext("All"), get_ifname($config['interfaces']['lan']['if']) => "LAN"); for ($i = 1; isset($config['interfaces']['opt' . $i]); ++$i) { $a_interface[$config['interfaces']['opt' . $i]['if']] = $config['interfaces']['opt' . $i]['descr']; }
					html_combobox("if", gtext("Interface"), $pconfig['if'], $a_interface, gtext("Choose on which interface packets must come in to match this rule."), true);
					html_combobox("protocol", gtext("Protocol"), $pconfig['protocol'], array("udp" => "UDP", "tcp" => "TCP", "icmp" => "ICMP", "all" => gtext("All")), gtext("Choose which IP protocol this rule should match."), true);
					html_inputbox("src", gtext("Source"), $pconfig['src'], gtext("To match any IP address leave this field empty."), false, 40);
					html_inputbox("srcport", gtext("Source port"), $pconfig['srcport'], "", false, 5);
					html_inputbox("dst", gtext("Destination"), $pconfig['dst'], gtext("To match any IP address leave this field empty."), false, 40);
					html_inputbox("dstport", gtext("Destination port"), $pconfig['dstport'], "", false, 5);
					html_inputbox("extraoptions", gtext("Options"), $pconfig['extraoptions'], "", false, 40);
					html_combobox("direction", gtext("Direction"), $pconfig['direction'], array("in" => gtext("In"), "out" => gtext("Out"), "" => gtext("Any")), "", true);
					html_checkbox("log", gtext("Log"), !empty($pconfig['log']) ? true : false, gtext("Log packets that are handled by this rule to syslog."), "", false);
					html_inputbox("desc", gtext("Description"), $pconfig['desc'], gtext("You may enter a description here for your reference."), false, 40);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gtext("Save") : gtext("Add")?>" />
					<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<div id="remarks">
					<?php
					$helpinghand = '<a href="' . 'http://www.freebsd.org/doc/en/books/handbook/firewalls-ipfw.html' . '" target="_blank">'
						. gtext('To get detailed informations about writing firewall rules check the FreeBSD documentation')
						. '</a>.';
					html_remark("note", gtext('Note'), $helpinghand);
					?>
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<?php include 'fend.inc';?>
