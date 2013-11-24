<?php
/*
	unzip.php

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
require_once("./_include/permissions.php");
require_once("./_include/debug.php");

function dir_list($dir) {			// make list of directories
	// this list is used to copy/move items to a specific location
	$dir_list = array();
	$handle = @opendir(get_abs_dir($dir));
	if($handle===false) return;		// unable to open dir
	
	while(($new_item=readdir($handle))!==false) {
		//if(!@file_exists(get_abs_item($dir, $new_item))) continue;
		
		if(!get_show_item($dir, $new_item)) continue;
		if(!get_is_dir($dir,$new_item)) continue;
		$dir_list[$new_item] = $new_item;
	}
	
	// sort
	if(is_array($dir_list)) ksort($dir_list);
	return $dir_list;
}
//------------------------------------------------------------------------------
function dir_print($dir_list, $new_dir) {	// print list of directories
	// this list is used to copy/move items to a specific location
	
	// Link to Parent Directory
	$dir_up = dirname($new_dir);
	if($dir_up==".") $dir_up = "";
	
	echo "<TR><TD><A HREF=\"javascript:NewDir('".$dir_up;
	echo "');\"><IMG border=\"0\" width=\"16\" height=\"16\"";
	echo " align=\"ABSMIDDLE\" src=\"".$GLOBALS["baricons"]["up"]."\" ALT=\"\">&nbsp;..</A><BR></BR></TD></TR>\n";
	
	// Print List Of Target Directories
	if(!is_array($dir_list)) return;
	while(list($new_item,) = each($dir_list)) {
		$s_item=$new_item;	if(strlen($s_item)>40) $s_item=substr($s_item,0,37)."...";
		echo "<TR><TD><A HREF=\"javascript:NewDir('".get_rel_item($new_dir,$new_item).
			"');\"><IMG border=\"0\" width=\"16\" height=\"16\" align=\"ABSMIDDLE\" ".
			"src=\"_img/dir.gif\" ALT=\"\">&nbsp;".$s_item."</A></TD></TR>\n";
	}
}
//------------------------------------------------------------------------------
	// copy/move file/dir
function unzip_item($dir)
{
    _debug("unzip_item($dir)");

    global $home_dir;

	// copy and move are only allowed if the user may read and change files
	if ( !permissions_grant_all( $dir, NULL, array( "read", "create" ) ) )
    { 
        show_error($GLOBALS["error_msg"]["accessfunc"]);
    }
	
	// Vars

	$new_dir = ( isset($GLOBALS['__POST']["new_dir"]) ) ? stripslashes($GLOBALS['__POST']["new_dir"]) : $dir;
	

	$_img = $GLOBALS["baricons"]["unzip"];
	
	// Get Selected Item
	if(!isset($GLOBALS['__POST']["item"]) && isset($GLOBALS['__GET']["item"])){
		$s_item = $GLOBALS['__GET']["item"];
	}elseif(isset($GLOBALS['__POST']["item"])){
		$s_item = $GLOBALS['__POST']["item"];
	}
	
	$dir_extract = "$home_dir/$new_dir";

    if( $new_dir != "")
    {
        $dir_extract .= "/";
    }

	$zip_name = "$home_dir/$dir/$s_item";
	
	// Get New Location & Names
	if ( ! isset( $GLOBALS['__POST']["confirm"] ) || $GLOBALS['__POST']["confirm"] != "true")
    {
		show_header($GLOBALS["messages"]["actunzipitem"]);
		
		// JavaScript for Form:
		// Select new target directory / execute action
?><script language="JavaScript1.2" type="text/javascript">
<!--
	function NewDir(newdir) {
		document.selform.new_dir.value = newdir;
		document.selform.submit();
	}
	
	function Execute() {
		document.selform.confirm.value = "true";
	}
//-->
</script><?php
		
		// "Copy / Move from .. to .."
		$s_dir=$dir;		if(strlen($s_dir)>40) $s_dir="...".substr($s_dir,-37);
		$s_ndir=$new_dir;	if(strlen($s_ndir)>40) $s_ndir="...".substr($s_ndir,-37);
		echo "<!-- dirextr = ".$dir_extract." -->\n";
		echo "<!-- zipname = ".$zip_name." -->\n";
		echo "<BR><CENTER><IMG SRC=\"".$_img."\" align=\"ABSMIDDLE\" ALT=\"\">&nbsp;";
		echo "<IMG SRC=\"".$GLOBALS["baricons"]["unzipto"]."\" align=\"ABSMIDDLE\" ALT=\"\">\n";
		
		// Form for Target Directory & New Names
		echo "<BR><BR><BR><FORM name=\"selform\" method=\"post\" action=\"";
		echo make_link("post",$dir,NULL)."\"><TABLE>\n";
		echo "<INPUT type=\"hidden\" name=\"do_action\" value=\"".$GLOBALS["action"]."\">\n";
		echo "<INPUT type=\"hidden\" name=\"confirm\" value=\"false\">\n";
		//echo "<INPUT type=\"hidden\" name=\"dir\" value=\"n\">\n";
		echo "<INPUT type=\"hidden\" name=\"new_dir\" value=\"".$new_dir."\">\n";
		
		// List Directories to select Target
		dir_print(dir_list($new_dir),$new_dir);
		echo "</TABLE><BR><TABLE>\n";
		
		// Print Text Inputs to change Names
		
		echo "<TR><TD><IMG SRC=\"".$GLOBALS["baricons"]["zip"]."\" align=\"ABSMIDDLE\" ALT=\"\">";
		echo "<INPUT type=\"hidden\" name=\"item\" value=\"".$s_item."\">&nbsp;".$s_item."&nbsp;";
		
		// Submit & Cancel
		echo "</TABLE><BR><TABLE><TR>\n<TD>";
		echo "<INPUT type=\"submit\" value=\"";
		echo $GLOBALS["messages"]["btnunzip"];
		echo "\" onclick=\"javascript:Execute();\"></TD>\n<TD>";
		echo "<input type=\"button\" value=\"".$GLOBALS["messages"]["btncancel"];
		echo "\" onClick=\"javascript:location='".make_link("list",$dir,NULL);
		echo "';\"></TD>\n</TR></FORM></TABLE><BR><BR></BR></BR>\n";
		return;
	}
	
	
	// DO COPY/MOVE
	
	// ALL OK?
	if(!@file_exists(get_abs_dir($new_dir))) show_error($new_dir.": ".$GLOBALS["error_msg"]["targetexist"]);
	if(!get_show_item($new_dir,"")) show_error($new_dir.": ".$GLOBALS["error_msg"]["accesstarget"]);
	if(!down_home(get_abs_dir($new_dir))) show_error($new_dir.": ".$GLOBALS["error_msg"]["targetabovehome"]);
	
	
	// copy / move files
	$err=false;
	/*for($i=0;$i<$cnt;++$i) {
		$tmp = stripslashes($GLOBALS['__POST']["selitems"][$i]);
		$new = basename(stripslashes($GLOBALS['__POST']["newitems"][$i]));
		$abs_item = get_abs_item($dir,$tmp);
		$abs_new_item = get_abs_item($new_dir,$new);
		$items[$i] = $tmp;
	
		// Check
		if($new=="") {
			$error[$i]= $GLOBALS["error_msg"]["miscnoname"];
			$err=true;	continue;
		}
		if(!@file_exists($abs_item)) {
			$error[$i]= $GLOBALS["error_msg"]["itemexist"];
			$err=true;	continue;
		}
		if(!get_show_item($dir, $tmp)) {
			$error[$i]= $GLOBALS["error_msg"]["accessitem"];
			$err=true;	continue;
		}
		if(@file_exists($abs_new_item)) {
			$error[$i]= $GLOBALS["error_msg"]["targetdoesexist"];
			$err=true;	continue;
		}
	*/
		// Copy / Move
		//if($GLOBALS["action"]=="copy") {
		//if($GLOBALS["action"]=="unzip") {
		/*
			if(@is_link($abs_item) || @is_file($abs_item)) {
				// check file-exists to avoid error with 0-size files (PHP 4.3.0)
				$ok=@copy($abs_item,$abs_new_item);	//||@file_exists($abs_new_item);
			} elseif(@is_dir($abs_item)) {
				$ok=copy_dir($abs_item,$abs_new_item);
			}
		*/

        //----------------------------------          print_r($GLOBALS);
                        
    _debug("unzip_item(): Extracting $zip_name to $dir_extract");

    //$dir_extract[0]='/';
    //$dir_extract = '.'. $dir_extract;
    //------------------------------------------------------echo $zip_name.' aa'.$dir_extract.'aa';
    $exx = pathinfo($zip_name, PATHINFO_EXTENSION);

    if ($exx == 'zip')
    {
        $zip = new ZipArchive;
        $res = $zip->open($zip_name);
        if ($res === TRUE)
        {
            $zip->extractTo($dir_extract);
            $zip->close();
        } else
        {
        }
    }
    else
    {
        // gz, tar, bz2, ....
        include_once './_lib/archive.php';
        extArchive::extract($zip_name,$dir_extract);
    }

    // FIXME $i is not set anymore.. remove code?
    if ( !isset($i) )
        $i=0;
    if( $res == false )
    {
        $error[$i]=$GLOBALS["error_msg"]["unzip"];
        $err=true;	continue;
    }
		
    $error[$i]=NULL;
	
	if($err)
    {			// there were errors
        $err_msg="";
        for($i=0;$i<$cnt;++$i)
        {
            if($error[$i]==NULL) continue;

            $err_msg .= $items[$i]." : ".$error[$i]."<BR>\n";
        }
        show_error($err_msg);
	}
	
	header("Location: ".make_link("list",$dir,NULL));
}
//------------------------------------------------------------------------------
?>
