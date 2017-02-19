<?php
/*
	guiconfig.inc

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

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")) {
	@date_default_timezone_set(@date_default_timezone_get());
}

/* make sure nothing is cached */
if (isset($omit_nocacheheaders) && $omit_nocacheheaders) {
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}

/* parse the configuration and include all configuration functions */
require_once("config.inc");
require_once("functions.inc");
require_once("rc.inc");
require_once("wui.inc");
require_once('wui2.php');
require_once("updatenotify.inc");
require_once("session.inc");

/* Set the current language */
system_language_load();

$d_fwupenabled_path = $g['varrun_path'] . "/fwup.enabled";
$d_firmwarelock_path = $g['varrun_path'] . "/firmware.lock";
$d_sysrebootreqd_path = $g['varrun_path'] . "/sysreboot.reqd";
$d_upnpconfdirty_path = $g['varrun_path'] . "/upnp.conf.dirty";
$d_packagesconfdirty_path = $g['varrun_path'] . "/packages.conf.dirty";

if (file_exists($d_firmwarelock_path)) {
	if (!$d_isfwfile) {
		header("Location: system_firmware.php");
		exit;
	} else {
		return;
	}
}

/* Reserverd login names */
$reservedlogin = array("root", "toor", "daemon", "operator", "bin", "tty", "kmem", "www", "nobody", "ftp", "sshd");

/* TCP flags */
$tcpflags = array("fin", "syn", "rst", "psh", "ack", "urg");

/* platforms that support firmware updating */
$fwupplatforms = array('embedded', 'full');

/* default setting */
$input_errors = array();
$errormsg = "";
$savemsg = "";

/* AJAX misc */
function is_ajax() {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
		if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') == 0)
			return true;
	}
	return false;
}

function render_ajax($data, $result = 0)
{
	$a = array('result' => $result);
	if (isset($data)) {
		if (is_array($data)) {
			$a = array_merge($a, $data);
		} else {
			$a = array_merge($a, array('data' => $data));
		}
	}

	header("Content-Type: application/json; UTF-8\n");
	echo json_encode($a);
	exit;
}


function do_input_validation($postdata, $reqdfields, $reqdfieldsn, &$input_errors) {
	/* check for bad control characters */
	foreach ($postdata as $pn => $pd) {
		if (is_string($pd) && preg_match("/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f]/", $pd)) {
			$input_errors[] = sprintf( gtext("The attribute '%s' contains invalid characters."), $pn);
		}
	}

	for ($i = 0; $i < count($reqdfields); $i++) {
		if (!isset($postdata[$reqdfields[$i]]) || ($postdata[$reqdfields[$i]] === "")) {
			$input_errors[] = sprintf( gtext("The attribute '%s' is required."), $reqdfieldsn[$i]);
		}
	}
}

/* Validate attribute type. */
function do_input_validation_type($postdata, $reqdfields, $reqdfieldsn, $reqdfieldst, &$input_errors) {
	/* Validate type. */
	for ($i = 0; $i < count($reqdfields); $i++) {
		if (isset($postdata[$reqdfields[$i]]) && ($postdata[$reqdfields[$i]] !== "")) {
			$valid = false;
			$message = "";

			switch($reqdfieldst[$i]) {
				case 'string':				$valid = is_string($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' must be a string."), $reqdfieldsn[$i]);
															break;
				case 'numeric':				$valid = is_numeric($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' must be a number."), $reqdfieldsn[$i]);
															break;
				case 'numericint':		$valid = is_numericint($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' must be a number."), $reqdfieldsn[$i]);
															break;
				case 'ipaddr':				$valid = is_ipaddr($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is not a valid IP address."), $reqdfieldsn[$i]);
															break;
				case 'macaddr': 			$valid = is_macaddr($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is not a valid MAC address."), $reqdfieldsn[$i]);
															break;
				case 'subnet': 				$valid = is_subnet($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is not a valid subnet mask."), $reqdfieldsn[$i]);
															break;
				case 'domain': 				$valid = is_domain($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid domain name."), $reqdfieldsn[$i]);
															break;
				case 'netbios': 			$valid = is_netbios($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid NetBIOS name."), $reqdfieldsn[$i]);
															break;
				case 'hostname': 			$valid = is_hostname($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid host name."), $reqdfieldsn[$i]);
															break;
				case 'workgroup': 		$valid = is_workgroup($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' contains invalid characters and is not a valid workgroup name."), $reqdfieldsn[$i]);
															break;
				case 'filemode': 			$valid = is_filemode($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is not a valid file mode mask."), $reqdfieldsn[$i]);
															break;
				case 'mtu': 					$valid = is_mtu($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is invalid."), $reqdfieldsn[$i]);
															break;
				case 'port': 					$valid = is_port($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' is an invalid port number."), $reqdfieldsn[$i]);
															break;
				case 'password':			$valid = is_validpassword($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' contains the illegal character ':' and is not a valid password."), $reqdfieldsn[$i]);
															break;
				case 'certificate':		$valid = is_valid_certificate($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' does not appear to be a valid certificate."), $reqdfieldsn[$i]);
															break;
				case 'privatekey':		$valid = is_valid_privatekey($postdata[$reqdfields[$i]], "RSA");
															$message = sprintf( gtext("The attribute '%s' does not appear to be a valid private key."), $reqdfieldsn[$i]);
															break;
				case 'privatedsakey':	$valid = is_valid_privatekey($postdata[$reqdfields[$i]], "DSA");
															$message = sprintf( gtext("The attribute '%s' does not appear to be a valid private %s key."), $reqdfieldsn[$i], "DSA");
															break;
				case 'alias':					$valid = is_validaliasname($postdata[$reqdfields[$i]]);
															$message = sprintf( gtext("The attribute '%s' may only consist of the characters a-z, A-Z, 0-9."), $reqdfieldsn[$i]);
															break;
				case 'time':					$valid = (FALSE !== strptime($postdata[$reqdfields[$i]], "%H:%M:%S"));
															$message = sprintf( gtext("The attribute '%s' is not a valid time value."), $reqdfieldsn[$i]);
															break;
				case 'array':					$valid = (is_array($postdata[$reqdfields[$i]]) && !empty($postdata[$reqdfields[$i]]));
															$message = sprintf( gtext("The attribute '%s' must contain at least one entry."), $reqdfieldsn[$i]);
															break;
			}
			if (!$valid) {
				$input_errors[] = $message;
			}
		}
	}
}

function do_input_validate_synctime($postdata, &$input_errors) {
	$data = array();
	$data[] = array("field" => "minute", "all" => "all_mins", "text" => "minutes");
	$data[] = array("field" => "hour", "all" => "all_hours", "text" => "hours");
	$data[] = array("field" => "day", "all" => "all_days", "text" => "days");
	$data[] = array("field" => "month", "all" => "all_months", "text" => "months");
	$data[] = array("field" => "weekday", "all" => "all_weekdays", "text" => "week days");

	foreach ($data as $datak => $datav) {
		if (!$postdata[$datav['all']]) {
			if (!isset($postdata[$datav['field']]) || empty($postdata[$datav['field']])) {
				$input_errors[] = sprintf(gtext("You have to select at least one item for '%s'."), $datav['text']);
			}
		}
	}
}

function print_input_errors($input_errors) {
	echo "  <div id='errorbox'>\n";
	echo "    <table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
	echo "      <tr>\n";
	echo "        <td class=\"icon\" align=\"center\" valign=\"center\"><img src=\"images/error_box.png\" alt=\"\" /></td>\n";
	echo "        <td class=\"message\">\n";
	echo "          <div>" . gtext("The following input errors were detected").":\n";
	echo "            <ul>\n";
	foreach ($input_errors as $msg) {
		echo "              <li>{$msg}</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";
	echo "        </td>\n";
	echo "      </tr>\n";
	echo "    </table>\n";
	echo "  </div>\n";
}

function verify_xz_file($fname) {
	$returnvar = mwexec("/usr/bin/xz -t " . escapeshellarg($fname), true);
	if ($returnvar != 0)
		return 0;
	else
		return 1;
}

function verify_gzip_file($fname) {
	$returnvar = mwexec("/usr/bin/gzip -t " . escapeshellarg($fname), true);
	if ($returnvar != 0)
		return 0;
	else
		return 1;
}

function print_core_box($type, $msg) {
	switch ($type) {
		case "info":
			$id = "infobox";
			$img = "images/info_box.png";
			break;
		case "warning":
			$id = "warningbox";
			$img = "images/warn_box.png";
			break;
		case "error":
			$id = "errorbox";
			$img = "images/error_box.png";
			break;
	}

	echo "<div id='{$id}'>\n";
	echo "  <table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
	echo "    <tr>\n";
	echo "      <td class=\"icon\" align=\"center\" valign=\"center\"><img src=\"/{$img}\" alt=\"\" /></td>\n";
	echo "      <td class=\"message\">{$msg}</td>\n";
	echo "    </tr>\n";
	echo "  </table>\n";
	echo "</div>\n";
}

function print_info_box($msg) {
	print_core_box("info", $msg);
}

function print_error_box($msg) {
	print_core_box("error", $msg);
}

function print_warning_box($msg) {
	print_core_box("warning", $msg);
}

function print_config_change_box() {
	$message = gtext("Apply changes");
	$gt_info = gtext('The configuration has been changed.')
		. '<br />'
		. gtext('You must apply the changes in order for them to take effect.')
		. '<br />'
		. '<b>'
		. '<a href="' . 'diag_log.php' . '">'
		. gtext('If this message persist take a look at the system log for more information.')
		. '</a>'
		. '</b>';
	echo "<div id='applybox'>";
	print_info_box($gt_info);
	echo "<input name=\"apply\" type=\"submit\" class=\"formbtn\" id=\"apply\" value=\"{$message}\" />";
	echo "</div>";
}

function format_bytes($bytes) {
	if ($bytes >= 1099511627776) {
		return sprintf("%.2f TB", $bytes/1099511627776);
	} else if ($bytes >= 1073741824) {
		return sprintf("%.2f GB", $bytes/1073741824);
	} else if ($bytes >= 1048576) {
		return sprintf("%.2f MB", $bytes/1048576);
	} else if ($bytes >= 1024) {
		return sprintf("%.0f KB", $bytes/1024);
	} else {
		return sprintf("%d bytes", $bytes);
	}
}

function get_std_save_message($ok) {
	global $d_sysrebootreqd_path;

	if ($ok == 0) {
		if (file_exists($d_sysrebootreqd_path)) {
			$gt_ret = gtext('The changes have been saved.')
			. ' '
			. '<a href="' . 'reboot.php' . '">'
			. gtext('You have to reboot the system for the changes to take effect.')
			. '</a>';
		} else {
			$gt_ret = gtext('The changes have been applied successfully.');
		}
	} else {
		$gt_ret = gtext('Error')
			. ': '
			. gtext('The changes could not be applied')
			. ' ('
			. gtext('Error Code')
			. ' '
			. sprintf('%s',$ok)
			. ').';
	}
	return $gt_ret;
}

function html_inputbox($ctrlname, $title, $value, $desc, $required = false, $size = 40, $readonly = false) {
	$ctrl = new HTMLEditBox($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_passwordbox($ctrlname, $title, $value, $desc, $required = false, $size = 25, $readonly = false) {
	$ctrl = new HTMLPasswordBox($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_passwordconfbox($ctrlname, $ctrlnameconf, $title, $value, $valueconf, $desc, $required = false, $size = 25, $readonly = false) {
	$ctrl = new HTMLPasswordConfBox($ctrlname, $ctrlnameconf, $title, $value, $valueconf, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_textarea($ctrlname, $title, $value, $desc, $required = false, $columns = 40, $rows = 5, $readonly = false, $wrap = true) {
	$ctrl = new HTMLTextArea($ctrlname, $title, $value, $desc, $columns, $rows);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetWrap($wrap);
	$ctrl->Render();
}

function html_filechooser($ctrlname, $title, $value, $desc, $path, $required = false, $size = 67, $readonly = false) {
	$ctrl = new HTMLFileChooser($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_combobox($ctrlname, $title, $value, $options, $desc, $required = false, $readonly = false, $onclick = "") {
	$ctrl = new HTMLComboBox($ctrlname, $title, $value, $options, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_mountcombobox($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLMountComboBox($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_timezonecombobox($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLTimeZoneComboBox($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_languagecombobox($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLLanguageComboBox($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_interfacecombobox($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLInterfaceComboBox($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_checkbox($ctrlname, $title, $checked, $caption = "", $desc = "", $required = false, $onclick = "") {
	$ctrl = new HTMLCheckBox($ctrlname, $title, $checked, $caption, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_separator($colspan = 2, $idname = '') {
	$ctrl = new HTMLSeparator();
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline($title, $colspan = 2, $idname = '') {
	$ctrl = new HTMLTitleLine($title);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline_checkbox($ctrlname, $title, $value, $caption, $onclick = "", $colspan = 2) {
	$ctrl = new HTMLTitleLineCheckBox($ctrlname, $title, $value, $caption);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_text($ctrlname, $title, $text) {
	$ctrl = new HTMLText($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_textinfo($ctrlname, $title, $text) {
	$ctrl = new HTMLTextInfo($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_remark($ctrlname, $title, $text) {
	$ctrl = new HTMLRemark($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_listbox($ctrlname, $title, $value, $options, $desc, $required = false, $readonly = false, $onclick = "") {
	$ctrl = new HTMLListBox($ctrlname, $title, $value, $options, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_ipv4addrbox($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLIPv4AddressBox($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_ipv6addrbox($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLIPv6AddressBox($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_folderbox($ctrlname, $title, $value, $desc, $path, $required = false, $readonly = false) {
	$ctrl = new HTMLFolderBox($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_minidlnabox($ctrlname, $title, $value, $desc, $path, $required = false, $readonly = false) {
	$ctrl = new HTMLFolderBox1($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_inputbox2($ctrlname, $title, $value, $desc, $required = false, $size = 40, $readonly = false, $altpadding = false, $maxlength = 40) {
	$ctrl = new HTMLEditBox2($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetAltPadding($altpadding);
	$ctrl->SetMaxLength($maxlength);
	$ctrl->Render();
}

function html_passwordbox2($ctrlname, $title, $value, $desc, $required = false, $size = 25, $readonly = false) {
	$ctrl = new HTMLPasswordBox2($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_passwordconfbox2($ctrlname, $ctrlnameconf, $title, $value, $valueconf, $desc, $required = false, $size = 25, $readonly = false) {
	$ctrl = new HTMLPasswordConfBox2($ctrlname, $ctrlnameconf, $title, $value, $valueconf, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_textarea2($ctrlname, $title, $value, $desc, $required = false, $columns = 40, $rows = 5, $readonly = false, $wrap = true) {
	$ctrl = new HTMLTextArea2($ctrlname, $title, $value, $desc, $columns, $rows);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetWrap($wrap);
	$ctrl->Render();
}

function html_filechooser2($ctrlname, $title, $value, $desc, $path, $required = false, $size = 67, $readonly = false) {
	$ctrl = new HTMLFileChooser2($ctrlname, $title, $value, $desc, $size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_combobox2($ctrlname, $title, $value, $options, $desc, $required = false, $readonly = false, $onclick = "") {
	$ctrl = new HTMLComboBox2($ctrlname, $title, $value, $options, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_mountcombobox2($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLMountComboBox2($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_timezonecombobox2($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLTimeZoneComboBox2($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_languagecombobox2($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLLanguageComboBox2($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_interfacecombobox2($ctrlname, $title, $value, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLInterfaceComboBox2($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_checkbox2($ctrlname, $title, $checked, $caption = "", $desc = "", $required = false, $readonly = false, $onclick = "", $altpadding = false) {
	$ctrl = new HTMLCheckBox2($ctrlname, $title, $checked, $caption, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetAltPadding($altpadding);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_separator2($colspan = 2, $idname = '') {
	$ctrl = new HTMLSeparator2();
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline2($title, $colspan = 2, $idname = '') {
	$ctrl = new HTMLTitleLine2($title);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetIdName($idname);
	$ctrl->Render();
}

function html_titleline_checkbox2($ctrlname, $title, $value, $caption, $onclick = "", $colspan = 2) {
	$ctrl = new HTMLTitleLineCheckBox2($ctrlname, $title, $value, $caption);
	$ctrl->SetColSpan($colspan);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_text2($ctrlname, $title, $text) {
	$ctrl = new HTMLText2($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_textinfo2($ctrlname, $title, $text) {
	$ctrl = new HTMLTextInfo2($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_remark2($ctrlname, $title, $text) {
	$ctrl = new HTMLRemark2($ctrlname, $title, $text);
	$ctrl->Render();
}

function html_listbox2($ctrlname, $title, $value, $options, $desc, $required = false, $readonly = false, $onclick = "") {
	$ctrl = new HTMLListBox2($ctrlname, $title, $value, $options, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetJSonClick($onclick);
	$ctrl->Render();
}

function html_ipv4addrbox2($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLIPv4AddressBox2($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_ipv6addrbox2($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc, $required = false, $readonly = false) {
	$ctrl = new HTMLIPv6AddressBox2($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->Render();
}

function html_folderbox2($ctrlname, $title, $value, $desc, $path, $required = false, $readonly = false) {
	$ctrl = new HTMLFolderBox2($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function html_minidlnabox2($ctrlname, $title, $value, $desc, $path, $required = false, $readonly = false) {
	$ctrl = new HTMLFolderBox12($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}

function get_weekday_names($long = NULL, $english = NULL) {
	$actioncode = (is_null($long) ? 0 : 1) || (is_null($english) ? 0 : 2);
	switch ($actioncode) {
		case 0: // short, locale 
			$retval = [gtext('Mon'), gtext('Tue'), gtext('Wed'), gtext('Thu'), gtext('Fri'), gtext('Sat'), gtext('Sun')];
			break;
		case 1: // long, locale
			$retval = [gtext('Monday'),gtext('Tuesday'),gtext('Wednesday'),gtext('Thursday'),gtext('Friday'),gtext('Saturday'),gtext('Sunday')];
			break;
		case 2: // short, english
			$retval = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
			break;
		case 3: // long, english
			$retval = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
			break;
	}
	return $retval;
}

function get_month_names($long = NULL, $english = NULL) {
	$actioncode = (is_null($long) ? 0 : 1) || (is_null($english) ? 0 : 2);
	switch ($actioncode) {
		case 0: // short, locale 
			$retval = [gtext('Jan'), gtext('Feb'), gtext('Mar'), gtext('Apr'), gtext('May'), gtext('Jun'), gtext('Jul'), gtext('Aug'), gtext('Sep'), gtext('Oct'), gtext('Nov'), gtext('Dec')];
			break;
		case 1: // long, locale
			$retval = [gtext('January'),gtext('February'),gtext('March'),gtext('April'),gtext('May'),gtext('June'),gtext('July'),gtext('August'),gtext('September'),gtext('October'),gtext('November'),gtext('December')];
			break;
		case 2: // short, english
			$retval = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
			break;
		case 3: // long, english
			$retval = ['January','February','March','April','May','June','July','August','September','October','November','December'];
			break;
	}
	return $retval;
}
?>
