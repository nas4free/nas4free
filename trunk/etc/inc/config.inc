<?php
/*
	config.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (C) 2012 by NAS4Free Team <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (C) 2005-2011 by Olivier Cochard (olivier@freenas.org).
	All rights reserved.

	Portions of m0n0wall (http://m0n0.ch/wall).
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
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

		if (stristr($g['platform'], "livecd")) {
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
if (!$noparseconfig) {
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
				if (stristr($g['platform'], "liveCD")) {
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
		$filename_bk2 = preg_replace("/.xml$/", ".bk2", $filename);
		$filename_bk1 = preg_replace("/.xml$/", ".bk1", $filename);
		$filename_bk0 = preg_replace("/.xml$/", ".bk0", $filename);
		if (file_exists($filename_bk2) && unlink($filename_bk2) === FALSE) {
			die("Error: Failed to delete configuration file '{$filename_bk2}'");
		}
		if (file_exists($filename_bk1) && rename($filename_bk1, $filename_bk2) === FALSE) {
			die("Error: Failed to rename configuration file '{$filename_bk1}'");
		}
		if (file_exists($filename_bk0) && rename($filename_bk0, $filename_bk1) === FALSE) {
			die("Error: Failed to rename configuration file '{$filename_bk0}'");
		}
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
		echo "Converting configuration. ";

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

	write_config();

	if (is_booting())
		echo "done\n";

	return 0;
}

// hook for preinit
function config_preinit() {
	global $config, $g;

	$need_write_config = false;

	// temporary disable AIO
	if ($config['samba']['maxprotocol'] == "SMB2") {
		unset($config['samba']['aio']);
	}

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
