<?php
/*
	cs.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
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
// Czech Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "CHYBA(Y)",
	"back"			=> "Zpět",

	// root
	"home"			=> "Domovský adresář neexistuje, opravte své zadání.",
	"abovehome"		=> "Daný adresář nemůže být použit jako domovský adresář.",
	"targetabovehome"	=> "Cílový adresář nemůže být domovským adresářem.",

	// exist
	"direxist"		=> "Adresář neexistuje.",
	"fileexist"		=> "Soubor neexistuje.",
	"itemdoesexist"	=> "Tato položka existuje.",
	"itemexist"		=> "Tato položka neexistuje.",
	"targetexist"		=> "Cílový adresář neexistuje.",
	"targetdoesexist"	=> "Cílová položka existuje.",

	// open
	"opendir"		=> "Nemohu otevřít adresář.",
	"readdir"		=> "Nemohu číst adresář.",

	// access
	"accessdir"		=> "Nemáte povolen přístup do tohoto adresáře.",
	"accessfile"		=> "Nemáte povolen přístup k tomuto souboru.",
	"accessitem"		=> "Nemáte povolen přístup k této položce.",
	"accessfunc"		=> "Nemáte povoleno užití této funkce.",
	"accesstarget"	=> "Nemáte povolen přistup k tomuto cílovému adresáři.",

	// actions
	"chmod_not_allowed" => 'Changing Permissions to NONE is not allowed!',
	"permread"		=> "Nastavení práv selhalo.",
	"permchange"		=> "Změna práv selhala.",
	"openfile"		=> "Otevření souboru selhalo.",
	"savefile"		=> "Uložení souboru selhalo.",
	"createfile"		=> "Vytvoření souboru selhalo.",
	"createdir"		=> "Vytvoření adresáře selhalo.",
	"uploadfile"		=> "Nahrání souboru se nezdařilo.",
	"copyitem"		=> "Kopírování selhalo.",
	"moveitem"		=> "Přesun se nezdařil.",
	"delitem"		=> "Smazání se nezdařilo.",
	"chpass"		=> "Změna hesla se nezdařila.",
	"deluser"		=> "Smazání uživatele se nezdařilo.",
	"adduser"		=> "Přidání uživatele se nezdařilo.",
	"saveuser"		=> "Uložení uživatele se nezdařilo.",
	"searchnothing"	=> "Musíte zadat název hledaného souboru/adresáře.",

	// misc
	"miscnofunc"		=> "Funkce nepřístupná.",
	"miscfilesize"	=> "Soubor překračuje maximální velikost.",
	"miscfilepart"	=> "Soubor byl uložen pouze částečně.",
	"miscnoname"		=> "Musíte zadat jméno.",
	"miscselitems"	=> "Nevybral jste žádnou položku(y).",
	"miscdelitems"	=> "Jste si jisti, že chcete smazat tuto \"+num+\" položku(y)?",
	"miscdeluser"		=> "Jste si jisti, že chcete smazat tohoto uživatele '\"+user+\"'?",
	"miscnopassdiff"	=> "Nové heslo nesouhlasí s původním.",
	"miscnopassmatch"	=> "Hesla se neshodují.",
	"miscfieldmissed"	=> "Zapomněl jste vyplnit požadované pole.",
	"miscnouserpass"	=> "Zadané jméno nebo heslo je chybné.",
	"miscselfremove"	=> "Nemůžete smazat sám sebe.",
	"miscuserexist"	=> "Uživatel již existuje.",
	"miscnofinduser"	=> "Nemohu najít uživatele.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ZMĚNA PRÁV",
	"editlink"		=> "EDITACE",
	"downlink"		=> "STÁHNOUT",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "VÝŠ",
	"homelink"		=> "ÚVOD",
	"reloadlink"		=> "RELOAD",
	"copylink"		=> "KOPÍROVÁNÍ",
	"movelink"		=> "PŘESUN",
	"dellink"		=> "SMAZAT",
	"comprlink"		=> "ARCHÍV",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "ODHLÁŠENÍ",
	"uploadlink"		=> "NAHRÁT",
	"searchlink"		=> "VYHLEDAT",
	"unziplink"		=> "UNZIP",

	// list
	"nameheader"		=> "Název",
	"sizeheader"		=> "Velikost",
	"typeheader"		=> "Typ",
	"modifheader"		=> "Upraveno",
	"permheader"		=> "Práva",
	"actionheader"	=> "Akce",
	"pathheader"		=> "Cesta",

	// buttons
	"btncancel"		=> "Zrušit",
	"btnsave"		=> "Uložit",
	"btnchange"		=> "Změnit",
	"btnreset"		=> "Reset",
	"btnclose"		=> "Zavřít",
	"btncreate"		=> "Vytvořit",
	"btnsearch"		=> "Vyhledat",
	"btnupload"		=> "Nahrát",
	"btncopy"		=> "Kopírovat",
	"btnmove"		=> "Přesunout",
	"btnlogin"		=> "Přihlásit",
	"btnlogout"		=> "Odhlásit",
	"btnadd"		=> "Přidat",
	"btnedit"		=> "Editovat",
	"btnremove"		=> "Smazat",
	"btnunzip"		=> "Unzip",

	// actions
	"actdir"		=> "Adresář",
	"actperms"		=> "Změna práv",
	"actedit"		=> "Editace souboru",
	"actsearchresults"	=> "Najít výsledky",
	"actcopyitems"	=> "Kopírovat položku(y)",
	"actcopyfrom"		=> "Kopírovat z /%s do /%s ",
	"actmoveitems"	=> "Přesunout položku(y)",
	"actmovefrom"		=> "Přesunout z /%s do /%s ",
	"actlogin"		=> "Přihlásit k FTP ADASERVIS s.r.o.",
	"actloginheader"	=> "WEB/FTP QuiXplorer",
	"actadmin"		=> "Administrace",
	"actchpwd"		=> "Změna hesla",
	"actusers"		=> "Uživatelé",
	"actarchive"		=> "Archív položek",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Nahrát soubror(y)",

	// misc
	"miscitems"		=> "Položka(y)",
	"miscfree"		=> "Free",
	"miscusername"	=> "Jméno",
	"miscpassword"	=> "Heslo",
	"miscoldpass"		=> "Staré heslo",
	"miscnewpass"		=> "Nové heslo",
	"miscconfpass"	=> "Potvrdit heslo",
	"miscconfnewpass"	=> "Potvrdit nové heslo",
	"miscchpass"		=> "Změnit heslo",
	"mischomedir"		=> "Domovský adresář",
	"mischomeurl"		=> "Domovké URL",
	"miscshowhidden"	=> "Zobrazit skryté položky",
	"mischidepattern"	=> "Skrýt vzor",
	"miscperms"		=> "Práva",
	"miscuseritems"	=> "(jméno, domovský adresář, zobrazit skryté položky, práva, aktivní)",
	"miscadduser"		=> "Přidat uživatele",
	"miscedituser"	=> "Editovat uživatele '%s'",
	"miscactive"		=> "Aktivní",
	"misclang"		=> "Jazyk",
	"miscnoresult"	=> "Nenalezeny žádné výsledky.",
	"miscsubdirs"		=> "Hledat podadresáře",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Ano","Ne","A","N"),
	"miscchmod"		=> array("Vlastník", "Skupina", "Veřejné"),
);

?>