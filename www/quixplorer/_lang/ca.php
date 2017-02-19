<?php
/*
	ca.php

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
// Catalan Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "A/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "ERROR(S)",
	"back"			=> "Enrrere",

	// root
	"home"			=> "El directori principal no existeix, comprova la configuració.",
	"abovehome"		=> "El directori actual no pot estar per sobre del directori principal.",
	"targetabovehome"	=> "El directori de destinació no pot estar per sobre del directori d'inici.",

	// exist
	"direxist"		=> "Aquest directori no existeix.",
	"fileexist"		=> "Aquest arxiu no existeix.",
	"itemdoesexist"		=> "Aquest objecte ja existeix.",
	"itemexist"		=> "Aquest objecte no existeix.",
	"targetexist"		=> "El directori de destinació no existeix.",
	"targetdoesexist"	=> "El objecte de destinació ja existeix.",

	// open
	"opendir"		=> "No es pot obrir el directori.",
	"readdir"		=> "No es pot llegir el directori.",

	// access
	"accessdir"		=> "No se li permet accedir a aquest directori.",
	"accessfile"		=> "No se li permet accedir al fitxer.",
	"accessitem"		=> "No se li permet accedir al objecte.",
	"accessfunc"		=> "No se li permet fer us d'aquesta funció.",
	"accesstarget"		=> "No se li permet accedir al directori de destinació.",

	// actions
	"chmod_not_allowed"	=> 'No està permès canviar permisos a cap!',
	"permread"		=> "Obtenció de permisos fallida.",
	"permchange"		=> "Canvi de permisos fallit.",
	"openfile"		=> "Obertura d'arxius ha fallat.",
	"savefile"		=> "Desat d'arxius ha fallat.",
	"createfile"		=> "Error en la creació de l'arxiu.",
	"createdir"		=> "Error en la creació de directori.",
	"uploadfile"		=> "La càrrega de fitxers ha fallat.",
	"copyitem"		=> "Còpia ha fallat.",
	"moveitem"		=> "El moure ha fallat.",
	"delitem"		=> "Ha fallat l'esborrat.",
	"chpass"		=> "El canvi de contrasenya ha fallat.",
	"deluser"		=> "L'eliminació de usuari ha fallat.",
	"adduser"		=> "L'afegir de nou usuari ha fallat.",
	"saveuser"		=> "El desat de nou usuari ha fallat.",
	"searchnothing"		=> "Has de introduir quelcom per buscar.",

	// misc
	"miscnofunc"		=> "Funció no disponible.",
	"miscfilesize"		=> "Arxiu supera el tamany màxim.",
	"miscfilepart"		=> "L'arxiu es va pujar només parcialment.",
	"miscnoname"		=> "Has de introduir un nom.",
	"miscselitems"		=> "No has sleccionat cap objecte (s).",
	"miscdelitems"		=> "Estàs segur per eliminar aquests \"+num+\" objecte(s)?",
	"miscdeluser"		=> "Estàs segur que vols eliminar l'usuari '\"+user+\"'?",
	"miscnopassdiff"	=> "La nova contrasenya no es diferent de l'actual.",
	"miscnopassmatch"	=> "Les contrasenyas no coincideixen.",
	"miscfieldmissed"	=> "Has oblidat un camp important.",
	"miscnouserpass"	=> "Nom d'usuari o contrasenya no correcte.",
	"miscselfremove"	=> "No et pots eliminar a tu mateix.",
	"miscuserexist"		=> "Ja existeix l'usuari.",
	"miscnofinduser"	=> "No es trova l'usuari.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "CANVIA PERMISOS",
	"editlink"		=> "EDITA",
	"downlink"		=> "DESCARREGA",
	"download_selected"	=> "DESCÀRREGA D'ARXIUS SELECCIONADA",
	"uplink"		=> "AMUNT",
	"homelink"		=> "INICI",
	"reloadlink"		=> "RECARGA",
	"copylink"		=> "COPIA",
	"movelink"		=> "PASSA",
	"dellink"		=> "ESBORRAR",
	"comprlink"		=> "ARXIU",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "SURT DE SESSIÓ",
	"uploadlink"		=> "CARREGA",
	"searchlink"		=> "RECERCA",
	"unziplink"		=> "DESCOMPRIMEIX",

	// list
	"nameheader"		=> "Nom",
	"sizeheader"		=> "Tamany",
	"typeheader"		=> "Tipus",
	"modifheader"		=> "Modificat",
	"permheader"		=> "Permisos",
	"actionheader"		=> "Accions",
	"pathheader"		=> "Senda",

	// buttons
	"btncancel"		=> "Cancel.la",
	"btnsave"		=> "Desa",
	"btnchange"		=> "Canviar",
	"btnreset"		=> "Reinicia",
	"btnclose"		=> "Tancar",
	"btncreate"		=> "Crea",
	"btnsearch"		=> "Busca",
	"btnupload"		=> "Carrega",
	"btncopy"		=> "Copia",
	"btnmove"		=> "Passa",
	"btnlogin"		=> "Inicia Sessió",
	"btnlogout"		=> "Tanca Sessió",
	"btnadd"		=> "Afegeix",
	"btnedit"		=> "Edita",
	"btnremove"		=> "Elimina",
	"btnunzip"		=> "Descomprimeix",

	// actions
	"actdir"		=> "Directori",
	"actperms"		=> "Canvia permisos",
	"actedit"		=> "Edita arxiu",
	"actsearchresults"	=> "Recerca resultats",
	"actcopyitems"		=> "Copiar objecte (s)",
	"actcopyfrom"		=> "Copiar des de /%s a /%s ",
	"actmoveitems"		=> "Passar objecte (s)",
	"actmovefrom"		=> "Passar des de /%s a /%s ",
	"actlogin"		=> "Inici de sessió",
	"actloginheader"	=> "Inici de sessió per usar l'Administrador d'arxius",
	"actadmin"		=> "Administració",
	"actchpwd"		=> "Canvi Contransenya",
	"actusers"		=> "Usuaris",
	"actarchive"		=> "Objecte (s) d'arxius",
	"actunzipitem"		=> "Extraient",
	"actupload"		=> "Càrrega arxiu (s)",

	// misc
	"miscitems"		=> "Objecte(s)",
	"miscfree"		=> "Lliure",
	"miscusername"		=> "Nom d'usuari",
	"miscpassword"		=> "Contrasenya",
	"miscoldpass"		=> "Contrasenya antiga",
	"miscnewpass"		=> "Nova contrasenya",
	"miscconfpass"		=> "Confirmar Contrasenya",
	"miscconfnewpass"	=> "Confirmar nova contrasenya",
	"miscchpass"		=> "Canvia contrasenya",
	"mischomedir"		=> "Directori d'inici",
	"mischomeurl"		=> "URL d'inici",
	"miscshowhidden"	=> "Mostra objectes ocults",
	"mischidepattern"	=> "Oculta patrons",
	"miscperms"		=> "Permisos",
	"miscuseritems"		=> "(nom, directori d'inici, mostra objectes ocults, permisos, actiu)",
	"miscadduser"		=> "Afegir usuari",
	"miscedituser"		=> "editar usuari '%s'",
	"miscactive"		=> "Actiu",
	"misclang"		=> "Idioma",
	"miscnoresult"		=> "No hi ha resultats disponibles.",
	"miscsubdirs"		=> "Subdirectoris de recerca",
	"miscpermissions"	=> array(
					"read"		=> array("Llegir", "L'usuari pot llegir i descarregar un arxiu"),
					"create" 	=> array("Escriure", "L'usuari pot crear un nou arxiu"),
					"change"	=> array("Canvi", "L'usuari pot canviar (carregar, modificar) un arxiu existent"),
					"delete"	=> array("Esborrar", "L'usuari pot esborrar un fitxer existent"),
					"password"	=> array("Canviar contrasenya", "L'usuari pot canviar la contrasenya"),
					"admin"		=> array("Administrador", "Accés total"),
			),
	"miscyesno"		=> array("Si","No","S","N"),
	"miscchmod"		=> array("Propietari", "Grup", "Públic"),
);

?>
