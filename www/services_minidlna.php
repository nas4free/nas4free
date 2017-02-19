<?php
/*
	services_minidlna.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE NAS4FREE PROJECT ``AS IS'' AND ANY
	EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE NAS4FREE PROJECT OR ITS CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
	THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies,
	either expressed or implied, of the NAS4Free Project.
*/
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
unset($currentconfig);

$pgtitle = array(gtext("Services"),gtext("DLNA/UPnP MiniDLNA"));

$homechanged =0;

if (!isset($config['minidlna']) || !is_array($config['minidlna'])) {
	$config['minidlna'] = array();
	$config['minidlna']['inotify'] = true;
}

if (!isset($config['minidlna']['content']) || !is_array($config['minidlna']['content']))
	$config['minidlna']['content'] = array();

sort($config['minidlna']['content']);

$pconfig['enable'] = isset($config['minidlna']['enable']);
$pconfig['name'] = !empty($config['minidlna']['name']) ? $config['minidlna']['name'] : $pconfig['name'] = $config['system']['hostname'];
$pconfig['if'] = !empty($config['minidlna']['if']) ? $config['minidlna']['if'] : "";
$pconfig['port'] = !empty($config['minidlna']['port']) ? $config['minidlna']['port'] : "8200";
$pconfig['home'] = !empty($config['minidlna']['home']) ? $config['minidlna']['home'] : "";
$pconfig['notify_int'] = !empty($config['minidlna']['notify_int']) ? $config['minidlna']['notify_int'] : "300";
$pconfig['strict'] = isset($config['minidlna']['strict']);
$pconfig['loglevel'] = !empty($config['minidlna']['loglevel']) ? $config['minidlna']['loglevel'] : "warn";
$pconfig['tivo'] = isset($config['minidlna']['tivo']);
$pconfig['content'] = $config['minidlna']['content'];
$pconfig['container'] = !empty($config['minidlna']['container']) ? $config['minidlna']['container'] : "B";
$pconfig['inotify'] = isset($config['minidlna']['inotify']);

if ($_POST) {

	if (isset($_POST['Submit']) && $_POST['Submit']) {
	unset($input_errors);

	// Input validation.
	if ( !is_array ($_POST['content'])) $input_errors[] = gtext("Please define the Media library location.");
	if ( empty ($_POST['home']) || !is_dir ($_POST['home'])) $input_errors[] = gtext("Please define the Database directory location.");
	$pconfig = $_POST;

	if (empty($input_errors)) {
		if (isset ($config['minidlna']['content']) || is_array ($config['minidlna']['content'])) $currentconfig = $config['minidlna']; else unset($currentconfig);

		if (!isset($config['minidlna']['home']) || ($config['minidlna']['home'] !== $_POST['home'])) {
			$homechanged = 1;
			chown($_POST['home'], "dlna");
			chmod ($_POST['home'], 0755);
			unlink_if_exists ($_POST['home']."/files.db");
		}
		$config['minidlna']['enable'] = isset($_POST['enable']) ? true : false;
		$config['minidlna']['name'] = $_POST['name'];
		$config['minidlna']['if'] = $_POST['if'];
		$config['minidlna']['port'] = $_POST['port'];
		$config['minidlna']['notify_int'] = $_POST['notify_int'];
		$config['minidlna']['home'] = $_POST['home'];
		$config['minidlna']['strict'] = isset($_POST['strict']) ? true : false;
		$config['minidlna']['inotify'] = isset($_POST['inotify']) ? true : false;
		$config['minidlna']['tivo'] =  isset($_POST['tivo']) ? true : false;
		$config['minidlna']['content'] = $_POST['content'];
		$config['minidlna']['loglevel'] =  $_POST['loglevel'];
		$config['minidlna']['container'] =  $_POST['container'];

		if (empty ($currentconfig['content']) || $homechanged == 1) {
		updatenotify_set("minidlna", UPDATENOTIFY_MODE_NEW, gtext("Building database in progress"));
		}	else {
			$a_content = $config['minidlna']['content'];
			$b_content = $currentconfig['content'];
			sort ($a_content);
			sort ($b_content);
			$check_differences = array_merge (  array_diff_assoc ( $a_content ,$b_content ), array_diff_assoc ( $b_content ,  $a_content));
			if (count ($check_differences) > 0 ) {
				updatenotify_set("minidlna", UPDATENOTIFY_MODE_MODIFIED, gtext("Rescan database in progress"));
					} else {
				updatenotify_set("minidlna", UPDATENOTIFY_MODE_DIRTY, gtext("Minidlna configuration has been updated."));
			}
	}
		write_config();
		header("Location: services_minidlna.php");
		exit;
	}
}

// End POST save
	if (isset($_POST['apply']) && $_POST['apply']) {
			$retval =0;
			if (!file_exists($d_sysrebootreqd_path)) {
					config_lock();
					$retval != rc_stop_service('minidlna') ;
					$retval = $retval << 1;
					$retval |=  rc_update_service ( 'minidlna' );
					$retval = $retval << 1;
					$retval |= rc_update_service("mdnsresponder");
					config_unlock();
					$savemsg = get_std_save_message($retval);
					if ($retval === 0) {
					unset ($savemsg);
					$notification = updatenotify_get("minidlna");			
					$savemsg = $notification[0]['data'];
					updatenotify_delete("minidlna"); }
				}
	}
}

$a_interface = get_interface_list();

// Use first interface as default if it is not set.
if (empty($pconfig['if']) && is_array($a_interface))
	$pconfig['if'] = key($a_interface);

include("fbegin.inc"); ?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.name.disabled = endis;
	document.iform.xif.disabled = endis;
	document.iform.port.disabled = endis;
	document.iform.notify_int.disabled = endis;
	document.iform.content.disabled = endis;
	document.iform.contentfiletype.disabled = endis;
	document.iform.contentaddbtn.disabled = endis;
	document.iform.contentchangebtn.disabled = endis;
	document.iform.contentdeletebtn.disabled = endis;
	document.iform.contentdata.disabled = endis;
	document.iform.contentbrowsebtn.disabled = endis;
	document.iform.home.disabled = endis;
	document.iform.homebrowsebtn.disabled = endis;
	document.iform.inotify.disabled = endis;
	document.iform.container.disabled = endis;
	document.iform.strict.disabled = endis;
	document.iform.tivo.disabled = endis;
	document.iform.loglevel.disabled = endis;
}
//-->
</script>
<form action="services_minidlna.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr id="tabnavtbl"><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="services_fuppes.php"><span><?=gtext("Fuppes")?></span></a></li>
		    <li class="tabact"><a href="services_minidlna.php"><span><?=gtext("MiniDLNA");?></span></a></li>
			</ul>
		</td></tr>
		   <tr>
			<td class="tabcont">
				<?php if (true === isset($config['upnp']['enable'])) {
				$savemsg = gtext("Fuppes is enabled. If you wish to use MiniDLNA, you will need to disable Fuppes first.");
				if (!empty($savemsg)) print_info_box($savemsg);
				}else{?>
			<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
			<?php if (updatenotify_exists("minidlna" )) print_config_change_box();?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline_checkbox("enable", gtext("MiniDLNA A/V Media Server"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)" ); ?>
			<?php html_inputbox("name", gtext("Name"), $pconfig['name'], gtext("Give your media library a friendly name."), true, 35);?>
			<tr>
					<td width="22%" valign="top" class="vncellreq"><?=gtext("Interface selection");?></td>
					<td width="78%" class="vtable">
					<select name="if" class="formfld" id="xif">
						<?php foreach($a_interface as $if => $ifinfo):?>
							<?php $ifinfo = get_interface_info($if); if (("up" == $ifinfo['status']) || ("associated" == $ifinfo['status'])):?>
							<option value="<?=$if;?>"<?php if ($if == $pconfig['if']) echo "selected=\"selected\"";?>><?=$if?></option>
							<?php endif;?>
						<?php endforeach;?>
					</select>
					<br /><?=gtext("Select which interface to use. (Only selectable if your server has more than one)");?>
					</td>
				</tr>
					<?php html_inputbox("port", gtext("Port"), $pconfig['port'], sprintf(gtext("Port to listen on. Only dynamic or private ports can be used (from %d through %d). Default port is %d."), 1025, 65535, 8200), true, 5);?>
					<?php html_inputbox("notify_int", gtext("Broadcast interval"), $pconfig['notify_int'], sprintf(gtext("Broadcasts its availability every N seconds on the network. (Default 300 seconds)"), 1025, 65535, 60), true, 5);?>
					<?php html_filechooser("home", gtext("Database directory"), $pconfig['home'], gtext("Location where the database with media contents will be stored."), $g['media_path'], true, 67);?>
					<?php html_minidlnabox("content", gtext("Media library"), !empty($pconfig['content']) ? $pconfig['content'] : array(), gtext("Set the content location(s) to or from the media library."), $g['media_path'], true);?>
					<?php html_checkbox ("inotify", gtext("Inotify"), !empty($pconfig['inotify']) ? true : false, gtext("Enable inotify."), gtext("Use inotify monitoring to automatically discover new files."), false);?>
					<?php html_combobox("container", gtext("Container"), $pconfig['container'], array("." => gtext("Standard"), "B" => gtext("Browse Directory"), "M" => gtext("Music"), "V" => gtext("Video"), "P" => gtext("Pictures")), gtext("Use different container as root of the tree."), false, false, "" );?>
					<?php html_checkbox ("strict", gtext("Strict DLNA"), !empty($pconfig['strict']) ? true : false, gtext("Enable to strictly adhere to DLNA standards."), gtext("This will allow server-side downscaling of very large JPEG images, it can hurt JPEG serving performance on (at least) Sony DLNA products."), false);?>
					<?php html_checkbox ("tivo", gtext("TiVo support"), !empty($pconfig['tivo']) ? true : false, gtext("Enable TiVo support."), gtext("This will support streaming .jpg and .mp3 files to a TiVo supporting HMO."), false);?>
					<?php html_combobox("loglevel", gtext("Log level"), $pconfig['loglevel'], array("off" => gtext("Off"), "fatal" => gtext("Fatal"), "error" => gtext("Error"), "warn" => gtext("Warning"), "info" => gtext("Info"),"debug" => gtext("debug")), "", false, false, "" );?>
					<?php html_separator();?>
					<?php html_titleline(gtext("Presentation WebGUI"));?>
					<?php
						$if = get_ifname($pconfig['if']);
						$ipaddr = get_ipaddr($if);
						$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}/status");
						$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					?>
					<?php html_text("url", gtext("URL"), $text);?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="onsubmit_content(); enable_change(true)" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>
<?php } ?>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>

<?php include("fend.inc");?>
