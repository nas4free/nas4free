<?php
/*
	system_firmware.php

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
$d_isfwfile = 1; //	for guiconfig.inc, set means do not execute header('system_firmware.php') when file_exists($d_firmwarelock_path);

require 'auth.inc';
require 'guiconfig.inc';


function check_firmware_version($locale) {
/*
	UNUSED FUNCTION
	checks with /etc/firm.url to see if a newer firmware version online is available;
	returns any HTML message it gets from the server
 */
	global $g;
	$post = "product=".rawurlencode(get_product_name())
	      . "&platform=".rawurlencode($g['fullplatform'])
	      . "&version=".rawurlencode(get_product_version())
	      . "&revision=".rawurlencode(get_product_revision());
	$url = trim(get_firm_url());
	if(preg_match('/^([^\/]+)(\/.*)/',$url,$m)):
		$host = $m[1];
		$path = $m[2];
	else:
		$host = $url;
		$path = "";
	endif;
	$rfd = @fsockopen($host,80,$errno,$errstr,3);
	if($rfd):
		$hdr = "POST $path/checkversion.php?locale=$locale HTTP/1.0\r\n";
		$hdr .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$hdr .= "User-Agent: ".get_product_name()."-webGUI/1.0\r\n";
		$hdr .= "Host: ".$host."\r\n";
		$hdr .= "Content-Length: ".strlen($post)."\r\n\r\n";
		fwrite($rfd,$hdr);
		fwrite($rfd,$post);
		$inhdr = true;
		$resp = "";
		while(!feof($rfd)):
			$line = fgets($rfd);
			if($inhdr):
				if (trim($line) === ""):
					$inhdr = false;
				endif;
			else:
				$resp .= $line;
			endif;
		endwhile;
		fclose($rfd);
		return $resp;
	endif;
	return null;
}
function simplexml_load_file_from_url($url,$timeout = 5) {
	if(false !== ($ch = curl_init($url))): // get handle
		curl_setopt_array($ch,[
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true, // follow location
			CURLOPT_RETURNTRANSFER => true, // return content
			CURLOPT_SSL_VERIFYPEER => true, // verify certificate of peer
			CURLOPT_CAPATH => '/etc/ssl', // certificate directory
			CURLOPT_CAINFO => '/etc/ssl/cert.pem', // root certificates from the Mozilla project
			CURLOPT_CONNECTTIMEOUT => (int)$timeout // set connection and read timeout
		]);
		$data = curl_exec($ch);
		if(curl_errno($ch)):
			write_log('CURL error: ' . curl_error($ch)); // write error to log
		else:
			curl_close($ch);
			if(false !== $data): // just to be on the safe side
				$previous_value = libxml_use_internal_errors(true);
				$xml_data = simplexml_load_string($data); // get xml structure
				libxml_clear_errors();
				libxml_use_internal_errors($previous_value); // revert to previous setting
				return $xml_data;
			endif;
		endif;
	endif;
	return false;
}
function get_path_version($rss) {
	$version = get_product_version();
	$resp = "$version";
	// e.g. version = 9.1.0.1 -> 9001, 0001
	if(preg_match("/^.*(\d+)\.(\d+)\.(\d)\.(\d).*$/",$version,$m)):
		$os_ver = $m[1] * 1000 + $m[2];
		$pd_ver = $m[3] * 1000 + $m[4];
	else:
		return $resp;
	endif;
	$xml = simplexml_load_file_from_url($rss);
	if(empty($xml)):
		return $resp;
	endif;
	if(empty($xml->channel)):
		return $resp;
	endif;
	foreach($xml->channel->item as $item):
		$title = $item->title;
		$parts = pathinfo($title);
		if($parts['dirname'] === "/"):
			if(preg_match("/^.*(\d+)\.(\d+)\.(\d)\.(\d).*$/",$parts['basename'],$m)):
				$os_ver2 = $m[1] * 1000 + $m[2];
				$pd_ver2 = $m[3] * 1000 + $m[4];
				$rss_version = sprintf("%d.%d.%d.%d",$m[1],$m[2],$m[3],$m[4]);
				// Compare with rss version, equal or greater?
				if($os_ver2 > $os_ver || ($os_ver2 == $os_ver && $pd_ver2 >= $pd_ver)):
					$resp = $rss_version;
					break;
				endif;
			endif;
		endif;
	endforeach;
	return $resp;
}
function get_latest_file($rss) {
	global $g;
	$product = get_product_name();
	$platform = $g['fullplatform'];
	$version = get_product_version();
	$revision = get_product_revision();
	if(preg_match("/^(.*?)(\d+)$/",$revision,$m)):
		$revision = $m[2];
	endif;
	$ext = 'img';
	$ext2 = 'xz';
	$resp = '';
	$xml = simplexml_load_file_from_url($rss); // @simplexml_load_file($rss);
	if(empty($xml)):
		return $resp;
	endif;
	if(empty($xml->channel)):
		return $resp;
	endif;
	foreach($xml->channel->item as $item):
		$link = $item->link;
		$title = $item->title;
		$pubdate = $item->pubDate;
		$parts = pathinfo($title);
		//	convert to local time
		$date = preg_replace('/UT$/','GMT',$pubdate);
		$time = strtotime($date);
		if($time === FALSE):
			//	convert error
			$date = $pubdate;
		else:
			$date = date("D, d M Y H:i:s T",$time);
		endif;
		if (empty($parts['extension'])):
			continue;
		endif;
		if(strcasecmp($parts['extension'],$ext) != 0 && strcasecmp($parts['extension'],$ext2) != 0):
			continue;
		endif;
		$filename = $parts['filename'];
		$fullname = $parts['filename'] . '.' . $parts['extension'];
		if(preg_match("/^{$product}-{$platform}-(.*?)\.(\d+)(\.img)?$/",$filename,$m)):
			$filever = $m[1];
			$filerev = $m[2];
			if($version < $filever || ($version == $filever && $revision < $filerev)):
				$resp .= sprintf("<a href=\"%s\" title=\"%s\" target=\"_blank\">%s</a> (%s)",htmlspecialchars($link),htmlspecialchars($title),htmlspecialchars($fullname),htmlspecialchars($date));
			else:
				$resp .= sprintf("%s (%s)",htmlspecialchars($fullname),htmlspecialchars($date));
			endif;
			break;
		endif;
	endforeach;
	return $resp;
}
function check_firmware_version_rss($locale) {
	$rss_path = 'https://sourceforge.net/projects/nas4free/rss?limit=40';
	$rss_release = 'https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-@@VERSION@@&limit=20';
	$rss_beta = 'https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-Beta&limit=20';
	$rss_arm = 'https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-ARM&limit=20';
	$rss_arm_beta = 'https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-ARM/Beta&limit=20';

	//	replace with existing version
	$path_version = get_path_version($rss_path);
	if(empty($path_version)):
		return '';
	endif;
	$rss_release = str_replace('@@VERSION@@',$path_version,$rss_release);
	$release = get_latest_file($rss_release);
	$beta = get_latest_file($rss_beta);
	$hw = @exec('/usr/bin/uname -m');
	if($hw == 'arm'):
		$arm = get_latest_file($rss_arm);
		$arm_beta = get_latest_file($rss_arm_beta);
	endif;
	$resp = '';
	if(!empty($release)):
		$resp .= sprintf(gtext('Latest Release: %s'),$release);
		$resp .= "<br />\n";
	endif;
	if(!empty($beta)):
		$resp .= sprintf(gtext('Latest Beta Release: %s'),$beta);
		$resp .= "<br />\n";
	endif;
	if(!empty($arm)):
		$resp .= sprintf(gtext('Latest Release: %s'),$arm);
		$resp .= "<br />\n";
	endif;
	if(!empty($arm_beta)):
		$resp .= sprintf(gtext('Latest Beta Release: %s'),$arm_beta);
		$resp .= "<br />\n";
	endif;
	return $resp;
}

$page_mode = 'default';
$input_errors = [];
$errormsg = '';
$savemsg = '';
$locale = $config['system']['language'] ?? 'en_US';
$firmware_file = sprintf('%s/firmware.img',$g['ftmp_path']);
//	check boot partition
$part1size = $g_install['part1size_embedded'];
$cfdevice = trim(file_get_contents(sprintf('%s/cfdevice',$g['etc_path'])));
$diskinfo = disks_get_diskinfo($cfdevice);
$part1ok = true;
//	determine part1min
switch($g['arch']):
	case 'rpi':
	case 'rpi2':
	case 'oc1':
		$part1min = 320; //	rpi uses 320MB
		break;
	default:
		$part1min = $g_install['part1min_embedded'];
		break;
endswitch;
switch($page_mode):
	case 'default':
		if(in_array($g['platform'],$fwupplatforms)):
			//	check boot partition size
			if($diskinfo['mediasize_mbytes'] < $part1min):
				$part1ok = false;
				$errormsg = sprintf(gtext("Boot partition is too small. You need to reinstall from LiveCD/LiveUSB or resize boot partition of %s.\n"),$cfdevice);
				$page_mode = 'info';
			endif;
		else:
			$errormsg = gtext('Firmware uploading is not supported on this platform.');
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if(file_exists($d_firmwarelock_path)):
			$input_errors[] = gtext('A firmware upgrade is in progress.');
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if(file_exists($d_sysrebootreqd_path)):
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if($_POST && isset($_POST['submit'])):
			switch($_POST['submit']):
				case 'enable':
				case 'disable':
				case 'upgrade':
					$page_mode = $_POST['submit'];
					break;
			endswitch;
		endif;
		break;
endswitch;
switch($page_mode):
	case 'enable':
		$retval = rc_exec_script('/etc/rc.firmware enable');
		if(0 == $retval):
			touch($d_fwupenabled_path);
			unlink_if_exists($firmware_file);
		else:
			$input_errors[] = gtext('Failed to access in-memory file system.');
			$page_mode = 'disable';
		endif;
		break;
	case 'upgrade':
		if(!isset($_FILES['ulfile'])):
			$page_mode = 'disable';
		else:
			if(is_uploaded_file($_FILES['ulfile']['tmp_name'])):
				//	verify firmware image(s)
				if(!stristr($_FILES['ulfile']['name'],$g['fullplatform'])):
					$input_errors[] = gtext('The file you try to flash is not for this platform') . ' (' . $g['fullplatform'] . ').';
				elseif(!file_exists($_FILES['ulfile']['tmp_name'])):
					//	probably out of memory for the MFS
					$input_errors[] = gtext('Firmware upload failed (out of memory?)');
				else:
					//	move the image so PHP won't delete it
					move_uploaded_file($_FILES['ulfile']['tmp_name'],$firmware_file);
					if(!verify_xz_file($firmware_file)):
						$input_errors[] = gtext('The firmware file is corrupt.');
					endif;
				endif;
			else:
				$input_errors[] = gtext('Firmware upload failed with error message:') . sprintf(' %s',$g_file_upload_error[$_FILES['ulfile']['error']]);
			endif;
			//	Upgrade firmware if there were no errors.
			if(empty($input_errors)):
				touch($d_firmwarelock_path);
				switch($g['platform']):
					case 'embedded':
						rc_exec_script_async(sprintf('/etc/rc.firmware upgrade %s',$firmware_file));
						break;
					case 'full':
						rc_exec_script_async(sprintf('/etc/rc.firmware fullupgrade %s',$firmware_file));
						break;
				endswitch;
				$savemsg = gtext('The firmware is now being installed. The server will reboot automatically.');
				//	Clean firmwarelock: Permit to force all pages to be redirect on the firmware page.
//				unlink_if_exists($d_firmwarelock_path);
				//	Clean fwupenabled: Permit to know if the ram drive /ftmp is created.
				unlink_if_exists($d_fwupenabled_path);
			else:
				$page_mode = 'disable';
			endif;
		endif;
		break;
endswitch;
//	must be seperate from enable and upgrade, mode could have been updated to disable on error
switch($page_mode):
	case 'disable':
		rc_exec_script('/etc/rc.firmware disable');
		unlink_if_exists($firmware_file);
		unlink_if_exists($d_fwupenabled_path);
		$page_mode = 'default';
		break;
endswitch;
//	valid modes at this point: info, default, enable, upgrade
$fw_info_current_osver = '';
switch($page_mode):
	case 'info':
	case 'default':
	case 'enable':
		if(!isset($config['system']['disablefirmwarecheck'])):
			$fw_info_current_osver = check_firmware_version_rss($locale);
		endif;
		break;
endswitch;
$pgtitle = [gtext('System'),gtext('Firmware Update')];
include 'fbegin.inc';
?>
<table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	switch($page_mode):
		case 'info':
		case 'default':
		case 'enable':
?>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag"
					<col class="area_data_settings_col_data"
				</colgroup>
				<thead>
<?php
					html_titleline2(gtext('Firmware'));
?>
				</thead>
				<tbody>
<?php
					html_text2('currentversion',gtext('Current Version'),sprintf('%s %s (%s)',get_product_name(),get_product_version(),get_product_revision()));
					if(isset($config['system']['disablefirmwarecheck'])):
						html_text2('onlineversion',gtext('Online Information'),gtext('Firmware upgrade check has been disabled.'));
					else:
						if(preg_match('/\S/',$fw_info_current_osver)):
							html_text2('onlineversion',gtext('Online Information'),$fw_info_current_osver);
						endif;
					endif;
?>
				</tbody>
			</table>
<?php
			break;
	endswitch;
	switch($page_mode):
		case 'default':
?>
			<form action="system_firmware.php" method="post" enctype="multipart/form-data" onsubmit="spinner()">
				<div id="submit">
					<button type="submit" name="submit" class="formbtn" id="button_enable" value="enable"><?=gtext('Enable Firmware Upgrade');?></button>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
<?php
			break;
		case 'enable':
?>
			<form action="system_firmware.php" method="post" enctype="multipart/form-data" onsubmit="spinner()">
				<div id="submit">
					<strong><?=gtext('Select firmware:');?></strong>&nbsp;<input name="ulfile" type="file" class="formfld" size="40"/>
				</div>
				<div id="submit">
					<button type="submit" name="submit" class="formbtn" id="button_disable" value="disable"><?=gtext('Disable Firmware Upgrade');?></button>
					<button type="submit" name="submit" class="formbtn" id="button_upgrade" value="upgrade"><?=gtext('Upgrade Firmware');?></button>
				</div>
				<br />
				<div id="remarks">
<?php
					$helpinghand = gtext('Do not abort the firmware upgrade process once it has started.')
						. '<br />'
						. '<a href="' . 'system_backup.php' . '">'
						. gtext('It is recommended that you backup the server configuration before you upgrade')
						. '</a>.';
					html_remark2('warning',gtext('Warning'),$helpinghand);
?>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
<?php
			break;
	endswitch;				
?>
</td></tr></tbody></table>
<?php
include 'fend.inc';
?>
