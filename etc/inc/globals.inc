<?php
/*
	globals.inc

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
require_once 'super_fun.inc';
//	system configuration parameter
$g = [
	'varrun_path' => '/var/run',
	'varetc_path' => '/var/etc',
	'vardb_path' => '/var/db',
	"varlog_path" => '/var/log',
	'etc_path' => '/etc',
	'tmp_path' => '/tmp',
	'conf_path' => '/conf',
	'ftmp_path' => '/var/tmp/ftmp',
	'media_path' => '/mnt',
	'conf_default_path' => '/conf.default',
	'cf_path' => '/cf',
	'cf_conf_path' => '/cf/conf',
	'www_path' => '/usr/local/www',
	'xml_rootobj' => 'nas4free',
	'debug' => false,
	'latest_config' => '2.9',
	'wireless_regex' => '/^(ndis|wlan|an|ath|bwi|bwn|ipw|iwi|iwn|malo|mwl|ral|wi|wpi|rsu|rum|run|uath|upgt|ural|urtw|urtwn|usie|zyd)/',
	'default_passwd' => 'nas4free',
	'default_ip' => '192.168.1.250'
];
// directory of images
$g_img= [
	'add' => 'images/add.png',
	'mod' => 'images/edit.png',
	'del' => 'images/delete.png',
	'loc' => 'images/locked.png',
	'unl' => 'images/unlocked.png',
	'mai' => 'images/maintain.png',
	'inf' => 'images/info.png',
	'ena' => 'images/status_enabled.png',
	'dis' => 'images/status_disabled.png',
	'box.error' => 'images/error_box.png',
	'box.info' => 'images/info_box.png',
	'box.warning' => 'images/warn_box.png'
];
//	installation parameter
$g_install = [
	"default_swap" => "1024",
	"part1min_embedded" => "466",	/*  500MB= 476MiB ->  470MB - alignment */
	"part1size_embedded" => "1024",	/* 2000MB=1907MiB ->  768MB + SWAP1024MB */
	"part1size_full" => "3072"	/* 4000MB=3814MiB -> 2560MB + SWAP1024MB */
];
//	available languages/locales
$g_languages = [
	'af'    => ['codeset' => 'UTF-8', 'desc' => gtext('Afrikaans'), 'desc.localized' => 'Afrikaans'],
	'bg'    => ['codeset' => 'UTF-8', 'desc' => gtext('Bulgarian'), 'desc.localized' => 'Български'],
	'ca'    => ['codeset' => 'UTF-8', 'desc' => gtext('Catalan'), 'desc.localized' => 'català'],
	'cs'    => ['codeset' => 'UTF-8', 'desc' => gtext('Czech'), 'desc.localized' => 'čeština'],
	'zh_CN' => ['codeset' => 'UTF-8', 'desc' => gtext('Chinese (Simplified)'), 'desc.localized' => '中文（簡體）'],
	'zh_TW' => ['codeset' => 'UTF-8', 'desc' => gtext('Chinese (Traditional)'), 'desc.localized' => '正體中文'],
	'nl'    => ['codeset' => 'UTF-8', 'desc' => gtext('Dutch'), 'desc.localized' => 'Nederlands'],
	'da'    => ['codeset' => 'UTF-8', 'desc' => gtext('Danish'), 'desc.localized' => 'Dansk'],
	'en_US' => ['codeset' => 'UTF-8', 'desc' => gtext('English'), 'desc.localized' => "English"],
	'fi'    => ['codeset' => 'UTF-8', 'desc' => gtext('Finnish'), 'desc.localized' => 'Suomi'],
	'fr'    => ['codeset' => 'UTF-8', 'desc' => gtext('French'), 'desc.localized' => 'Français'],
	'de'    => ['codeset' => 'UTF-8', 'desc' => gtext('German'), 'desc.localized' => 'Deutsch'],
	'el'    => ['codeset' => 'UTF-8', 'desc' => gtext('Greek'), 'desc.localized' => 'Ελληνικά'],
	'hu'    => ['codeset' => 'UTF-8', 'desc' => gtext('Hungarian'), 'desc.localized' => 'Magyar'],
	'it'    => ['codeset' => 'UTF-8', 'desc' => gtext('Italian'), 'desc.localized' => 'Italiano'],
	'ja'    => ['codeset' => 'UTF-8', 'desc' => gtext('Japanese'), 'desc.localized' => '日本語'],
	'ko'    => ['codeset' => 'UTF-8', 'desc' => gtext('Korean'), 'desc.localized' => '한국어'],
	'nb'    => ['codeset' => 'UTF-8', 'desc' => gtext('Norwegian (Bokmal)'), 'desc.localized' => 'Norsk (bokmål)'],
	'pl'    => ['codeset' => 'UTF-8', 'desc' => gtext('Polish'), 'desc.localized' => 'Polski'],
	'pt_BR' => ['codeset' => 'UTF-8', 'desc' => gtext('Portuguese (Brazil)'), 'desc.localized' => 'Português (Brasil)'],
	'pt'    => ['codeset' => 'UTF-8', 'desc' => gtext('Portuguese (Portugal)'), 'desc.localized' => 'Português (Portugal)'],
	'ro'    => ['codeset' => 'UTF-8', 'desc' => gtext('Romanian'), 'desc.localized' => 'Română'],
	'ru'    => ['codeset' => 'UTF-8', 'desc' => gtext('Russian'), 'desc.localized' => 'Русский'],
	'sl'    => ['codeset' => 'UTF-8', 'desc' => gtext('Slovenian'), 'desc.localized' => 'Slovenščina'],
	'es'    => ['codeset' => 'UTF-8', 'desc' => gtext('Spanish'), 'desc.localized' => 'Español'],
	'sv'    => ['codeset' => 'UTF-8', 'desc' => gtext('Swedish'), 'desc.localized' => "Svenska"],
	'sk'    => ['codeset' => 'UTF-8', 'desc' => gtext('Slovak'), 'desc.localized' => 'Slovenský'],
	'tr'    => ['codeset' => 'UTF-8', 'desc' => gtext('Turkish'), 'desc.localized' => 'Türkçe'],
	'uk'    => ['codeset' => 'UTF-8', 'desc' => gtext('Ukrainian'), 'desc.localized' => 'Українська']
];
//	supported filesystems
$g_filesystems = [
	'' => 'Unformated',
	'ufs' => gtext('UFS with Soft Updates'),
	'ufs_no_su' => 'UFS',
	'ufsgpt' => gtext('UFS (GPT and Soft Updates)'),
	'ufsgpt_no_su' => 'UFS (EFI/GPT)',
	'msdos' => 'FAT32',
	'cd9660' => 'CD/DVD',
	'ntfs' => 'NTFS',
	'ext2' => 'EXT2',
	'exfat' => 'exFAT',
	'softraid' => 'Software RAID',
	'geli' => 'Encrypted',
	'zfs' => 'ZFS Storage Pool'
];
//	upload status messages
$g_file_upload_error = [
	UPLOAD_ERR_OK => gtext('There is no error, the file uploaded with success.'),
	UPLOAD_ERR_INI_SIZE => gtext('The uploaded file exceeds the upload_max_filesize directive in php.ini.'),
	UPLOAD_ERR_FORM_SIZE => gtext('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.'),
	UPLOAD_ERR_PARTIAL => gtext('The uploaded file was only partially uploaded.'),
	UPLOAD_ERR_NO_FILE => gtext('No file was uploaded.'),
	UPLOAD_ERR_NO_TMP_DIR => gtext('Missing a temporary folder.'),
	UPLOAD_ERR_CANT_WRITE => gtext('Failed to write file to disk.'),
	UPLOAD_ERR_EXTENSION => gtext('File upload stopped by extension.')
];
?>
