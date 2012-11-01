<?php
/*
	el.php
	
	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Author: The QuiX project.

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

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "ΛΑΘΟΣ",
	"back"			=> "ΠΙΣΩ",
	
	// root
	"home"			=> "Ο home ΚΑΤΑΛΟΓΟΣ ΔΕΝ ΥΠΑΡΧΕΙ, ΕΛΕΞΤΕ ΤΙΣ ΡΥΘΜΙΣΕΙΣ",
	"abovehome"		=> "Ο τρέχων κατάλογος μπορεί να μην είναι απο πάνω απο τον home κατάλογο",
	"targetabovehome"	=> "Ο κατάλογος προορισμού μπορεί να μην είναι απο πάνω απο τον home κατάλογο.",
	
	// exist
	"direxist"		=> "Αυτός ο κατάλογος δεν υπάρχει.",
	//"filedoesexist"	=> "Αυτό το αρχείο υπάρχει ήδη.",
	"fileexist"		=> "Αυτό το αρχείο υπάρχει ήδη",
	"itemdoesexist"	=> "Αυτό το αντικείμενο υπάρχει ήδη",
	"itemexist"		=> "Αυτό το αντικείμενο δεν υπάρχει",
	"targetexist"		=> "Ο κατάλογος προορισμού δεν υπάρχει.",
	"targetdoesexist"	=> "Το αντικείμενο προορισμού υπάρχει ήδη.",
	
	// open
	"opendir"		=> "Αδύνατον να ανοιχτεί ο κατάλογος.",
	"readdir"		=> "Αδύνατον να διαβαστεί ο κατάλογος.",
	
	// access
	"accessdir"		=> "Απαγορεύεται η πρόσβαση στον φάκελο.",
	"accessfile"		=> "Απαγορεύεται η πρόσβαση στο αρχείο.",
	"accessitem"		=> "Απαγορεύεται η πρόσβαση στο αντικείμενο.",
	"accessfunc"		=> "Απαγορεύεται η χρήση αυτής της συνάρτησης.",
	"accesstarget"	=> "Απαγορεύεται η πρόσβαση στο κατάλογο προορισμού.",
	
	// actions
	"chmod_not_allowed"	=> 'Απαγορεύεται αλλαγή δικαιωμάτων σε ΚΑΝΕΝΑΣ',
	"permread"		=> "Ανάληψη δικαιωμάτων απέτυχε.",
	"permchange"		=> "Αλλαγή δικαιωμάτων απέτυχε.",
	"openfile"		=> "’νοιγμα φακέλου απέτυχε.",
	"savefile"		=> "Αποθήκευση αρχείου απέτυχε.",
	"createfile"		=> "Δημιουργία αρχείου απέτυχε.",
	"createdir"		=> "Δημιουργία καταλόγου απέτυχε.",
	"uploadfile"		=> "Ανέβασμα αρχείου απέτυχε",
	"copyitem"		=> "Αντιγραφή απέτυχε.",
	"moveitem"		=> "ΜΕταφορά απέτυχε.",
	"delitem"		=> "Διαγραφή απέτυχε.",
	"chpass"		=> "Αλλαγή κωδικού απέτυχε.",
	"deluser"		=> "Διαγραφή χρήστη απέτυχε.",
	"adduser"		=> "Προσθήκη χρήστη απέτυχε.",
	"saveuser"		=> "Αποθήκευση χρήστη απέτυχε.",
	"searchnothing"	=> "Πρέπει να συμπληρώσεις κάτι για αναζήτηση.",
	
	// misc
	"miscnofunc"		=> "Μη διαθέσιμη συνάρτηση.",
	"miscfilesize"	=> "Το αρχείο υπερέβει το ανώτατο μέγεθος.",
	"miscfilepart"	=> "Το αρχείο ήταν μερικώς ανεβασμένο.",
	"miscnoname"		=> "Πρέπει να συμπληρώσεις όνομα",
	"miscselitems"	=> "Δεν διάλεξες αντικείμενο/α",
	"miscdelitems"	=> "Είσαι σίγουρος οτι θέλεις να διαγράψεις αυτό το\"+num+\" αντικείμενο/α;",
	"miscdeluser"		=> "Είσαι σίγουρος οτι θέλεις να διαγράψεις τον χρήστη'\"+user+\"'?",
	"miscnopassdiff"	=> "Ο νέος κωδικός δεν διαφέρει απο τον υπάρχων.",
	"miscnopassmatch"	=> "Οι κωδικοί δεν ταιριάζουν",
	"miscfieldmissed"	=> "Δεν συμπλήρωσες σημαντικό πεδίο.",
	"miscnouserpass"	=> "Όνομα χρήστη ή κωδικός λανθασμένα.",
	"miscselfremove"	=> "Δεν μπορείς να διαγράψεις τον εαυτό σου",
	"miscuserexist"	=> "Ο χρήστης υπάρχει ήδη.",
	"miscnofinduser"	=> "Δεν μπορώ να βρώ τον χρήστη.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ΑΛΛΑΞΕ ΔΙΚΑΙΩΜΑΤΑ",
	"editlink"		=> "ΕΠΕΞΕΡΓΑΣΙΑ",
	"downlink"		=> "ΜΕΤΑΦΟΡΤΩΣΗ",
	"uplink"		=> "ΑΝΕΒΑΣΜΑ",
	"homelink"		=> "HOME",
	"reloadlink"		=> "ΕΠΑΝΑΦΟΡΤΩΣΗ(RELOAD)",
	"copylink"		=> "ΑΝΤΙΓΡΑΦΗ",
	"movelink"		=> "ΜΕΤΑΚΙΝΗΣΗ",
	"dellink"		=> "ΔΙΑΓΡΑΦΗ",
	"comprlink"		=> "ΑΡΧΕΙΟΘΕΤΗΣΗ",
	"adminlink"		=> "ΔΙΑΧΕΙΡΗΣΤΗΣ",
	"logoutlink"		=> "ΑΠΟΣΥΝΔΕΣΗ",
	"uploadlink"		=> "ΑΝΕΒΑΣΜΑ",
	"searchlink"		=> "ΑΝΑΖΗΤΗΣΗ",
	
	// list
	"nameheader"		=> "Όνομα",
	"sizeheader"		=> "Μέγεθος",
	"typeheader"		=> "Τύπος",
	"modifheader"		=> "Τροποποίηση",
	"permheader"		=> "Δικ/ματα",
	"actionheader"	=> "Ενέργειες",
	"pathheader"		=> "Μονοπάτι",
	
	// buttons
	"btncancel"		=> "Ακύρωση",
	"btnsave"		=> "Αποθήκευση",
	"btnchange"		=> "Αλλαγή",
	"btnreset"		=> "Επαναφορά",
	"btnclose"		=> "Κλείσιμο",
	"btncreate"		=> "Δημιουργία",
	"btnsearch"		=> "Αναζήτηση",
	"btnupload"		=> "Ανέβασμα",
	"btncopy"		=> "Αντιγραφή",
	"btnmove"		=> "ΜΕτακίνηση",
	"btnlogin"		=> "Είσοδος",
	"btnlogout"		=> "Αποσύνδεση",
	"btnadd"		=> "Προσθήκη",
	"btnedit"		=> "Επεξεργασία",
	"btnremove"		=> "Αφαίρεση",
	
	// actions
	"actdir"		=> "Κατάλογος",
	"actperms"		=> "Αλλαγή δικαιωμάτων",
	"actedit"		=> "Επεξεργασία αρχείου",
	"actsearchresults"	=> "Αποτελέσματα αναζήτησης",
	"actcopyitems"	=> "Αντιγραφή αντικειμένου/ων",
	"actcopyfrom"		=> "Αντιγραφή από /%s σε /%s ",
	"actmoveitems"	=> "Μεταφορά αντικειμένου/ων",
	"actmovefrom"		=> "Μεταφορά /%s σε /%s ",
	"actlogin"		=> "Είσοδος",
	"actloginheader"	=> "Είσοδος για χρήση QuiXplorer",
	"actadmin"		=> "Διαχείρηση",
	"actchpwd"		=> "Αλλαγή κωδικού",
	"actusers"		=> "Χρήστες",
	"actarchive"		=> "Αρχειοθέτησε αντικείμενο/α",
	"actupload"		=> "Ανέβασμα αρχείων",
	
	// misc
	"miscitems"		=> "Αντικείμενο/α",
	"miscfree"		=> "Ελεύθερο",
	"miscusername"	=> "Όνομα χρήστη",
	"miscpassword"	=> "Κωδικός",
	"miscoldpass"		=> "Παλιός Κωδικός",
	"miscnewpass"		=> "Νέος Κωδικός",
	"miscconfpass"	=> "Επιβεβαίωση κωδικού",
	"miscconfnewpass"	=> "Επιβεβαίωση νέου κωδικού",
	"miscchpass"		=> "Αλλαγή κωδικού",
	"mischomedir"		=> "Home κατάλογος",
	"mischomeurl"		=> "Home URL",
	"miscshowhidden"	=> "Εμφάνιση κρυμμένων items",
	"mischidepattern"	=> "Απόκρυψη pattern",
	"miscperms"		=> "Δικαιώματα",
	"miscuseritems"	=> "(Όνομα, home κατάλογος, show hidden items, δικαιώματα, ενεργός)",
	"miscadduser"		=> "Προσθήκη χρήστη",
	"miscedituser"	=> "Επεξεργασία χρήστη'%s'",
	"miscactive"		=> "Ενεργός",
	"misclang"		=> "Γλώσσα",
	"miscnoresult"	=> "Κανένα αποτέλεσμα διαθέσιμο.",
	"miscsubdirs"		=> "Ψάξε υποκαταλόγους",
	"miscpermnames"	=> array("Προβολή μόνο","Μετατροπή","Αλλαγή κωδικού","Μετέτρεψε & άλλαξε κωδικό","Administrator"),
	"miscyesno"		=> array("Ναι","Οχι","Ν","Ο"),
	"miscchmod"		=> array("Ιδιοκτήτης", "Γκρουπ", "Δημόσιο(public)"),
);
?>