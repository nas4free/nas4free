<?php
/*
	services_hast_info.php
	
	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

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
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Services"), gettext("HAST"), gettext("Information"));

if (!isset($config['hast']['auxparam']) || !is_array($config['hast']['auxparam']))
	$config['hast']['auxparam'] = array();
//if (!isset($config['hast']['hastresource']) || !is_array($config['hast']['hastresource']))
//	$config['hast']['hastresource'] = array();

function hast_get_status() {
	global $config;

	$cmd = "/sbin/hastctl status";
	if (isset($_GET['name'])) {
		$cmd .= " {$_GET['name']}";
	}
	$cmd .= " 2>&1";
	mwexec2($cmd, $rawdata);
	return implode("\n", $rawdata);
}

if (is_ajax()) {
	$status = hast_get_status();
	render_ajax($status);
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(0, 5000, 'services_hast_info.php', null, function(data) {
		$('#hast_status').text(data.data);
	});
});
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tabnavtbl">
      <ul id="tabnav">
	<li class="tabinact"><a href="services_hast.php"><span><?=gettext("Settings");?></span></a></li>
	<li class="tabinact"><a href="services_hast_resource.php"><span><?=gettext("Resources");?></span></a></li>
	<li class="tabact"><a href="services_hast_info.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Information");?></span></a></li>
      </ul>
    </td>
  </tr>
  <tr>
    <td class="tabcont">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php html_titleline(gettext("HAST status of the configured resources"));?>
	<tr>
	  <td class="listt">
	  <pre><span id="hast_status"></span></pre>
	  </td>
	</tr>
	</table>
    </td>
  </tr>
</table>
<?php include("fend.inc");?>
