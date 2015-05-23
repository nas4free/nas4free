<?php
/*
	javascript.php

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
?>
<script language="JavaScript1.2" type="text/javascript">
<!--
	// Checkboxes
	function Toggle(e) {
		if(e.checked) {
			Highlight(e);
			document.selform.toggleAllC.checked = AllChecked();
		} else {
			UnHighlight(e);
			document.selform.toggleAllC.checked = false;
		}
   	}

	function ToggleAll(e) {
		if(e.checked) CheckAll();
		else ClearAll();
	}
	
	function CheckAll() {
		var ml = document.selform;
		var len = ml.elements.length;
		for(var i=0; i<len; ++i) {
			var e = ml.elements[i];
			if(e.name == "selitems[]") {
				e.checked = true;
				Highlight(e);
			}
		}
		ml.toggleAllC.checked = true;
	}

	function ClearAll() {
		var ml = document.selform;
		var len = ml.elements.length;
		for (var i=0; i<len; ++i) {
			var e = ml.elements[i];
			if(e.name == "selitems[]") {
				e.checked = false;
				UnHighlight(e);
			}
		}
		ml.toggleAllC.checked = false;
	}
   
	function AllChecked() {
		ml = document.selform;
		len = ml.elements.length;
		for(var i=0; i<len; ++i) {
			if(ml.elements[i].name == "selitems[]" && !ml.elements[i].checked) return false;
		}
		return true;
	}
	
	function NumChecked() {
		ml = document.selform;
		len = ml.elements.length;
		num = 0;
		for(var i=0; i<len; ++i) {
			if(ml.elements[i].name == "selitems[]" && ml.elements[i].checked) ++num;
		}
		return num;
	}
	
	
	// Row highlight

	function Highlight(e) {
		var r = null;
		if(e.parentNode && e.parentNode.parentNode) {
			r = e.parentNode.parentNode;
		} else if(e.parentElement && e.parentElement.parentElement) {
			r = e.parentElement.parentElement;
		}
		if(r && r.className=="rowdata") {
			r.className = "rowdatasel";
		}
	}

	function UnHighlight(e) {
		var r = null;
		if(e.parentNode && e.parentNode.parentNode) {
			r = e.parentNode.parentNode;
		} else if (e.parentElement && e.parentElement.parentElement) {
			r = e.parentElement.parentElement;
		}
		if(r && r.className=="rowdatasel") {
			r.className = "rowdata";
		}
	}
	
	// Copy / Move / Delete
	
	function Copy() {
		if(NumChecked()==0) {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		document.selform.do_action.value = "copy";
		document.selform.submit();
	}
	
	function Move() {
		if(NumChecked()==0) {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		document.selform.do_action.value = "move";
		document.selform.submit();
	}
	
	function Delete() {
		num=NumChecked();
		if(num==0) {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		if(confirm("<?php echo $GLOBALS["error_msg"]["miscdelitems"]; ?>")) {
			document.selform.do_action.value = "delete";
			document.selform.submit();
		}
	}
	
    function Archive()
    {
        if(NumChecked()==0)
        {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		document.selform.do_action.value = "arch";
		document.selform.submit();
	}

    function DownloadSelected()
    {
        if(NumChecked()==0)
        {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		document.selform.do_action.value = "download_selected";
		document.selform.submit();
	}

    function Unzip()
    {
        if (NumChecked()==0)
        {
			alert("<?php echo $GLOBALS["error_msg"]["miscselitems"]; ?>");
			return;
		}
		document.selform.do_action.value = "unzip";
		document.selform.submit();
	}
	

// -->
</script>
