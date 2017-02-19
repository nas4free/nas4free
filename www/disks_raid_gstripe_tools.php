<?php
/*
	disks_raid_gstripe_tools.php

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

$pgtitle = array(gtext("Disks"), gtext("Software RAID"), gtext("RAID-0"), gtext("Maintenance"));

if (!isset($config['gstripe']['vdisk']) || !is_array($config['gstripe']['vdisk']))
	$config['gstripe']['vdisk'] = array();

array_sort_key($config['gstripe']['vdisk'], "name");
$a_raid = &$config['gstripe']['vdisk'];

if ($_POST) {
	unset($input_errors);
	unset($do_action);

	/* input validation */
	$reqdfields = explode(" ", "action raid disk");
	$reqdfieldsn = array(gtext("Command"), gtext("Volume Name"), gtext("Disk"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

	if (empty($input_errors)) {
		$do_action = true;
		$action = $_POST['action'];
		$raid = $_POST['raid'];
		$disk = $_POST['disk'];
	}
}

if (!isset($do_action)) {
	$do_action = false;
	$action = '';
	$object = '';
	$raid = '';
	$disk = '';
}
?>
<?php include("fbegin.inc"); ?>
<script type="text/javascript">
//<![CDATA[
$(window).on("load", function() {
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
}); 
function raid_change() {
	var next = null;
	// Remove all entries from partition combobox.
	document.iform.disk.length = 0;
	// Insert entries for disk combobox.
	switch(document.iform.raid.value) {
		<?php foreach ($a_raid as $raidv): ?>
			case "<?= $raidv['name']; ?>":
				<?php foreach ($raidv['device'] as $devicen => $devicev): ?>
					<?php $name = str_replace("/dev/", "", $devicev); ?>
					if(document.all) // MS IE workaround.
						next = document.iform.disk.length;
					document.iform.disk.add(new Option("<?=$name;?>","<?=$name;?>",false,<?php if ($name === $disk) { echo "true";  } else { echo "false"; }; ?>), next);
				<?php endforeach; ?>
				break;
		<?php endforeach; ?>
	}
}
//]]>
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabact"><a href="disks_raid_geom.php" title="<?= gtext('Reload page'); ?>"><span><?= gtext('GEOM'); ?></span></a></li>
		<li class="tabinact"><a href="disks_raid_gvinum.php"><span><?= gtext('RAID 0/1/5'); ?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabnavtbl"><ul id="tabnav2">
		<li class="tabinact"><a href="disks_raid_geom.php"><span><?= gtext('Management'); ?></span></a></li>
		<li class="tabact"><a href="disks_raid_gstripe_tools.php" title="<?= gtext('Reload page'); ?>" ><span><?= gtext('Maintenance'); ?></span></a></li>
		<li class="tabinact"><a href="disks_raid_gstripe_info.php"><span><?= gtext('Information'); ?></span></a></li>
	</ul></td></tr>
	<tr><td class="tabcont">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php html_titleline(gtext('RAID-0 Maintenance')); ?>
			<tr><td>
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<form action="disks_raid_gstripe_tools.php" method="post" name="iform" id="iform">
					<table width="100%" border="0" cellpadding="6" cellspacing="0">
						<tr>
							<td width="22%" valign="top" class="vncellreq"><?= gtext("Volume Name"); ?></td>
							<td width="78%" class="vtable">
								<select name="raid" class="formfld" id="raid" onchange="raid_change()">
									<option value=""><?= gtext("Must choose one"); ?></option>
									<?php foreach ($a_raid as $raidv): ?>
										<option value="<?= $raidv['name']; ?>" <?php if ($raid === $raidv['name']) echo "selected=\"selected\""; ?>>
											<?php echo htmlspecialchars($raidv['name']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncellreq"><?= gtext("Disk"); ?></td>
							<td width="78%" class="vtable">
								<select name="disk" class="formfld" id="disk"></select>
							</td>
						</tr>
						<tr>
							<td width="22%" valign="top" class="vncellreq"><?= gtext("Unix Command"); ?></td>
							<td width="78%" class="vtable">
								<select name="action" class="formfld" id="action">
									<option value="list" <?php if ($action == "list") echo "selected=\"selected\""; ?>>list</option>
									<option value="status" <?php if ($action == "status") echo "selected=\"selected\""; ?>>status</option>
									<option value="clear" <?php if ($action == "clear") echo "selected=\"selected\""; ?>>clear</option>
									<option value="stop" <?php if ($action == "stop") echo "selected=\"selected\""; ?>>stop</option>
									<option value="dump" <?php if ($action == "dump") echo "selected=\"selected\""; ?>>dump</option>
								</select>
							</td>
						</tr>
					</table>
					<div id="submit">
						<input name="Submit" type="submit" class="formbtn" value="<?= gtext("Send Command!"); ?>" />
					</div>
					<?php
					if ($do_action) {
						echo(sprintf("<div id='cmdoutput'>%s</div>", gtext("Command output:")));
						echo('<pre class="cmdoutput">');
							//ob_end_flush();
							switch ($action) {
								case "list":
									disks_geom_cmd("stripe", "list", $raid, true);
									break;
								case "status":
									disks_geom_cmd("stripe", "status", $raid, true);
									break;
								case "clear":
									disks_geom_cmd("stripe", "clear -v", $disk, true);
									break;
								case "stop":
									disks_geom_cmd("stripe", "stop -v", $raid, true);
									break;
								case "dump":
									disks_geom_cmd("stripe", "dump", $disk, true);
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
					<?php include("formend.inc"); ?>
				</form>
			</td></tr>
		</table>
	</td></tr>
</table>
<script type="text/javascript">
//<![CDATA[
raid_change();
//]]>
</script>
<?php include("fend.inc"); ?>
