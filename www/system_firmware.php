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
$d_isfwfile = 1;

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gtext("System"), gtext("Firmware Update"));

// check boot partition
$part1size = $g_install['part1size_embedded'];
$part1min = $g_install['part1min_embedded'];
$cfdevice = trim(file_get_contents("{$g['etc_path']}/cfdevice"));
$diskinfo = disks_get_diskinfo($cfdevice);
unset($errormsg);
$part1ok = true;
if ($g['arch'] == "rpi" || $g['arch'] == "rpi2" || $g['arch'] == "oc1")
	$part1min = 320; /* rpi use 320MB */
if ($diskinfo['mediasize_mbytes'] < $part1min) {
	if (in_array($g['platform'], $fwupplatforms)) {
		$part1ok = false;
		$errormsg = sprintf(gtext("Boot partition is too small. You need reinstall from LiveCD or LiveUSB, or resize boot partition of %s.\n"), $cfdevice);
	}
}

/* checks with /etc/firm.url to see if a newer firmware version online is available;
   returns any HTML message it gets from the server */
$locale = $config['system']['language'];

function check_firmware_version($locale) {
	global $g;

	$post = "product=".rawurlencode(get_product_name())
	      . "&platform=".rawurlencode($g['fullplatform'])
	      . "&version=".rawurlencode(get_product_version())
	      . "&revision=".rawurlencode(get_product_revision());
	$url = trim(get_firm_url());
	if (preg_match('/^([^\/]+)(\/.*)/', $url, $m)) {
		$host = $m[1];
		$path = $m[2];
	} else {
		$host = $url;
		$path = "";
	}

	$rfd = @fsockopen($host, 80, $errno, $errstr, 3);
	if ($rfd) {
		$hdr = "POST $path/checkversion.php?locale=$locale HTTP/1.0\r\n";
		$hdr .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$hdr .= "User-Agent: ".get_product_name()."-webGUI/1.0\r\n";
		$hdr .= "Host: ".$host."\r\n";
		$hdr .= "Content-Length: ".strlen($post)."\r\n\r\n";

		fwrite($rfd, $hdr);
		fwrite($rfd, $post);

		$inhdr = true;
		$resp = "";
		while (!feof($rfd)) {
			$line = fgets($rfd);
			if ($inhdr) {
				if (trim($line) === "")
					$inhdr = false;
			} else {
				$resp .= $line;
			}
		}

		fclose($rfd);

		return $resp;
	}

	return null;
}

function simplexml_load_file_from_url($url, $timeout = 5) {
	if (false !== ($ch = curl_init($url))) { // get handle
		curl_setopt_array($ch, [
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true, // follow location
			CURLOPT_RETURNTRANSFER => true, // return content
			CURLOPT_SSL_VERIFYPEER => true, // verify certificate of peer
			CURLOPT_CAPATH => '/etc/ssl', // certificate directory
			CURLOPT_CAINFO => '/etc/ssl/cert.pem', // root certificates from the Mozilla project
			CURLOPT_CONNECTTIMEOUT => (int)$timeout // set connection and read timeout
		]);
		$data = curl_exec($ch);
		if (curl_errno($ch)) {
			write_log('CURL error: '.curl_error($ch)); // write error to log
		} else {
			curl_close($ch);
			if (false !== $data) { // just to be on the safe side
				$previous_value = libxml_use_internal_errors(true);
				$xml_data = simplexml_load_string($data); // get xml structure
				libxml_clear_errors();
				libxml_use_internal_errors($previous_value); // revert to previous setting
				return $xml_data;
			}
		}
	}
	return false;
}

function get_path_version($rss) {
	$version = get_product_version();

	$resp = "$version";
	// e.g. version = 9.1.0.1 -> 9001, 0001
	if (preg_match("/^.*(\d+)\.(\d+)\.(\d)\.(\d).*$/", $version, $m)) {
		$os_ver = $m[1] * 1000 + $m[2];
		$pd_ver = $m[3] * 1000 + $m[4];
	} else {
		return $resp;
	}

	$xml = simplexml_load_file_from_url($rss);
	if (empty($xml)) return $resp;
	if (empty($xml->channel)) return $resp;
	foreach ($xml->channel->item as $item) {
		$title = $item->title;
		$parts = pathinfo($title);
		if ($parts['dirname'] === "/") {
			if (preg_match("/^.*(\d+)\.(\d+)\.(\d)\.(\d).*$/", $parts['basename'], $m)) {
				$os_ver2 = $m[1] * 1000 + $m[2];
				$pd_ver2 = $m[3] * 1000 + $m[4];
				$rss_version = sprintf("%d.%d.%d.%d", $m[1], $m[2], $m[3], $m[4]);
				// Compare with rss version, equal or greater?
				if ($os_ver2 > $os_ver || ($os_ver2 == $os_ver && $pd_ver2 >= $pd_ver)) {
					$resp = $rss_version;
					break;
				}
			}
		}
	}
	return $resp;
}

function get_latest_file($rss) {
	global $g;
	$product = get_product_name();
	$platform = $g['fullplatform'];
	$version = get_product_version();
	$revision = get_product_revision();
	if (preg_match("/^(.*?)(\d+)$/", $revision, $m)) {
		$revision = $m[2];
	}
	$ext = "img";
	$ext2 = "xz";

	$resp = "";
	$xml = simplexml_load_file_from_url($rss); // @simplexml_load_file($rss);
	if (empty($xml)) return $resp;
	if (empty($xml->channel)) return $resp;
	foreach ($xml->channel->item as $item) {
		$link = $item->link;
		$title = $item->title;
		$pubdate = $item->pubDate;
		$parts = pathinfo($title);

		// convert to local time
		$date = preg_replace('/UT$/', 'GMT', $pubdate);
		$time = strtotime($date);
		if ($time === FALSE) {
			// convert error
			$date = $pubdate;
		} else {
			$date = date("D, d M Y H:i:s T", $time);
		}

		if (empty($parts['extension']))
			continue;
		if (strcasecmp($parts['extension'], $ext) != 0 && strcasecmp($parts['extension'], $ext2) != 0)
			continue;
		$filename = $parts['filename'];
		$fullname = $parts['filename'].".".$parts['extension'];

		if (preg_match("/^{$product}-{$platform}-(.*?)\.(\d+)(\.img)?$/", $filename, $m)) {
			$filever = $m[1];
			$filerev = $m[2];
			if ($version < $filever || ($version == $filever && $revision < $filerev)) {
				$resp .= sprintf("<a href=\"%s\" title=\"%s\" target=\"_blank\">%s</a> (%s)", htmlspecialchars($link), htmlspecialchars($title), htmlspecialchars($fullname), htmlspecialchars($date));
			} else {
				$resp .= sprintf("%s (%s)", htmlspecialchars($fullname), htmlspecialchars($date));
			}
			break;
		}
	}
	return $resp;
}

function check_firmware_version_rss($locale) {
	$rss_path = "https://sourceforge.net/projects/nas4free/rss?limit=40";
	$rss_release = "https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-@@VERSION@@&limit=20";
	$rss_beta = "https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-Beta&limit=20";
	$rss_arm = "https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-ARM&limit=20";
	$rss_arm_beta = "https://sourceforge.net/projects/nas4free/rss?path=/NAS4Free-ARM/Beta&limit=20";

	// replace with existing version
	$path_version = get_path_version($rss_path);
	if (empty($path_version)) {
		return "";
	}
	$rss_release = str_replace('@@VERSION@@', $path_version, $rss_release);

	$release = get_latest_file($rss_release);
	$beta = get_latest_file($rss_beta);
	$hw = @exec("/usr/bin/uname -m");
	if ($hw == 'arm') {
		$arm = get_latest_file($rss_arm);
		$arm_beta = get_latest_file($rss_arm_beta);
	}
	$resp = "";
	if (!empty($release)) {
		$resp .= sprintf(gtext("Latest Release: %s"), $release);
		$resp .= "<br />\n";
	}
	if (!empty($beta)) {
		$resp .= sprintf(gtext("Latest Beta Build: %s"), $beta);
		$resp .= "<br />\n";
	}
	if (!empty($arm)) {
		$resp .= sprintf(gtext("Latest Release: %s"), $arm);
		$resp .= "<br />\n";
	}
	if (!empty($arm_beta)) {
		$resp .= sprintf(gtext("Latest Beta Build: %s"), $arm_beta);
		$resp .= "<br />\n";
	}
	return $resp;
}

if ($_POST && !file_exists($d_firmwarelock_path)) {
	unset($input_errors);
	unset($sig_warning);

	if (isset($_POST['EnableFirmwareUpdate'])) {
		$mode = "enable";
	} elseif (isset($_POST['DisableFirmwareUpdate'])) {
		$mode = "disable";
	} elseif (isset($_POST['PerformFirmwareUpdate'])) {
		$mode = "upgrade";
	} elseif ($_POST['sig_no']) {
		unlink("{$g['ftmp_path']}/firmware.img");
	}
	if ($mode) {
		if ($mode === "enable") {
			$retval = rc_exec_script("/etc/rc.firmware enable");
			if (0 == $retval) {
				touch($d_fwupenabled_path);
			} else {
				$input_errors[] = gtext("Failed to create in-memory file system.");
			}
		} else if ($mode === "disable") {
			rc_exec_script("/etc/rc.firmware disable");
			if (file_exists($d_fwupenabled_path))
				unlink($d_fwupenabled_path);
		} else if ($mode === "upgrade") {
			if (!empty($_FILES) && is_uploaded_file($_FILES['ulfile']['tmp_name'])) {
				/* verify firmware image(s) */
				if (!stristr($_FILES['ulfile']['name'], $g['fullplatform']) && !$_POST['sig_override'])
					$input_errors[] = gtext("The file you try to flash is not for this platform")." ({$g['fullplatform']}).";
				else if (!file_exists($_FILES['ulfile']['tmp_name'])) {
					/* probably out of memory for the MFS */
					$input_errors[] = gtext("Firmware upload failed (out of memory?)");
				} else {
					/* move the image so PHP won't delete it */
					move_uploaded_file($_FILES['ulfile']['tmp_name'], "{$g['ftmp_path']}/firmware.img");

					if (!verify_xz_file("{$g['ftmp_path']}/firmware.img")) {
						$input_errors[] = gtext("The firmware file is corrupt");
						unlink("{$g['ftmp_path']}/firmware.img");
					}
				}
			} else {
				$input_errors[] = gtext("Firmware upload failed (out of memory?)");
			}

			// Cleanup if there were errors.
			if (!empty($input_errors)) {
				rc_exec_script("/etc/rc.firmware disable");
				unlink_if_exists($d_fwupenabled_path);
			}

			// Upgrade firmware if there were no errors.
			if (empty($input_errors) && !file_exists($d_firmwarelock_path) && (empty($sig_warning) || $_POST['sig_override'])) {
				touch($d_firmwarelock_path);

				switch($g['platform']) {
					case "embedded":
						rc_exec_script_async("/etc/rc.firmware upgrade {$g['ftmp_path']}/firmware.img");
						break;

					case "full":
						rc_exec_script_async("/etc/rc.firmware fullupgrade {$g['ftmp_path']}/firmware.img");
						break;
				}

				$savemsg = sprintf(gtext("The firmware is now being installed. The server will reboot automatically."));

				// Clean firmwarelock: Permit to force all pages to be redirect on the firmware page.
				if (file_exists($d_firmwarelock_path))
					unlink($d_firmwarelock_path);

				// Clean fwupenabled: Permit to know if the ram drive /ftmp is created.
				if (file_exists($d_fwupenabled_path))
					unlink($d_fwupenabled_path);
			}
		}
	}
} else {
	$mode = "default";
}
if ($mode === "default" || $mode === "enable" || $mode === "disable") {
	if (!isset($config['system']['disablefirmwarecheck'])) {
		$fwinfo = check_firmware_version_rss($locale);
		if (empty($fwinfo)) {
			//$fwinfo = check_firmware_version($locale);
		}
	}
}
?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			<?php if (!empty($errormsg)) print_error_box($errormsg); ?>
			<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_titleline(gtext("Firmware"));?>
				<?php html_text("Current version", gtext("Current Version:"), sprintf("%s %s (%s)", get_product_name(), get_product_version(), get_product_revision()));?>
				<?php html_separator();?>
				<?php if (isset($fwinfo) && $fwinfo) {
						html_titleline(gtext("Online"));
						echo "<tr id='fwinfo'><td class='vtable' colspan='2'>";
						echo "{$fwinfo}";
						echo "</td></tr>\n";
						html_separator();
					}
				?>
			</table>
			<?php if (!in_array($g['platform'], $fwupplatforms)): ?>
				<?php print_error_box(gtext("Firmware uploading is not supported on this platform."));?>
			<?php elseif (!empty($sig_warning) && empty($input_errors)): ?>
				<form action="system_firmware.php" method="post">
					<?php
						$sig_warning = '<strong>' . $sig_warning . '</strong>'
							. '<br />'
							. gtext('This means that the firmware you flashed is not an official/supported firmware and may lead to unexpected behavior or security compromises.')
							. ' '
							. gtext('Only install firmwares that come from sources that you trust, and make sure that the firmware has not been tampered with.')
							. '<br /><br />'
							. gtext('Do you want to install this firmware anyway (at your own risk)?');
						print_info_box($sig_warning);
					?>
					<input name="sig_override" type="submit" class="formbtn" id="sig_override" value=" Yes ">
					<input name="sig_no" type="submit" class="formbtn" id="sig_no" value=" No ">
					<?php include("formend.inc");?>
				</form>
			<?php else:?>
				<?php if ($part1ok): ?>
					<?php if (!file_exists($d_firmwarelock_path)):?>
					<form action="system_firmware.php" method="post" enctype="multipart/form-data" onsubmit="spinner()">
						<?php if (!file_exists($d_sysrebootreqd_path)):?>
								<?php if (!file_exists($d_fwupenabled_path)):?>
									<div id="submit">
										<input name="EnableFirmwareUpdate" id="Enable" type="submit" class="formbtn" value="<?=gtext("Enable Firmware Update");?>" />
									</div>
								<?php else:?>
									<div id="submit">
										<input name="DisableFirmwareUpdate" id="Disable" type="submit" class="formbtn" value="<?=gtext("Disable Firmware Update");?>" />
									</div>
									<div id="submit">
										<strong><?=gtext("Select firmware:");?> </strong>&nbsp;<input name="ulfile" type="file" class="formfld" size="40" />
									</div>
									<div id="submit">
										<input name="PerformFirmwareUpdate" id="Upgrade" type="submit" class="formbtn" value="<?=gtext("Upgrade Firmware");?>" />
									</div>
									<br />
									<div id="remarks">
										<?php
										$helpinghand = gtext('DO NOT abort the firmware upgrade process once it has started.')
											. '<br />'
											. sprintf(gtext("DO NOT try to flash other files than a valid '%s-%s-embedded.img.xz' file."), get_product_name(), $g['arch'])
											. '<br />'
											. '<a href="' . 'system_backup.php' . '">'
											. gtext('It is recommended that you backup the server configuration before you upgrade')
											. '</a>.';
										html_remark("warning", gtext('Warning'), $helpinghand);
										?>
									</div>
								<?php endif;?>
							<?php else:?>
								<?php
								$helpinghand = '<a href="' . 'reboot.php' . '">'
									. gtext('You must reboot the server before you can upgrade the firmware')
									. '</a>.';
								?>
								<strong><?=$helpinghand;?></strong>
							<?php endif;?>
							<?php include("formend.inc");?>
						</form>
					<?php endif;?>
				<?php endif;?>
			<?php endif;?>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
