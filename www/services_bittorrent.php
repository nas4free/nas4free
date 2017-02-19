<?php
/*
	services_bittorrent.php

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
require 'auth.inc';
require 'guiconfig.inc';
require 'services.inc';

array_make_branch($config,'bittorrent');
$os_release = exec('uname -r | cut -d - -f1');

$pconfig['enable'] = isset($config['bittorrent']['enable']);
$pconfig['port'] = $config['bittorrent']['port'];
$pconfig['downloaddir'] = $config['bittorrent']['downloaddir'];
$pconfig['configdir'] = $config['bittorrent']['configdir'];
$pconfig['username'] = $config['bittorrent']['username'];
$pconfig['password'] = $config['bittorrent']['password'];
$pconfig['authrequired'] = isset($config['bittorrent']['authrequired']);
$pconfig['peerport'] = $config['bittorrent']['peerport'];
$pconfig['portforwarding'] = isset($config['bittorrent']['portforwarding']);
$pconfig['uplimit'] = !empty($config['bittorrent']['uplimit']) ? $config['bittorrent']['uplimit'] : "";
$pconfig['downlimit'] = !empty($config['bittorrent']['downlimit']) ? $config['bittorrent']['downlimit'] : "";
$pconfig['pex'] = isset($config['bittorrent']['pex']);
$pconfig['dht'] = isset($config['bittorrent']['dht']);
$pconfig['preallocation'] = $config['bittorrent']['preallocation'];
$pconfig['encryption'] = $config['bittorrent']['encryption'];
$pconfig['watchdir'] = $config['bittorrent']['watchdir'];
$pconfig['incompletedir'] = !empty($config['bittorrent']['incompletedir']) ? $config['bittorrent']['incompletedir'] : "";
$pconfig['umask'] = $config['bittorrent']['umask'];
$pconfig['extraoptions'] = $config['bittorrent']['extraoptions'];

// Set default values.
if (!$pconfig['port']) $pconfig['port'] = "9091";

// Function to check directories (if exists & permisssions)
function change_perms($dir) {
	global $input_errors;

	$path = rtrim($dir,'/'); // remove trailing slash
	if (strlen($path) > 1) {
		if (!is_dir($path)) { // check if directory exists
			$input_errors[] = "Directory $path doesn't exist!";
		} else {
			$path_check = explode("/",$path); // split path to get directory names
			$path_elements = count($path_check); // get path depth
			$fp = substr(sprintf('%o',fileperms("/$path_check[1]/$path_check[2]")),-1); // get mountpoint permissions for others
			if ($fp >= 5) { // transmission needs at least read & search permission at the mountpoint
				$directory = "/$path_check[1]/$path_check[2]"; // set to the mountpoint
				for ($i = 3; $i < $path_elements - 1; $i++) { // traverse the path and set permissions to rx
					$directory = $directory."/$path_check[$i]"; // add next level
					exec("chmod o=+r+x \"$directory\""); // set permissions to o=+r+x
				}
				$path_elements = $path_elements - 1;
				$directory = $directory."/$path_check[$path_elements]"; // add last level
				exec("chmod o=rwx \"$directory\""); // set permissions to o=rwx
			} else {
				$link = '<a href="'
					. 'disks_mount.php'
					. '">'
					. gtext('Disks | Mount Point | Management')
					. '</a>.';
				$helpinghand = sprintf(gtext('BitTorrent needs at least read & execute permissions at the mount point for directory %s!'),$path)
					. ' '
					. sprintf(gtext('Set the Read and Execute bits permission for Others (Access Restrictions | Mode) for the mount point %s in %s and hit Save in order to take them effect.'), '/' . $path_check[1] . '/' . $path_check[2], $link);
				$input_errors[] = $helpinghand;
			}
		}
	}
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = ['port','downloaddir','peerport'];
		$reqdfieldsn = [gtext('Port'),gtext('Download Directory'),gtext('Peer Port')];
		$reqdfieldst = ['port','string','port'];

		if (!empty($_POST['authrequired'])) {
			// !!! Note !!! It seems TransmissionBT does not support special characters,
			// so use 'alias' instead of 'password' check.
			$reqdfields = array_merge($reqdfields, ['username','password']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Username'),gtext('Password')]);
			$reqdfieldst = array_merge($reqdfieldst, ['alias','alias']);
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		// Add additional type checks
		if (isset($_POST['umask'])) {
			$reqdfields = array_merge($reqdfields, ['umask']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('User Mask')]);
			$reqdfieldst = array_merge($reqdfieldst, ['filemode']);
		}

		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		// Check if port is already used.
		if (services_is_port_used($_POST['port'], "bittorrent")) {
			$input_errors[] = sprintf(gtext("Port %ld is already used by another service."), $_POST['port']);
		}

		// Check port range.
		if ($_POST['port'] && ((1024 > $_POST['port']) || (65535 < $_POST['port']))) {
			$input_errors[] = sprintf(gtext("The attribute '%s' must be in the range from %d to %d."), gtext("Port"), 1024, 65535);
		}
	
		// Check directories (if exist & permisssions)
		if (!empty($_POST['incompletedir'])) change_perms($_POST['incompletedir']);
		if (!empty($_POST['watchdir'])) change_perms($_POST['watchdir']);
		if (!empty($_POST['downloaddir'])) change_perms($_POST['downloaddir']);
		if (!empty($_POST['configdir'])) change_perms($_POST['configdir']);
	}

	if (empty($input_errors)) {
		$config['bittorrent']['enable'] = isset($_POST['enable']) ? true : false;
		$config['bittorrent']['port'] = $_POST['port'];
		$config['bittorrent']['downloaddir'] = strlen($_POST['downloaddir']) > 1 ? rtrim($_POST['downloaddir'],'/') : $_POST['downloaddir'];
		$config['bittorrent']['configdir'] = strlen($_POST['configdir']) > 1 ? rtrim($_POST['configdir'],'/') : $_POST['configdir'];
		$config['bittorrent']['username'] = $_POST['username'];
		$config['bittorrent']['password'] = $_POST['password'];
		$config['bittorrent']['authrequired'] = isset($_POST['authrequired']) ? true : false;
		$config['bittorrent']['peerport'] = $_POST['peerport'];
		$config['bittorrent']['portforwarding'] = isset($_POST['portforwarding']) ? true : false;
		$config['bittorrent']['uplimit'] = $_POST['uplimit'];
		$config['bittorrent']['downlimit'] = $_POST['downlimit'];
		$config['bittorrent']['pex'] = isset($_POST['pex']) ? true : false;
		$config['bittorrent']['dht'] = isset($_POST['dht']) ? true : false;
		$config['bittorrent']['preallocation'] = $_POST['preallocation'];
		$config['bittorrent']['encryption'] = $_POST['encryption'];
		$config['bittorrent']['watchdir'] = strlen($_POST['watchdir']) > 1 ? rtrim($_POST['watchdir'],'/') : $_POST['watchdir'];
		$config['bittorrent']['incompletedir'] = strlen($_POST['incompletedir']) > 1 ? rtrim($_POST['incompletedir'],'/') : $_POST['incompletedir'];
		$config['bittorrent']['umask'] = $_POST['umask'];
		$config['bittorrent']['extraoptions'] = $_POST['extraoptions'];

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("transmission");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}
$pgtitle = [gtext('Services'),gtext('BitTorrent')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.port.disabled = endis;
	document.iform.downloaddir.disabled = endis;
	document.iform.downloaddirbrowsebtn.disabled = endis;
	document.iform.configdir.disabled = endis;
	document.iform.configdirbrowsebtn.disabled = endis;
	document.iform.authrequired.disabled = endis;
	document.iform.username.disabled = endis;
	document.iform.password.disabled = endis;
	document.iform.peerport.disabled = endis;
	document.iform.portforwarding.disabled = endis;
	document.iform.uplimit.disabled = endis;
	document.iform.downlimit.disabled = endis;
	document.iform.pex.disabled = endis;
	document.iform.dht.disabled = endis;
	document.iform.preallocation.disabled = endis;
	document.iform.encryption.disabled = endis;
	document.iform.watchdir.disabled = endis;
	document.iform.watchdirbrowsebtn.disabled = endis;
	document.iform.incompletedir.disabled = endis;
	document.iform.incompletedirbrowsebtn.disabled = endis;
	document.iform.umask.disabled = endis;
	document.iform.extraoptions.disabled = endis;
}

function authrequired_change() {
	switch (document.iform.authrequired.checked) {
		case true:
			showElementById('username_tr','show');
			showElementById('password_tr','show');
			break;

		case false:
			showElementById('username_tr','hide');
			showElementById('password_tr','hide');
			break;
	}
}
//-->
</script>
<form action="services_bittorrent.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php
					html_titleline_checkbox('enable',gtext('BitTorrent'),!empty($pconfig['enable']) ? true : false,gtext('Enable'),'enable_change(false)');
					html_inputbox('peerport',gtext('Peer Port'),$pconfig['peerport'],sprintf(gtext("Port to listen for incoming peer connections. (Default is %d)."),51413),true,5);
					html_filechooser("downloaddir",gtext("Download Directory"),$pconfig['downloaddir'],gtext("Where to save downloaded data."),$g['media_path'],true,60);
					html_filechooser("configdir",gtext("Configuration Directory"),$pconfig['configdir'],gtext("Alternative configuration directory."),$g['media_path'],false,60);
					html_checkbox("portforwarding",gtext("Port Forwarding"),!empty($pconfig['portforwarding']) ? true : false,gtext("Enable port forwarding via NAT-PMP or UPnP."),"",false);
					html_checkbox("pex",gtext("Peer Exchange"),!empty($pconfig['pex']) ? true : false,gtext("Enable peer exchange (PEX)."),"",false);
					html_checkbox("dht",gtext("Distributed Hash Table"),!empty($pconfig['dht']) ? true : false,gtext("Enable distributed hash table."),"",false);
					html_combobox("preallocation",gtext("Preallocation"),$pconfig['preallocation'],['0' => gtext('Disabled'),'1' => gtext('Fast'),'2' => gtext('Full')],gtext("Select pre-allocation mode for files. (Default is Fast)."),false);
					html_combobox("encryption",gtext("Encryption"),$pconfig['encryption'],['0' => gtext('Tolerated'),'1' => gtext('Preferred'),'2' => gtext('Required')],gtext("The peer connection encryption mode."),false);
					html_inputbox("uplimit",gtext("Upload Bandwidth"),$pconfig['uplimit'],gtext("The maximum upload bandwith in KB/s. An empty field means infinity."),false,5);
					html_inputbox("downlimit",gtext("Download Bandwidth"),$pconfig['downlimit'],gtext("The maximum download bandwith in KiB/s. An empty field means infinity."),false,5);
					html_filechooser("watchdir",gtext("Watch Directory"),$pconfig['watchdir'],gtext("Directory to watch for new .torrent files."),$g['media_path'],false,60);
					html_filechooser("incompletedir",gtext("Incomplete Directory"),$pconfig['incompletedir'],gtext("Directory for incomplete files. An empty field means disable."),$g['media_path'],false,60);
					html_inputbox("umask",gtext("User Mask"),$pconfig['umask'],sprintf(gtext("Use this option to override the default permission modes for newly created files. (%s by default)."),"0002"),false,3);
					$helpinghand = '<a href="'
						. 'http://www.freebsd.org/cgi/man.cgi?query=transmission-remote&sektion=1&manpath=FreeBSD+Ports+' . $os_release . '-RELEASE&arch=default&format=html'
						. '" target="_blank">'
						. gtext('Please check the documentation')
						. '</a>.';
					html_inputbox("extraoptions",gtext("Extra Options"),$pconfig['extraoptions'],gtext("Extra options to pass over rpc using transmission-remote.") . " " . $helpinghand,false,40);
					html_separator();
					html_titleline(gtext("Administrative WebGUI"));
					html_inputbox("port",gtext("Port"),$pconfig['port'],sprintf(gtext("Port to listen on. Default port is %d."),9091),true,5);
					html_checkbox("authrequired",gtext("Authentication"),!empty($pconfig['authrequired']) ? true : false,gtext("Require authentication."),"",false,"authrequired_change()");
					html_inputbox("username",gtext("Username"),$pconfig['username'],"",true,20);
					html_passwordbox("password",gtext("Password"),$pconfig['password'],gtext("Password for the administrative pages."),true,20);
					$if = get_ifname($config['interfaces']['lan']['if']);
					$ipaddr = get_ipaddr($if);
					$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}");
					$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					html_text("url",gtext("URL"),$text);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
				</div>
			</td>
		</tr>
	</table>
	<?php include 'formend.inc';?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
authrequired_change();
//-->
</script>
<?php include 'fend.inc';?>

