<?php
/*
	pl.php

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
// Polish Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "B£¡D(ÊDY)",
	"back"			=> "Z Powrotem",

	// root
	"home"			=> "Katalog domowy nie istnieje. Sprawd¼ swoje ustawienia.",
	"abovehome"		=> "Obecny katalog nie mo¿e byæ powy¿ej katalogu domowego.",
	"targetabovehome"	=> "Katalog docelowy nie mo¿e byæ powy¿ej katalogu domowego.",

	// exist
	"direxist"		=> "Ten katalog nie istnieje.",
	//"filedoesexist"	=> "This file already exists.",
	"fileexist"		=> "Ten plik nie istnieje.",
	"itemdoesexist"		=> "Ta pozycja ju¿ istnieje.",
	"itemexist"		=> "Ta pozycja nie istnieje.",
	"targetexist"		=> "Katalog docelowy nie istnieje.",
	"targetdoesexist"	=> "Pozycja docelowa ju¿ istnieje.",

	// open
	"opendir"		=> "Nie mogê otworzyæ katalogu.",
	"readdir"		=> "Nie mogê odczytaæ katalogu.",

	// access
	"accessdir"		=> "Nie masz dostêpu do tego katalogu.",
	"accessfile"		=> "Nie masz dostêpu do tego pliku.",
	"accessitem"		=> "Nie masz dostêpu do tej pozycji.",
	"accessfunc"		=> "Nie masz dostêpu do tej funkcji.",
	"accesstarget"	=> "Nie masz dostêpu do katalogu docelowego.",

	// actions
	"chmod_not_allowed"  => 'Changing Permissions to NONE is not allowed!',
	"permread"		=> "Pobranie uprawnieñ nie uda³o siê.",
	"permchange"		=> "Zmiana uprawnieñ siê nie powiod³a.",
	"openfile"		=> "Otawrcie pliku siê nie powiod³o.",
	"savefile"		=> "Zapis pliku siê nie powiod³o.",
	"createfile"		=> "Utworzenie pliku siê nie powiod³o.",
	"createdir"		=> "Utworzenie katalogu siê nie powiod³o.",
	"uploadfile"		=> "Wrzucanie pliku na serwer siê nie powiod³o.",
	"copyitem"		=> "Kopiowanie siê nie powiod³o.",
	"moveitem"		=> "Przenoszenie siê nie powiod³o.",
	"delitem"		=> "Usuwanie siê nie powiod³o.",
	"chpass"		=> "Zmiana has³a nie powiod³a siê.",
	"deluser"		=> "Usuwanie u¿ytkowika siê nie powiod³o.",
	"adduser"		=> "Dodanie u¿ytkownika siê nie powiod³o.",
	"saveuser"		=> "Zapis u¿ytkownika siê nie powiod³o.",
	"searchnothing"	=> "Musisz dostarczyæ czego¶ do szukania.",

	// misc
	"miscnofunc"		=> "Funkcja niedostêpna.",
	"miscfilesize"	=> "Rozmiar pliku przekroczy³ maksymaln± warto¶æ.",
	"miscfilepart"	=> "Plik zosta³ za³adowany tylko czê¶ciowo.",
	"miscnoname"		=> "Musisz nadaæ nazwê.",
	"miscselitems"	=> "Nie zaznaczy³e¶ ¿adnej pozycji.",
	"miscdelitems"	=> "Jeste¶ pewny ¿e chcesz usun±æ te (\"+num+\") pozycje?",
	"miscdeluser"		=> "Jeste¶ pewny ¿e chcesz usun±æ u¿ytkownika '\"+user+\"'?",
	"miscnopassdiff"	=> "Nowe has³o nie ró¿ni siê od obecnego.",
	"miscnopassmatch"	=> "Podane has³a ró¿ni± siê.",
	"miscfieldmissed"	=> "Opuszczono wa¿ne pole.",
	"miscnouserpass"	=> "U¿ytkownik i has³o s± niezgodne.",
	"miscselfremove"	=> "Nie mo¿esz siebie usun±æ.",
	"miscuserexist"	=> "U¿ytkownik ju¿ istnieje.",
	"miscnofinduser"	=> "U¿ytkownika nie znaleziono.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ZMIANA UPRAWNIEÑ",
	"editlink"		=> "EDYCJA",
	"downlink"		=> "DOWNLOAD",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "KATALOG WY¯EJ",
	"homelink"		=> "KATALOG DOMOWY",
	"reloadlink"		=> "OD¦WIE¯",
	"copylink"		=> "KOPIUJ",
	"movelink"		=> "PRZENIE¦",
	"dellink"		=> "USUÑ",
	"comprlink"		=> "ARCHIWIZUJ",
	"adminlink"		=> "ADMINISTRUJ",
	"logoutlink"		=> "WYLOGUJ",
	"uploadlink"		=> "WRZUÆ PLIK NA SERWER - UPLOAD",
	"searchlink"		=> "SZUKAJ",
	"unziplink"		=> "UNZIP",

	// list
	"nameheader"		=> "Nazwa",
	"sizeheader"		=> "Rozmiar",
	"typeheader"		=> "Typ",
	"modifheader"		=> "Zmodyfikowano",
	"permheader"		=> "Prawa dostêpu",
	"actionheader"	=> "Akcje",
	"pathheader"		=> "¦cie¿ka",

	// buttons
	"btncancel"		=> "Zrezygnuj",
	"btnsave"		=> "Zapisz",
	"btnchange"		=> "Zmieñ",
	"btnreset"		=> "Reset",
	"btnclose"		=> "Zamknij",
	"btncreate"		=> "Utwórz",
	"btnsearch"		=> "Szukaj",
	"btnupload"		=> "Wrzuæ na serwer",
	"btncopy"		=> "Kopiuj",
	"btnmove"		=> "Przenie¶",
	"btnlogin"		=> "Zaloguj",
	"btnlogout"		=> "Wyloguj",
	"btnadd"		=> "Dodaj",
	"btnedit"		=> "Edycja",
	"btnremove"		=> "Usuñ",
	"btnunzip"		=> "Unzip",

	// actions
	"actdir"		=> "Katalog",
	"actperms"		=> "Zmiana uprawnieñ",
	"actedit"		=> "Edycja pliku",
	"actsearchresults"	=> "Rezultaty szukania",
	"actcopyitems"	=> "Kopiuj pozycje",
	"actcopyfrom"		=> "Kpiuj z /%s do /%s ",
	"actmoveitems"	=> "Przenie¶ pozycje",
	"actmovefrom"		=> "Przenie¶ z /%s do /%s ",
	"actlogin"		=> "Nazwa u¿ytkownika",
	"actloginheader"	=> "Zaloguj siê by u¿ywaæ QuiXplorer",
	"actadmin"		=> "Administracja",
	"actchpwd"		=> "Zmieñ has³o",
	"actusers"		=> "U¿ytkownicy",
	"actarchive"		=> "Pozycje zarchiwizowane",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Wrzucanie na serwer- Upload",

	// misc
	"miscitems"		=> " -Ilo¶c elementów",
	"miscfree"		=> "Wolnego miejsca",
	"miscusername"	=> "Nazwa u¿ytkownika",
	"miscpassword"	=> "Has³o",
	"miscoldpass"		=> "Stare has³o",
	"miscnewpass"		=> "Nowe has³o",
	"miscconfpass"	=> "Potwierd¼ has³o",
	"miscconfnewpass"	=> "Potwierd¼ nowe has³o",
	"miscchpass"		=> "Zmieñ has³o",
	"mischomedir"		=> "Katalog g³ówny",
	"mischomeurl"		=> "URL Katalogu domowego",
	"miscshowhidden"	=> "Show hidden items",
	"mischidepattern"	=> "Hide pattern",
	"miscperms"		=> "Uprawnienia",
	"miscuseritems"	=> "(nazwa, katalog domowy, poka¿ pozycje ukryte, uprawnienia, czy aktywny)",
	"miscadduser"		=> "dodaj u¿ytkownika",
	"miscedituser"	=> "edycja u¿ytkownika '%s'",
	"miscactive"		=> "Aktywny",
	"misclang"		=> "Jêzyk",
	"miscnoresult"	=> "Bez rezultatu.",
	"miscsubdirs"		=> "Szukaj w podkatalogach",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Tak","Nie","T","N"),
	"miscchmod"		=> array("W³a¶ciciel", "Grupa", "Publiczny"),
);
?>