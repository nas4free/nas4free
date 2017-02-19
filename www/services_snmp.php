<?php
/*
	services_snmp.php

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

$pgtitle = array(gtext("Services"), gtext("SNMP"));

if (!isset($config['snmpd']) || !is_array($config['snmpd']))
	$config['snmpd'] = array();

$os_release = exec('uname -r | cut -d - -f1');

$pconfig['enable'] = isset($config['snmpd']['enable']);
$pconfig['location'] = $config['snmpd']['location'];
$pconfig['contact'] = $config['snmpd']['contact'];
$pconfig['read'] = $config['snmpd']['read'];
$pconfig['trapenable'] = isset($config['snmpd']['trapenable']);
$pconfig['traphost'] = $config['snmpd']['traphost'];
$pconfig['trapport'] = $config['snmpd']['trapport'];
$pconfig['trap'] = $config['snmpd']['trap'];
$pconfig['mibii'] = isset($config['snmpd']['modules']['mibii']);
$pconfig['netgraph'] = isset($config['snmpd']['modules']['netgraph']);
$pconfig['hostres'] = isset($config['snmpd']['modules']['hostres']);
$pconfig['ucd'] = isset($config['snmpd']['modules']['ucd']);
if (isset($config['snmpd']['auxparam']) && is_array($config['snmpd']['auxparam']))
	$pconfig['auxparam'] = implode("\n", $config['snmpd']['auxparam']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation
	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = explode(" ", "location contact read");
		$reqdfieldsn = array(gtext("Location"), gtext("Contact"), gtext("Community"));
		$reqdfieldst = explode(" ", "string string string");

		if (isset($_POST['trapenable']) && $_POST['trapenable']) {
			$reqdfields = array_merge($reqdfields, explode(" ", "traphost trapport trap"));
			$reqdfieldsn = array_merge($reqdfieldsn, array(gtext("Trap host"), gtext("Trap port"), gtext("Trap string")));
			$reqdfieldst = array_merge($reqdfieldst, explode(" ", "string port string"));
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	if (empty($input_errors)) {
		$config['snmpd']['enable'] = isset($_POST['enable']) ? true : false;
		$config['snmpd']['location'] = $_POST['location'];
		$config['snmpd']['contact'] = $_POST['contact'];
		$config['snmpd']['read'] = $_POST['read'];
		$config['snmpd']['trapenable'] = isset($_POST['trapenable']) ? true : false;
		$config['snmpd']['traphost'] = $_POST['traphost'];
		$config['snmpd']['trapport'] = $_POST['trapport'];
		$config['snmpd']['trap'] = $_POST['trap'];
		$config['snmpd']['modules']['mibii'] = isset($_POST['mibii']) ? true : false;
		$config['snmpd']['modules']['netgraph'] = isset($_POST['netgraph']) ? true : false;
		$config['snmpd']['modules']['hostres'] = isset($_POST['hostres']) ? true : false;
		$config['snmpd']['modules']['ucd'] = isset($_POST['ucd']) ? true : false;

		// Write additional parameters.
		unset($config['snmpd']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['snmpd']['auxparam'][] = $auxparam;
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("bsnmpd");
			config_unlock();
		}
		$savemsg = get_std_save_message($retval);
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.location.disabled = endis;
	document.iform.contact.disabled = endis;
	document.iform.read.disabled = endis;
	document.iform.netgraph.disabled = endis;
	document.iform.hostres.disabled = endis;
	document.iform.ucd.disabled = endis;
	document.iform.auxparam.disabled = endis;
	document.iform.mibii.disabled = endis;
	document.iform.trapenable.disabled = endis;
	document.iform.traphost.disabled = endis;
	document.iform.trapport.disabled = endis;
	document.iform.trap.disabled = endis;
}

function trapenable_change() {
	switch (document.iform.trapenable.checked) {
		case false:
			showElementById('traphost_tr','hide');
			showElementById('trapport_tr','hide');
			showElementById('trap_tr','hide');
			break;

		case true:
			showElementById('traphost_tr','show');
			showElementById('trapport_tr','show');
			showElementById('trap_tr','show');
			break;
	}
}
//-->
</script>
<form action="services_snmp.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("Simple Network Management Protocol"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");
					html_inputbox("location", gtext("Location"), $pconfig['location'], gtext("Location information, e.g. physical location of this system: 'Floor of building, Room xyz'."), true, 40);
					html_inputbox("contact", gtext("Contact"), $pconfig['contact'], gtext("Contact information, e.g. name or email of the person responsible for this system: 'admin@email.address'."), true, 40);
					html_inputbox("read", gtext("Community"), $pconfig['read'], gtext("In most cases, 'public' is used here."), true, 40);
					html_checkbox("trapenable", gtext("Traps"), !empty($pconfig['trapenable']) ? true : false, gtext("Enable traps."), "", false, "trapenable_change()");
					html_inputbox("traphost", gtext("Trap host"), $pconfig['traphost'], gtext("Enter trap host name."), true, 40);
					html_inputbox("trapport", gtext("Trap port"), $pconfig['trapport'], gtext("Enter the port to send the traps to (default 162)."), true, 5);
					html_inputbox("trap", gtext("Trap string"), $pconfig['trap'], gtext("Trap string."), true, 40);
					$helpinghand = '<a href="'
						. 'http://www.freebsd.org/cgi/man.cgi?query=bsnmpd&amp;apropos=0&amp;sektion=0&amp;manpath=FreeBSD+' . $os_release . '-RELEASE&amp;format=html'
						. '" target="_blank">'
						. gtext('Please check the documentation')
						. '</a>.';
					html_textarea("auxparam", gtext("Auxiliary parameters"), !empty($pconfig['auxparam']) ? $pconfig['auxparam'] : "", sprintf(gtext("These parameters will be added to %s."), "snmpd.config") . ' ' . $helpinghand, false, 65, 5, false, false);
					html_separator();
					html_titleline(gtext("Modules"));
					?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("SNMP Modules");?></td>
						<td width="78%" class="vtable">
							<input name="mibii" type="checkbox" id="mibii" value="yes" <?php if (!empty($pconfig['mibii'])) echo "checked=\"checked\""; ?> /><?=gtext("MibII");?><br />
							<input name="netgraph" type="checkbox" id="netgraph" value="yes" <?php if (!empty($pconfig['netgraph'])) echo "checked=\"checked\""; ?> /><?=gtext("Netgraph");?><br />
							<input name="hostres" type="checkbox" id="hostres" value="yes" <?php if (!empty($pconfig['hostres'])) echo "checked=\"checked\""; ?> /><?=gtext("Host resources");?><br />
							<input name="ucd" type="checkbox" id="ucd" value="yes" <?php if (!empty($pconfig['ucd'])) echo "checked=\"checked\""; ?> /><?=gtext("UCD-SNMP-MIB");?>
						</td>
					</tr>
			  </table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
					<?php html_remark("note", gtext("Note"), sprintf(gtext("The associated MIB files can be found at %s."), "/usr/share/snmp/mibs"));?>
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
trapenable_change();
enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
