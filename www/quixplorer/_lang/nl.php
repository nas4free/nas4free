<?php
/*
	nl.php

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
// Dutch Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "FOUT(EN)",
	"back"			=> "Ga Terug",

	// root
	"home"			=> "De hoofdfolder bestaat niet, controleer uw instellingen.",
	"abovehome"		=> "De huidige map mag niet hoger liggen dan de hoofdfolder.",
	"targetabovehome"	=> "De doelmap mag niet hoger liggen dan de hoofdfolder.",

	// exist
	"direxist"		=> "Deze map bestaat niet.",
	"fileexist"		=> "Dit bestand bestaat niet.",
	"itemdoesexist"	=> "Dit item bestaat reeds.",
	"itemexist"		=> "Dit item bestaat niet.",
	"targetexist"		=> "De doel item bestaat niet.",
	"targetdoesexist"	=> "Het doel item bestaat reeds.",

	// open
	"opendir"		=> "Kan de map niet openen.",
	"readdir"		=> "Kan de map niet lezen.",

	// access
	"accessdir"		=> "U heeft geen toegang tot deze map.",
	"accessfile"		=> "U heeft geen toegang tot het bestand.",
	"accessitem"		=> "U heeft geen toegang tot het item.",
	"accessfunc"		=> "U heeft geen rechten om deze functie te gebruiken.",
	"accesstarget"	=> "U heeft geen toegang tot de doel.",

	// actions
	//"chmod_not_allowed"  => 'Rechten veranderen naar GEEN is niet toegestaan!',
	"permread"		=> "Rechten opvragen mislukt.",
	"permchange"		=> "Het wijzigen van rechten mislukt.",
	"openfile"		=> "Het Bestand openen mislukt.",
	"savefile"		=> "Bestand opslaan mislukt.",
	"createfile"		=> "Bestand aanmaken mislukt.",
	"createdir"		=> "Aanmaken folder mislukt.",
	"uploadfile"		=> "Item uploaden is mislukt.",
	"copyitem"		=> "Kopie maken mislukt.",
	"moveitem"		=> "Items(s) verplaatsen mislukt.",
	"delitem"		=> "item(s) verwijderen mislukt.",
	"chpass"		=> "Het wachtwoord wijzigen is mislukt.",
	"deluser"		=> "Gebruiker verwijderen is mislukt.",
	"adduser"		=> "Toevoegen gebruiker is mislukt.",
	"saveuser"		=> "Opslaan gebruiker is mislukt.",
	"searchnothing"	=> "U moet om iets te zoeken het juiste opgeven.",

	// misc
	"miscnofunc"		=> "Deze functie is niet beschikbaar.",
	"miscfilesize"	=> "Het bestand is groter dan de maximum grootte.",
	"miscfilepart"	=> "Het bestand is maar gedeeltelijk geupload.",
	"miscnoname"		=> "U moet een naam opgeven.",
	"miscselitems"	=> "U heeft geen item(s) geselecteerd.",
	"miscdelitems"	=> "Weet u zeker dat u \"+num+\" item(s) wilt verwijderen?",
	"miscdeluser"		=> "Weet u zeker dat u gebruiker '\"+user+\"' wilt verwijderen?",
	"miscnopassdiff"	=> "Het nieuwe wachtwoord verschilt niet van het huidige.",
	"miscnopassmatch"	=> "De opgegeven wachtwoorden komen niet overeen.",
	"miscfieldmissed"	=> "U heeft een belangrijk veld vergeten in te vullen.",
	"miscnouserpass"	=> "Gebruiker/wachtwoord is onjuist.",
	"miscselfremove"	=> "U kunt zichzelf niet verwijderen.",
	"miscuserexist"	=> "Deze gebruiker bestaat reeds.",
	"miscnofinduser"	=> "Kan deze gebruiker niet vinden.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "RECHTEN WIJZIGEN",
	"editlink"		=> "BEWERKEN",
	"downlink"		=> "DOWNLOADEN",
	"download_selected"	=> "DOWNLOAD GESELECTEERDE BESTANDEN",
	"uplink"		=> "TERUG",
	"homelink"		=> "HOOFDFOLDER",
	"reloadlink"		=> "VERNIEUWEN",
	"copylink"		=> "KOPIE",
	"movelink"		=> "VERPLAATSEN",
	"dellink"		=> "VERWIJDEREN",
	"comprlink"		=> "ARCHIVEREN",
	"adminlink"		=> "ADMINISTRATIE",
	"logoutlink"		=> "AFMELDEN",
	"uploadlink"		=> "UPLOADEN",
	"searchlink"		=> "ZOEKEN",
	"unziplink"		=> "Uitpakken",

	// list
	"nameheader"		=> "Naam",
	"sizeheader"		=> "Grootte",
	"typeheader"		=> "Type",
	"modifheader"		=> "Gewijzigd",
	"permheader"		=> "Rechten",
	"actionheader"	=> "Acties",
	"pathheader"		=> "Pad",

	// buttons
	"btncancel"		=> "Annuleren",
	"btnsave"		=> "Opslaan",
	"btnchange"		=> "Wijzigen",
	"btnreset"		=> "Opnieuw",
	"btnclose"		=> "Sluiten",
	"btncreate"		=> "Maken",
	"btnsearch"		=> "Zoeken",
	"btnupload"		=> "Uploaden",
	"btncopy"		=> "Kopie maken",
	"btnmove"		=> "Verplaatsen",
	"btnlogin"		=> "Aanmelden",
	"btnlogout"		=> "Afmelden",
	"btnadd"		=> "Toevoegen",
	"btnedit"		=> "Bewerken",
	"btnremove"		=> "Verwijderen",
	"btnunzip"		=> "Uitpakken",

	// actions
	"actdir"		=> "Map",
	"actperms"		=> "Rechten wijzigen",
	"actedit"		=> "Bestand bewerken",
	"actsearchresults"	=> "Zoek resultaten",
	"actcopyitems"	=> "Item(s)Kopiëren",
	"actcopyfrom"		=> "Kopie maken van /%s naar /%s ",
	"actmoveitems"	=> "Verplaats item(s)",
	"actmovefrom"		=> "Verplaats van /%s naar /%s ",
	"actlogin"		=> "Aanmelden",
	"actloginheader"	=> "Aanmelden om Bestands Manager te gebruiken",
	"actadmin"		=> "Beheer",
	"actchpwd"		=> "Wachtwoord wijzigen",
	"actusers"		=> "Gebruikers",
	"actarchive"		=> "Archiveren item(s)",
	"actunzipitem"	=> "Uitpakken",
	"actupload"		=> "Bestand(en) uploaden",

	// misc
	"miscitems"		=> "Item(s)",
	"miscfree"		=> "Beschikbaar",
	"miscusername"	=> "Gebruikersnaam",
	"miscpassword"	=> "Wachtwoord",
	"miscoldpass"		=> "Oud wachtwoord",
	"miscnewpass"		=> "Nieuw wachtwoord",
	"miscconfpass"	=> "Bevestig wachtwoord",
	"miscconfnewpass"	=> "Bevestig wachtwoord",
	"miscchpass"		=> "Wijzig wachtwoord",
	"mischomedir"		=> "Hoofdmap",
	"mischomeurl"		=> "Hoofd URL",
	"miscshowhidden"	=> "Verborgen items weergeven",
	"mischidepattern"	=> "Verberg patroon",
	"miscperms"		=> "Rechten",
	"miscuseritems"	=> "(naam, hoofdmap, verborgen items weergeven, rechten, geactiveerd)",
	"miscadduser"		=> "gebruiker toevoegen",
	"miscedituser"	=> "gebruiker '%s' bewerken",
	"miscactive"		=> "Geactiveerd",
	"misclang"		=> "Taal",
	"miscnoresult"	=> "Er zijn geen resultaten beschikbaar.",
	"miscsubdirs"		=> "Zoek in subdirectories",
	"miscpermissions"	=> array(
					"read"		=> array("Lezen", "Gebruiker mag lezen en bestanden downloaden"),
					"create" 	=> array("Schrijven", "Gebruiker mag nieuwe bestanden aanmaken"),
					"change"	=> array("Bewerken", "Gebruiker mag bewerken van bestaande bestanden (upload, aanpassen)"),
					"delete"	=> array("Verwijderen", "Gebruiker mag een bestaand bestand verwijderen"),
					"password"	=> array("Wachtwoord veranderen", "Gebruiker kan het wachtwoord veranderen"),
					"admin"		=> array("Administrator", "Volledige toegang"),
			),
	"miscyesno"		=> array("Ja","Nee","J","N"),
	"miscchmod"		=> array("Eigenaar", "Groep", "Publiek"),
);

?>