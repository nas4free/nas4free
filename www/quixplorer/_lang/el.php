<?php
/*
	el.php

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
// Greek Language Module

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d/m/Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"			=> "ΣΦΑΛΜΑ(ΤΑ)",
	"back"			=> "Πίσω",
	
	// root
	"home"			=> "Ο αρχικός φάκελος δεν υπάρχει, ελέγξτε τις ρυθμίσεις.",
	"abovehome"		=> "Ο τρέχων φάκελος δεν μπορεί να είναι πάνω από τον αρχικό φάκελο.",
	"targetabovehome"	=> "Ο φάκελος προορισμού δεν μπορεί να είναι πάνω από τον αρχικό φάκελο.",
	
	// exist
	"direxist"		=> "Αυτός ο φάκελος δεν υπάρχει.",
	//"filedoesexist"	=> "This file already exists.",
	"fileexist"		=> "Αυτό το αρχείο υπάρχει ήδη.",
	"itemdoesexist"		=> "Αυτό το αντικείμενο υπάρχει ήδη.",
	"itemexist"		=> "Αυτό το αντικείμενο δεν υπάρχει.",
	"targetexist"		=> "Ο φάκελος προορισμού δεν υπάρχει.",
	"targetdoesexist"	=> "Το αντικείμενο προορισμού υπάρχει ήδη.",
	
	// open
	"opendir"		=> "Αδυναμία ανοίγματος φακέλου.",
	"readdir"		=> "Αδυναμία διαβάσματος φακέλου.",
	
	// access
	"accessdir"		=> "Δεν επιτρέπεται να προσπελάσετε αυτό το φάκελο.",
	"accessfile"		=> "Δεν επιτρέπεται να προσπελάσετε αυτό το αρχείο.",
	"accessitem"		=> "Δεν επιτρέπεται να προσπελάσετε αυτό το αντικείμενο.",
	"accessfunc"		=> "Δεν επιτρέπεται να χρησιμοποιήσετε αυτή τη λειτουργία.",
	"accesstarget"		=> "Δεν επιτρέπεται να προσπελάσετε το φάκελο προορισμού.",
	
	// actions
	"chmod_not_allowed" => 'Η αλλαγή δικαιωμάτων σε ΤΙΠΟΤΑ δεν επιτρέπεται!',
	"permread"		=> "Απέτυχε η λήψη των δικαιωμάτων.",
	"permchange"		=> "Η αλλαγή δικαιωμάτων απέτυχε.",
	"openfile"		=> "Αποτυχία ανοίγματος αρχείου.",
	"savefile"		=> "Αποτυχία αποθήκευσης αρχείου.",
	"createfile"		=> "Αποτυχία δημιουργίας αρχείου.",
	"createdir"		=> "Αποτυχία δημιουργίας φακέλου.",
	"uploadfile"		=> "Αποτυχία ανεβάσματος αρχείου.",
	"copyitem"		=> "Αποτυχία αντιγραφής.",
	"moveitem"		=> "Αποτυχία μετακίνησης.",
	"delitem"		=> "Αποτυχία διαγραφής.",
	"chpass"		=> "Αποτυχία αλλαγής κωδικού.",
	"deluser"		=> "Αποτυχία διαγραφής χρήστη.",
	"adduser"		=> "Αποτυχία προσθήκης χρήστη.",
	"saveuser"		=> "Αποτυχία αποθήκευσης χρήστη.",
	"searchnothing"		=> "Πρέπει να γράψετε κάτι προς αναζήτηση.",
	
	// misc
	"miscnofunc"		=> "Η λειτουργία δεν είναι διαθέσιμη.",
	"miscfilesize"		=> "Το αρχείο ξεπερνά το μέγιστο μέγεθος.",
	"miscfilepart"		=> "Το αρχείο δεν ανεβάστηκε ολόκληρο.",
	"miscnoname"		=> "Πρέπει να παρέχετε ένα όνομα.",
	"miscselitems"		=> "Δεν επιλέξατε κανένα αντικείμενο.",
	"miscdelitems"		=> "Θέλετε σίγουρα να διαγράψετε αυτά τα \"+num+\" αντικείμενα;",
	"miscdeluser"		=> "Θέλετε σίγουρα να διαγράψετε τον χρήστη '\"+user+\"';",
	"miscnopassdiff"	=> "Ο νέος κωδικός δεν διαφέρει από τον τρέχων.",
	"miscnopassmatch"	=> "Οι κωδικοί δεν ταιριάζουν.",
	"miscfieldmissed"	=> "Δεν συμπληρώσατε ένα σημαντικό πεδίο.",
	"miscnouserpass"	=> "Το όνομα χρήστη ή ο κωδικός είναι εσφαλμένα.",
	"miscselfremove"	=> "Δεν μπορείτε να διαγράψετε τον εαυτό σας.",
	"miscuserexist"		=> "Ο χρήστης υπάρχει ήδη.",
	"miscnofinduser"	=> "Ο χρήστης δεν βρέθηκε.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ΑΛΛΑΓΗ ΔΙΚΑΙΩΜΑΤΩΝ",
	"editlink"		=> "ΕΠΕΞΕΡΓΑΣΙΑ",
	"downlink"		=> "ΜΕΤΑΦΟΡΤΩΣΗ",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "ΠΑΝΩ",
	"homelink"		=> "ΑΡΧΗ",
	"reloadlink"		=> "ΑΝΑΝΕΩΣΗ",
	"copylink"		=> "ΑΝΤΙΓΡΑΦΗ",
	"movelink"		=> "ΜΕΤΑΚΙΝΗΣΗ",
	"dellink"		=> "ΔΙΑΓΡΑΦΗ",
	"comprlink"		=> "ΑΡΧΕΙΟΘΕΤΗΣΗ",
	"adminlink"		=> "ΔΙΑΧΕΙΡΙΣΤΗΣ",
	"logoutlink"		=> "ΑΠΟΣΥΝΔΕΣΗ",
	"uploadlink"		=> "ΑΝΕΒΑΣΜΑ",
	"searchlink"		=> "ΑΝΑΖΗΤΗΣΗ",
	"unziplink"		=> "UNZIP",
	
	// list
	"nameheader"		=> "Όνομα",
	"sizeheader"		=> "Μέγεθος",
	"typeheader"		=> "Τύπος",
	"modifheader"		=> "Ημ/νία Τροποποίησης",
	"permheader"		=> "Δικαιώματα",
	"actionheader"		=> "Ενέργειες",
	"pathheader"		=> "Διαδρομή",
	
	// buttons
	"btncancel"		=> "Άκυρο",
	"btnsave"		=> "Αποθήκευση",
	"btnchange"		=> "Αλλαγή",
	"btnreset"		=> "Ακύρωση",
	"btnclose"		=> "Κλείσιμο",
	"btncreate"		=> "Δημιουργία",
	"btnsearch"		=> "Αναζήτηση",
	"btnupload"		=> "Ανέβασμα",
	"btncopy"		=> "Αντιγραφή",
	"btnmove"		=> "Μετακίνηση",
	"btnlogin"		=> "Σύνδεση",
	"btnlogout"		=> "Αποσύνδεση",
	"btnadd"		=> "Προσθήκη",
	"btnedit"		=> "Επεξεργασία",
	"btnremove"		=> "Αφαίρεση",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Φάκελος",
	"actperms"		=> "Αλλαγή δικαιωμάτων",
	"actedit"		=> "Επεξεργασία αρχείου",
	"actsearchresults"	=> "Αποτελέσματα αναζήτησης",
	"actcopyitems"		=> "Αντιγραφή αντικειμένων",
	"actcopyfrom"		=> "Αντιγραφή από /%s σε /%s ",
	"actmoveitems"		=> "Μετακίνηση αντικειμένων",
	"actmovefrom"		=> "Μετακίνηση από /%s σε /%s ",
	"actlogin"		=> "Σύνδεση",
	"actloginheader"	=> "Συνδεθείτε για να χρησιμοποιήσετε το QuiXplorer",
	"actadmin"		=> "Διαχείριση",
	"actchpwd"		=> "Αλλαγή κωδικού",
	"actusers"		=> "Χρήστες",
	"actarchive"		=> "Aντικείμενα σε συμπιεσμένο αρχείο",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Ανέβασμα αρχείων",
	
	// misc
	"miscitems"		=> "Αντικείμενα",
	"miscfree"		=> "Ελεύθερος χώρος",
	"miscusername"		=> "Όνομα χρήστη",
	"miscpassword"		=> "Κωδικός",
	"miscoldpass"		=> "Παλιός κωδικός",
	"miscnewpass"		=> "Νέος κωδικός",
	"miscconfpass"		=> "Επιβεβαίωση κωδικού",
	"miscconfnewpass"	=> "Επιβεβαίωση νέου κωδικού",
	"miscchpass"		=> "Αλλαγή κωδικού",
	"mischomedir"		=> "Γονικός φάκελος",
	"mischomeurl"		=> "URL αρχικής σελίδας",
	"miscshowhidden"	=> "Προβολή κρυφών αρχείων",
	"mischidepattern"	=> "Πρότυπο απόκρυψης",
	"miscperms"		=> "Δικαιώματα",
	"miscuseritems"		=> "(όνομα, αρχικός φάκελος, εμφάνιση κρυφών αρχείων, δικαιώματα, ενεργός)",
	"miscadduser"		=> "προσθήκη χρήστη",
	"miscedituser"		=> "επεξεργασία χρήστη '%s'",
	"miscactive"		=> "Ενεργός",
	"misclang"		=> "Γλώσσα",
	"miscnoresult"		=> "Δεν υπάρχουν αποτελέσματα.",
	"miscsubdirs"		=> "Αναζήτηση σε υποφακέλους",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Ναι","Όχι","Ν","Ο"),
	"miscchmod"		=> array("Κάτοχος", "Ομάδα", "Κοινόχρηστο"),
);
?>