<?php
/*
	system_filemanager.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
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
/*------------------------------------------------------------------------------
			QuiXplorer v2.5.8 Modified for NAS4Free
------------------------------------------------------------------------------*/

umask(002); // Added to make created files/dirs group writable

require_once 'qx.php';
require './_include/init.php';	// Init

global $action;

$current_dir = qx_request('dir','');
switch($action): // Execute action
	case 'edit': // EDIT FILE
		require './_include/edit_editarea.php';
		edit_file($current_dir, $GLOBALS['item']);
		break;
	case 'delete': // DELETE FILE(S)/DIR(S)
		require './_include/del.php';
		del_items($current_dir);
		break;
	case 'copy': // COPY/MOVE FILE(S)/DIR(S)
	case 'move': 
		require './_include/copy_move.php';
		copy_move_items($current_dir);
		break;
	case 'download': // DOWNLOAD FILE
		ob_start(); // prevent unwanted output
		require './_include/down.php';
		ob_end_clean(); // get rid of cached unwanted output
		global $item;
		if ($item == ''):
			show_error($GLOBALS['error_msg']['miscselitems']);
		endif;
		download_item($current_dir,$item);
		ob_start(false); // prevent unwanted output
		exit;
		break;
	case 'download_selected':
		ob_start(); // prevent unwanted output
		require './_include/down.php';
		ob_end_clean(); // get rid of cached unwanted output
		download_selected($current_dir);
		ob_start(false); // prevent unwanted output
		exit;
		break;
	case 'unzip': // UNZIP ZIP FILE
		require './_include/unzip.php';
		unzip_item($current_dir);
		break;
	case 'mkitem': // CREATE DIR/FILE
		require './_include/mkitem.php';
		make_item($current_dir);
		break;
	case 'chmod': // CHMOD FILE/DIR
		require './_include/chmod.php';
		chmod_item($current_dir, $GLOBALS['item']);
		break;
	case 'search': // SEARCH FOR FILE(S)/DIR(S)
		require './_include/search.php';
		search_items($current_dir);
		break;
	case 'arch': // CREATE ARCHIVE
		require './_include/archive.php';
		archive_items($current_dir);
		break;
	case 'admin': // USER-ADMINISTRATION
		require './_include/admin.php';
		show_admin($current_dir);
		break;
	case 'login':
	    login();
	    require './_include/list.php';
		list_dir($current_dir);
		break;
	case 'logout':
		logout();
		break;
	case 'list': // DEFAULT: LIST FILES & DIRS
	default:
		require './_include/list.php';
		list_dir($current_dir);
		break;
endswitch;
show_footer();
?>
