<?php
/*
	config.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard (olivier@freenas.org).
	All rights reserved.

	Portions of m0n0wall (http://m0n0.ch/wall).
	Copyright (c) 2003-2006 Manuel Kasper <mk@neon1.net>.
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
require_once("globals.inc");
require_once("util.inc");
require_once("xmlparse.inc");
require_once("system.inc");
require_once("array.inc");
require_once("rc.inc");
require_once("tui.inc");

/* Get product information */
$productname = get_product_name();

/* read platform */
if (file_exists("{$g['etc_path']}/platform")) {
	$g['fullplatform'] = strtolower(chop(file_get_contents("{$g['etc_path']}/platform")));
	$data = explode("-", $g['fullplatform']);
	$g['arch'] = $data[0];
	$g['platform'] = $data[1];
} else {
	$g['arch'] = "unknown";
	$g['platform'] = "unknown";
	$g['fullplatform'] = "unknown";
}

if (is_booting()) {
	// Find the device where config.xml resides and write out an fstab
	unset($cfgdevice);

	// Check if there's already an fstab ('full' release booting?)
	if (!file_exists("{$g['etc_path']}/fstab")) {
		// Display platform type.
		echo "Booting platform: {$g['platform']}\n";

		echo "Looking for configuration file:";

		// Get list of kernel known disks
		$disks = explode(" ", trim(preg_replace("/kern.disks: /", "", exec("/sbin/sysctl kern.disks"))));

		if (stristr($g['platform'], "livecd") || stristr($g['platform'], "liveusb")) {
			// Config is on floppy disk/USB stick for LiveCD version per default
			$cfgdevice = $cfgpartition = "fd0";
			$cfgfstype = "msdos";

			// Display progress...
			echo " fd0";

			// Check if there is any USB device that can be used
			foreach ($disks as $disk) {
				$found = false;

				// Skip non USB (SCSI) device. The filesystem type must be MSDOS
				if (FALSE === ereg("da[0-9]*", $disk))
					continue;

				// Display progress...
				echo " {$disk}";

				// Try to mount device using file system MSDOS (FAT16/32)
				if (0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk}s1 {$g['cf_path']}")) {
					$cfgdevice = $disk;
					$cfgpartition = "{$cfgdevice}s1";
					$found = true;
				} else if (0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk} {$g['cf_path']}")) {
					$cfgdevice = $disk;
					$cfgpartition = $cfgdevice;
					$found = true;
				}

				// Unmount device if necessary and exit foreach loop
				if (true == $found) {
					mwexec("/sbin/umount -f {$g['cf_path']}");
					break;
				}
			}

			// Display device information used to store the configuration
			echo "\n";
			echo "Using device=/dev/{$cfgpartition} fstype={$cfgfstype} to store configuration.\n";
		} else {
			// Probe kernel known disks until we find one with config.xml
			foreach ($disks as $disk) {
				// Skip mfs-mounted filesystems
				if (TRUE === ereg("md[0-9]*", $disk))
					continue;

				// Display progress...
				echo " {$disk}";

				// Try to mount device using UFS
				if (0 == mwexec("/sbin/mount -r /dev/{$disk}s1a {$g['cf_path']}")) { // Search on slice 1
					if (file_exists("{$g['cf_conf_path']}/config.xml")) {
						$cfgdevice = $disk;
						$cfgpartition = "{$cfgdevice}s1a";
						$cfgfstype = "ufs";
					}
					// Unmount device
					mwexec("/sbin/umount -f {$g['cf_path']}");
				} else if (0 == mwexec("/sbin/mount -r /dev/{$disk}a {$g['cf_path']}")) { // Search on partition 'a'
					if (file_exists("{$g['cf_conf_path']}/config.xml")) {
						$cfgdevice = $disk;
						$cfgpartition = "{$cfgdevice}a";
						$cfgfstype = "ufs";
					}
					// Unmount device
					mwexec("/sbin/umount -f {$g['cf_path']}");
				}

				// Device found, exit
				if ($cfgdevice) {
					break;
				}
			}

			if (!$cfgdevice) {
				// Check if there is any USB device that can be used
				foreach ($disks as $disk) {
					// Skip non USB (SCSI) device. The filesystem type must be MSDOS
					if (FALSE === ereg("da[0-9]*", $disk))
						continue;

					// Display progress...
					echo " {$disk}";

					// Try to mount device using file system MSDOS (FAT16/32)
					if (0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk}s1 {$g['cf_path']}")) {
						if (file_exists("{$g['cf_conf_path']}/config.xml")) {
							$cfgdevice = $disk;
							$cfgpartition = "{$cfgdevice}s1";
							$cfgfstype = "msdos";
						}
						// Unmount device
						mwexec("/sbin/umount -f {$g['cf_path']}");
					} else if (0 == mwexec("/sbin/mount -r -t msdos /dev/{$disk} {$g['cf_path']}")) {
						if (file_exists("{$g['cf_conf_path']}/config.xml")) {
							$cfgdevice = $disk;
							$cfgpartition = $cfgdevice;
							$cfgfstype = "msdos";
						}
						// Unmount device
						mwexec("/sbin/umount -f {$g['cf_path']}");
					}

					// Device found, exit
					if ($cfgdevice) {
						break;
					}
				}
			}

			// Display device information where configuration is found
			echo "\n";
			if ($cfgdevice) {
				echo "Found configuration on device=/dev/{$cfgpartition} fstype={$cfgfstype}.\n";
			}
		}

		if (!$cfgdevice) {
			// No device found, print an error and die
			echo <<<EOD

*******************************************************************************
FATAL ERROR!
The device that contains the configuration file (config.xml) could not be
found. {$productname} cannot continue booting.
*******************************************************************************

EOD;

			mwexec("/sbin/halt");
			exit;
		}

		// Write device name to a file (used by rc.firmware for example)
		@file_put_contents("{$g['etc_path']}/cfdevice", $cfgpartition, FILE_TEXT);

		// Write /etc/fstab
		$data = "/dev/{$cfgpartition} {$g['cf_path']} {$cfgfstype} ro 1 1\n";
		@file_put_contents("{$g['etc_path']}/fstab", $data, FILE_TEXT);
	}

	// Append if not found
	if (mwexec("/usr/bin/grep '^proc ' {$g['etc_path']}/fstab") != 0) {
		// Append process file system to /etc/fstab
		$data = "proc /proc procfs rw 0 0\n";
		@file_put_contents("{$g['etc_path']}/fstab", $data, FILE_TEXT | FILE_APPEND);
	}

	// Mount all filesystems
	mwexec("/sbin/mount -a");
}

/* parse configuration */
if (!isset($noparseconfig) || !$noparseconfig) {
	config_lock();

	/* see if there's a newer cache file */
	if (file_exists("{$g['tmp_path']}/config.cache") &&
		(@filemtime("{$g['tmp_path']}/config.cache") >=
		 @filemtime("{$g['conf_path']}/config.xml")) &&
		!is_booting()) {

		/* read cache */
		$config = unserialize(file_get_contents("{$g['tmp_path']}/config.cache"));
	} else {
		if (!file_exists("{$g['conf_path']}/config.xml")) {
			if (is_booting()) {
				if (stristr($g['platform'], "livecd") || stristr($g['platform'], "liveusb")) {
					// Try copying the default config.xml to the floppy disk/USB stick
					config_unlock();
					reset_factory_defaults();
					config_lock();

					echo "\n";
					echo "*******************************************************************************\n";
					echo "No XML configuration file found - using factory defaults.\n";
					echo "Make sure that the configuration floppy disk or USB stick (MSDOS FAT formatted)\n";
					echo "with the config.xml file is inserted. If it isn't, your configuration changes\n";
					echo "will be lost on reboot.\n";
					echo "*******************************************************************************\n";
					echo "\n";
				} else {
					echo "\n";
					echo "*******************************************************************************\n";
					echo "XML configuration file not found. {$productname} cannot continue booting.\n";
					echo "*******************************************************************************\n";
					echo "\n";

					mwexec("/sbin/halt");
					exit;
				}
			} else {
				config_unlock();
				exit(0);
			}
		}

		if (isset($load_raw_config) && $load_raw_config != 0) {
			// Load any root object, but never write to cache
			$config = parse_xml_config_raw("{$g['conf_path']}/config.xml", "");

			if (false === $config) {
				echo "Error: Failed to load '{$g['conf_path']}/config.xml' file. {$productname} cannot continue booting.\n";
				mwexec("/sbin/halt");
				exit;
			}
		} else {
			/* Load configuration file into array. */
			$config = parse_xml_config("{$g['conf_path']}/config.xml", $g['xml_rootobj']);
			if (false === $config) {
				echo "Error: Failed to load '{$g['conf_path']}/config.xml' file. {$productname} cannot continue booting.\n";
				mwexec("/sbin/halt");
				exit;
			}

			if ((float)$config['version'] > (float)$g['latest_config']) {
				if (is_booting()) {
					echo <<<EOD

*******************************************************************************
WARNING!
The current configuration has been created with a newer version of
{$productname} than this one! This can lead to serious misbehaviour and
even security holes! You are urged to either upgrade to a newer version of
{$productname} or revert to the default configuration immediately!
*******************************************************************************

EOD;
				}
			}

			// Write configuration cache file
			config_write_cache($config);
		}
	}

	config_unlock();
	global $config_disks;
	// get S.M.A.R.T. arguments from config and store it in a hash to get better performance:
        if (is_array($config['disks']) && is_array($config['disks']['disk'])) {
                foreach ($config['disks']['disk'] as $disk) {
			$config_disks[$disk['devicespecialfile']]['smart']['devicefilepath'] = $disk['smart']['devicefilepath'];
			$config_disks[$disk['devicespecialfile']]['smart']['devicetypearg'] = $disk['smart']['devicetypearg'];
			$config_disks[$disk['devicespecialfile']]['desc'] = $disk['desc'];
		}
	}
}

/* mount flash card read/write */
function conf_mount_rw() {
	global $g;

	if ($g['platform'] != "full") {
		/* don't use mount -u anymore
		(doesn't sync the files properly and /bin/sync won't help either) */
		mwexec("/sbin/umount -f {$g['cf_path']}");
		mwexec("/sbin/mount -w -o noatime {$g['cf_path']}");
	}
}

/* mount flash card read only */
function conf_mount_ro() {
	global $g;

	if ($g['platform'] != "full") {
		mwexec("/sbin/umount -f {$g['cf_path']}");
		mwexec("/sbin/mount -r {$g['cf_path']}");
	}
}

/**
 * Save the system configuration
 */
function write_config() {
	global $config, $g;

	config_lock();
	conf_mount_rw();

	// Make sure the clock settings is plausible
	if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
        @date_default_timezone_set(@date_default_timezone_get());
	if (time() > mktime(0, 0, 0, 9, 1, 2004)) {
		$config['lastchange'] = time();
	}

	// Dump configuration as XML data
	$xmlconfig = dump_xml_config($config, $g['xml_rootobj'],
		system_get_language_codeset());

	// Save current configuration file
	$filename = "{$g['cf_conf_path']}/config.xml";
	if (file_exists($filename)) {
		for ($n = 6; --$n > 0; ) {
			$filename_bk2 = preg_replace("/.xml$/", ".bk".$n, $filename);
			$filename_bk1 = preg_replace("/.xml$/", ".bk".($n-1), $filename);
			if (file_exists($filename_bk2) && unlink($filename_bk2) === FALSE) {
				die("Error: Failed to delete configuration file '{$filename_bk2}'");
			}
			if (file_exists($filename_bk1) && rename($filename_bk1, $filename_bk2) === FALSE) {
				die("Error: Failed to rename configuration file '{$filename_bk1}'");
			}
		}
		$filename_bk0 = preg_replace("/.xml$/", ".bk0", $filename);
		if (rename($filename, $filename_bk0) === FALSE) {
			die("Error: Failed to rename configuration file '{$filename}'");
		}
	}

	// Write configuration file
	if (!file_put_contents_safe($filename, $xmlconfig)) {
		die("Error: Failed to write configuration file '{$filename}'");
	}

	// Modify file permissions
	@chmod("{$g['cf_conf_path']}/config.xml", 0600);

	conf_mount_ro();

	// Re-read configuration
	$config = parse_xml_config("{$g['conf_path']}/config.xml", $g['xml_rootobj']);

	// Write config cache (to prevent parsing on every access)
	config_write_cache($config);

	// Force completion of pending disk writes
	mwexec("/bin/sync");

	config_unlock();
}

function write_loader_config(){
	global $config, $g;

	// Make sure the clock settings is plausible
	if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
        @date_default_timezone_set(@date_default_timezone_get());
	if (time() > mktime(0, 0, 0, 9, 1, 2004)) {
		$config['lastchange'] = time();
	}

	if(!isset($config['system']['loaderconf']['param'])){
		/* Load configuration file into array. */
		$config = parse_xml_config("{$g['conf_path']}/config.xml", $g['xml_rootobj']);
		
		/* read platform */
		if (file_exists("{$g['etc_path']}/platform")) {
			$g['fullplatform'] = strtolower(chop(file_get_contents("{$g['etc_path']}/platform")));
			$data = explode("-", $g['fullplatform']);
			$g['arch'] = $data[0];
			$g['platform'] = $data[1];
		}
	}

	if (isset($config['system']['loaderconf']['param']) && is_array($config['system']['loaderconf']['param'])){
		config_lock();
		conf_mount_rw();

		// Set loader.conf.local location
		if( $g['platform'] === 'full' ){
			$loader_filename = "/boot/loader.conf.local";
		}else{
			$loader_filename = "{$g['cf_path']}/boot/loader.conf.local";
		}

		array_sort_key($config['system']['loaderconf']['param'], "name");

		$lines = '';
		$loader_param_list = &$config['system']['loaderconf']['param'];

		foreach($loader_param_list as $param){
			if(isset($param['enable'])){
				$lines .= $param['name'].' = "'.trim($param['value'])."\"\n";
			}
		}

		if(strlen($lines) == 0){
			unlink($loader_filename);
		}
		// Write configuration file
		else if (file_put_contents_safe($loader_filename, $lines ) === FALSE) {
			die("Error: Failed to write configuration file '{$loader_filename}'");
		}

		// Modify file permissions
		@chmod("{$g['cf_path']}/boot/loader.conf.local", 0600);

		conf_mount_ro();

		// Force completion of pending disk writes
		mwexec("/bin/sync");

		config_unlock();
	}

}

function reset_factory_defaults() {
	global $g;

	config_lock();
	conf_mount_rw();

	/* create conf directory, if necessary */
	if (!file_exists("{$g['cf_conf_path']}"))
		@mkdir("{$g['cf_conf_path']}");

	/* clear out /conf */
	$dh = opendir($g['conf_path']);
	while ($filename = readdir($dh)) {
		if (($filename != ".") && ($filename != "..")) {
			unlink($g['conf_path'] . "/" . $filename);
		}
	}
	closedir($dh);

	/* copy default configuration */
	@copy("{$g['conf_default_path']}/config.xml", "{$g['conf_path']}/config.xml");

	// Modify file permissions
	@chmod("{$g['cf_conf_path']}/config.xml", 0600);

	$loader_filename = "{$g['cf_path']}/boot/loader.conf.local";

	if (file_exists($loader_filename) && unlink($loader_filename) === FALSE) {
		die("Error: Failed to delete configuration file '{$loader_filename}'");
	}
	mwexec2("/bin/echo loader filename is ".$loader_filename);
	sleep(30);

	conf_mount_ro();
	config_unlock();

	return 0;
}

/**
 * Install the given configuration file.
 * @param[in] conffile The path to the file to be installed.
 * @return Returns 0 on success or 1 on failure.
 */
function config_install($conffile) {
	global $config, $g;

	if (!file_exists($conffile))
		return 1;

	config_lock();
	conf_mount_rw();

	$result = copy($conffile, "{$g['conf_path']}/config.xml");

	conf_mount_ro();
	config_unlock();

	if($result === TRUE) {
		write_loader_config();
	}

	return (TRUE === $result) ? 0 : 1;
}

/* Lock configuration file, decide that the lock file is stale after 10 seconds */
function config_lock() {
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";

	$n = 0;
	while ($n < 15) {
		/* open the lock file in append mode to avoid race condition */
		if ($fd = @fopen($lockfile, "x")) {
			/* succeeded */
			fclose($fd);
			return;
		} else {
			/* file locked, wait and try again */
			sleep(1);
			$n++;
		}
	}
	mwexec("/usr/bin/logger 'Lock error {$lockfile}'");
	while (1) {
		/* can't continue without lock */
		sleep(1);
	}
	exit;
}

/* Unlock configuration file */
function config_unlock() {
	global $g;

	$lockfile = "{$g['varrun_path']}/config.lock";

	if (file_exists($lockfile))
		unlink($lockfile);
}

/**
 * Write configuration cache file.
 * @param[in] data	The data to write.
 * @return True if successful, otherwise false.
 */
function config_write_cache($data) {
	global $g;

	$filename = "{$g['tmp_path']}/config.cache";

	// Ensure configuration cache file is written
	if (!file_put_contents_safe($filename, serialize($data), TRUE)) {
		write_log("Error: Failed to write configuration cache file '{$filename}'.");
		return FALSE;
	}

	// Modify file permissions
	@chmod($filename, 0600);

	return TRUE;
}

// Convert the config file.
// Return 0 if successful, otherwise 1
function config_upgrade() {
	global $config, $g;

	/* Reject blacklist config */
	if ($config['version'] == '9.6') {
		putenv("TERM=xterm"); // not defined at boot
		$result = tui_display_yesno("Your config is a blacklist. You must reset the config or reinstall.\n\nDo you want to reset to Factory Default now?", 50, 8, false);
		if (0 == $result) {
			config_unlock();
			reset_factory_defaults();
			write_console("\nrebooting now.\n");
			mwexec("/sbin/reboot");
			exit;
		}
		write_console("\ngoing to halt.\n");
		mwexec("/sbin/halt");
		exit;
	}

	if ($config['version'] == $g['latest_config'])
		return 0;		/* already at latest version */

	if (is_booting())
		echo "Converting configuration. ({$config['version']} to {$g['latest_config']}) ";

	/* Convert from older config */
	if ($config['version'] === "0.9") {
		// XXX password is not changed now.
		$config['system']['hostname'] = 'nas4free';
		$config['samba']['netbiosname'] = 'nas4free';
		$config['samba']['serverdesc'] = 'NAS4Free Server';
		$config['version'] = "1.0";
	}

	/* Convert 1.0 -> 1.1 */
	if ($config['version'] === "1.0") {
		$config['system']['motd'] = 'V2VsY29tZSB0byBOQVM0RnJlZSE=';
		$config['version'] = "1.1";
	}

	/* Convert 1.1 -> 1.2 */
	if ($config['version'] === "1.1") {
		$params = array(
			"# USB connected HD44780 based LCD",
			"ConnectionType=lcd2usb",
			"Contrast=825",
			"Brightness=4",
			"OffBrightness=0",
			"Keypad=yes",
			"Backlight=yes",
			"Size=20x4",
			"KeyDirect_1=Enter",
			"KeyDirect_2=Down",
			"KeyDirect_3=Escape",
		);
		unset($config['lcdproc']['param']);
		foreach ($params as $param) {
			$config['lcdproc']['param'][] = $param;
		}
		$config['lcdproc']['titlespeed'] = 10;
		$config['version'] = "1.2";
	}

	/* Convert 1.2 -> 1.3 */
	if ($config['version'] === "1.2") {
		// modify existing key
		if ($config['system']['motd'] === "V2VsY29tZSB0byBOQVM0RnJlZSE=")
			$config['system']['motd'] = "V2VsY29tZSB0byBOQVM0RnJlZSENCg==";
		if ($config['samba']['aiorsize'] < 4096)
			$config['samba']['aiorsize'] = 4096;
		if ($config['samba']['aiowsize'] < 4096)
			$config['samba']['aiowsize'] = 4096;
		if (empty($config['iscsitarget']['uctladdress'])) {
			$config['iscsitarget']['uctladdress'] = "127.0.0.1";
			$config['iscsitarget']['uctlport'] = "3261";
			$config['iscsitarget']['uctlnetmask'] = "127.0.0.1/8";
			$config['iscsitarget']['uctlauthmethod'] = "CHAP";
			$config['iscsitarget']['uctlauthgroup'] = 0;
			$config['iscsitarget']['mediadirectory'] = "/mnt";
		}
		if (!empty($config['system']['sysctl']) && !empty($config['system']['sysctl']['param'])) {
			$index = array_search_ex("kern.maxfilesperproc", $config['system']['sysctl']['param'], "name");
			if ($index !== false) {
				if ($config['system']['sysctl']['param'][$index]['value'] === "32768") {
					$config['system']['sysctl']['param'][$index]['value'] = "50000";
				}
			}
		}
		// remove unused key
		if (!empty($config['system']['sysctl']) && !empty($config['system']['sysctl']['param'])) {
			$index = array_search_ex("hw.ata.to", $config['system']['sysctl']['param'], "name");
			if ($index !== false) {
				unset($config['system']['sysctl']['param'][$index]);
			}
		}
		unset($a);
		// remove empty entry
		if (isset($config['system']['hosts'])) {
			$r = array();
			foreach ($config['system']['hosts'] as &$a) {
				if (empty($a) || !isset($a['uuid'])) continue;
				$r[] = $a;
			}
			$config['system']['hosts'] = $r;
			unset($a);
		}
		// add missing values
		if (!empty($config['zfs']['pools']) && !empty($config['zfs']['pools']['pool'])) {
			foreach ($config['zfs']['pools']['pool'] as &$a) {
				if (!isset($a['desc']))
					$a['desc'] = "";
			}
			unset($a);
		}
		if (!empty($config['zfs']['vdevices']) && !empty($config['zfs']['vdevices']['vdevice'])) {
			foreach ($config['zfs']['vdevices']['vdevice'] as &$a) {
				if (!isset($a['desc']))
					$a['desc'] = "";
			}
			unset($a);
		}
		if (!empty($config['zfs']['datasets']) && !empty($config['zfs']['datasets']['dataset'])) {
			foreach ($config['zfs']['datasets']['dataset'] as &$a) {
				if (!isset($a['desc']))
					$a['desc'] = "";
				if (!isset($a['atime']))
					$a['atime'] = "on";
			}
			unset($a);
		}
		if (!empty($config['zfs']['volumes']) && !empty($config['zfs']['volumes']['volume'])) {
			foreach ($config['zfs']['volumes']['volume'] as &$a) {
				if (!isset($a['desc']))
					$a['desc'] = "";
			}
			unset($a);
		}
		// replace certificate and privatekey
		if (md5($config['system']['webgui']['certificate']) === "ee3e3c2ddc0f186e8d40e797e8af6f36") {
			$config['system']['webgui']['certificate'] = "LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpNSUlEWURDQ0FrZ0NDUUNZaG9rNlRyekZCakFOQmdrcWhraUc5dzBCQVFVRkFEQnlNUXN3Q1FZRFZRUUdFd0pLDQpVREVUTUJFR0ExVUVDQk1LVTI5dFpTMVRkR0YwWlRFUk1BOEdBMVVFQ2hNSVRrRlRORVp5WldVeEZ6QVZCZ05WDQpCQU1URG01aGN6Um1jbVZsTG14dlkyRnNNU0l3SUFZSktvWklodmNOQVFrQkZoTnBibVp2UUc1aGN6Um1jbVZsDQpMbXh2WTJGc01CNFhEVEV5TURreE56RXlNVEkxTmxvWERUTXlNRGt4TWpFeU1USTFObG93Y2pFTE1Ba0dBMVVFDQpCaE1DU2xBeEV6QVJCZ05WQkFnVENsTnZiV1V0VTNSaGRHVXhFVEFQQmdOVkJBb1RDRTVCVXpSR2NtVmxNUmN3DQpGUVlEVlFRREV3NXVZWE0wWm5KbFpTNXNiMk5oYkRFaU1DQUdDU3FHU0liM0RRRUpBUllUYVc1bWIwQnVZWE0wDQpabkpsWlM1c2IyTmhiRENDQVNJd0RRWUpLb1pJaHZjTkFRRUJCUUFEZ2dFUEFEQ0NBUW9DZ2dFQkFMSCtTR1hrDQpjSzRnNVp2MWRSRFJKQVh0NVhHMVVpZVEzZ1UzdlIvZjBYZnJ5RFBPNjJ3WkhnTWdONTJocGVRL1VvNXl4dTRMDQp1azJtUlpTYlpPai92b0xSZWlpSy8zY0grUStRVml4SFpaMlpQNThBeWpHTXNUOGJ0T3pwYlN4MWtkQkEvUkhqDQpmYmpPamRFK0FocDJiWkVoQzVmaXlYVjlrdjFzTmZmMnFlM0REMUZtd0RGbDM0cVdPT1g2SEFYNHpWOWcrY1dJDQo0S0FRdEYwZ3FJWWd5Z29OckM5dDI4VVc4Z2dqcWs4T0EycnNMUFRsMmkzMk5DanVJSThjL1EzOUhYNkZBRUdvDQo1VGZGL3V0WTlBaFBaaHpWbk0zazJtUFR1RGdMbVZaZmtieml1eHVES293ZzBGUC9IZlZabWRoMkV3M3Y1NmZGDQpDLzh3N1F2QkdPRjF2SXNDQXdFQUFUQU5CZ2txaGtpRzl3MEJBUVVGQUFPQ0FRRUFidWxJNlJCTXFJTzFMT21jDQo1OGg5aHJ1Z25vTFkxNndIRFZQdTE4WmpUSzZYcDBDYWlONVYyR1MwdWdUNzJ2QzVrcXQ0cW1CUWFBa2MwbVdWDQpQQi9YUXhyaEFTd25VMzh1TGVJeWlnMzFCWkx3Yll5S3IzTUJ4cVI5dWwvdWY1MWErR2dVOGFETk42VDRQbGJJDQp5Z0M2dDUxa2E1VzBLbFFGeWhwNTlXT21GOElLOURRTlZpQWpTWkk0WUZsZXhJc1dFTC9DNCtMTGNhTVp3YUUzDQpLUTVKK1lFcG5qMFBKOUYvU1Y4MHFNdnE0RWJ4YmUzbGpFSGlMSVU0VktwV2I0VWJMSWxVNEZNeWtTSzNYMGp4DQpESzJGcERPRnUxL3c2d0FrYW5KanFSQXozUVViRVVocFhCNnVUVUVGcTVJSHNqNkZkbTBkMThkU2NiT0xWcmJGDQpBYlYrZHc9PQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==";
		}
		if (md5($config['system']['webgui']['privatekey']) === "d0b0ae6c3eed857b471287b96f2bd072") {
			$config['system']['webgui']['privatekey'] = "LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJRXBBSUJBQUtDQVFFQXNmNUlaZVJ3cmlEbG0vVjFFTkVrQmUzbGNiVlNKNURlQlRlOUg5L1JkK3ZJTTg3cg0KYkJrZUF5QTNuYUdsNUQ5U2puTEc3Z3U2VGFaRmxKdGs2UCsrZ3RGNktJci9kd2Y1RDVCV0xFZGxuWmsvbndESw0KTVl5eFB4dTA3T2x0TEhXUjBFRDlFZU45dU02TjBUNENHblp0a1NFTGwrTEpkWDJTL1d3MTkvYXA3Y01QVVdiQQ0KTVdYZmlwWTQ1Zm9jQmZqTlgyRDV4WWpnb0JDMFhTQ29oaURLQ2cyc0wyM2J4UmJ5Q0NPcVR3NERhdXdzOU9YYQ0KTGZZMEtPNGdqeHo5RGYwZGZvVUFRYWpsTjhYKzYxajBDRTltSE5XY3plVGFZOU80T0F1WlZsK1J2T0s3RzRNcQ0KakNEUVUvOGQ5Vm1aMkhZVERlL25wOFVML3pEdEM4RVk0WFc4aXdJREFRQUJBb0lCQUU0TTFTNXBCOGd6Z0x2cw0KM0pLdFF3Q2VteGhMakdUeEw0cFdNTWFEWE5VYlBVVUtuVTFmamp1TXF4YWlSWjVhN2hhczJmS1VqZk55ejl3aw0KY0JHRDQ4UjFtdjJnUmZtcU0xclJ6aGZJanhTV21NRmVsVXM1Q0poRnpBWi9MWEpyYzY5QWNEaWg2d1ZZL2ZNZA0KSDhyKzEzam1ackVKcjVHbnFRY2JwbWEvY1RCaXR2TnNib3VSZmlQSFB5ck84UlBEdXVIc3k1VXovSVB6cDFMTA0KWmNNd2hwK1RsSjlnVmNDMHRvN2VlemxUMEZ6a2hsMTgwVTlla09wTlB1WUJGc1NsdWZyVEhqTnBzRVE5aGtmKw0KeGJjQjdlZTFvZXJtUjdOSmdBRWVHeS9FeXVuK0UvOWZpOUovb1JCbjZnVXhiaDkvQ1dPd2VGeVRZVVZvd1d1RA0KUC9NY0prRUNnWUVBNUs4YzJKdmtQbWhpWGtNRU9GQkxZQkxvaFBKa2hqdU0rQWlMaERxeENpRTg4S0pod3d3WQ0KdWFpaU0rbXkraTcrbzBDZVF5NlJvb0d2UVRYVHRFUkhEMldtaS9JZzlDdFF0cWNibnRsbEtGYVQrcnZHdmp0RA0KZ3dzV1Y0N09Ia3pwUThWUkZueVZOMnBLZVNudzVnR1F3a2RXaFdIR0wwdXBJNklIakExVVI1c0NnWUVBeDBFYg0KY01BYXVkNXZlZW1NQzN5ZmZTMEx0Z2dtR2dGMGxLSFBJTm5XbXBWdkhjTkZZaGtXMENKMVdNQm5sTlhESkdJKw0KWHkyU3lBNVlteWZCN2FTWWdxVTI5S3ZSejJVR0kvOUlJY1NTM09TK2NEdjcyQjljQjlQN3VMUVBSN281S0s3eA0KSEM4NkxuWVIrb2lqbDBXYzJYUmxrdmt4U0h0YkhVVm8yUXIzeGRFQ2dZRUFubmgyN2VnQm4vbW9DUmc1eVdoag0KQTEyM0FjSVRSUzNwUDB4MG1ob3Q2dzRqYzhHaks0MnJhS1RpV2tVZGdOK21GNjFFdnR0N2tuaDU0UitTNkQzVQ0KWEdnZHBLY1NqUUdqT2gvTk5TUURSSlBTa2hNajQxbWdMQ1EzeEU4eXFLNjFpd0FtZEFXN1JaNkRvemtjTmRkcA0KNENlZFhtcmw4b3BrMkV1RkhWbHpTUGNDZ1lCdFRsb3ZVRE5aSTUwajArcnZvajRiL1VvZllma0U2eWQ1SWdydQ0KaXBJZjJ6cEpxWVcyZXV6NDRPNWU4YTNoQ0NNMUltLzA1cHRQQkErM2plZms1aGFFRlA1QTJzRU9kTzBaY2tHdg0KeEphTWR5L0JnaVoyT1QzVFRZY1puQzJGdTdML2pualdiVklOaXRmeUNHTTE2UXBTZ0hmNFdKdWdsNHMwVTQ5aA0KQVpOTEFRS0JnUURKZzNlUWV0TWR3WUpKbjFzZmVTbUVnTVFNMjZOLzNidURSUG1rTUVtdTgvcVJRYTM4T3VXOA0KUUFmTFVNOC83Vm5OUlhvbmZMR2s0eUpKdG5RSlQ0TlZLdjEvUzgweldNSXJ5azM4L3ZreGVsbDA1Q1Y2Rnp5eA0KdGVtcjRZcE8yMDBxWFl5MjR0aC9QM0RhbFYwcDhWcXJvemJ4eExiS1k5TUpLSWZpWHo5bnV3PT0NCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t";
		}
		$config['version'] = "1.3";
	}

	/* Convert 1.3 -> 1.4 */
	if ($config['version'] === "1.3") {
		// add missing values
		if (!empty($config['zfs']['datasets']) && !empty($config['zfs']['datasets']['dataset'])) {
			foreach ($config['zfs']['datasets']['dataset'] as &$a) {
				if (!isset($a['dedup']))
					$a['dedup'] = "off";
				if (!isset($a['sync']))
					$a['sync'] = "standard";
			}
			unset($a);
		}
		if (!empty($config['zfs']['volumes']) && !empty($config['zfs']['volumes']['volume'])) {
			foreach ($config['zfs']['volumes']['volume'] as &$a) {
				if (!isset($a['dedup']))
					$a['dedup'] = "off";
				if (!isset($a['sync']))
					$a['sync'] = "standard";
			}
			unset($a);
		}
		$config['version'] = "1.4";
	}

	/* Convert 1.4 -> 1.5 */
	if ($config['version'] === "1.4") {
		// add missing values
		if (!empty($config['zfs']['datasets']) && !empty($config['zfs']['datasets']['dataset'])) {
			foreach ($config['zfs']['datasets']['dataset'] as &$a) {
				$a['reservation'] = "";
				$a['accessrestrictions']['owner'] = "root";
				$a['accessrestrictions']['group'] = "wheel";
				$a['accessrestrictions']['mode'] = "0777";
			}
			unset($a);
		}
		$config['version'] = "1.5";
	}

	/* Convert 1.5 -> 1.6 */
	if ($config['version'] === "1.5") {
		$config['system']['pwmode'] = "hiadaptive";
		$config['system']['pwmax'] = "";
		$config['system']['pwmin'] = "";
		$pwopt = "-a hiadaptive -b hiadaptive -n hiadaptive";
		$index = array_search_ex("powerd_flags", $config['system']['rcconf']['param'], "name");
		if ($index !== false) {
			$config['system']['rcconf']['param'][$index]['value'] = $pwopt;
		} else {
			$config['system']['rcconf']['param'][] = array(
				"uuid" => uuid(),
				"name" => "powerd_flags",
				"value" => $pwopt,
				"comment" => "System power control options",
				"enable" => true );
		}
		$config['version'] = "1.6";
	}

	/* Convert 1.6 -> 1.7 */
	if ($config['version'] === "1.6") {
		if (!empty($config['system']['sysctl']) && !empty($config['system']['sysctl']['param'])) {
			$index = array_search_ex("kern.maxfilesperproc", $config['system']['sysctl']['param'], "name");
			if ($index !== false) {
				if ($config['system']['sysctl']['param'][$index]['value'] < 50000) {
					$config['system']['sysctl']['param'][$index]['value'] = "50000";
				}
			}
		}
		if (!empty($config['ldap']['hostname'])) {
			$config['ldap']['hostname'] = "ldap://".$config['ldap']['hostname'];
		}
		$config['version'] = "1.7";
	}

	/* Convert 1.7 -> 1.8 */
	if ($config['version'] === "1.7") {
		if (!empty($config['system']['sysctl']) && !empty($config['system']['sysctl']['param'])) {
			$a_mib = array(
				"net.inet.tcp.sendspace" => 131072,
				"net.inet.tcp.recvspace" => 131072,
				"net.inet.tcp.sendbuf_inc" => 131072,
				"net.inet.tcp.recvbuf_inc" => 131072,
				"kern.ipc.nmbclusters" => 131072,
				"kern.ipc.nmbjumbop" => 131072,
				"kern.ipc.nmbjumbo9" => 32768,
				"kern.ipc.nmbjumbo16" => 16384,
				"kern.maxfilesperproc" => 60000,
			);
			foreach ($a_mib as $key => $val) {
				$index = array_search_ex($key, $config['system']['sysctl']['param'], "name");
				if ($index !== false) {
					$config['system']['sysctl']['param'][$index]['value'] = $val;
				}
			}
		}
		$index = array_search_ex("varsize", $config['system']['rcconf']['param'], "name");
		if ($index === false) {
			$config['system']['rcconf']['param'][] = array(
				"uuid" => uuid(),
				"name" => "varsize",
				"value" => "128m",
				"comment" => "Default /var size",
				"enable" => true );
		} else {
			$config['system']['rcconf']['param'][$index]['value'] = "128m";
		}
		$index = array_search_ex("vartmpsize", $config['system']['rcconf']['param'], "name");
		if ($index === false) {
			$config['system']['rcconf']['param'][] = array(
				"uuid" => uuid(),
				"name" => "vartmpsize",
				"value" => "64m",
				"comment" => "Default /var/tmp size",
				"enable" => true );
		} else {
			$config['system']['rcconf']['param'][$index]['value'] = "64m";
		}

		$config['system']['webgui']['certificate'] = "LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpNSUlFT1RDQ0F5R2dBd0lCQWdJSkFQUnVoc3FVRWdKcE1BMEdDU3FHU0liM0RRRUJDd1VBTUhBeEN6QUpCZ05WDQpCQVlUQWtwUU1STXdFUVlEVlFRSUV3cFRiMjFsTFZOMFlYUmxNUkV3RHdZRFZRUUtFd2hPUVZNMFJuSmxaVEVYDQpNQlVHQTFVRUF4TU9ibUZ6TkdaeVpXVXViRzlqWVd3eElEQWVCZ2txaGtpRzl3MEJDUUVXRVdsdVptOUFibUZ6DQpOR1p5WldVdWIzSm5NQjRYRFRFME1USXdNekF3TURJd01sb1hEVE0wTVRJd016QXdNREl3TWxvd2NERUxNQWtHDQpBMVVFQmhNQ1NsQXhFekFSQmdOVkJBZ1RDbE52YldVdFUzUmhkR1V4RVRBUEJnTlZCQW9UQ0U1QlV6UkdjbVZsDQpNUmN3RlFZRFZRUURFdzV1WVhNMFpuSmxaUzVzYjJOaGJERWdNQjRHQ1NxR1NJYjNEUUVKQVJZUmFXNW1iMEJ1DQpZWE0wWm5KbFpTNXZjbWN3Z2dFaU1BMEdDU3FHU0liM0RRRUJBUVVBQTRJQkR3QXdnZ0VLQW9JQkFRQ3RqVTRnDQpWY1hWSG1BUzBVbW5mRzZ0K050N2JzZ3Y1TytSR3dRa1NSVk0rdTZ0d0dZc2Jmck4zSHpNV3I1elBHUGJxNU9kDQpiN1JZa0plVVBIM1kxRXhoMlUwNVA5YmorWkpXVGxrTXZSbXVJU2hlZWJ2Qlp2WjNlQXhuaC9IWVozWDVabisyDQpibVR0bzVTOVRNMXNoeEFtenNGdVBMZFRuSFhRWjdWZzl4VHZXNFYvUklMWGZNQ3hjVUtwVjZyQjVpN1BjVW9SDQpmNWErNEpWWjBNMlVPdTZSdllHdmQvT09yUkhWbEd0cUpIclBiOVo1ODBkMFFzOCt4cVgzWThHc3l2VmYrTUJtDQpycmQ1UzZqNGdSWlpGeUJzeS9QcGhEcWt0bks2VHBjMlN1VE5xbWtLd0JadTZyZzlDNTB4a011dVp5dFA3UkpRDQpYS3lNVWs3bHg2MU1Na0xSQWdNQkFBR2pnZFV3Z2RJd0hRWURWUjBPQkJZRUZHcEZWMHV6RGJYK3dNc3FjQkVFDQpJOTArMER1dE1JR2lCZ05WSFNNRWdab3dnWmVBRkdwRlYwdXpEYlgrd01zcWNCRUVJOTArMER1dG9YU2tjakJ3DQpNUXN3Q1FZRFZRUUdFd0pLVURFVE1CRUdBMVVFQ0JNS1UyOXRaUzFUZEdGMFpURVJNQThHQTFVRUNoTUlUa0ZUDQpORVp5WldVeEZ6QVZCZ05WQkFNVERtNWhjelJtY21WbExteHZZMkZzTVNBd0hnWUpLb1pJaHZjTkFRa0JGaEZwDQpibVp2UUc1aGN6Um1jbVZsTG05eVo0SUpBUFJ1aHNxVUVnSnBNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJDQpodmNOQVFFTEJRQURnZ0VCQUQ3aUxLaWVsRHBVVk9BWGVCeDk0MDVmZ0p4ZUtLVXZUMDVBNWtSQVhTanFnYkdjDQp2eWkySUpWaTNadlV4OWJtL3RyME5MclhQaVBMRlU5T3pTd0tQQXJnbGdjY2JKbDlBV0xTN3d3RTdaQzRnTTE5DQpBSmYyR09RcEwzYnoyaXk0TTBkdGJjUnBHdDRHV2szdDZ6NXozZTdJblNaNDdTcWhtbVNIekUwdTlhWTRJUWZwDQovVHJ1ZTBVYTVYNGJsVUhvTnhVb2xmQ1ptdTU3VkZpWlJLSlhEZGhVSHFKbEhMT1BUYmxtZWZtSTg3RVhDTjFBDQo5YWIyVEhWWVZhSnlVM2p5VUo3by9xNmtzYlB5UDlTeFBSZCsvSmszbHVJemp4V1NHRXZoNDhZc0dlWWo2MVN1DQpkVUZoUWZ6S3FzeXNUN21ZQ1NuL1RqUkpnYnZOaWpVaGZiemtMZUE9DQotLS0tLUVORCBDRVJUSUZJQ0FURS0tLS0t";
		$config['system']['webgui']['privatekey'] = "LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KTUlJRXBRSUJBQUtDQVFFQXJZMU9JRlhGMVI1Z0V0RkpwM3h1cmZqYmUyN0lMK1R2a1JzRUpFa1ZUUHJ1cmNCbQ0KTEczNnpkeDh6RnErY3p4ajI2dVRuVyswV0pDWGxEeDkyTlJNWWRsTk9UL1c0L21TVms1WkRMMFpyaUVvWG5tNw0Kd1diMmQzZ01aNGZ4MkdkMStXWi90bTVrN2FPVXZVek5iSWNRSnM3QmJqeTNVNXgxMEdlMVlQY1U3MXVGZjBTQw0KMTN6QXNYRkNxVmVxd2VZdXozRktFWCtXdnVDVldkRE5sRHJ1a2IyQnIzZnpqcTBSMVpScmFpUjZ6Mi9XZWZOSA0KZEVMUFBzYWw5MlBCck1yMVgvakFacTYzZVV1bytJRVdXUmNnYk12ejZZUTZwTFp5dWs2WE5rcmt6YXBwQ3NBVw0KYnVxNFBRdWRNWkRMcm1jclQrMFNVRnlzakZKTzVjZXRUREpDMFFJREFRQUJBb0lCQVFDSWU2NnNDU2dhVU5mMw0KRkhXYTdHVlNGWG50bVJKL2I4ZmkwbDh3ZGZUMVpBVmR5UWZRK3ZDWkFrTjJwZ3BPTDNqTVlTTW1WWmw3YU1KNw0KeGhLbCtWYlVRSUxWZWRueDBPdi9TWUFJWDVJWmVXUDQ0RDBDZklscExKUjh1dEU3Z1NtcTU2dVorVGtRRld1OA0KYnZEQjMzbVpPMUdzOHJ5dFdVYmhqUjE3MHZPNHczR1pOQUk2VHl4K3NuZnE5bklUTkY4VjFPcGxMKzMrUWduQw0KU3JHRjFRek1jTUJnckQ3RGVGeElyN0l1UGZka1czd3dlZzFaL1VjZXVrM3hMaFM2Ly9kcHVOT3dGQjVrK0Nwcg0KdnNpWGFxSTl4cDFhZ0lJaXJOam4vbWlQMGo0Y00zUTFqcDFRZUk4K2h0NWIxaGRsYndwTzJmMit3MkFnWlU3Vg0KNzdjVUF5ZHRBb0dCQU4vV1AxSi9DenAxOG9DcFdudVU0Sk5oYlhpV09ETnYrcDRpbHpFZWh6M1Y1OUtZS3Y2dw0KaXJUSEZaZzR6WEZvNWhxcjY3Yk90SExsRk5SeFNmQWlzNGxFUm9FUWpuemdUeFl3ZFB6cm15dFB2eW8yWndISA0KWnB2Y2x5VU9YUlU5MzRDTnkyMkQwRHNON0Z5eUZEa0FBZ3hhTHY1ZXRETFpCS2ZjaGJ3WWhadDNBb0dCQU1aOQ0KV0szTVdwODQrMkp6VUVpc2ZaMjVPaGZCZVBRdTJ2VmZESklBV3lOMkx1RVpDVGZFSlV1YUdlWWMzcXBneVdZNw0KU1BLdXNVVGhkL0EybFEyU1VFWVlyMTFNYTg1ZVp0Wjd6bGkxS3gvWDhESlEvOTBEQWVNMXkveTFIREx4QkJ6RA0KRHJQNnpoMjRnSDBic0t2KytXdWFYdmc2ak1UMmxlN0k3a1ZuMjVYM0FvR0JBTlplak9TSU1ZdTZuQlltbXphUg0KL1lhSW4xd0dtaXFCQ3dkWjMyeTJiUldHZisvTHRobVhvQy9FSHVWYk9UNXRGVHJqL2VwbENxS2l6SkV0TW4rZA0KN0U0QktuaS9ENGQvRjU4THAyNzJnZlEzK3NBRnZCOFRCaTB0VlQrekF1aXVDaGhITkZWZEVSY05uWm1sSHdMOA0KQS9vcWdLa3lyK2VtdWZPOW1nTHJoVUcvQW9HQVo0YWRLQmo3OHl2L1Fid3ZWWmhSOUYvVnd1b3dNZnYyVENDRg0KSitsem42MVdpaVZLdjRGWWluUVhxZ1BJeDBpd0F0aE5GZmF6a3QrZWRQV2pERzhSMjEyb3FacmdaOFNwRXBtRQ0KYUpRbHAvZ3kyYXVScUM2WFgzQ0JzbzZyNUkzOHZSdW5vWXZnMFhPWDRLaitudGJnTkpWUmoxcktiVGpIdnNHaQ0KTlNVVVRNVUNnWUVBdnZrZFV5UkU0R3IrWDR3V0pOYmZYcEplRFBwSndHS2RrSWJmeXJzUEVSWUg5ejNrclZVNA0KalZrVUtZUjdGYXFzclRMdGFraWs1VXZ2T1V5dzVVYkoxZW54aHVZOWdYdklmMkZUTzhjUEJqUjhDN3RSSTRRQg0KYmRpaTV5THpGQXBaRi85cHptaW15QU5rckp6WldTMFg3MFFtU1V1OS8vMnluNU9vM3VKa2JIVT0NCi0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0t";

		if ($config['samba']['security'] == "share") {
			$config['samba']['security'] = "user";
			$config['samba']['maptoguest'] = "Bad User";
		}
		$config['version'] = "1.8";
	}

	/* XXX Convert 9.5 -> 9.7 (must skip 9.6) */

	write_config();

	if (is_booting())
		echo "done\n";

	return 0;
}

// hook for preinit
function config_preinit() {
	global $config, $g;

	$need_write_config = false;

/* remove after 9.1.0.1.294
	// temporary disable AIO
	if ($config['samba']['maxprotocol'] == "SMB2") {
		unset($config['samba']['aio']);
	}
*/

	if ($need_write_config) {
		echo "save config\n";
		write_config();
	}
}

// hook for postinit
function config_postinit() {
	global $config, $g;

	$need_write_config = false;

	if (isset($config['old_after072'])) {
		// remove database
		if (isset($config['upnp']['enable'])) {
			rc_stop_service("fuppes");
		}
		if (!empty($config['upnp']['home'])) {
			$file = $config['upnp']['home']."/fuppes.db";
			if (file_exists($file)) {
				echo "remove old $file\n";
				@unlink($file);
			}
		}
		if (isset($config['upnp']['enable'])) {
			rc_start_service("fuppes");
		}
		unset($config['old_after072']);
		$need_write_config = true;
	}

	if ($need_write_config) {
		echo "save config\n";
		write_config();
	}
}
?>