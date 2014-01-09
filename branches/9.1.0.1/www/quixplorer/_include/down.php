<?php
/*
	down.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Authors: quix@free.fr, ck@realtime-projects.com.
	The Initial Developer of the Original Code is The QuiX project.

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
require_once("./_include/permissions.php");
require_once("qxpage.php");

/**
 * download_selected
 * @return void
 **/
function download_selected($dir)
{
    $dir = get_abs_dir($dir);
    global $site_name;
    require_once("_include/archive.php");
    $items = qxpage_selected_items();
    if (count($items) == 1 && is_file($items[0]))
    {
        download_item( $dir, $items[0] );
    }
    else
    {
        zip_download( $dir, $items );
    }
}

// download file
function download_item($dir, $item)
{
	// Security Fix:
	$item=basename($item);

	if (!permissions_grant($dir, $item, "read"))
		show_error($GLOBALS["error_msg"]["accessfunc"]);

	if (!get_is_file($dir,$item))    show_error($item.": ".$GLOBALS["error_msg"]["fileexist"]);
	if (!get_show_item($dir, $item)) show_error($item.": ".$GLOBALS["error_msg"]["accessfile"]);

	$abs_item = get_abs_item($dir,$item);
    _download($abs_item, $item);
}

function _download_header($filename, $filesize = 0)
{
	$browser=id_browser();
	header('Content-Type: '.(($browser=='IE' || $browser=='OPERA')?
		'application/octetstream':'application/octet-stream'));
	header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Transfer-Encoding: binary');
    if ($filesize != 0)
    {
        header('Content-Length: '.$filesize);
    }
    header('Content-Disposition: attachment; filename="'.$filename.'"');
	if($browser=='IE') {
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
	} else {
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
	}
}

function _download($file, $localname)
{
    _download_header($localname, @filesize($file));
	@readfile($file);
	exit;
}

?>
