<?php
/*
	disks_raid_gvinum_tools.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
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

$pgtitle = array(gtext("Disks"), gtext("Software RAID"), gtext("RAID 0/1/5"), gtext("Maintenance"));

if ($_POST) {
	unset($input_errors);
	unset($do_action);

	/* input validation */
	$reqdfields = explode(" ", "action object");
	$reqdfieldsn = array(gtext("Command"),gtext("Object name"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

if (empty($input_errors)) {
	$do_action = true;
	$action = $_POST['action'];
	$object = $_POST['object'];
	}
}

if (!isset($do_action)) {
	$do_action = false;
	$action = '';
	$object = '';
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="disks_raid_geom.php"><span><?=gtext('GEOM');?></span></a></li>
		<li class="tabact"><a href="disks_raid_gvinum.php" title="<?=gtext('Reload page');?>"><span><?=gtext('RAID 0/1/5');?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabinact"><a href="disks_raid_gvinum.php"><span><?=gtext("Management"); ?></span></a></li>
		<li class="tabact"><a href="disks_raid_gvinum_tools.php" title="<?=gtext('Reload page');?>" ><span><?=gtext("Maintenance");?></span></a></li>
		<li class="tabinact"><a href="disks_raid_gvinum_info.php"><span><?=gtext("Information"); ?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabcont">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php html_titleline(gtext("RAID 0/1/5 Maintenance"));?>
			<tr><td>			
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<form action="disks_raid_gvinum_tools.php" method="post" name="iform" id="iform">
					<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Object name");?></td>
						<td width="78%" class="vtable">
							<input name="object" type="text" class="formfld" id="object" size="20" value="<?=htmlspecialchars($object);?>" />
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Unix Command");?></td>
						<td width="78%" class="vtable">
							<select name="action" class="formfld" id="action">
								<option value="start" <?php if ($action == "start") echo "selected=\"selected\""; ?>>start</option>
								<option value="rebuild" <?php if ($action == "rebuild") echo "selected=\"selected\""; ?>>rebuild</option>
								<option value="list" <?php if ($action == "list") echo "selected=\"selected\""; ?>>list</option>
								<option value="remove" <?php if ($action == "remove") echo "selected=\"selected\""; ?>>remove</option>
								<option value="forceup" <?php if ($action == "forceup") echo "selected=\"selected\""; ?>>forceup</option>
								<option value="saveconfig" <?php if ($action == "saveconfig") echo "selected=\"selected\""; ?>>saveconfig</option>
							</select>
						</td>
					</tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Send Command!");?>"/>
				</div>
				<?php 
				if ($do_action) {
					echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
					echo('<pre class="cmdoutput">');
						//ob_end_flush();
						// Function disks_geom_cmd() can't be used. That's because gvinum can't be accessed
						// via 'geom vinum xxx'.
						switch ($action) {
							case "start":
								disks_geom_cmd("vinum", "start", $object, true);
								break;
							case "rebuild":
								disks_geom_cmd("vinum", "rebuildparity", $object, true);
								break;
							case "list":
								disks_geom_cmd("vinum", "list", $object, true);
								break;
							case "remove":
								disks_geom_cmd("vinum", "rm", "-r {$object}", true);
								break;
							case "forceup":
								disks_geom_cmd("vinum", "setstate", "-f up {$object}", true);
								break;
							case "saveconfig":
								disks_geom_cmd("vinum", "saveconfig", "", true);
								break;
						}
					echo('</pre>');
				};
				?>
				<div id="remarks">
					<?php
					$helpinghand = '1. ' . gtext('Use these specials actions for debugging only!') . '<br />2. ' . gtext('There is no need to start a RAID volume from here (It starts automatically).');
					html_remark('warning', gtext('Warning'), $helpinghand);
					?>
				</div>
				<?php include("formend.inc");?>
			</form>
		</td></tr></table>
	</td></tr>
</table>
<?php include("fend.inc");?>
