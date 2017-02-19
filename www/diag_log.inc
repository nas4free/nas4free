<?php
/*
	diag_log.inc

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE NAS4FREE PROJECT ``AS IS'' AND ANY
	EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE NAS4FREE PROJECT OR ITS CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
	THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies,
	either expressed or implied, of the NAS4Free Project.
*/
require_once("globals.inc");
require_once("rc.inc");

// Get the location of the log files (may be modified via rc.conf variables).
$clogdir = rc_getenv_ex("clog_logdir", $g['varlog_path']);
$rsync_client_logfile = rc_getenv_ex("rsync_client_logfile", "{$g['varlog_path']}/rsync_client.log");
$rsync_local_logfile = rc_getenv_ex("rsync_local_logfile", "{$g['varlog_path']}/rsync_local.log");
$fuppes_logfile = rc_getenv_ex("fuppes_logfile", "{$g['varlog_path']}/fuppes.log");
$minidlna_logdir = rc_getenv_ex("minidlna_logdir", "{$g['varlog_path']}");
$minidlna_logfile = "$minidlna_logdir/minidlna.log";
$mtdaapd_logfile = rc_getenv_ex("mtdaapd_logfile", "{$g['varlog_path']}/mt-daapd.log");
$proftpd_modban_logfile = rc_getenv_ex("proftpd_modban_banlog", "{$g['varlog_path']}/proftpd/ban.log");

// The log configuration
$loginfo = array(
	array(
		"visible" => TRUE,
		"desc" => gtext("System"),
		"logfile" => "{$clogdir}/system.log",
		"filename" => "system.log",
		"type" => "clog",
		"size" => "262144",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("SSH"),
		"logfile" => "{$clogdir}/sshd.log",
		"filename" => "sshd.log",
		"type" => "clog",
		"size" => "32768",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("Daemon"),
		"logfile" => "{$clogdir}/daemon.log",
		"filename" => "daemon.log",
		"type" => "clog",
		"size" => "32768",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("FTP"),
		"logfile" => "{$clogdir}/ftp.log",
		"filename" => "ftp.log",
		"type" => "clog",
		"size" => "32768",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => isset($config['ftpd']['mod_ban']['enable']),
		"desc" => gtext("FTP - Ban module"),
		"logfile" => $proftpd_modban_logfile,
		"filename" => "ban.log",
		"type" => "plain",
		"pattern" => "/^(\S+\s+\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 2)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("FTP - File transfer"),
		"logfile" => "{$g['varlog_path']}/xferlog",
		"filename" => "xferlog",
		"type" => "plain",
		"pattern" => "/^(\S+\s+\S+\s+\d+\s+\S+\s+\d+) ((\d+) (\S+) (\d+) (\S+) (a|b) (C|U|T|_) (o|i|d) (a|g|r) (\S+) (\S+) (0|1) (\S+) (c|i))$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("IP"), "class" => "listr", "param" => "", "pmid" => 4),
			array("title" => gtext("User"), "class" => "listr", "param" => "", "pmid" => 11),
//		array("title" => gtext("File"), "class" => "listr", "param" => "", "pmid" => 6),
//		array("title" => gtext("Filesize"), "class" => "listr", "param" => "", "pmid" => 5),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 2)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("S.M.A.R.T."),
		"logfile" => "{$clogdir}/smartd.log",
		"filename" => "smartd.log",
		"type" => "clog",
		"size" => "32768",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("RSYNC - Server"),
		"logfile" => "{$clogdir}/rsyncd.log",
		"filename" => "rsyncd.log",
		"type" => "clog",
		"size" => "32768",
		"pattern" => "/^(\S+\s+\d+\s+\S+)\s+(\S+)\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("User"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("RSYNC - Client"),
		"logfile" => $rsync_client_logfile,
		"filename" => "rsync_client.log",
		"type" => "plain",
		"pattern" => "/^(.*)$/",
		"columns" => array(
			array("title" => gtext("Event"), "class" => "listlr", "param" => "", "pmid" => 1)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("RSYNC - Local"),
		"logfile" => $rsync_local_logfile,
		"filename" => "rsync_local.log",
		"type" => "plain",
		"pattern" => "/^(.*)$/",
		"columns" => array(
			array("title" => gtext("Event"), "class" => "listlr", "param" => "", "pmid" => 1)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("UPnP/DLNA - Fuppes"),
		"logfile" => $fuppes_logfile,
		"filename" => "fuppes.log",
		"type" => "plain",
		"pattern" => "/^(.*)$/",
		"columns" => array(
			array("title" => gtext("Event"), "class" => "listlr", "param" => "", "pmid" => 1)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("UPnP/DLNA - MiniDLNA"),
		"logfile" =>  $minidlna_logfile,
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^\[(.*?)\]\s+(.*?)\..*?:\d+:\s+.*?:\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gtext("DAAP"),
		"logfile" => $mtdaapd_logfile,
		"filename" => "daap.log",
		"type" => "plain",
		"pattern" => "/^(\S+\s+\S+)\s+\S+:\s+(.*)$/",
		"columns" => array(
			array("title" => gtext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gtext("Event"), "class" => "listr", "param" => "", "pmid" => 2)
		))
);

function log_get_contents($logfile, $type) {
	global $config;

	$content = array();

	$param = (isset($config['syslogd']['reverse']) ? "-r " : "");
	$param .= "-n " . $config['syslogd']['nentries'];

	switch ($type) {
		case "clog":
			$searchlog = isset($_POST['searchlog']) ? $_POST['searchlog'] : '';
		    $filter_str = ($searchlog == '') ? "" : "| grep -i \"".htmlspecialchars($searchlog)."\"";
			exec("/usr/sbin/clog {$logfile} | /usr/bin/tail {$param} {$filter_str}", $content);
			break;

		case "plain":
			$searchlog = isset($_POST['searchlog']) ? $_POST['searchlog'] : '';
		    $filter_str = ($searchlog == '') ? "" : "| grep -i \"".htmlspecialchars($searchlog)."\"";
			exec("/bin/cat {$logfile} | /usr/bin/tail {$param} {$filter_str}", $content);
	}

	return $content;
}

function log_display($loginfo) {
	if (!is_array($loginfo))
		return;

	// Create table header
	echo "<tr>";
	foreach ($loginfo['columns'] as $columnk => $columnv) {
		echo "<td {$columnv['param']} class='" . (($columnk == 0) ? "listhdrlr" : "listhdrr") . "'>".$columnv['title']."</td>\n";
	}
	echo "</tr>";

	// Get log file content
	$content = log_get_contents($loginfo['logfile'], $loginfo['type']);
	if (empty($content))
		return;

	// Create table data
	foreach ($content as $contentv) {
		// Skip invalid pattern matches
		$result = preg_match($loginfo['pattern'], $contentv, $matches);
		if ((FALSE === $result) || (0 == $result))
			continue;

		// Skip empty lines
		if (count($loginfo['columns']) == 1 && empty($matches[1]))
			continue;

		echo "<tr valign=\"top\">\n";
		foreach ($loginfo['columns'] as $columnk => $columnv) {
			echo "<td {$columnv['param']} class='{$columnv['class']}'>" . htmlspecialchars($matches[$columnv['pmid']]) . "</td>\n";
		}
		echo "</tr>\n";
	}
}

function log_clear($loginfo) {
	if (!is_array($loginfo))
		return;

	switch ($loginfo['type']) {
		case "clog":
			exec("/usr/sbin/clog -i -s {$loginfo['size']} {$loginfo['logfile']}");
			break;

		case "plain":
			exec("/bin/cat /dev/null > {$loginfo['logfile']}");
	}
}

function log_download($loginfo) {
	$gt_logisempty = gtext('Log file is empty.');
	if (is_array($loginfo)) {
		switch ($loginfo['type']) {
			case "clog":
				if(file_exists($loginfo['logfile'])) {
					exec(sprintf('/usr/sbin/clog %s', escapeshellarg($loginfo['logfile'])), $output);
					$content = implode("\n", $output);
				} else {
					$content = $gt_logisempty;
				}
				$fs = strlen($content);
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename={$loginfo['filename']}");
				header("Content-Length: {$fs}");
				header("Pragma: hack");
				echo $content;
				break;
			case "plain":
				if(file_exists($loginfo['logfile'])) {
					if(false === ($content = file_get_contents($loginfo['logfile']))) {
						$content = $gt_logisempty;
					}
				} else {
					$content = $gt_logisempty;
				}
				if(empty($content)) {
					$content = $gt_logisempty;
				}
				$fs = strlen($content);
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename={$loginfo['filename']}");
				header("Content-Length: {$fs}");
				header("Pragma: hack");
				echo $content;
				break;
		}
	}
}
?>
