<?php
/*
	globals.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard (olivier@freenas.org).
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

$g = array(
	"varrun_path" => "/var/run",
	"varetc_path" => "/var/etc",
	"vardb_path" => "/var/db",
	"varlog_path" => "/var/log",
	"etc_path" => "/etc",
	"tmp_path" => "/tmp",
	"conf_path" => "/conf",
	"ftmp_path" => "/var/tmp/ftmp",
	"media_path" => "/mnt",
	"conf_default_path" => "/conf.default",
	"cf_path" => "/cf",
	"cf_conf_path" => "/cf/conf",
	"www_path" => "/usr/local/www",
	"xml_rootobj" => "nas4free",
	"debug" => false,
	"latest_config" => "2.0",
	"wireless_regex" => "/^(ndis|wlan|an|ath|bwi|bwn|ipw|iwi|iwn|malo|mwl|ral|wi|wpi|rsu|rum|run|uath|upgt|ural|urtw|urtwn|usie|zyd)/",
	"default_passwd" => "nas4free",
	"default_ip" => "192.168.1.250"
);

$g_install = array(
	"default_swap" => "1024",
	"part1min_embedded" => "466",	/*  500MB= 476MiB ->  470MB - alignment */
	"part1size_embedded" => "800",	/* 2000MB=1907MiB ->  768MB + SWAP1024MB */
	"part1size_full" => "2600"	/* 4000MB=3814MiB -> 2560MB + SWAP1024MB */
);

/* Available languages/locales */
$g_languages = array (
	"sq"    => array("codeset" => "UTF-8", "desc" => gettext("Albanian")),
	"bg"    => array("codeset" => "UTF-8", "desc" => gettext("Bulgarian")),
	"cs"    => array("codeset" => "UTF-8", "desc" => gettext("Czech")),
	"zh_CN" => array("codeset" => "UTF-8", "desc" => gettext("Chinese (Simplified)")),
	"zh_TW" => array("codeset" => "UTF-8", "desc" => gettext("Chinese (Traditional)")),
	"nl"    => array("codeset" => "UTF-8", "desc" => gettext("Dutch")),
	"da"    => array("codeset" => "UTF-8", "desc" => gettext("Danish")),
	"en_US" => array("codeset" => "UTF-8", "desc" => gettext("English")),
	"fi"    => array("codeset" => "UTF-8", "desc" => gettext("Finnish")),
	"fr"    => array("codeset" => "UTF-8", "desc" => gettext("French")),
	"de"    => array("codeset" => "UTF-8", "desc" => gettext("German")),
	"el"    => array("codeset" => "UTF-8", "desc" => gettext("Greek")),
	"hu"    => array("codeset" => "UTF-8", "desc" => gettext("Hungarian")),
	"it"    => array("codeset" => "UTF-8", "desc" => gettext("Italian")),
	"ja"    => array("codeset" => "UTF-8", "desc" => gettext("Japanese")),
	"ko"    => array("codeset" => "UTF-8", "desc" => gettext("Korean")),
	"lv"    => array("codeset" => "UTF-8", "desc" => gettext("Latvian")),
	"nb"    => array("codeset" => "UTF-8", "desc" => gettext("Norwegian (Bokmal)")),
	"pl"    => array("codeset" => "UTF-8", "desc" => gettext("Polish")),
	"pt_BR" => array("codeset" => "UTF-8", "desc" => gettext("Portuguese (Brazil)")),
	"pt"    => array("codeset" => "UTF-8", "desc" => gettext("Portuguese (Portugal)")),
	"ro"    => array("codeset" => "UTF-8", "desc" => gettext("Romanian")),
	"ru"    => array("codeset" => "UTF-8", "desc" => gettext("Russian")),
	"sl"    => array("codeset" => "UTF-8", "desc" => gettext("Slovenian")),
	"es"    => array("codeset" => "UTF-8", "desc" => gettext("Spanish")),
	"sv"    => array("codeset" => "UTF-8", "desc" => gettext("Swedish")),
	"sk"    => array("codeset" => "UTF-8", "desc" => gettext("Slovak")),
	"tr"    => array("codeset" => "UTF-8", "desc" => gettext("Turkish")),
	"uk"    => array("codeset" => "UTF-8", "desc" => gettext("Ukrainian"))
);

/* Supported filesystems */
$g_filesystems = array(
	"" => "Unformated",
	"ufs" => "UFS with Soft Updates",
	"ufs_no_su" => "UFS",
	"ufsgpt" => "UFS (GPT and Soft Updates)",
	"ufsgpt_no_su" => "UFS (EFI/GPT)",
	"msdos" => "FAT32",
	"cd9660" => "CD/DVD",
	"ntfs" => "NTFS",
	"ext2" => "EXT2",
	"softraid" => "Software RAID",
	"geli" => "Encrypted",
	"zfs" => "ZFS storage pool device"
);

$g_file_upload_error = array(
	UPLOAD_ERR_OK => gettext("There is no error, the file uploaded with success."),
	UPLOAD_ERR_INI_SIZE => gettext("The uploaded file exceeds the upload_max_filesize directive in php.ini."),
	UPLOAD_ERR_FORM_SIZE => gettext("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form."),
	UPLOAD_ERR_PARTIAL => gettext("The uploaded file was only partially uploaded."),
	UPLOAD_ERR_NO_FILE => gettext("No file was uploaded."),
	UPLOAD_ERR_NO_TMP_DIR => gettext("Missing a temporary folder."),
	UPLOAD_ERR_CANT_WRITE => gettext("Failed to write file to disk."),
	UPLOAD_ERR_EXTENSION => gettext("File upload stopped by extension.")
);
?>
