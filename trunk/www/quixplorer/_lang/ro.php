<?php
/*
	ro.php

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
// Romanian Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "EROARE(I)",
	"back"			=> "Înapoi",
	
	// root
	"home"			=> "Directorul implicit nu existã, verificã-þi parametrii.",
	"abovehome"		=> "Directorul curent ar putea sã nu fie deasupra directorului implicit.",
	"targetabovehome"	=> "Directorul þintã ar putea sã nu fie deasupra directorului implicit.",
	
	// exist
	"direxist"		=> "Acest director nu existã.",
	//"filedoesexist"	=> "Acest fiºier existã deja.",
	"fileexist"		=> "Acest fiºier nu existã.",
	"itemdoesexist"	=> "Acest element existã deja.",
	"itemexist"		=> "Acest element nu existã.",
	"targetexist"		=> "Directorul þintã nu existã.",
	"targetdoesexist"	=> "Elementul þintã existã deja.",
	
	// open
	"opendir"		=> "Nu pot deschide directorul.",
	"readdir"		=> "Nu pot citi directorul.",
	
	// access
	"accessdir"		=> "Nu ai permisiunea de a accesa acest director.",
	"accessfile"		=> "Nu ai permisiunea de a accesa acest fiºier.",
	"accessitem"		=> "Nu eºti autorizat sã accesezi acest element.",
	"accessfunc"		=> "Nu eºti autorizat sã foloseºti aceastã funcþie.",
	"accesstarget"	=> "Nu eºti autorizat sã accesezi directorul þintã.",
	
	// actions
	"permread"		=> "Obþinerea permisiunii a eºuat.",
	"permchange"		=> "Schimbarea permisiunii a eºuat.",
	"openfile"		=> "Deschiderea fiºierului a eºuat.",
	"savefile"		=> "Salvarea fiºierului a eºuat.",
	"createfile"		=> "Crearea fiºierului a eºuat.",
	"createdir"		=> "Crearea directorului a esuat.",
	"uploadfile"		=> "Încãrcarea fiºierului a eºuat.",
	"copyitem"		=> "Copierea a eºuat.",
	"moveitem"		=> "Mutarea fiºierului a eºuat.",
	"delitem"		=> "ªtergerea a eºuat.",
	"chpass"		=> "Schimbarea parolei a eªuat.",
	"deluser"		=> "ªtergerea utilizatorului a eºuat.",
	"adduser"		=> "Adãugarea utilizatorului a eºuat.",
	"saveuser"		=> "Salvarea utilizatorului a eºuat.",
	"searchnothing"	=> "Trebuie sã defineºti ce trebuie cãutat.",
	
	// misc
	"miscnofunc"		=> "Funcþie indisponibilã.",
	"miscfilesize"	=> "Fiºierul depãºeºte dimensiunea maximã.",
	"miscfilepart"	=> "Fiºierul a fost încãrcat parþial.",
	"miscnoname"		=> "Trebuie sã furnizezi un nume.",
	"miscselitems"	=> "Nu ai selectat nici un element.",
	"miscdelitems"	=> "Sigur vrei sã ºtergi acest(e) \"+num+\" element(e)?",
	"miscdeluser"		=> "Sigur vrei sã ºtergi utilizatorul '\"+user+\"'?",
	"miscnopassdiff"	=> "Parola nouã nu diferã de cea curentã.",
	"miscnopassmatch"	=> "Parolele nu sunt identice.",
	"miscfieldmissed"	=> "Ai sãrit un câmp important.",
	"miscnouserpass"	=> "Utilizator sau parolã incorect(ã).",
	"miscselfremove"	=> "Nu te poþi ºterge pe tine insuþi.",
	"miscuserexist"	=> "Utilizatorul existã deja.",
	"miscnofinduser"	=> "Nu gãsesc utilizatorul.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "SCHIMBARE PERMISIUNI",
	"editlink"		=> "EDITARE",
	"downlink"		=> "DESCÃRCARE",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "SUS",
	"homelink"		=> "ACASÃ",
	"reloadlink"		=> "REÎNCÃRCARE",
	"copylink"		=> "COPIERE",
	"movelink"		=> "MUTARE",
	"dellink"		=> "ªTERGERE",
	"comprlink"		=> "ARHIVÃ",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "DELOGARE",
	"uploadlink"		=> "ÎNCÃRCARE",
	"searchlink"		=> "CÃUTARE",
	"unziplink"		=> "UNZIP",
	
	// list
	"nameheader"		=> "Nume",
	"sizeheader"		=> "Dimensiune",
	"typeheader"		=> "Tip",
	"modifheader"		=> "Modificat",
	"permheader"		=> "Permisiuni",
	"actionheader"	=> "Acþiuni",
	"pathheader"		=> "Cale",
	
	// buttons
	"btncancel"		=> "Anulare",
	"btnsave"		=> "Salvare",
	"btnchange"		=> "Modificare",
	"btnreset"		=> "Resetare",
	"btnclose"		=> "Închide",
	"btncreate"		=> "Creeazã",
	"btnsearch"		=> "Cautã",
	"btnupload"		=> "Încãrcare",
	"btncopy"		=> "Copiere",
	"btnmove"		=> "Mutare",
	"btnlogin"		=> "Logare",
	"btnlogout"		=> "Delogare",
	"btnadd"		=> "Adãugare",
	"btnedit"		=> "Editare",
	"btnremove"		=> "ªtergere",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Director",
	"actperms"		=> "Schimbare permisiuni",
	"actedit"		=> "Editare fiºier",
	"actsearchresults"	=> "Cãutare rezultate",
	"actcopyitems"	=> "Copiere element(e)",
	"actcopyfrom"		=> "Copiere din /%s în /%s ",
	"actmoveitems"	=> "Mutare element(e)",
	"actmovefrom"		=> "Mutare din /%s în /%s ",
	"actlogin"		=> "Logare",
	"actloginheader"	=> "Logare pentru folosirea QuiXplorer",
	"actadmin"		=> "Administrare",
	"actchpwd"		=> "Schimbare parolã",
	"actusers"		=> "Utilizatori",
	"actarchive"		=> "Archivare element(e)",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Încãrcare fiºier(e)",
	
	// misc
	"miscitems"		=> "Element(e)",
	"miscfree"		=> "Liber",
	"miscusername"	=> "Utilizator",
	"miscpassword"	=> "Parola",
	"miscoldpass"		=> "Parola veche",
	"miscnewpass"		=> "Parola nouã",
	"miscconfpass"	=> "Confirmare parolã",
	"miscconfnewpass"	=> "Confirmare parolã nouã",
	"miscchpass"		=> "Schimbare parolã",
	"mischomedir"		=> "Director implicit",
	"mischomeurl"		=> "URL implicit",
	"miscshowhidden"	=> "Aratã elementele ascunse",
	"mischidepattern"	=> "Ascunde elementul",
	"miscperms"		=> "Permisiuni",
	"miscuseritems"	=> "(nume, director implicit, aratã elementele ascunse, permisiuni, activ)",
	"miscadduser"		=> "adãugare utilizator",
	"miscedituser"	=> "editare utilizator '%s'",
	"miscactive"		=> "Activ",
	"misclang"		=> "Limba",
	"miscnoresult"	=> "Nu existã rezultate disponibile.",
	"miscsubdirs"		=> "Cãutare subdirectoare",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Da","Nu","D","N"),
	"miscchmod"		=> array("Proprietar", "Grup", "Public"),
);
?>