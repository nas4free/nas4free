<?php
/*
	it.php

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
// Italian Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "ERRORE(I)",
	"back"			=> "Torna indietro",
	
	// root
	"home"			=> "La directory home non esiste, controlla le impostazioni.",
	"abovehome"		=> "La directory corrente potrebbe non essere sopra al livello della directory home.",
	"targetabovehome"	=> "La directory di destinazione potrebbe non essere sopra al livello della directory home.",
	
	// exist
	"direxist"		=> "Questa cartella non esiste.",
	//"filedoesexist"	=> "Questo file esiste già.",
	"fileexist"		=> "Questo file non esiste.",
	"itemdoesexist"	=> "Questo elemento esiste già.",
	"itemexist"		=> "Questo elemento non esiste.",
	"targetexist"		=> "La directory di destinazione non esiste.",
	"targetdoesexist"	=> "L'elemento di destinazione esiste già.",
	
	// open
	"opendir"		=> "Impossibile aprire la directory.",
	"readdir"		=> "Impossibile leggere la directory.",
	
	// access
	"accessdir"		=> "Non hai il permesso di accedere a questa directory.",
	"accessfile"		=> "Non hai il permesso di accedere a questo file.",
	"accessitem"		=> "Non hai il permesso di accedere a questo elemento.",
	"accessfunc"		=> "Non hai il permesso di usare questa funzione.",
	"accesstarget"	=> "Non hai il permesso di accedere alla directory di destinazione.",
	
	// actions
	"chmod_not_allowed"  => 'Changing Permissions to NONE is not allowed!',
	"permread"		=> "Recupero dei permessi fallito.",
	"permchange"		=> "Cambiamento dei permessi fallito.",
	"openfile"		=> "Apertura del file fallita.",
	"savefile"		=> "Salvataggio del file fallito.",
	"createfile"		=> "Creazione del file fallita.",
	"createdir"		=> "Creazione della directory fallita.",
	"uploadfile"		=> "Caricamento del file fallito.",
	"copyitem"		=> "Copia del file fallita.",
	"moveitem"		=> "Spostamento del file fallito.",
	"delitem"		=> "Eliminazione del file fallita.",
	"chpass"		=> "Cambiamento della password fallito.",
	"deluser"		=> "Rimozione dell'utente fallita.",
	"adduser"		=> "Aggiunta dell'utente fallita.",
	"saveuser"		=> "Salvataggio dell'utente fallito.",
	"searchnothing"	=> "Devi fornire un criterio di ricerca.",
	
	// misc
	"miscnofunc"		=> "Funzione non disponibile.",
	"miscfilesize"	=> "Il file supera la dimensione massima.",
	"miscfilepart"	=> "Il file è stato caricato solo parzialmente.",
	"miscnoname"		=> "Devi fornire un nome.",
	"miscselitems"	=> "Non hai selezionato nessuno elemento(i).",
	"miscdelitems"	=> "Sei sicuro di voler eliminare questi \"+num+\" elemento(i)?",
	"miscdeluser"		=> "Sei sicuro di voler eliminare l'utente '\"+user+\"'?",
	"miscnopassdiff"	=> "La nuova password non è diversa da quella attualmente impostata.",
	"miscnopassmatch"	=> "La password non corrisponde.",
	"miscfieldmissed"	=> "Hai dimenticato un campo importante.",
	"miscnouserpass"	=> "Nome utente o password non corretti.",
	"miscselfremove"	=> "Non puoi rimuovere te stesso.",
	"miscuserexist"	=> "L'utente esiste già.",
	"miscnofinduser"	=> "Impossibile trovare l'utente.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "CAMBIA I PERMESSI",
	"editlink"		=> "MODIFICA",
	"downlink"		=> "SCARICA",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "SU",
	"homelink"		=> "HOME",
	"reloadlink"		=> "RICARICA",
	"copylink"		=> "COPIA",
	"movelink"		=> "SPOSTA",
	"dellink"		=> "ELIMINA",
	"comprlink"		=> "COMPRIMI",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "LOGOUT",
	"uploadlink"		=> "CARICA",
	"searchlink"		=> "CERCA",
	"unziplink"		=> "UNZIP",
	
	// list
	"nameheader"		=> "Nome",
	"sizeheader"		=> "Dimensione",
	"typeheader"		=> "Tipo",
	"modifheader"		=> "Modificato",
	"permheader"		=> "Permessi",
	"actionheader"	=> "Azioni",
	"pathheader"		=> "Percorso",
	
	// buttons
	"btncancel"		=> "Annulla",
	"btnsave"		=> "Salva",
	"btnchange"		=> "Cambia",
	"btnreset"		=> "Resetta",
	"btnclose"		=> "Chiudi",
	"btncreate"		=> "Crea",
	"btnsearch"		=> "Cerca",
	"btnupload"		=> "Carica",
	"btncopy"		=> "Copia",
	"btnmove"		=> "Sposta",
	"btnlogin"		=> "Login",
	"btnlogout"		=> "Logout",
	"btnadd"		=> "Aggiungi",
	"btnedit"		=> "Modifica",
	"btnremove"		=> "Rimuovi",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Directory",
	"actperms"		=> "Cambia i permessi",
	"actedit"		=> "Modifica il file",
	"actsearchresults"	=> "Risultati della ricerca",
	"actcopyitems"	=> "Copia elemento(i)",
	"actcopyfrom"		=> "Copia da /%s a /%s ",
	"actmoveitems"	=> "Copia elemento(i)",
	"actmovefrom"		=> "Sposta da /%s a /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Login per usare QuiXplorer",
	"actadmin"		=> "Amministrazione",
	"actchpwd"		=> "Cambia la password",
	"actusers"		=> "Utenti",
	"actarchive"		=> "Archivia elemento(i)",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Carica file(s)",
	
	// misc
	"miscitems"		=> "Elemento(i)",
	"miscfree"		=> "Liberi",
	"miscusername"	=> "Nome utente",
	"miscpassword"	=> "Password",
	"miscoldpass"		=> "Vecchia password",
	"miscnewpass"		=> "Nuova password",
	"miscconfpass"	=> "Conferma la password",
	"miscconfnewpass"	=> "Conferma la nuova password",
	"miscchpass"		=> "cambia la password",
	"mischomedir"		=> "Directory home",
	"mischomeurl"		=> "URL della home",
	"miscshowhidden"	=> "Mostra elementi nascosti",
	"mischidepattern"	=> "Nascondi il motivo",
	"miscperms"		=> "Permessi",
	"miscuseritems"	=> "(nome, directory home, mostra elementi nascosti, permessi, attivo)",
	"miscadduser"		=> "aggiungi utente",
	"miscedituser"	=> "modifica l'utente '%s'",
	"miscactive"		=> "Attivo",
	"misclang"		=> "Lingua",
	"miscnoresult"	=> "Nessun risultato disponibile.",
	"miscsubdirs"		=> "Cerca nelle subdirectories",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Si","No","S","N"),
	"miscchmod"		=> array("Proprietario", "Gruppo", "Pubblico"),
);
?>