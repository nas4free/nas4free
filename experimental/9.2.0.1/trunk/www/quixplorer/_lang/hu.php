<?php
/*
	hu.php

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
// Hungarian Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "HIBA",
	"back"			=> "Vissza",
	
	// root
	"home"			=> "A home könyvtár nem létezik, ellenőrizd a beállításokat.",
	"abovehome"		=> "A könyvtár nem a home könyvtár alatt van.",
	"targetabovehome"	=> "A célkönyvtár nem a home könyvtár alatt van.",
	
	// exist
	"direxist"		=> "A könyvtár nem létezik.",
	"fileexist"		=> "A fájl nem létezik.",
	"itemdoesexist"	=> "Az elem már létezik.",
	"itemexist"		=> "az elem nem létezik.",
	"targetexist"		=> "A célkönyvtár nem létezik.",
	"targetdoesexist"	=> "A cél már létezik.",
	
	// open
	"opendir"		=> "Nem lehet megnyitni a könyvtárat.",
	"readdir"		=> "Nem lehet olvasni a könyvtárat.",
	
	// access
	"accessdir"		=> "Nem érheted el ezt a könyvtárat.",
	"accessfile"		=> "Nem érheted el ezt a fájlt.",
	"accessitem"		=> "Nem érheted el ezt az elemet.",
	"accessfunc"		=> "Nem érheted el ezt a függvényt.",
	"accesstarget"	=> "Nem érheted el a célkönyvtárat.",
	
	// actions
	"chmod_not_allowed" => "NEM-re állítani a jogokat nem megengedett!",
	"permread"		=> "Jogosultságok beállítása sikertelen.",
	"permchange"		=> "Jogosultságok cseréje sikertelen.",
	"openfile"		=> "Fájl megnyitása sikertelen.",
	"savefile"		=> "Fájl mentése sikertelen.",
	"createfile"		=> "Fájl létrehozása sikertelen.",
	"createdir"		=> "Könyvtár létrehozása sikertelen.",
	"uploadfile"		=> "Fájl feltöltése sikertelen.",
	"copyitem"		=> "Másolás sikertelen.",
	"moveitem"		=> "Mozgatás sikertelen.",
	"delitem"		=> "Törlés sikertelen.",
	"chpass"		=> "Jelszócsere sikertelen.",
	"deluser"		=> "Felhasználó törlése sikertelen.",
	"adduser"		=> "Felhasználó hozzáadása sikertelen.",
	"saveuser"		=> "Felhasználó mentése sikertelen.",
	"searchnothing"	=> "Meg kell adnod valamit, amit keressek.",
	
	// misc
	"miscnofunc"		=> "Függvény nem elérhető.",
	"miscfilesize"	=> "Fájl elérte a maximális méretet.",
	"miscfilepart"	=> "Fájl csak részben került feltöltésre.",
	"miscnoname"		=> "Meg kell adnod egy nevet!",
	"miscselitems"	=> "Nem választottál ki semmit.",
	"miscdelitems"	=> "Biztos törölni akarod ezt a(z) \"+num+\" elemet?",
	"miscdeluser"		=> "Biztos törölni akarod a(z) '\"+user+\"' felhasználót?",
	"miscnopassdiff"	=> "Az új jelszó nem tér el a jelenlegitől.",
	"miscnopassmatch"	=> "Jelszavak nem egyeznek.",
	"miscfieldmissed"	=> "Fontos mező maradt ki.",
	"miscnouserpass"	=> "Nem megfelelő felhasználónév illetve jelszó.",
	"miscselfremove"	=> "Magadat nem tudod törölni.",
	"miscuserexist"	=> "Felhasználó már létezik.",
	"miscnofinduser"	=> "Nincs ilyen felhasználó.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "JOGOSULTSÁGOK MEGVÁLTOZTATÁSA",
	"editlink"		=> "SZERKESZTÉS",
	"downlink"		=> "LETÖLT",
	"download_selected"	=> "KIVÁLASZTOTT FÁJLT LETÖLT",
	"uplink"		=> "FEL",
	"homelink"		=> "HOME",
	"reloadlink"		=> "FRISSÍTÉS",
	"copylink"		=> "MÁSOL",
	"movelink"		=> "MOZGAT",
	"dellink"		=> "TÖRÖL",
	"comprlink"		=> "ARCHIVÁL",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "KIJELENTKEZÉS",
	"uploadlink"		=> "FELTÖLTÉS",
	"searchlink"		=> "KERESÉS",
	"unziplink"		=> "KITÖMÖRÍT",
	
	// list
	"nameheader"		=> "Név",
	"sizeheader"		=> "Méret",
	"typeheader"		=> "Típus",
	"modifheader"		=> "Módosítva",
	"permheader"		=> "Jogosultságok",
	"actionheader"	=> "Műveletek",
	"pathheader"		=> "Útvonal",
	
	// buttons
	"btncancel"		=> "Mégsem",
	"btnsave"		=> "Mentés",
	"btnchange"		=> "Cserél",
	"btnreset"		=> "Mégsem",
	"btnclose"		=> "Bezár",
	"btncreate"		=> "Létrehoz",
	"btnsearch"		=> "Keres",
	"btnupload"		=> "Feltölt",
	"btncopy"		=> "Másol",
	"btnmove"		=> "Mozgaz",
	"btnlogin"		=> "Belép",
	"btnlogout"		=> "Kilép",
	"btnadd"		=> "Hozzáad",
	"btnedit"		=> "Szerkeszt",
	"btnremove"		=> "Eltávolít",
	"btnunzip"		=> "Kitömörít",
	
	// actions
	"actdir"		=> "Könyvtár",
	"actperms"		=> "Jogosultságok megváltoztatása",
	"actedit"		=> "Fájl szerkesztése",
	"actsearchresults"	=> "Keresési eredmények",
	"actcopyitems"	=> "Elem(ek) másolása",
	"actcopyfrom"		=> "Másolás /%s - /%s ",
	"actmoveitems"	=> "Elem(ek) mozgatása",
	"actmovefrom"		=> "Mozgatás /%s - /%s ",
	"actlogin"		=> "Bejelentkezés",
	"actloginheader"	=> "Bejelentkezés a Fájl Menedzser használatához",
	"actadmin"		=> "Adminisztráció",
	"actchpwd"		=> "Jelszócsere",
	"actusers"		=> "Felhasználók",
	"actarchive"		=> "Archív elem(ek)",
    "actunzipitem"	=> "Kicsomagolás",
	"actupload"		=> "Fájl(ok) feltöltése",
	
	// misc
	"miscitems"		=> "Elem",
	"miscfree"		=> "Szabad",
	"miscusername"	=> "Felhasználónév",
	"miscpassword"	=> "Jelszó",
	"miscoldpass"		=> "Jelenlegi jelszó",
	"miscnewpass"		=> "Új jelszó",
	"miscconfpass"	=> "Jelszó megerősítése",
	"miscconfnewpass"	=> "Új jelszó megerősítése",
	"miscchpass"		=> "Jelszó cseréje",
	"mischomedir"		=> "Home könyvtár",
	"mischomeurl"		=> "Home URL",
	"miscshowhidden"	=> "Rejtett elemeket láthatóak",
	"mischidepattern"	=> "Rejtett minták",
	"miscperms"		=> "Jogosultságok",
	"miscuseritems"	=> "(név, home könyvtár, rejtett elemeket láthatóak, jogosultságok, aktív)",
	"miscadduser"		=> "felhasználó hozzáadása",
	"miscedituser"	=> "'%s' felhasználó szerkesztése",
	"miscactive"		=> "Aktivál",
	"misclang"		=> "Nyelv",
	"miscnoresult"	=> "Nincs elérhető eredmény.",
	"miscsubdirs"		=> "Keresési alkönyvtárak",
	"miscpermissions"	=> array(
					"read"		=> array("Olvasás", "A felhasználó olvashat és letölthet fájlt"),
					"create" 	=> array("Írása", "A felhasználó új fájlt hozhat létre"),
					"change"	=> array("Változtatás", "A felhasználó módosíthat (feltölthet, módosíthat) meglévő fájlt"),
					"delete"	=> array("Törlés", "A felhasználó meglévő fájlokat törölhet"),
					"password"	=> array("Jelszó cseréje", "A felhasználó jelszót cserélhet"),
					"admin"		=> array("Rendszergazda", "Teljes hozzáférés"),
			),
	"miscyesno"		=> array("Igen","Nem","I","N"),
	"miscchmod"		=> array("Tulajdonos", "Csoport", "Nyilvános"),
);
?>