<?php
/*
	util.inc

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
require_once("array.inc");
require_once("system.inc");

// Set PATH, otherwise some commands will not be found during exec.
putenv("PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin");

/* kill a process by pid file */
function killbypid($pidfile) {
	sigkillbypid($pidfile, "TERM");
	unlink_if_exists($pidfile);
}

/* sigkill a process by pid file */
function sigkillbypid($pidfile, $sig) {
	if (file_exists($pidfile)) {
		mwexec("/bin/kill -s $sig `/bin/cat " . $pidfile . "`");
	}
}

/* kill a process by name */
function killbyname($procname) {
	return mwexec("/usr/bin/killall " . escapeshellarg($procname));
}

/* Force kill a process by name */
function forcekillbyname($procname) {
	return mwexec("/usr/bin/killall -9 " . escapeshellarg($procname));
}

/* return the subnet address given a host address and a subnet bit count */
function gen_subnet($ipaddr, $bits) {
	if (!is_ipaddr($ipaddr) || !is_numeric($bits))
		return "";

	if (is_ipv4addr($ipaddr))
		return long2ip(ip2long($ipaddr) & gen_subnet_mask_long($bits));
	// STILL NOT READY FOR IPv6
	else if (is_ipv6addr($ipaddr))
		return long2ip6(ip2long6($ipaddr) & gen_subnet_mask_long6($bits));

}

function ip2long6($ip)   {
    if (substr_count($ip, '::'))     {
        $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip) ;
    }

    $ip = explode(':', $ip) ;

    $r_ip = '' ;
    foreach ($ip as $v)    {
        $r_ip .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT) ;
    }

    return base_convert($r_ip, 2, 10) ;
}

/* Convert a long to an IPv6 */
/* NEED TO FINISH THIS FUNCTION */
/* This function bug for IPv6 bigger than ::1f:ffff:ffff:ffff */
function long2ip6($long) {
    // Valid range: ::0000 -> ffff:ffff:ffff:ffff:ffff:fffff:fffff:fffff
    if ($long < 0 || $long > 340282366920938463463374607431768211456) return false;
	$ipv6 = "";
    for ($i=7;$i>=0;$i--) {
        $ipv6 .= base_convert((int)($long / pow(65536,$i)),10,16);
        $long -= (int)($long / pow(65536,$i))*pow(65536,$i);
        if ($i>0) $ipv6 .= ":";
    }
    return $ipv6;

}

/* return the highest (broadcast) address in the subnet given a host address and a subnet bit count */
function gen_subnet_max($ipaddr, $bits) {
	if (!is_ipaddr($ipaddr) || !is_numeric($bits))
		return "";

	if (is_ipv4addr($ipaddr))
		return long2ip(ip2long($ipaddr) | ~gen_subnet_mask_long($bits));
	// STILL NOT READY FOR IPv6
	else if (is_ipv6addr($ipaddr))
		return long2ip6(ip2long6($ipaddr) | ~gen_subnet_mask_long6($bits));

}

/* returns a IPv4 subnet mask (long given a bit count) */
/* Warning: Bitwise operator depend of the OS architecture */
/* No problem using IPv4 32 bit on 32bit OS... but with 64bit OS, there might be a bug here */
/* see: http://www.php.net/manual/en/language.operators.bitwise.php */
function gen_subnet_mask_long($bits) {
	$sm = 0;
	for ($i = 0; $i < $bits; $i++) {
		$sm >>= 1;
		$sm |= 0x80000000;
	}
	return $sm;
}

/* returns a IPv6 subnet mask (long given a bit count) */
/* NEED TO BE ADAPTED FOR IPv6 !!!!! */
/* WARNING: Bitwise operator depend of the OS architecture */
/* It's not possile to use bitwise on number greater than 32 bit on 32bit OS*/
/* Must found a solution for working with 128bits..... */
/* see: http://www.php.net/manual/en/language.operators.bitwise.php */
function gen_subnet_mask_long6($bits) {
	$sm = 0;
	for ($i = 0; $i < $bits; $i++) {
		$sm >>= 1;
		$sm |= 0x80000000000000000000000000000000;
	}
	return $sm;
}

/* same as above for IPv4 but returns a string */
function gen_subnet_mask($bits) {
	return long2ip(gen_subnet_mask_long($bits));
}
/* same as above for IPv6 but returns a string */
function gen_subnet_mask6($bits) {
	return long2ip6(gen_subnet_mask_long6($bits));
}

function is_numericint($arg) {
	return (preg_match("/[^0-9]/", $arg) ? false : true);
}

/* returns true if $ipaddr is a valid IPv4 OR IPv6 address */
function is_ipaddr($ipaddr) {

	if  (filter_var($ipaddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$result = true ;
	} else if (filter_var($ipaddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
		$result = true ;
	} else {
		$result = false ;
	}

	return $result;
}

/* returns true if $ipv4addr is a valid dotted IPv4 address */
function is_ipv4addr($ipv4addr) {
	return filter_var($ipv4addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

/* returns true if $ipv6addr is a valid dotted IPv6 address */
function is_ipv6addr($ipv6addr) {
	return filter_var($ipv6addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

/* returns true if $ipaddr is a valid dotted IPv4/IPv6 address or any alias */
function is_ipaddroranyalias($ipaddr) {
	global $aliastable;

	if (isset($aliastable[$ipaddr])) {
		return true;
	} else {
		return is_ipaddr($ipaddr);
	}
}

/* returns true if $subnet is a valid subnet in IPv4/IPv6 CIDR format */
function is_subnet($subnet) {
	if (!is_string($subnet)) {
		$result = false ;
	}

	list($hp,$np) = explode('/', $subnet);

	if (!is_ipaddr($hp)) {
		$result = false ;
	}

	// If it's an IPv4 address, but with a bitcount outise 1-32 range, there is a problem
	if (is_ipv4addr($hp) && (!is_numeric($np) || ($np < 1) || ($np > 32))) {
			$result = false ;
	// If it's an IPv6 address, but with a bitcount outise 1-64 range, there is a problem
	} else if (is_ipv6addr($hp) && (!is_numeric($np) || ($np < 1) || ($np > 128))) {
		$result = false ;
	} else {
		$result = true ;
	}

	return $result;
}

/* returns true if $subnet is a valid subnet in IPv4/IPv6 CIDR format or an alias thereof */
function is_subnetoralias($subnet) {
	global $aliastable;

	if (isset($aliastable[$subnet]) && is_subnet($aliastable[$subnet]))
		return true;

	return is_subnet($subnet);
}

/* returns true if $hostname is a valid hostname */
function is_hostname($hostname) {
	if (!is_string($hostname))
		return false;

	if (preg_match("/^[a-z0-9\-]+$/i", $hostname))
		return true;

	return false;
}

/* returns true if $desc is a valid description (alphanum and space, _ , - , .)*/
function is_validdesc($desc) {
	if (!is_string($desc))
		return false;

	if (preg_match("/^[A-Za-z0-9]([A-Za-z0-9_\-\.\s]*[A-Za-z0-9])*$/", $desc))
		return true;

	return false;
}

/* returns true if $login is a valid login name (alphanum,dot, _ , -)*/
function is_validlogin($login) {
	if (!is_string($login))
		return false;

	if (preg_match("/[^a-zA-Z0-9\.\-_]/", $login))
		return false;

	return true;
}

/* returns true if $password is a valid password (not used of special : character)*/
function is_validpassword($password) {
	if (!is_string($password))
		return false;

	if (strstr($password, ':'))
			return false;

	return true;
}

/* returns true if $sharename is a valid share name (alphanum, dot, _ , -)*/
function is_validsharename($sharename) {
	if (!is_string($sharename))
		return false;

	if (preg_match("/^[A-Za-z0-9]([A-Za-z0-9_\-\.\s]*[A-Za-z0-9])*$/", $sharename))
		return true;

	return false;
}

/* Returns true if $domain is a valid domain name */
function is_domain($domain) {
	if (!is_string($domain))
		return false;

	if (preg_match("/^([a-z0-9_\-]+\.?)*$/i", $domain))
		return true;

	return false;
}

/* Returns true if $string is a valid NetBIOS name */
function is_netbios($string) {
	if (15 < strlen($string))
		return false;

	// See reference http://support.microsoft.com/kb/909264
	// Update reference http://support.microsoft.com/kb/188997/en-us
	// The following characters are not allowed: \ / : * ? " < > |
	if (preg_match("/^[^\.]([^\\/:*\?\"<>|])*$/i", $string))
		return true;

	return false;
}

/* Returns true if $workgroup is a valid workgroup name */
/* A workgroup name can contain up to 15 characters, including letters, numbers, */
/* and the following characters: ! @ # $ % ^ & ( ) _ - ; : ' " , . */
/* It cannot contain any spaces, and must begin with a letter or number. */
function is_workgroup($workgroup) {
	if (!is_string($workgroup))
		return false;

	if (preg_match("/^[\w\d]+[\w\d\!\@\#\$\%\^\&\(\)\_\-\;\:\'\"\,\.]*$/", $workgroup))
		return true;

	return false;
}

/* returns true if $macaddr is a valid MAC address */
function is_macaddr($macaddr) {
	if (!is_string($macaddr))
		return false;

	$maca = explode(":", $macaddr);
	if (count($maca) != 6)
		return false;

	foreach ($maca as $macel) {
		if (($macel === "") || (strlen($macel) > 2))
			return false;
		if (preg_match("/[^0-9a-f]/i", $macel))
			return false;
	}

	return true;
}

/* returns true if $name is a valid name for an alias */
function is_validaliasname($name) {
	if (!is_string($name))
		return false;

	if (!preg_match("/^[a-zA-Z0-9]*$/", $name))
		return false;

	return true;
}

/* returns true if $port is a valid TCP/UDP port */
function is_port($port) {
	if (!is_numericint($port))
		return false;

	if (($port < 1) || ($port > 65535))
		return false;

	return true;
}

/* returns true if $mtu is a valid MTU size */
function is_mtu($mtu) {
	if (!is_numericint($mtu))
		return false;

	if (($mtu < 100) || ($mtu > 9000))
		return false;

	return true;
}

/**
 * Validate the given file mode mask. Valid masks are in the
 * range from [0]000 to [0]777.
 * @param mode The file mode mask as octal value to validate
 * @return Returns true if mode is a valid file mode mask.
 */
function is_filemode($mode) {
	if (!is_numericint($mode))
		return false;

	if (!preg_match("/^[0]?[0-7]{3}$/", $mode))
		return false;

	return true;
}

// Returns true if $certificate is a valid certificate.
function is_valid_certificate($certificate) {
	if (preg_match('/^(-----BEGIN CERTIFICATE-----).*(-----END CERTIFICATE-----)(\r|\n|\r\n)?\z/s', $certificate))
		return true;
	return false;
}

// Returns true if $privatekey is a valid private key.
function is_valid_privatekey($privatekey, $type = "RSA") {
	switch ($type) {
		default:
		case 'RSA':
			if (preg_match('/^(-----BEGIN RSA PRIVATE KEY-----).*(-----END RSA PRIVATE KEY-----)(\r|\n|\r\n)?\z/s', $privatekey))
				return true;
			break;
		case 'DSA':
			if (preg_match('/^(-----BEGIN DSA PRIVATE KEY-----).*(-----END DSA PRIVATE KEY-----)(\r|\n|\r\n)?\z/s', $privatekey))
				return true;
			break;
	}
	return false;
}

function get_interface_list() {
	/* build interface list with netstat */
	exec("/usr/bin/netstat -inW -f link", $linkinfo);
	array_shift($linkinfo);

	$iflist = array();

	foreach ($linkinfo as $link) {
		$alink = preg_split("/\s+/", $link);
		$ifname = chop($alink[0]);

		if (substr($ifname, -1) === "*")
			$ifname = substr($ifname, 0, strlen($ifname) - 1);
		/* add the plip interface to be excluded too */
		if (!preg_match("/^(ppp|sl|gif|faith|lo|vlan|tun|plip|ipfw|usbus|carp)/", $ifname)) {
			$iflist[$ifname] = array();

			$iflist[$ifname]['mac'] = chop($alink[3]);
			$iflist[$ifname]['up'] = false;

			/* find out if the link on this interface is up */
			unset($ifinfo);
			exec("/sbin/ifconfig {$ifname}", $ifinfo);

			foreach ($ifinfo as $ifil) {
				if (preg_match("/status: (.*)$/", $ifil, $matches)) {
					if ($matches[1] === "active")
						$iflist[$ifname]['up'] = true;
					break;
				}
			}
		}
	}

	return $iflist;
}

// Get list of mounted disk
// example: Array
//	[0] => Array
//	    (
//        [mp] => /mnt/sharename
//        [mdisk] => ad0s1
//        [devicespecialfile] => /dev/ad0s1
//        [sharename] => sharename
//        [fsid] => 090db747f2d532ed
//      )
function get_mounts_list()
{
	global $g;

	$amountlist = array();
	$i = 0;

	// Get list of mount points.
	exec("/sbin/mount -v", $rawdata);

	foreach ($rawdata as $line) {
		// e.g.: /dev/ad8p1 on /mnt/xyz (ufs, local, soft-updates, acls, fsid 66ad394809fce81b)
		if (0 == preg_match("/^(\S+) on (.+) \(.+fsid (\S+).+$/", $line, $aline))
			continue;

		// Get device path
		$devicespecialfile = chop($aline[1]);

		// Get the mount path
		$mountpath = chop($aline[2]);

		// Ignore various mountpoints
		if (("/" === $mountpath) || ("devfs" === $devicespecialfile) || ("/var" === $mountpath) ||
				("/dev/fd0" === $devicespecialfile) || ($g['cf_path'] === $mountpath))
			continue;

		// Get the complete name /dev/devicename
		$adevicespecialfile = explode("/", $devicespecialfile);
		if (empty($adevicespecialfile[2])) {
                       $devicename = $adevicespecialfile[1];
                       #continue;
               }  else {
                       $devicename = $adevicespecialfile[2];
               }

		// Test if it's a gvinum, gmirror, gconcat or gstripe device
		if ((strcmp($devicename,"gvinum") == 0) ||
				(strcmp($devicename,"mirror") == 0) || (strcmp($devicename,"concat") == 0) ||
				(strcmp($devicename,"stripe") == 0) || (strcmp($devicename,"raid5") == 0)) {
			$devicename = $adevicespecialfile[3];
		}

		// Get the sharename
		$amountpath = explode("/", $mountpath);

		$amountlist[$i]['mp'] = $mountpath;
		$amountlist[$i]['mdisk'] = $devicename;
		$amountlist[$i]['devicespecialfile'] = $devicespecialfile;
		$amountlist[$i]['sharename'] = end($amountpath);
		$amountlist[$i]['fsid'] = chop($aline[3]);

		$i++;
	}

	return $amountlist;
}

// Retrieve the fsid from mount point.
// $devicespecialfile - The device special name, e.g. /dev/ad6p1
// $mp - Name of the mount point, e.g. /mnt/1
// Return fsid or empty string.
function get_mount_fsid($devicespecialfile, $mp) {
	exec("/sbin/mount -v", $rawdata);
	if (empty($rawdata))
		return NULL;

	foreach ($rawdata as $mountinfo) {
		// Parse string: /dev/ad6p1 on /mnt/1 (ufs, local, soft-updates, acls, fsid 090db747f2d532ed)
		if (0 == preg_match("/(\S+) on (\S+).+fsid (\S+).+/", $mountinfo, $info))
			continue;
		if (($info[1] === $devicespecialfile) && ($info[2] === $mp))
			return $info[3];
	}

	return NULL;
}

/* Return list of ALL software volume: gvinum, gmirror */
function get_sraid_disks_list()
{
	$disklist = array_merge((array)get_gvinum_disks_list(),(array)get_gmirror_disks_list(),(array)get_gconcat_disks_list(),(array)get_gstripe_disks_list(),(array)get_graid5_disks_list());
	return $disklist;
}

// Get list of software gvinum RAID disks.
//[volumename] => Array
//    (
//        [type] => gvinum
//        [state] => UP
//        [size] => 6149 MB
//        [name] => VolumeName
//        [devicespecialfile] => /dev/gvinum/VolumeName
//        [desc] => Software RAID
//    )
function get_gvinum_disks_list()
{
	$disklist = array();

	/* Send the shell cmd and get the result */
	exec("/sbin/gvinum list",$rawdata);

	foreach ($rawdata as $line) {
		/* Split the line using the space as separator */
		$aline = preg_split("/\s+/", $line);

		/* Get the line that begin with the letter  'V' */
		if ($aline[0] != "V")
			continue ;

		$diskname = chop($aline[1]);

		$disklist[$diskname] = array();
		$disklist[$diskname]['name'] = $diskname;
		$disklist[$diskname]['devicespecialfile'] = "/dev/gvinum/" . $diskname;
		$disklist[$diskname]['type'] = "gvinum";
		$disklist[$diskname]['desc'] = "Software RAID";
		$disklist[$diskname]['state'] = $aline[3];

		/* Get diskinfo to get disk size */
		$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
		$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";
	}

	return $disklist;
}

// Get list of software gmirror RAID disks.
//[volumename] => Array
//    (
//        [type] => gmirror
//        [name] => volumename
//        [state] => COMPLETE
//        [size] => 6149M
//        [devicespecialfile] => /dev/mirror/volumename
//        [desc] => Software RAID
//    )
function get_gmirror_disks_list()
{
	$disklist = array();

	/* Display all configured gmirror volume */
	exec("/sbin/gmirror list",$rawdata);

	$foundstate = $foundname = 0 ;

	foreach ($rawdata as $line) {
		/* Use space for break the line */
		$aline = preg_split("/\s+/", $line);

		// First Step: Getting the array name
		// look for this output:
		// Geom name: xxx
		if ( (strcmp($aline[0],"Geom") == 0) && (strcmp($aline[1],"name:") == 0) ) {
			$diskname = $aline[2];

			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/mirror/" . $diskname;
			$disklist[$diskname]['type'] = "gmirror";
			$disklist[$diskname]['desc'] = "Software RAID";
			$disklist[$diskname]['state'] = gettext("Unknown");

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			$foundname = 1 ;
			continue ;
		}

		// Second Step: Getting the array status
		// look for this output:
		// State: COMPLETE
		if ( (strcmp($aline[0],"State:") == 0) && $foundname) {
			$disklist[$diskname]['state'] = $aline[1];

			// check next
			$foundname = 0 ;
			$foundstate = 1 ;
			continue ;
		}

		// look for this output:
		// Balance: round-robin
		if ( (strcmp($aline[0],"Balance:") == 0) && $foundstate) {
			$disklist[$diskname]['balance'] = $aline[1];

			// init the check variable for the next RAID volume
			$foundstate = 0 ;
			continue ;
		}
	}

	return $disklist;
}

// Get list of software gconcat RAID disks.
//[volumename] => Array
//    (
//        [type] => gconcat
//        [state] => COMPLETE
//        [size] => 6149M
//        [name] => concat1
//        [devicespecialfile] => /dev/concat/concat1
//        [desc] => Software RAID
//    )
function get_gconcat_disks_list()
{
	$disklist = array();

	/* Display all configured gconcat volume */
	exec("/sbin/gconcat list",$rawdata);

	$foundname = 0 ;

	foreach ($rawdata as $line) {
		/* Use space for break the line */
		$aline = preg_split("/\s+/", $line);

		// First Step: Getting the array name
		// look for this output:
		// Geom name: xxx
		if ( (strcmp($aline[0],"Geom") == 0) && (strcmp($aline[1],"name:") == 0) ) {
			$diskname = $aline[2];

			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/concat/" . $diskname;
			$disklist[$diskname]['type'] = "gconcat";
			$disklist[$diskname]['desc'] = "Software RAID";
			$disklist[$diskname]['state'] = gettext("Unknown");

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			$foundname = 1 ;
			continue ;
		}

		// Second Step: Getting the array status
		// look for this output:
		// State: UP
		if ( (strcmp($aline[0],"State:") == 0) && $foundname) {
			$disklist[$diskname]['state'] = $aline[1];

			// init the check variable for the next RAID volume
			$foundname = 0 ;
			continue ;
		}
	}

	return $disklist;
}

// Get list of software gstripe RAID disks.
//[volumename] => Array
//    (
//        [type] => gstripe
//        [state] => COMPLETE
//        [size] => 6149M
//        [name] => raid0
//        [devicespecialfile] => /dev/stripe/raid0
//        [desc] => Software RAID
//    )
function get_gstripe_disks_list()
{
	$disklist = array();

	/* Display all configured gstripe volume */
	exec("/sbin/gstripe list",$rawdata);

	$foundname = 0 ;

	foreach ($rawdata as $line) {
		/* Use space for break the line */
		$aline = preg_split("/\s+/", $line);

		// First Step: Getting the array name
		// look for this output:
		// Geom name: xxx
		if ( (strcmp($aline[0],"Geom") == 0) && (strcmp($aline[1],"name:") == 0) ) {
			$diskname = $aline[2];

			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/stripe/" . $diskname;
			$disklist[$diskname]['type'] = "gstripe";
			$disklist[$diskname]['desc'] = "Software RAID";
			$disklist[$diskname]['state'] = gettext("Unknown");

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			$foundname = 1 ;
			continue ;
		}

		// Second Step: Getting the array status
		// look for this output:
		// State: UP
		if ( (strcmp($aline[0],"State:") == 0) && $foundname) {
			$disklist[$diskname]['state'] = $aline[1];

			// init the check variable for the next RAID volume
			$foundname = 0 ;
			continue ;
		}
	}

	return $disklist;
}

// Get list of software graid5 RAID disks.
//[volumename] => Array
//    (
//        [type] => graid5
//        [state] => COMPLETE
//        [size] => 6149M
//        [name] => BIG1
//        [devicespecialfile] => /dev/raid5/BIG1
//        [desc] => Software RAID
//    )
function get_graid5_disks_list()
{
	$disklist = array();

	/* Display all configured graid5 volume */
	exec("/sbin/graid5 list",$rawdata);

	$foundname = 0 ;

	foreach ($rawdata as $line) {
		/* Use space for break the line */
		$aline = preg_split("/\s+/", $line);

		// First Step: Getting the array name
		// look for this output:
		// Geom name: xxx
		if ( (strcmp($aline[0],"Geom") == 0) && (strcmp($aline[1],"name:") == 0) ) {
			$diskname = $aline[2];

			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/raid5/" . $diskname;
			$disklist[$diskname]['type'] = "graid5";
			$disklist[$diskname]['desc'] = "Software RAID";
			$disklist[$diskname]['state'] = gettext("Unknown");

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			$foundname = 1 ;
			continue ;
		}

		// Second Step: Getting the array status
		// look for this output:
		// State: COMPLETE
		if ( (strcmp($aline[0],"State:") == 0) && $foundname) {
			$disklist[$diskname]['state'] = $aline[1];

			// init the check variable for the next RAID volume
			$foundname = 0 ;
			continue ;
		}
	}

	return $disklist;
}

// Get list of HAST disks.
//[volumename] => Array
//    (
//        [type] => HAST
//        [name] => volumename
//        [role] => primary
//        [status] => complete
//        [size] => 6149MB
//        [devicespecialfile] => /dev/hast/volumename
//        [localpath] => /dev/da1
//        [desc] => HAST device
//    )
function get_hast_disks_list()
{
	$disklist = array();

	/* Send the shell cmd and get the result */
	exec("/sbin/hastctl list",$rawdata);

	foreach ($rawdata as $line) {
		if (preg_match("/^(\S+):$/",$line,$match)) {
			$diskname = trim($match[1]);
			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/hast/" . $diskname;
			$disklist[$diskname]['type'] = "HAST";
			$disklist[$diskname]['desc'] = "HAST device";
			$disklist[$diskname]['role'] = gettext("Unknown");
			$disklist[$diskname]['status'] = gettext("Unknown");
			$disklist[$diskname]['localpath'] = '';
		} else if (preg_match("/^\s+role:\s+(.+)$/",$line,$match)) {
			$role = trim($match[1]);
			$disklist[$diskname]['role'] = $role;
			if ($role == 'primary') {
				$diskinfo = disks_get_diskinfo($disklist[$diskname]['devicespecialfile']);
				$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";
			} else {
				$disklist[$diskname]['size'] = 'N/A';
			}
		} else if (preg_match("/^\s+localpath:\s+(.+)$/",$line,$match)) {
			$localpath = trim($match[1]);
			$disklist[$diskname]['localpath'] = $localpath;
		} else if (preg_match("/^\s+status:\s+(.+)$/",$line,$match)) {
			$state = trim($match[1]);
			$disklist[$diskname]['status'] = $state;
		}
	}

	return $disklist;
}

// Parse infos from camcontrol
function parse_camcontrol_info($devtype){
	$devlist = array();
	$device = '';
	$controller = '';
	$controller_id = ''; 
	$target_id = '';
	$lun_id = '';

	mwexec2("/sbin/camcontrol devlist -v 2> /dev/null", $rawdata);

	foreach($rawdata as $line) {
		// Get controller
		if( preg_match("/(?<= on )[-A-z0-9]*(?= bus )+/", $line, $matches)){
			$controller = preg_split( '/([0-9]+\z)/i', $matches[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$controller_desc = '';
			$controller_id = ''; 
			$target_id = '';
			$lun_id = '';
			$device = '';
		}
		else if ( !strncmp($line, '<>' ,2) == 0){
			// Get target id
			if ( preg_match("/(?<= target )[0-9]*(?= lun )+/",$line, $matches )){
				$target_id = $matches[0];
			}

			// Get lun id
			if ( preg_match("/(?<= lun )[0-9]*/",$line, $matches )){
				$lun_id = $matches[0];
			}

			// Get device name
			// Parse substring between parentheses: (pass0,da0) or (da0,pass0)
			preg_match("/.*\(([^>]*)\).*/",$line,$match);
			$temp = preg_split("/,/", $match[1]);

			// Check whether device name is at the first (da0,pass0) or the second (pass0,da0) arguement
			$device = preg_split( '/([-A-z]+)/', $temp[1], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			if (strncmp($device[0],'pass',4) == 0){
				$device = preg_split( '/([-A-z]+)/', $temp[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			}
			// if there is no other device name, use pass[0-9]
			// example : devices on 'mfi' will show :- <ATA WDC WD40EFRX-68W 0A80> at scbus6 target 1 lun 0 (pass5)
			else if( empty($device) && count($temp) == 1 && strncmp($temp[0],'pass',4) == 0){
				$device = preg_split( '/([-A-z]+)/', $temp[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			}

			// legacy compatibility: for scsi option, do not include ata drives, for ata option, do not include scsi drives
			if( (($devtype!="scsi" & $controller[0] == $devtype) | ($devtype=="scsi" & $controller[0]!="ata") ) && $controller[0] != "mfi" ){
				// Ignore CD-ROMs and SCSI enclosure devices (ses)
				if($device[0] != "" & !preg_match("/^(cd|acd|ses)/", $device[0] )){
					// Add device into devlice list array
					$devlist["$device[0]$device[1]"]['device'] = $device[0];
					$devlist["$device[0]$device[1]"]['id'] = $device[1];
					$devlist["$device[0]$device[1]"]['name']  = $device[0].$device[1];
					$devlist["$device[0]$device[1]"]['devicespecialfile'] = '/dev/'.$device[0].$device[1];

					init_device_attributes($devlist["$device[0]$device[1]"]);
					
					// Get diskinfo to get disk size
					$diskinfo = disks_get_diskinfo($devlist["$device[0]$device[1]"]['devicespecialfile']);
					$devlist["$device[0]$device[1]"]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

					$devlist["$device[0]$device[1]"]['controller'] = $controller[0];
					$devlist["$device[0]$device[1]"]['controller_id'] = $controller[1];
					$devlist["$device[0]$device[1]"]['target_id'] = $target_id;
					$devlist["$device[0]$device[1]"]['lun_id'] = $lun_id;

					// for backward compatibility
					switch($controller[0]){
					case 'ata':
							$devlist["$device[0]$device[1]"]['type'] = "IDE";
							break;
					case 'iscsi':
							$devlist["$device[0]$device[1]"]['type'] = "iSCSI";
							break;
					// scsi-controller list from /etc/devd.conf		
					case 'aac' || 'adv' || 'adw' || 'aha' || 'ahb' || 'ahc' || 'ahd' || 'aic' || 'amd' || 'amr' || 'asr' || 'bt' || 'ciss' || 'ct' || 'dpt' || 'esp' || 'ida' || 'iir' || 'ips' || 'isp'|| 'mlx' || 'mly' || 'mpt' || 'ncr' ||'ncv' || 'nsp' || 'stg' || 'sym' || 'trm' || 'wds':
					case "mpt": // LSI Fusion-MPT SCSI/Fibre Channel driver
							$devlist["$device[0]$device[1]"]['type'] = "SCSI";
							break;
					}
				}
			}
			// display both SCSI and IDE cdroms
			elseif( $devtype == "cdrom"){
				if($device[0] != "" & preg_match("/^(cd|acd)/", $device[0] )){
					// Add device into devlice list array
					$devlist["$device[0]$device[1]"]['device'] = $device[0];
					$devlist["$device[0]$device[1]"]['id'] = $device[1];
					$devlist["$device[0]$device[1]"]['name']  = $device[0].$device[1];
					$devlist["$device[0]$device[1]"]['devicespecialfile'] = '/dev/'.$device[0].$device[1];

					init_device_attributes($devlist["$device[0]$device[1]"]);

					// Get diskinfo to get disk size
					$diskinfo = disks_get_diskinfo($devlist["$device[0]$device[1]"]['devicespecialfile']);
					$devlist["$device[0]$device[1]"]['size'] = $diskinfo['mediasize_mbytes'] . "MB";					

					$devlist["$device[0]$device[1]"]['controller'] = $controller[0];
					$devlist["$device[0]$device[1]"]['controller_id'] = $controller[1];
					$devlist["$device[0]$device[1]"]['target_id'] = $target_id;
					$devlist["$device[0]$device[1]"]['lun_id'] = $lun_id;

					// for backward compatibility
					switch($controller[0]){
					case 'ata':
							$devlist["$device[0]$device[1]"]['type'] = "IDE";
							break;
					case 'iscsi':
							$devlist["$device[0]$device[1]"]['type'] = "iSCSI";
							break;
					// scsi-controller list from /etc/devd.conf
					case 'aac' || 'adv' || 'adw' || 'aha' || 'ahb' || 'ahc' || 'ahd' || 'aic' || 'amd' || 'amr' || 'asr' || 'bt' || 'ciss' || 'ct' || 'dpt' || 'esp' || 'ida' || 'iir' || 'ips' || 'isp'|| 'mlx' || 'mly' || 'mpt' || 'ncr' ||'ncv' || 'nsp' || 'stg' || 'sym' || 'trm' || 'wds':
					case "mpt": // LSI Fusion-MPT SCSI/Fibre Channel driver
					case "twa": // 3ware
						$devlist["$device[0]$device[1]"]['type'] = "SCSI";
						break;
					}
				}
			}
		}
	}
	return $devlist;
}

// Parse controller dependent info
function parse_controller_config(&$devlist){
	global $config_disks;

	foreach($devlist as &$device){
		$devtype = '';
		$device['desc'] = $config_disks[$device['name']]['desc'];
		switch( $device['controller']){
		// Experimental : requires arcmsr to add enclosure support
		// Areca SATA[/SAS] RAID controller
		// SMART Device type format : areca,N/E (N =  channel (slot), E = enclosure)
		// smartctl -a -d areca,9/2 /dev/arcmsr0
			case "arcmsr":
				$device['controller_desc'] = parse_raid_controller_desc($device['controller'].$device['controller_id']);
				$device['smart']['devicefilepath'] = "/dev/".$device['controller'].$device['controller_id'];
				$device['smart']['devicetype'] = 'areca';
				$device['smart']['devicetypearg'] = 'areca,'.($device['target_id']+1);
				break;
			case 'ahcich':
			case 'ata':
				$device['model'] = parse_device_desc($device['name']);
				$device['controller_desc'] = parse_ata_ahci_controller_desc($device['controller'].$device['controller_id']);

				mwexec2("smartctl -d test "."/dev/".$device['name']." | grep \" opened\" | xargs -0 -J {} expr {} : \".*Device of type '\(.*\)' \[.*\] opened\"", $devtype);
				$device['smart']['devicefilepath'] = '/dev/'.$device['name'];
				$device['smart']['devicetype'] = $devtype[0];
				$device['smart']['devicetypearg'] = $devtype[0];
				break;
			// HP Smart Array controller
			// SMART Device type format : cciss,N (N = which disk on the controller is monitored)
			// e.g. smartctl -d cciss,1 -a /dev/ciss0
			case 'ciss':
				$device['controller_desc'] = parse_raid_controller_desc($device['controller'].$device['controller_id']);
				$device['smart']['devicefilepath'] = '/dev/'.$device['controller'].$device['controller_id'];
				$device['smart']['devicetype'] = 'cciss';
				$device['smart']['devicetypearg'] = "cciss,".$device['target_id'];
				break;
			case 'hptiop': // HighPoint RocketRAID 3xxx/4xxx device driver
			case 'hptmv': // HighPoint RocketRAID 182x device driver
			case 'hptrr': // HighPoint RocketRAID device driver
			case 'hpt27xx': // Highpoint RocketRAID 27xx controller
				$controller_id = $device['controller_id'] + 1;
				$disk_id = $device['target_id']+1;

				$device['controller_desc'] = parse_raid_controller_desc($device['controller'].$device['controller_id']);
				$device['smart']['devicefilepath'] = '/dev/'.$device['controller'];
				$device['smart']['devicetype'] = $device['controller'];
				$device['smart']['devicetypearg'] = "hpt,".$controller_id."/".$disk_id;
				break;
			// LSI MegaRAID SAS
			case 'mfi':
				$mfi_device = map_mfi_device_name($device['controller'].$device['controller_id'],$device['name']);

				$device['target_id'] = $mfi_device['target_id'];
				$device['model'] = parse_device_desc($mfi_device['device_name']);
				$device['controller_desc'] = parse_raid_controller_desc($device['controller'].$device['controller_id']);

				$device['smart']['devicefilepath'] = '/dev/'.$mfi_device['device_name'];
				$device['smart']['devicetype'] = 'sat';
				$device['smart']['devicetypearg'] = 'sat';
				break;
			// LSI 2008 (IT mode)
			case 'mps':
			case 'mpt':
				$device['model'] = parse_device_desc($device['name']);
				$device['controller_desc'] = parse_device_desc($device['controller'].$device['controller_id']);

				mwexec2('smartctl -d test /dev/'.$device['name']." | grep \" opened\" | xargs -0 -J {} expr {} : \".*Device of type '\(.*\)' \[.*\] opened\"", $devtype);
				$device['smart']['devicefilepath'] = '/dev/'.$device['name'];
				$device['smart']['devicetype'] = $devtype[0];
				$device['smart']['devicetypearg'] = $devtype[0];
				break;
			case 'twa': // 3ware 9000/9500/9550/9650 series SATA RAID controller
			case 'twe': // 3ware 5000/6000/7000/8000 series PATA/SATA RAID adapter
				$device['controller_desc'] = parse_raid_controller_desc($device['controller'].$device['controller_id']);
				$device['smart']['devicefilepath'] = '/dev/'.$device['controller'].$device['controller_id'];
				$device['smart']['devicetype'] = '3ware';

				$tw_line = array();
				$port = 0;
				$port_num = array();
				mwexec2('/usr/local/sbin/tw_cli /c'.$device['controller_id'].'/u'.$device['target_id'].' show | grep -E "^u"', $tw_line);
				if( preg_match("/(?<=p)[\d]+/", $tw_line[0], $port_num) ){
					$port = $port_num[0];
				}

				$device['smart']['devicetypearg'] = '3ware,'.$port;
				break;
			// USB Mass Storage
			case 'umass-sim':
				$device['model'] = parse_device_desc($device['name']);
				$device['controller_desc'] = parse_umass_controller_desc($device['controller_id']);
				$device['smart']['devicefilepath'] = '/dev/'.$device['name'];
				break;
			// Auto detect devices to cater for SATA disks pass through via HBA (non-RAID) (such as LSI SAS2008 RAID controller (e.g. IBM ServeRAID M1015) flashed to LSI9211-IT firmware)
			default:
				$device['model'] = parse_device_desc($device['name']);
				$device['controller_desc'] = parse_device_desc($device['controller'].$device['controller_id']);

				mwexec2('smartctl -d test /dev/'.$device['name']." | grep \" opened\" | xargs -0 -J {} expr {} : \".*Device of type '\(.*\)' \[.*\] opened\"", $devtype);
				$device['smart']['devicefilepath'] = '/dev/'.$device['name'];
				$device['smart']['devicetype'] = $devtype[0];
				$device['smart']['devicetypearg'] = $devtype[0];
				break;
		}

		// Parse info from S.M.A.R.T.
		$smartctl_info = get_smartctl_info($device);

		if(!empty($smartctl_info['desc'])) $device['desc'] = $smartctl_info['desc'];
		if(!empty($smartctl_info['model'])) $device['model'] = $smartctl_info['model'];
		if(!empty($smartctl_info['serial'])) $device['serial'] = $smartctl_info['serial'];
		if(!empty($smartctl_info['rotation_rate'])) $device['rotation_rate'] = $smartctl_info['rotation_rate'];
		if(!empty($smartctl_info['transfer_rate'])) $device['transfer_rate'] = $smartctl_info['transfer_rate'];

		// if S.M.A.R.T. is not available, get transfer speed from dmesg
		if(empty($smartctl_info['transfer_rate'])){
			mwexec2("dmesg | grep \"".$device['name'].": \"", $lines);

			foreach($lines as $line){
				$matches = preg_split("/[\s\:]+/",$line);

				if( strcmp($matches[0], $device['name']) == 0 && strcmp($matches[2],'transfers') == 0){
					$device['transfer_rate'] = $matches[1];
				}
			}
		}

		if(!empty($smartctl_info['smart_support'])) {
			$device['smart']['smart_support'] = $smartctl_info['smart_support'];
		}else{
			$device['smart']['smart_support'] = "Unavailable";
		}

		// retry empty case
		if(empty($device['model'])) $device['model'] = system_get_volume_model($device['devicespecialfile']);
		if(empty($device['desc'])) $device['desc'] = $device['model'];
		if(empty($device['serial'])) $device['serial'] = system_get_volume_serial($device['devicespecialfile']);
	}
	return $devlist;
}


function map_mfi_device_name($controller, $mfisyspd){
	$mfi_device = array();
	mwexec2("dmesg | grep \"".$device['controller']."\"", $lines);

	// look for deviceid '0' in mfisyspd0: 3815447MB (7814037168 sectors) SYSPD volume (deviceid: 0)
	foreach($lines as $line){
		if( preg_match("/^".$mfisyspd.": /", $line, $matches)){
			if( preg_match("/(?<= \(deviceid: )[0-9]+/", $line, $matches)){
				$mfi_device['target_id']=$matches[0];
			}
		}
	}

	// 2nd pass to retrieve actual device name
	foreach($lines as $line){
		if(preg_match("/[-A-z0-9].*(?= at ".$controller." [-A-z0-9 ].*(?=target ".$mfi_device['target_id']."))+/",$line, $matches)){
			$mfi_device['device_name'] = $matches[0];
		}
	}

	return $mfi_device;
}


// Parse info from S.M.A.R.T.
function get_smartctl_info($device){

	$smartctl_info = array();
	$type_arg = '';
	$vendor = '';
	$product = '';
	$revision = '';

	if( !empty($device['smart']['devicetypearg']) ){
		$type_arg = "-d ".$device['smart']['devicetypearg'];
	}

	mwexec2("smartctl -i ".$type_arg." ".$device['smart']['devicefilepath'],$lines);

	foreach($lines as $line){
        	// trim and convert token to lowercase
		$str_startswith = strtolower(trim(substr($line, 0, 18)));
		$matches = array();

		switch ($str_startswith){
        		// compare always to lowercase and trimmed keywords
			case 'model family:':
				preg_match("/(?<=Model Family:).*/",$line, $matches );
				$smartctl_info['desc'] = trim($matches[0]);
				break;
			case 'device model:':
				preg_match("/(?<=Device Model:).*/",$line, $matches );
				$smartctl_info['model'] = trim($matches[0]);
				break;
			case 'vendor:':
				preg_match("/(?<=Vendor:).*/",$line, $matches );
				$vendor = trim($matches[0]);
				break;
			case 'revision:':
				preg_match("/(?<=Revision:).*/",$line, $matches );
				$revision = trim($matches[0]);
				break;
			case 'product:':
				preg_match("/(?<=Product:).*/",$line, $matches );
				$product = trim($matches[0]);
				break;
			case 'serial number:':
				preg_match("/(?<=Serial [Nn]umber:).*/",$line, $matches );
				$smartctl_info['serial'] = trim($matches[0]);
				break;
			case 'rotation rate:':
				preg_match("/(?<=Rotation Rate:).*/",$line, $matches );
				$smartctl_info['rotation_rate'] = trim($matches[0]);
				break;
			case 'sata version is:':
				if(preg_match("/(?<= \(current:).*(?=\))+/", $line, $matches)){
					$smartctl_info['transfer_rate'] = trim($matches[0]);
				}
				else if(preg_match("/(?<=, ).*?(?>\/s)/", $line, $matches)){
					$smartctl_info['transfer_rate'] = trim($matches[0]);
				}
				break;
			case 'smart support is:':
				if(empty($smartctl_info['smart_support'])){
					if(preg_match("/(?<=SMART support is:).*(?=\-)+/", $line, $matches)){
						$smartctl_info['smart_support'] = ltrim($matches[0]);
					}
				}else if( strncmp($smartctl_info['smart_support'], 'Available', 9) == 0 )  {
					if( preg_match("/(?<=SMART support is: ).*/", $line, $matches)){
						$smartctl_info['smart_support'] = $smartctl_info['smart_support'].', '.$matches[0];
					}
				}
				break;
			}
	}
	// Set model to vendor,product,revision
	if(empty($smartctl_info['model'])){
		$smartctl_info['model'] = trim($vendor.' '.$product.' '.$revision);
 	}
	return $smartctl_info;
}

function init_device_attributes(&$device){
	$device['desc'] = '';
	$device['model'] = '';
	$device['serial'] = '';
	$device['type'] = '';
	$device['size'] = '';
	$device['rotation_rate'] = '';
	$device['transfer_rate'] = '';
	$device['controller'] = '';
	$device['controller_id'] = '';
	$device['controller_desc'] = '';
	$device['target_id'] = '';
	$device['lun_id'] = '';
	$device['smart']['devicefilepath'] = '';
	$device['smart']['devicetype'] = '';
	$device['smart']['devicetypearg'] = '';
	$device['smart']['smart_support'] = '';
}

function parse_device_desc($device){
	$matches = array();

	// device order may change, so take latest(last) line 
	mwexec2("dmesg | grep \"^$device: <\" | tail -r", $lines);

	foreach($lines as $line){
		// example: da2: <Corsair Voyager 1100> Removable Direct Access SCSI-0 device
		if ( preg_match("/(?<=\<).*(?=\>)+/", $line, $matches )){
			return $matches[0];
		}
	}
	return $matches[0];
}

function parse_ata_ahci_controller_desc($controller){
	$controller_desc = '';

	mwexec2("dmesg | grep -w $controller: | sort -u | uniq", $lines);

	foreach($lines as $line){
		// example : ahcich2: <AHCI channel> at channel 2 on ahci0
		if ( preg_match("/(?<= on )[a-z]+[0-9]+$/", $line, $matches )){
			mwexec2("dmesg | grep -w $matches[0]: | sort -u | uniq", $lines2);

			foreach($lines2 as $line2){
				// example : ahci0: <Intel ICH10 AHCI SATA controller> port 0xf900-0xf907,0xf800-0xf803,0xf700-0xf707,0xf600-0xf603,0xf500-0xf51f mem 0xfdffd000-0xfdffd7ff irq 19 at device 31.2 on pci0
				if ( preg_match("/(?<=: \<).*(?=\> )+/", $line2, $matches2) ){
					if(strncmp($controller_desc,'', 1)==0){
						$controller_desc = $matches2[0];
					}
					else{
						$controller_desc = $controller_desc.", ".$matches2[0];
					}
				}
			}
		}
	}
	return $controller_desc;
}

function parse_raid_controller_desc($controller){
	$matches = array();

	if (strncmp($line, $controller ,4)){
		mwexec2("dmesg | grep $controller", $lines);

		foreach ($lines as $line){
			if ( strncmp($line, $controller.': <' ,strlen($controller)+3) == 0){
				if ( preg_match("/(?<=\<).*(?=\>)|(?<=\<).*(?=$)/",$line,$matches)){
					return $matches[0];
				}
			}
		}
	}
	return $matches[0];
}

function parse_umass_controller_desc($controller_id){
	$matches3 = array();

	mwexec2("dmesg | grep -w umass$controller_id", $lines);

	foreach($lines as $line){
		// example: umass0: <Corsair Voyager, class 0/0, rev 2.00/11.00, addr 2> on usbus7
		if ( preg_match("/(?<=\> on )[a-z]+[0-9]+$/", $line, $matches )){
			mwexec2("dmesg | grep \"$matches[0] on \"", $lines2);

			foreach($lines2 as $line2){
				// example : usbus7 on ehci1
				if ( preg_match("/(?<= on )[a-z]+[0-9]+$/", $line2, $matches2 )){
					mwexec2("dmesg | grep \"$matches2[0]: <\"", $lines3);

					// example : ehci1: <Intel 82801JI (ICH10) USB 2.0 controller USB-A> mem 0xfdffe000-0xfdffe3ff irq 23 at device 29.7 on pci0
					foreach($lines3 as $line3){
						if ( preg_match("/(?<=: \<).*(?=\> )+/", $line3, $matches3) ){
							return $matches3[0];
						}
					}
				}
			}
		}
	}
	return $matches3[0];
}

// Get list of ATA disks.
function get_ata_disks_list()
{
	$devlist = array();
	$devlist = parse_camcontrol_info("ata");
	$devlist = parse_controller_config($devlist);

	return $devlist;
}

// Get list of IDE AND SCSI CDROM.
function get_cdrom_list()
{
	$devlist = array();
	$devlist = parse_camcontrol_info("cdrom");
	$devlist = parse_controller_config($devlist);

	return $devlist;
}

// Get list of SCSI disks.
function get_scsi_disks_list()
{
	$devlist = array();
	$devlist = parse_camcontrol_info("scsi");
	$devlist = parse_controller_config($devlist);

	return $devlist;
}

function get_iscsi_disks_list()
{
	$disklist = array();

	/* get all devlist w/bus */
	exec("/sbin/camcontrol devlist -v",$rawdata);
	$iscsi = false;
	foreach ($rawdata as $line) {
		/* iscsi bus? */
		if (preg_match("/^scbus([-\d]+)\s+on\s+iscsi\d+\s+/",$line)) {
			$iscsi = true;
			continue;
		} else if (preg_match("/^[^<]/",$line)) {
			continue;
		}
		if (!$iscsi)
			continue;

		/* Get information include between parenthese: (pass0,da0) or (da0,pass0)*/
		preg_match("/.*\(([^>]*)\).*/",$line,$match);
		if ($match[1] == "") {
			$iscsi = false;
			continue;
		}

		/* Spare le resultat par la virgule  */
		$temp = preg_split("/,/", $match[1]);

		// Check if diskname is the first (da0,pass0) or the second (pass0,da0) arguement
		$diskname = $temp[1];
		if ($diskname[0] === "p")
			$diskname = $temp[0];

		/* On exlus les lecteurs cd */
		if (!preg_match("/^(cd)/", $diskname  )) 	{
			$disklist[$diskname] = array();
			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['devicespecialfile'] = "/dev/" . $diskname;
			$disklist[$diskname]['type'] = "iSCSI";

			/* Match the description witch is include between < and > */
			preg_match("/.*\<([^>]*)>.*/",$line,$match);
			$disklist[$diskname]['desc'] = $match[1];

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($diskname);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			/* Get serial number */
			$serial = system_get_volume_serial("/dev/" . $diskname, $disklist[$diskname]['controller']);
			$disklist[$diskname]['serial'] = $serial;
		}
	}

	return $disklist;
}

// Get list of hardware RAID disks.
//[aacd0] => Array
//    (
//        [type] => RAID
//        [desc] => XYZ
//        [size] => xxxMB
//        [name] => aacd0
//        [devicespecialfile] => /dev/aacd0
//        [serial] => XX123456
//    )
function get_hraid_disks_list()
{
	$disklist = array();

	/* Recupere la liste des disques RAID */
	$kerneldisks = explode(" ", trim(preg_replace("/kern.disks: /", "", exec("/sbin/sysctl kern.disks"))));

	/* Recupere la liste des disques ATA et SCSI */
	$diskdetected = array_merge((array)get_ata_disks_list(),(array)get_scsi_disks_list());

	/* Recupere le dmesg */
	exec("/sbin/dmesg",$rawdmesg);

	foreach ($kerneldisks as $diskname) {
		$allready = 1;

		// Check of this entry is IDE or SCSI (allready detected)
		foreach ($diskdetected as $diskfoundk => $diskfoundv) 	{
			if (strcasecmp($diskfoundk,$diskname) == 0)
				$allready = 0;
		}
		// Exclude cdrom device
		if (preg_match("/^(cd)/", $diskname)) {
			$allready = 0;
		}

		if ($allready) 	{
			/* If not an IDE and SCSI disk */
			$device = array();

			$device = preg_split( '/([-A-z]+)/', $diskname, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			$disklist[$diskname]['name'] = $diskname;
			$disklist[$diskname]['id'] = $device[1];

			init_device_attributes($disklist[$diskname]);

			$disklist[$diskname]['devicespecialfile']= "/dev/" . $diskname;
			$disklist[$diskname]['type'] = "RAID";

			if($device[0] == "twed" || $device[0] == "mfisyspd"){

				if( $device[0] == "twed") {
					$disklist[$diskname]['target_id'] = $disklist[$diskname]['id'];
				}

				$matches = array();

				// look for controller details from dmesg
				foreach($rawdmesg as $line){
					// example : twed2 on twe0
					if ( preg_match("/(?<=".$diskname." on )[a-z]+[0-9]+$/", $line, $matches )){
						$controller = array();
						$controller = preg_split( '/([-A-z]+)/', $matches[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
						// get controller and controller id
						$disklist[$diskname]['controller'] = $controller[0];
						$disklist[$diskname]['controller_id'] = $controller[1];
					}
				}
			}
			else{
				/* Looking for the disk description in the dmesg */
				foreach ($rawdmesg as $dmesgline) {
					/* Separe la ligne par les espace */
					$dmesgtab = explode(" ", $dmesgline);
					$dmesgtab[0] = rtrim($dmesgtab[0],":");
					// si la ligne commence par le nom du disque: attention il y a 2 lignes
					if ($dmesgtab[0]!="" &&(strcasecmp($dmesgtab[0],$diskname) == 0)) {
						// the first line as this example "aacd0: <RAID 5> on aac0"
						if (strcasecmp(substr($dmesgtab[1], 0, 1),"<") == 0) {
							/* Match the description witch is include between < and > */
							preg_match("/.*\<([^>]*)>.*/",$dmesgline,$match);
							$disklist[$diskname]['desc'] = $match[1];
						}
					}
				}
			}

			/* Get diskinfo to get disk size */
			$diskinfo = disks_get_diskinfo($diskname);
			$disklist[$diskname]['size'] = $diskinfo['mediasize_mbytes'] . "MB";

			/* Get serial number */
			$serial = system_get_volume_serial("/dev/" . $diskname, $disklist[$diskname]['controller']);
			$disklist[$diskname]['serial'] = $serial;
		}
	}

	$disklist = parse_controller_config($disklist);


	return $disklist;
}


/* Return list of ALL disk: physical, hardware RAID, Software RAID disk and HAST disk */
function get_physical_disks_list()
{
	$disklist = array_merge((array)get_ata_disks_list(),(array)get_scsi_disks_list(),(array)get_hraid_disks_list(),(array)get_hast_disks_list());
	return $disklist;
}

/* Return list of ALL disk: physical, hardware RAID and Software RAID disk */
function get_all_disks_list()
{
	$disklist = array_merge((array)get_ata_disks_list(),(array)get_scsi_disks_list(),(array)get_hraid_disks_list(), (array)get_sraid_disks_list());
	return $disklist;
}

// Wrapper for exec().
// $command - Command to be executed.
// $logerr - Write error messages to log file.
function mwexec($command, $logerr = false) {
	global $g;

	if ($g['debug']) {
		if (!$_SERVER['REMOTE_ADDR'])
			echo "mwexec(): $command\n";
		passthru($command, $retval);
	} else {
		$redirect = ">/dev/null 2>&1";

		if (true === $logerr)
			$redirect = "2>&1 >/dev/null";

		exec("{$command} {$redirect}", $output, $retval);
		if ((true === $logerr) && (is_array($output))) {
			write_log(implode("\n", $output));
		}
	}

	return $retval;
}

function mwexec2($command, &$output = NULL, &$return_var = NULL) {
	return exec($command, $output, $return_var);
}

/* wrapper for exec() in background */
function mwexec_bg($command) {
	global $g;

	if ($g['debug']) {
		if (!$_SERVER['REMOTE_ADDR'])
			echo "mwexec(): $command\n";
	}

	exec("nohup $command > /dev/null 2>&1 &");
}

/* unlink a file, if it exists */
function unlink_if_exists($fn) {
	if (file_exists($fn))
		unlink($fn);
}

/* check if an alias exists */
function is_alias($name) {

	global $aliastable;

	return isset($aliastable[$name]);
}

/* expand a host or network alias, if necessary */
function alias_expand($name) {

	global $aliastable;

	if (isset($aliastable[$name]))
		return $aliastable[$name];
	else if (is_ipaddr($name) || is_subnet($name))
		return $name;
	else
		return null;
}

/* expand a host alias, if necessary */
function alias_expand_host($name) {

	global $aliastable;

	if (isset($aliastable[$name]) && is_ipaddr($aliastable[$name]))
		return $aliastable[$name];
	else if (is_ipaddr($name))
		return $name;
	else
		return null;
}

/* expand a network alias, if necessary */
function alias_expand_net($name) {

	global $aliastable;

	if (isset($aliastable[$name]) && is_subnet($aliastable[$name]))
		return $aliastable[$name];
	else if (is_subnet($name))
		return $name;
	else
		return null;
}

/* find out whether two subnets overlap */
function check_subnets_overlap($subnet1, $bits1, $subnet2, $bits2) {

	if (!is_numeric($bits1))
		$bits1 = 32;
	if (!is_numeric($bits2))
		$bits2 = 32;

	if ($bits1 < $bits2)
		$relbits = $bits1;
	else
		$relbits = $bits2;

	$sn1 = gen_subnet_mask_long($relbits) & ip2long($subnet1);
	$sn2 = gen_subnet_mask_long($relbits) & ip2long($subnet2);

	if ($sn1 == $sn2)
		return true;
	else
		return false;
}

/* compare two IP addresses */
function ipcmp($a, $b) {
	if (ip2long($a) < ip2long($b))
		return -1;
	else if (ip2long($a) > ip2long($b))
		return 1;
	else
		return 0;
}

/* return true if $addr is in $subnet, false if not */
function ip_in_subnet($addr,$subnet) {
	list($ip, $mask) = explode('/', $subnet);
	$mask = 0xffffffff << (32 - $mask);
	return ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
}

// Obtain MAC address given an IP address by looking at the ARP table
function arp_get_mac_by_ip($ip) {
	exec("/usr/sbin/arp -n {$ip}", $arpoutput);

	if ($arpoutput[0]) {
		$arpi = explode(" ", $arpoutput[0]);
		$macaddr = $arpi[3];
		if (is_macaddr($macaddr))
			return $macaddr;
		else
			return false;
	}

	return false;
}

// Get the interface name
function get_ifname($if)
{
  // Check if 'auto' mode is activated
  if ($if === "auto")   {
    $interfaces = explode(" ", exec("/sbin/ifconfig -l"));
    foreach($interfaces as $ifname) {
	if (!preg_match("/^(ppp|sl|gif|faith|lo|vlan|tun|plip|ipfw|usbus|carp)/", $ifname)) {
	    $if = $ifname;
	    break;
	}
    }
    if ($if === "auto") $if = "lo0";
  }

  return $if;
}

// Get local IPv4 address
function get_ipaddr($if)
{
  $if = get_ifname($if);

  unset($ipaddr);
  exec("/usr/bin/netstat -inW -f inet -I {$if}", $inetinfo);
  array_shift($inetinfo);

  foreach($inetinfo as $inet)
  {
    $ainet = preg_split("/\s+/", $inet);
    $ifname = chop($ainet[0]);

    if (strncmp($ifname,$if,strlen($if)) == 0)
    {
      $ipaddr = chop($ainet[3]);
      break;
    }
  }

  return !empty($ipaddr) ? $ipaddr : null;
}

// Get subnet bits.
function get_subnet_bits($if)
{
	$if = get_ifname($if);

	exec("/usr/local/bin/sipcalc --cidr-addr {$if}", $rawdata);
	if (preg_match("/.+Network mask \(bits\)	- (\d+).+/", implode($rawdata), $matches)) {
		return $matches[1];
	}

	return false;
}

// Get local IPv6 address
function get_ipv6addr($if)
{
  $if = get_ifname($if);

  unset($ipaddr);
  exec("/sbin/ifconfig {$if} inet6", $rawdata);
  foreach($rawdata as $line) {
    if (preg_match("/^\s+inet6\s+(\S+)\s/", $line, $m)) {
      $ipaddr = $m[1];
    }
  }
  if (!empty($ipaddr)) return $ipaddr;
  exec("/usr/bin/netstat -inW -f inet6 -I {$if}", $inetinfo);
  array_shift($inetinfo);

  foreach($inetinfo as $inet)
  {
    $ainet = preg_split("/\s+/", $inet);
    $ifname = chop($ainet[0]);

    if (strncmp($ifname,$if,strlen($if)) == 0)
    {
      $ipaddr = chop($ainet[3]);
      break;
    }
  }

  return !empty($ipaddr) ? $ipaddr : null;
}

// Get local MAC address
function get_macaddr($if)
{
  $if = get_ifname($if);

  unset($macaddr);
  exec("/usr/bin/netstat -inW -f link -I {$if}", $linkinfo);
  array_shift($linkinfo);

  foreach($linkinfo as $link)  {
    $alink = preg_split("/\s+/", $link);
    $ifname = chop($alink[0]);

    if (strncmp($ifname,$if,strlen($if)) == 0) {
      $macaddr = chop($alink[3]);
      break;
    }
  }

  return !empty($macaddr) ? $macaddr : null;
}

// Get DNS servers.
// Parse /etc/resolv.conf when using DHCP (will be updated
// when 'dhclient' is executed), otherwise use configured
// DNS servers from config.xml for static IP.
// Returns array of DNS servers.
function get_ipv4dnsserver() {
	global $config;

	$result = array();

	switch ($config['interfaces']['lan']['ipaddr']) {
		case 'dhcp':
			exec("cat /etc/resolv.conf", $rawdata);
			foreach($rawdata as $data) {
				$adata = preg_split("/\s+/", $data);
				if (0 == strcmp(strtolower(trim($adata[0])),"nameserver")) {
					if (is_ipv4addr(trim($adata[1]))) {
						$result[] = trim($adata[1]);
					}
				}
			}
			break;

		default:
			if (isset($config['system']['dnsserver'])) {
				$result = $config['system']['dnsserver'];
			}
			break;
	}

	$result = array_merge($result, array("", ""));
	return $result;
}

function get_ipv6dnsserver() {
	global $config;

	$result = array();

	switch ($config['interfaces']['lan']['ipv6addr']) {
		case 'auto':
			exec("cat /etc/resolv.conf", $rawdata);
			foreach($rawdata as $data) {
				$adata = preg_split("/\s+/", $data);
				if (0 == strcmp(strtolower(trim($adata[0])),"nameserver")) {
					if (is_ipv6addr(trim($adata[1]))) {
						$result[] = trim($adata[1]);
					}
				}
			}
			break;

		default:
			$result = $config['system']['ipv6dnsserver'];
			break;
	}

	$result = array_merge($result, array("", ""));
	return $result;
}

// Get the default gateway.
// Returns default gateway IPv4
function get_defaultgateway() {
	global $config;

	$result = "";

	switch ($config['interfaces']['lan']['ipaddr']) {
		case 'dhcp':
			exec("/sbin/route -n get -inet default", $rawdata);
			foreach($rawdata as $data) {
				$adata = preg_split("/\s+/", trim($data));
				if (false !== strstr($adata[0],"gateway")) {
					$result = $adata[1];
					break;
				}
			}
			break;

		default:
			$result = $config['interfaces']['lan']['gateway'];
			break;
	}

	return $result;
}

// Returns default gateway IPv6
function get_ipv6defaultgateway() {
	global $config;

	$result = "";

	switch ($config['interfaces']['lan']['ipv6addr']) {
		case 'auto':
			exec("/sbin/route -n get -inet6 default", $rawdata);
			foreach($rawdata as $data) {
				$adata = preg_split("/\s+/", trim($data));
				if (false !== strstr($adata[0],"gateway")) {
					$result = $adata[1];
					break;
				}
			}
			break;

		default:
			$result = $config['interfaces']['lan']['ipv6gateway'];
			break;
	}

	return $result;
}

/* Get list of supported filesystem types */
function get_fstype_list() {
	global $g_filesystems;
	return $g_filesystems;
}

function get_fstype_shortdesc($fstype) {
  $shortdesc = "";

  switch($fstype)
  {
    case "":              $shortdesc = gettext("Unformated");
                          break;
    case "ufs":
    case "ufs_no_su":
    case "ufsgpt":
    case "ufsgpt_no_su":  $shortdesc = "UFS";
                          break;
    case "msdos":         $shortdesc = "FAT32";
                          break;
    case "cd9660":				$shortdesc = "CD/DVD";
                          break;
    case "ntfs":          $shortdesc = "NTFS";
                          break;
    case "gmirror":       $shortdesc = "gmirror";
                          break;
    case "gconcat":       $shortdesc = "gconcat";
                          break;
    case "gstripe":       $shortdesc = "gstripe";
                          break;
    case "graid5":				$shortdesc = "graid5";
                          break;
    case "gvinum":        $shortdesc = "gvinum";
                          break;
    case "softraid":      $shortdesc = gettext("SoftRaid");
                          break;
		case "geli":					$shortdesc = gettext("Encrypted");
                          break;
    case "ext2":					$shortdesc = "EXT2";
    											break;
		case "zfs":						$shortdesc = "ZFS storage pool device";
    											break;
  }

  return $shortdesc;
}

// Get interface information.
// $if - Interface name (e.g. re0, ...)
// Result: Array
//	(
//		[hwif] => re0
//		[if] => re0
//		[status] => up
//		[mtu] = 1500
//		[macaddr] => 00:0e:2e:0a:62:75
//		[inpkts] => 367
//		[inerrs] => 0
//		[inbytes] => 54478
//		[outpkts] => 401
//		[outerrs] => 0
//		[outbytes] => 327601
//		[collisions] => 0
//		[media] => 100baseTX
//		[ipaddr] => 192.168.178.150
//		[subnet] => 255.255.255.0
//		[wolevents] => array("unicast",
//                           "multicast",
//                           "broadcast",
//                           "magic",
//                           "link")
// Additional:
//		[channel] => xxx
//		[ssid] => xxx
//	)
function get_interface_info($if) {
	global $config;

	$ifinfo = array();

	$ifinfo['hwif'] = get_ifname($if);
	$ifinfo['if'] = $ifinfo['hwif'];

	/* run netstat to determine link info */
	unset($linkinfo);
	exec("/usr/bin/netstat -I " . $ifinfo['hwif'] . " -nWb -f link", $linkinfo);
	$linkinfo = preg_split("/\s+/", $linkinfo[1]);
	if (preg_match("/\*$/", $linkinfo[0]) || preg_match("/^$/", $linkinfo[0])) {
		$ifinfo['status'] = "down";
	} else {
		$ifinfo['status'] = "up";
	}

	if (!strstr($ifinfo['if'],'tun')) {
		$ifinfo['mtu'] = $linkinfo[1];
		$ifinfo['macaddr'] = $linkinfo[3];
		$ifinfo['inpkts'] = $linkinfo[4];
		$ifinfo['inerrs'] = $linkinfo[5];
		$ifinfo['indrop'] = $linkinfo[6];
		$ifinfo['inbytes'] = $linkinfo[7];
		$ifinfo['outpkts'] = $linkinfo[8];
		$ifinfo['outerrs'] = $linkinfo[9];
		$ifinfo['outbytes'] = $linkinfo[10];
		$ifinfo['collisions'] = $linkinfo[11];
	} else {
		$ifinfo['inpkts'] = $linkinfo[3];
		$ifinfo['inbytes'] = $linkinfo[6];
		$ifinfo['outpkts'] = $linkinfo[7];
		$ifinfo['outbytes'] = $linkinfo[9];
	}

	if ($ifinfo['status'] === "up") {
		/* try to determine media with ifconfig */
		unset($ifconfiginfo);
		exec("/sbin/ifconfig -m " . $ifinfo['hwif'], $ifconfiginfo);

		foreach ($ifconfiginfo as $ici) {
			if (preg_match("/media: .*? \((.*?)\)/", $ici, $matches)) {
				$ifinfo['media'] = $matches[1];
			} else if (preg_match("/media: Ethernet (.*)/", $ici, $matches)) {
				$ifinfo['media'] = $matches[1];
			}
			if (preg_match("/status: (.*)$/", $ici, $matches)) {
				if ($matches[1] != "active")
					$ifinfo['status'] = $matches[1];
			}
			if (preg_match("/channel (\S*)/", $ici, $matches)) {
				$ifinfo['channel'] = $matches[1];
			}
			if (preg_match("/ssid (\".*?\"|\S*)/", $ici, $matches)) {
				if ($matches[1][0] == '"')
					$ifinfo['ssid'] = substr($matches[1], 1, -1);
				else
					$ifinfo['ssid'] = $matches[1];
			}
			if (preg_match("/supported wake events: (.*)$/", $ici, $matches)) {
				$ifinfo['wolevents'] = explode(" ", $matches[1]);
			}
			if (preg_match("/capabilities=.*<(.*)>$/", $ici, $matches)) {
				$info = explode(",", $matches[1]);
				$ifinfo['wolevents'] = preg_grep("/^WOL/", $info);
			}
		}

		if (isset($ifinfo['pppoelink']) && $ifinfo['pppoelink'] != "down" && isset($ifinfo['pptplink']) && $ifinfo['pptplink'] != "down") {
			/* try to determine IP address and netmask with ifconfig */
			unset($ifconfiginfo);
			exec("/sbin/ifconfig " . $ifinfo['if'], $ifconfiginfo);

			foreach ($ifconfiginfo as $ici) {
				if (preg_match("/inet (\S+)/", $ici, $matches)) {
					$ifinfo['ipaddr'] = $matches[1];
				}
				if (preg_match("/netmask (\S+)/", $ici, $matches)) {
					if (preg_match("/^0x/", $matches[1]))
						$ifinfo['subnet'] = long2ip(hexdec($matches[1]));
				}
				if (preg_match("/inet6 (\S+)%/", $ici, $matches)) {
					$ifinfo['ipv6addr'] = $matches[1];
				}
				if (preg_match("/prefixlen (\S+)/", $ici, $matches)) {
					$ifinfo['ipv6subnet'] = $matches[1];
				}
			}
		}
	}

	return $ifinfo;
}

// Get interface information.
// $iftype - interface type (e.g. lan, opt[n], ...)
// Result: Array
//	(
//		see get_interface_info()
// Additional:
//    [gateway] => 192.168.178.1
//	)
function get_interface_info_ex($iftype) {
  global $config;

  // Get interface information
	$ifinfo = get_interface_info($config['interfaces'][$iftype]['if']);

	// Get the default gateway if necessary
	if ($iftype === "lan") {
		// Run netstat to determine the default IPv4 gateway
		unset($netstatrninfo);
		exec("/usr/bin/netstat -rnf inet", $netstatrninfo);

		foreach ($netstatrninfo as $nsr) {
			if (preg_match("/^default\s*(\S+)/", $nsr, $matches)) {
				$ifinfo['gateway'] = $matches[1];
			}
		}
		// Run netstat to determine the default IPv6 gateway
		unset($netstatrninfo);
		exec("/usr/bin/netstat -rnf inet6", $netstatrninfo);

		foreach ($netstatrninfo as $nsr) {
			if (preg_match("/^default\s*(\S+)/", $nsr, $matches)) {
				$ifinfo['ipv6gateway'] = $matches[1];
			}
		}
	}

	return $ifinfo;
}

// Get carp interface information.
function get_carp_info($if) {
	$ifinfo = array();
	$ifinfo['hwif'] = get_ifname($if);
	$ifinfo['if'] = $ifinfo['hwif'];

	exec("/sbin/ifconfig " . $ifinfo['hwif'] . " | grep carp:", $info);
	$info = preg_split("/\s+/", $info[0]);
	$ifinfo['state'] = $info[2];
	$ifinfo['vhid'] = $info[4];
	$ifinfo['advbase'] = $info[6];
	$ifinfo['advskew'] = $info[8];
	return $ifinfo;
}

// Get hast device information.
function get_hvol_info($name)
{
	$hvolinfo = array();
	$hvolinfo['error'] = 0;
	$hvolinfo['status'] = 'unknown';

	exec("/sbin/hastctl list {$name}",$rawdata);
	foreach ($rawdata as $line) {
		// skip name
		if (preg_match('/^\S+:/', $line)) continue;

		$line = trim($line);
		list($key,$val) = preg_split('/:\s+/', $line);
		$hvolinfo[$key] = $val;
	}
	return $hvolinfo;
}

// Get hast role.
function get_hast_role()
{
	$role = "unknown";

	if (0 == mwexec("/bin/pgrep -q -lf 'hastd: .* \\(primary\\)'")) {
		$role = "primary";
	}
	if (0 == mwexec("/bin/pgrep -q -lf 'hastd: .* \\(secondary\\)'")) {
		$role = "secondary";
	}
	return $role;
}

/* Get the product name */
function get_product_name() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.name"));
}

/* Get the platform type */
function get_platform_type() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/platform"));
}

/* Get the product URL */
function get_product_url() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.url"));
}

/* Get the firmware check URL */
function get_firm_url() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/firm.url"));
}

/* Get the product copyright note */
function get_product_copyright() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.copyright"));
}

/* Get the product version */
function get_product_version() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.version"));
}

/* Get the product revision */
function get_product_revision() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.revision"));
}

/* Get the product version */
function get_product_buildtime() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.version.buildtime"));
}

/* Get the product version name */
function get_product_versionname() {
	global $g;
	return chop(file_get_contents("{$g['etc_path']}/prd.version.name"));
}

// Write message into log file.
// Return 0 if successful, otherwise 1
function write_log($message) {
	$cmd="/usr/bin/logger \"{$message}\"";
	return mwexec($cmd);
}

// Display message on console.
// Return 0 if successful, otherwise 1
function write_console($message) {
	echo($message);
	return 0;
}

// Check if booting process is performed.
// Return TRUE if booting, otherwise FALSE
function is_booting() {
	$val = getenv("BOOTING");
	return (1 == $val) ? TRUE : FALSE;
}

// Get list of configured physical disks.
function get_conf_physical_disks_list() {
	global $config;

	$a_physicaldisk = array();

	if (is_array($config['disks']) && is_array($config['disks']['disk'])) {
		array_sort_key($config['disks']['disk'], "name");
		$a_disks = $config['disks']['disk'];
		foreach ($a_disks as &$disk) { $disk['class'] = "physical"; } // Set disk type
		$a_physicaldisk = array_merge($a_physicaldisk, $a_disks);
  }

	return $a_physicaldisk;
}

// Get list of configured Software RAID disks.
function get_conf_sraid_disks_list() {
	global $config;

	$a_vdisk = array();

	if (is_array($config['gvinum']) && is_array($config['gvinum']['vdisk'])) {
		array_sort_key($config['gvinum']['vdisk'], "name");
		$a_gvinum = $config['gvinum']['vdisk'];
		foreach ($a_gvinum as &$vdisk) { $vdisk['class'] = "gvinum"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_gvinum);
  }

	if (is_array($config['gmirror']) && is_array($config['gmirror']['vdisk'])) {
		array_sort_key($config['gmirror']['vdisk'], "name");
		$a_gmirror = $config['gmirror']['vdisk'];
		foreach ($a_gmirror as &$vdisk) { $vdisk['class'] = "gmirror"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_gmirror);
  }

	if (is_array($config['gconcat']) && is_array($config['gconcat']['vdisk'])) {
		array_sort_key($config['gconcat']['vdisk'], "name");
		$a_gconcat = $config['gconcat']['vdisk'];
		foreach ($a_gconcat as &$vdisk) { $vdisk['class'] = "gconcat"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_gconcat);
  }

	if (is_array($config['gstripe']) && is_array($config['gstripe']['vdisk'])) {
		array_sort_key($config['gstripe']['vdisk'], "name");
		$a_gstripe = $config['gstripe']['vdisk'];
		foreach ($a_gstripe as &$vdisk) { $vdisk['class'] = "gstripe"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_gstripe);
  }

	if (is_array($config['graid5']) && is_array($config['graid5']['vdisk'])) {
		array_sort_key($config['graid5']['vdisk'], "name");
		$a_graid5 = $config['graid5']['vdisk'];
		foreach ($a_graid5 as &$vdisk) { $vdisk['class'] = "graid5"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_graid5);
  }

	return $a_vdisk;
}

// Get list of configured encrypted disks
function get_conf_encryped_disks_list() {
	global $config;

	$a_vdisk = array();

	if (is_array($config['geli']) && is_array($config['geli']['vdisk'])) {
		array_sort_key($config['geli']['vdisk'], "name");
		$a_geli = $config['geli']['vdisk'];
		foreach ($a_geli as &$vdisk) { $vdisk['class'] = "geli"; } // Set disk type
		$a_vdisk = array_merge($a_vdisk, $a_geli);
	}

	return $a_vdisk;
}

// Get list of configured ZFS virtual devices
function get_conf_zfs_vdevice_list() {
	global $config;

	$result = array();

	if (is_array($config['zfs']['vdevices']) && is_array($config['zfs']['vdevices']['vdevice'])) {
		array_sort_key($config['zfs']['vdevices']['vdevice'], "name");
		$a_vdevice = $config['zfs']['vdevices']['vdevice'];
		foreach ($a_vdevice as &$vdevice) { $vdevice['class'] = "vdevice"; } // Set disk type
		$result = array_merge($result, $a_vdevice);
	}

	return $result;
}

// Get a filtered list of all configured disks (real and virtual, e.g. SoftRAID or GEli encrypted).
// Physical disks used by any virtual disk is filtered out. Also virtual disks itself are
// filtered if they are used by another virtual disk.
function get_conf_all_disks_list_filtered() {
	global $config;

	$a_disk = array();

	// Get list of physical disks
	$a_physicaldisk = get_conf_physical_disks_list();

	// Get list of Software RAID disks
	$a_sraiddisk = get_conf_sraid_disks_list();

	// Get list of encrypted disks
	$a_encrypteddisk = get_conf_encryped_disks_list();

	// Get list of ZFS virtual devices
	$a_zfsvdevice = get_conf_zfs_vdevice_list();

	// First add physical disks if they are not used by a virtual disk
	foreach ($a_physicaldisk as $disk) {
		$unused = true;

		// Check Software RAID volumes
		foreach ($a_sraiddisk as $vdisk) {
			if (is_array($vdisk['device'])) {
				if (false !== array_search($disk['devicespecialfile'], $vdisk['device'])) {
					$unused = false;
					break;
				}
			}
		}

		// Check encrypted disks
		foreach ($a_encrypteddisk as $vdisk) {
			if (false !== array_search($disk['devicespecialfile'], $vdisk['device'])) {
				$unused = false;
				break;
			}
		}

		// Check ZFS virtual devices
		foreach ($a_zfsvdevice as $vdisk) {
			if (false !== array_search($disk['devicespecialfile'], $vdisk['device'])) {
				$unused = false;
				break;
			}
		}

		// Append physical disk if not used by any virtual disk
		if(true === $unused) {
			$a_disk[] = $disk;
		}
	}

	// Add configured Software RAID disks. Check if they are not used by a
	// virtual disk itself
	foreach ($a_sraiddisk as $disk) {
		$unused = true;

		// Check Software RAID volumes
		foreach ($a_sraiddisk as $vdisk) {
			if (is_array($vdisk['device'])) {
				if (false !== array_search($disk['devicespecialfile'], $vdisk['device'])) {
					$unused = false;
					break;
				}
			}
		}

		// Check encrypted disks
		foreach ($a_encrypteddisk as $vdisk) {
			if (false !== array_search($disk['devicespecialfile'], $vdisk['device'])) {
				$unused = false;
				break;
			}
		}

		// Append virtual disk if not used by any other virtual disk
		if(true === $unused) {
			$a_disk[] = $disk;
		}
	}

	// Add configured encrypted disks
	$a_disk = array_merge($a_disk, $a_encrypteddisk);

	return $a_disk;
}

// Get a filtered list of configured disks (real and virtual, e.g. SoftRAID or GEli encrypted).
// List is filtered by given key and value.
// $key - Key used to filter (e.g. fstype, devicespecialfile or name)
// $pattern - pattern used for filtering (e.g. key=fstype pattern=softraid)
// Return list containing disks matching filter criterias.
function get_conf_disks_filtered_ex($key, $pattern) {
	$result = array();

	// Get all configured disks
	$a_disk = array_merge(get_conf_physical_disks_list(), get_conf_sraid_disks_list(), get_conf_encryped_disks_list());

	if (is_array($a_disk)) {
		foreach ($a_disk as $disk) {
			if (!isset($disk[$key]))
				continue;
			if (1 <= preg_match("/{$pattern}/", $disk[$key])) {
				$result[] = $disk;
			}
		}
	}

	return $result;
}

// Set the given file system type in configuration.
// Return 0 if successful, otherwise 1.
function set_conf_disk_fstype($disk, $fstype)
{
	global $config;

	$result = 1;
	$diskconf = array("disks"   => "disk",
										"gconcat" => "vdisk",
										"gmirror" => "vdisk",
										"graid5"  => "vdisk",
										"gstripe" => "vdisk",
										"gvinum"  => "vdisk",
										"geli"    => "vdisk");

	while (list($class, $val) = each($diskconf)) {
		if (empty($config[$class][$val]) || !is_array($config[$class][$val]))
			continue;
//	echo "Searching array \$config['$class']['$val'] using key 'devicespecialfile'\n";
		$id = array_search_ex($disk, $config[$class][$val], "devicespecialfile");
		if ($id !== false) {
			$config[$class][$val][$id]['fstype'] = $fstype;
			$result = 0;
//		echo "Set \$config['$class']['$val'][$id]['fstype'] = $fstype\n";
			break;
		}
	}

	return $result;
}

// Convert bytes into best unit.
// Use FreeBSD unit suffix notation (see 'man df').
function decode_size($bytes) {
	$types = array('B', 'K', 'M', 'G', 'T', 'P');
	for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);
	return (round($bytes, 2) . $types[$i]);
}

// Get the Internet host name corresponding to a given IP address.
// Returns the host name or the unmodified ip_address  on failure.
function get_hostbyaddr($ip_address) {
	exec("/usr/bin/host {$ip_address}", $rawdata);
	if (preg_match("/^Name: (\S+)/", $rawdata[0], $matches)) {
		return $matches[1];
	}
	return $ip_address;
}

// Create a pseudo-random UUID according to RFC 4122.
function uuid() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

// Get the file size in bytes.
// Fix PHP file size problem (see http://de.php.net/manual/en/function.filesize.php).
function get_filesize($file) {
	return @exec("stat -f %z " . escapeshellarg($file));
}

/**
 * Check and get the ISO image type.
 * Valid identifiers:
 * @code
 * 32769   string    CD001     ISO 9660 CD-ROM filesystem data
 * 37633   string    CD001     ISO 9660 CD-ROM filesystem data (raw 2352 byte sectors)
 * 32776   string    CDROM     High Sierra CD-ROM filesystem data
 * 38913   string   !NSR0      ISO 9660 CD-ROM filesystem data
 * 38913   string    NSR01     UDF filesystem data (version 1.0)
 * 38913   string    NSR02     UDF filesystem data (version 1.5)
 * 38913   string    NSR03     UDF filesystem data (version 2.0)
 * @endcode
 * @param filename	Name of the file to check.
 * @return 'cd9660' or 'udf' if successful, otherwise FALSE.
 */
function util_is_iso_image($filename) {
	$return = FALSE;
	$validmagic = array(
		"CD001" => "cd9660",
		"CDROM" => "cd9660",
		"NSR0"  => "udf",
		"NSR01" => "udf",
		"NSR02" => "udf",
		"NSR03" => "udf",
		"BEA01" => "udf");

	$fh = fopen($filename, "r");
	if (FALSE === $fh)
		return $return;

	// Read various file positions.
	fseek($fh, 32769, SEEK_SET);
	$magic[] = fgets($fh, 6);
	fseek($fh, 37633, SEEK_SET);
	$magic[] = fgets($fh, 6);
	fseek($fh, 32776, SEEK_SET);
	$magic[] = fgets($fh, 6);
	fseek($fh, 38913, SEEK_SET);
	$magic[] = fgets($fh, 6);

	fclose($fh);

	foreach ($validmagic as $validmagick => $validmagicv) {
		if (FALSE !== array_search($validmagick, $magic)) {
			$return = $validmagicv;
			break;
		}
	}

	return $return;
}

/**
 * Write a string to a file. The data will be written to a temporary file of
 * the same name but ending with the pid of the current process. If successful,
 * the temporary file will be renamed to the original one.
 * @param[in] filename	Path to the file where to write the data.
 * @param[in] data			The data to write.
 * @param[in] binary		Force binary mode writing.
 * @return True if successful, otherwise false.
 */
function file_put_contents_safe($filename, $data, $binary = FALSE) {
	$tmpfilename = sprintf("%s.%s", $filename, getmypid());
	$mode = (TRUE === $binary) ? "wb" : "w";

	if (!($fd = fopen($tmpfilename, $mode)))
		return FALSE;

	if (!fwrite($fd, $data)) {
		fclose($fd);
		return FALSE;
	}

	fclose($fd);

	if (!rename($tmpfilename, $filename)) {
		unlink($tmpfilename);
		return FALSE;
	}

	return TRUE;
}

function set_php_timezone()
{
	global $g, $config;

	$tz = $config['system']['timezone'];
	if (empty($tz))
		$tz = "Etc/UTC";

	$lines = file("/usr/local/etc/php.ini");
	if ($lines === FALSE) {
		return FALSE;
	}

	// read lines of php.ini except timezone
	$out = "";
	foreach ($lines as $line) {
		if (preg_match('/^\s*date\.timezone\s*=\s*/', $line, $m)) {
			continue;
		}
		$out .= rtrim($line) . "\n";
	}
	// add timezone
	$out .= "date.timezone = '{$tz}'\n";
	// write php.ini
	if (!file_put_contents_safe("/usr/local/etc/php.ini", $out)) {
		return FALSE;
	}
	return TRUE;
}
?>