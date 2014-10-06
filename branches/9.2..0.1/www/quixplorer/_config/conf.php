<?php
/*
	conf.php

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
//------------------------------------------------------------------------------
// Configuration Variables

	// login to use QuiXplorer: (true/false)
	// if this variable is set to "false", no login is required
	// for access to quixplorer.
	// the functions allowed by the "anonymous" user are defined
	// via the global_permissions setting
	$GLOBALS["require_login"] = true;

	// This variable defines the permissions of anonymous
	// users.
	//
	// If 'require_login' is set to true, this settings are
	// ignored.
	//
	// The detailed permissions are defined in permissions.php
	//
	// A short overview:
	//
	// Value 0x0001 means read only access
	// Value 0x0002 means write only access
	// Value 0x0003 means read / write access
	$GLOBALS["global_permissions"] = 0x0001;

	// language: (en, de, es, fr, it, ja, nl, pl, ru)
	$GLOBALS["language"] = $config['system']['language'];

	// the filename of the QuiXplorer script: (you rarely need to change this)
	$GLOBALS["script_name"] = "{$config['system']['webgui']['protocol']}://".$GLOBALS['__SERVER']['HTTP_HOST'].$GLOBALS['__SERVER']["PHP_SELF"];

	// allow Zip, Tar, TGz -> Only (experimental) Zip-support
	$GLOBALS["zip"] = false;	//function_exists("gzcompress");
	$GLOBALS["tar"] = false;
	$GLOBALS["tgz"] = false;

	$GLOBALS["uploader"] = 'false';

//------------------------------------------------------------------------------
// Global User Variables (used when $require_login==false)

	// the home directory for the filemanager:
	// use forward slashed to seperate directories ('/')
	// not '\' or '\\', no trailing '/'
	// don't use the root directory as home_dir!
	$GLOBALS["home_dir"] = "/";

	// the url corresponding with the home directory: (no trailing '/')
	$GLOBALS["home_url"] = "{$config['system']['webgui']['protocol']}://localhost/~you";

	// show hidden files in QuiXplorer: (hide files starting with '.', as in Linux/UNIX)
	$GLOBALS["show_hidden"] = true;

	// filenames not allowed to access: (uses PCRE regex syntax)
	$GLOBALS["no_access"] = "^\.ht";

//------------------------------------------------------------------------------

	// Adding values for each language to this array changes
	// the login prompt message from the language-specific file.
	// If there is no value for a language here, the default value
	// of the language file is used.
	$GLOBALS["login_prompt"] = array(
		"en"	=> "Welcome to this download server");

	// The title which is displayed in the browser
	$GLOBALS["site_name"] = "My Download Server";

//------------------------------------------------------------------------------
?>
