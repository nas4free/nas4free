<?php
/*
	archive.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
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
//------------------------------------------------------------------------------
//if($GLOBALS["tar"]) include("./_lib/lib_tar.php");
//if($GLOBALS["tgz"]) include("./_lib/lib_tgz.php");
//

require_once("qxpage.php");
require_once("_lib/zipstream.php");

/**
 * _zip
 * @return void
 **/
function zip_selected_items($zipfilename, $directory, $items)
{
    $zipfile=new ZipArchive();
    $zipfile->open($zipfilename, ZIPARCHIVE::CREATE);
    foreach ($items as $item)
    {
        $srcfile = $directory . DIRECTORY_SEPARATOR . $item;
        if (!$zipfile->addFile($srcfile, $item))
        {
            show_error($srcfile . ": Failed adding item.");
        }
    }

    if (!$zipfile->close())
    {
      show_error($zipfilename . ": Failed saving zipfile.");
    }
}

function zip_items($dir, $name)
{
    $items = qxpage_selected_items();
    if (!preg_match("/\.zip$/", $name))
    {
        $name .= ".zip";
    }
    zip_selected_items(get_abs_item($dir, $name), $dir, $items);
	header("Location: " . make_link("list",$dir,NULL));
}

function zip_download($directory, $items)
{
    $zipfile = new ZipStream("downloads.zip");
    foreach ($items as $item)
    {
        _zipstream_add_file($zipfile, $directory, $item);
    }
    $zipfile->finish();
}

function _zipstream_add_file($zipfile, $directory, $file_to_add)
{
    $filename = $directory.DIRECTORY_SEPARATOR.$file_to_add;

    if (!@file_exists($filename))
    {
        show_error($filename." does not exist");
    }

    if (is_file($filename))
    {
        _debug("adding file $filename");
        return $zipfile->add_file($file_to_add, file_get_contents($filename));
    }

    if (is_dir($filename))
    {
        _debug("adding directory $filename");
        $files = glob($filename.DIRECTORY_SEPARATOR."*");
        foreach ($files as $file)
        {
            $file = str_replace($directory.DIRECTORY_SEPARATOR, "", $file);
            _zipstream_add_file($zipfile, $directory, $file);
        }
        return True;
    }

    _error("don't know how to handle $file_to_add");
    return False;
}

function tar_items($dir,$name) {
	// ...
}
//------------------------------------------------------------------------------
function tgz_items($dir,$name) {
	// ...
}
//------------------------------------------------------------------------------
function archive_items($dir)
{
        include_once "./_include/permissions.php";
	// archive is only allowed if user may change files
	if (!permissions_grant($dir, NULL, "change"))
		show_error($GLOBALS["error_msg"]["accessfunc"]);

	if(!$GLOBALS["zip"] && !$GLOBALS["tar"] && !$GLOBALS["tgz"]) show_error($GLOBALS["error_msg"]["miscnofunc"]);

	if(isset($GLOBALS['__POST']["name"])) {
		$name=basename(stripslashes($GLOBALS['__POST']["name"]));
		if($name=="") show_error($GLOBALS["error_msg"]["miscnoname"]);
		switch($GLOBALS['__POST']["type"]) {
			case "zip":	zip_items($dir,$name);	break;
			case "tar":	tar_items($dir,$name);	break;
			default:		tgz_items($dir,$name);
		}
		header("Location: ".make_link("list",$dir,NULL));
	}

	show_header($GLOBALS["messages"]["actarchive"]);
	echo "<BR><FORM name=\"archform\" method=\"post\" action=\"".make_link("arch",$dir,NULL)."\">\n";

	$cnt=count($GLOBALS['__POST']["selitems"]);
	for($i=0;$i<$cnt;++$i) {
		echo "<INPUT type=\"hidden\" name=\"selitems[]\" value=\"".stripslashes($GLOBALS['__POST']["selitems"][$i])."\">\n";
	}

	echo "<TABLE width=\"300\"><TR><TD>".$GLOBALS["messages"]["nameheader"].":</TD><TD align=\"right\">";
	echo "<INPUT type=\"text\" name=\"name\" size=\"25\"></TD></TR>\n";
	echo "<TR><TD>".$GLOBALS["messages"]["typeheader"].":</TD><TD align=\"right\"><SELECT name=\"type\">\n";
	if($GLOBALS["zip"]) echo "<OPTION value=\"zip\">Zip</OPTION>\n";
	if($GLOBALS["tar"]) echo "<OPTION value=\"tar\">Tar</OPTION>\n";
	if($GLOBALS["tgz"]) echo "<OPTION value=\"tgz\">TGz</OPTION>\n";
	echo "</SELECT></TD></TR>";
	echo "<TR><TD></TD><TD align=\"right\"><INPUT type=\"submit\" value=\"".$GLOBALS["messages"]["btncreate"]."\">\n";
	echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btncancel"];
	echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL)."';\">\n</TD></TR></FORM></TABLE><BR>\n";
?><script language="JavaScript1.2" type="text/javascript">
<!--
	if(document.archform) document.archform.name.focus();
// -->
</script><?php
}
//------------------------------------------------------------------------------
?>
