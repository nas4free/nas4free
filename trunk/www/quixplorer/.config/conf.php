<?php
	require_once("config.inc");
//------------------------------------------------------------------------------
// Configuration Variables

	// login to use QuiXplorer: (true/false)
	$GLOBALS["require_login"] = true;

	// language: (en, de, es, fr, it, ja, nl, pl, ru)
	$GLOBALS["language"] = "en";

	// the filename of the QuiXplorer script: (you rarely need to change this)
	$GLOBALS["script_name"] = "{$config['system']['webgui']['protocol']}://".$GLOBALS['__SERVER']['HTTP_HOST'].$GLOBALS['__SERVER']["PHP_SELF"];

	// allow Zip, Tar, TGz -> Only (experimental) Zip-support
	$GLOBALS["zip"] = false;	//function_exists("gzcompress");
	$GLOBALS["tar"] = false;
	$GLOBALS["tgz"] = false;
	
	// QuiXplorer version:
	$GLOBALS["version"] = "2.3.2";
//------------------------------------------------------------------------------
// Global User Variables (used when $require_login==false)
	
	// the home directory for the filemanager: (use '/', not '\' or '\\', no trailing '/')
	$GLOBALS["home_dir"] = "/";
	
	// the url corresponding with the home directory: (no trailing '/')
	$GLOBALS["home_url"] = "{$config['system']['webgui']['protocol']}://localhost/~you";
	
	// show hidden files in QuiXplorer: (hide files starting with '.', as in Linux/UNIX)
	$GLOBALS["show_hidden"] = true;
	
	// filenames not allowed to access: (uses PCRE regex syntax)
	$GLOBALS["no_access"] = "^\.ht";
	
	// user permissions bitfield: (1=modify, 2=password, 4=admin, add the numbers)
	$GLOBALS["permissions"] = 7;
//------------------------------------------------------------------------------

	// Adding values for each language to this array changes
	// the login prompt message from the language-specific file.
	// If there is no value for a language here, the default value
	// of the language file is used.
	$GLOBALS["login_prompt"] = array(
		"de"	=> "Willkommen beim Download-Server",
		"nl"	=> "Login File Manager",
		"en"	=> "Login to use File Manager");

/* NOTE:
	Users can be defined by using the Admin-section,
	or in the file ".config/.htusers.php".
	For more information about PCRE Regex Syntax,
	go to http://www.php.net/pcre.pattern.syntax
*/
//------------------------------------------------------------------------------
?>
