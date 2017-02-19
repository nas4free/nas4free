<?php
/*
	services_lcdproc.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
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

$pgtitle = array(gtext("Services"), gtext("LCDproc"));

if (!isset($config['lcdproc']) || !is_array($config['lcdproc']))
	$config['lcdproc'] = array();
if (!isset($config['lcdproc']['lcdproc']) || !is_array($config['lcdproc']['lcdproc']))
	$config['lcdproc']['lcdproc'] = array();

$pconfig['enable'] = isset($config['lcdproc']['enable']);
$pconfig['driver'] = $config['lcdproc']['driver'];
$pconfig['port'] = $config['lcdproc']['port'];
$pconfig['waittime'] = $config['lcdproc']['waittime'];
$pconfig['titlespeed'] = $config['lcdproc']['titlespeed'];
$pconfig['lcdproc_enable'] = isset($config['lcdproc']['lcdproc']['enable']);
if (isset($config['lcdproc']['param']) && is_array($config['lcdproc']['param']))
	$pconfig['param'] = implode("\n", $config['lcdproc']['param']);
if (isset($config['lcdproc']['auxparam']) && is_array($config['lcdproc']['auxparam']))
	$pconfig['auxparam'] = implode("\n", $config['lcdproc']['auxparam']);
if (isset($config['lcdproc']['lcdproc']['param']) && is_array($config['lcdproc']['lcdproc']['param']))
	$pconfig['lcdproc_param'] = implode("\n", $config['lcdproc']['lcdproc']['param']);
if (isset($config['lcdproc']['lcdproc']['auxparam']) && is_array($config['lcdproc']['lcdproc']['auxparam']))
	$pconfig['lcdproc_auxparam'] = implode("\n", $config['lcdproc']['lcdproc']['auxparam']);

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	$reqdfields = explode(" ", "driver port waittime titlespeed");
	$reqdfieldsn = array(gtext("Driver"), gtext("Port"), gtext("Wait time"), gtext("TitleSpeed"));
	$reqdfieldst = explode(" ", "string numeric numeric numeric");

	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

	if (empty($input_errors)) {
		$config['lcdproc']['enable'] = isset($_POST['enable']) ? true : false;
		$config['lcdproc']['driver'] = $_POST['driver'];
		$config['lcdproc']['port'] = $_POST['port'];
		$config['lcdproc']['waittime'] = $_POST['waittime'];
		$config['lcdproc']['titlespeed'] = $_POST['titlespeed'];
		$config['lcdproc']['lcdproc']['enable'] = isset($_POST['lcdproc_enable']) ? true : false;

		# Write additional parameters.
		unset($config['lcdproc']['param']);
		foreach (explode("\n", $_POST['param']) as $param) {
			$param = trim($param, "\t\n\r");
			if (!empty($param))
				$config['lcdproc']['param'][] = $param;
		}
		unset($config['lcdproc']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['lcdproc']['auxparam'][] = $auxparam;
		}
		unset($config['lcdproc']['lcdproc']['param']);
		foreach (explode("\n", $_POST['lcdproc_param']) as $param) {
			$param = trim($param, "\t\n\r");
			if (!empty($param))
				$config['lcdproc']['lcdproc']['param'][] = $param;
		}
		unset($config['lcdproc']['lcdproc']['auxparam']);
		foreach (explode("\n", $_POST['lcdproc_auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['lcdproc']['lcdproc']['auxparam'][] = $auxparam;
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("LCDd");
			$retval |= rc_update_service("lcdproc");
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

	document.iform.driver.disabled = endis;
	document.iform.port.disabled = endis;
	document.iform.waittime.disabled = endis;
	document.iform.titlespeed.disabled = endis;
	document.iform.param.disabled = endis;
	document.iform.auxparam.disabled = endis;
}
function lcdproc_enable_change(enable_change) {
	var endis = !(document.iform.lcdproc_enable.checked || enable_change);

	document.iform.lcdproc_param.disabled = endis;
	document.iform.lcdproc_auxparam.disabled = endis;
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_lcdproc.php" method="post" name="iform" id="iform">
				<?php
				if (!empty($input_errors)) {
					print_input_errors($input_errors);
				}
				if (!empty($savemsg)) {
					print_info_box($savemsg);
				}
				?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox("enable", gtext("LCDproc"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");
					$helpinghand = gtext('The driver used to connect with the LCD.')
						. ' '
						. '<a href="' . 'http://lcdproc.omnipotent.net/hardware.php3' . '" target="_blank">'
						. gtext('Check the list of available drivers')
						. '</a>.';
					html_inputbox("driver", gtext("Driver"), $pconfig['driver'], $helpinghand, true, 30);
					html_inputbox("port", gtext("Port"), $pconfig['port'], sprintf(gtext("Port to listen on. Default port is %d."), 13666), true, 10);
					html_inputbox("waittime", gtext("Wait time"), $pconfig['waittime'], gtext("The default time in seconds to display a screen."), true, 10);
					html_inputbox("titlespeed", gtext("TitleSpeed"), $pconfig['titlespeed'], gtext("Set title scrolling speed between 0-10 (default 10)."), true, 10);
					html_textarea("param", gtext("Driver parameters"), !empty($pconfig['param']) ? $pconfig['param'] : "", gtext("Additional parameters to the hardware-specific part of the driver."), false, 65, 10, false, false);
					html_textarea("auxparam", gtext("Auxiliary parameters"), !empty($pconfig['auxparam']) ? $pconfig['auxparam'] : "", "", false, 65, 5, false, false);
					html_separator();
					html_titleline_checkbox("lcdproc_enable", gtext("LCDproc (Client)"), !empty($pconfig['lcdproc_enable']) ? true : false, gtext("Enable"), "lcdproc_enable_change(false)");
					html_textarea("lcdproc_param", gtext("Extra options"), !empty($pconfig['lcdproc_param']) ? $pconfig['lcdproc_param'] : "", "", false, 65, 10, false, false);
					html_textarea("lcdproc_auxparam", gtext("Auxiliary parameters"), !empty($pconfig['lcdproc_auxparam']) ? $pconfig['lcdproc_auxparam'] : "", "", false, 65, 5, false, false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true); lcdproc_enable_change(true);" />
				</div>
				<div id="remarks">
					<?php
					$helpinghand = '<a href="' . 'http://lcdproc.omnipotent.net' . '" target="_blank">'
						. gtext('To get more information how to configure LCDproc check the LCDproc documentation')
						. '</a>.';
					html_remark("note", gtext('Note'), $helpinghand);
					?>
				</div>
				<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(false);
lcdproc_enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
