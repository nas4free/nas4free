<?php
/*
	services_samba.php

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

array_make_branch($config,'samba','auxparam');
sort($config['samba']['auxparam']);
array_make_branch($config,'mounts','mount');
array_sort_key($config['mounts']['mount'],'devicespecialfile');
$a_mount = &$config['mounts']['mount'];

$pconfig['netbiosname'] = $config['samba']['netbiosname'];
$pconfig['workgroup'] = $config['samba']['workgroup'];
$pconfig['serverdesc'] = $config['samba']['serverdesc'];
$pconfig['security'] = $config['samba']['security'];
$pconfig['maxprotocol'] = $config['samba']['maxprotocol'] ?? '';
$pconfig['minprotocol'] = $config['samba']['minprotocol'] ?? '';
$pconfig['clientmaxprotocol'] = $config['samba']['clientmaxprotocol'] ?? '';
$pconfig['clientminprotocol'] = $config['samba']['clientminprotocol'] ?? '';
$pconfig['if'] = !empty($config['samba']['if']) ? $config['samba']['if'] : "";
$pconfig['localmaster'] = $config['samba']['localmaster'];
$pconfig['pwdsrv'] = !empty($config['samba']['pwdsrv']) ? $config['samba']['pwdsrv'] : "";
$pconfig['winssrv'] = !empty($config['samba']['winssrv']) ? $config['samba']['winssrv'] : "";
$pconfig['timesrv'] = !empty($config['samba']['timesrv']) ? $config['samba']['timesrv'] : "";
$pconfig['trusteddomains'] = isset($config['samba']['trusteddomains']);
$pconfig['unixcharset'] = !empty($config['samba']['unixcharset']) ? $config['samba']['unixcharset'] : "";
$pconfig['doscharset'] = !empty($config['samba']['doscharset']) ? $config['samba']['doscharset'] : "";
$pconfig['loglevel'] = !empty($config['samba']['loglevel']) ? $config['samba']['loglevel'] : "";
$pconfig['sndbuf'] = $config['samba']['sndbuf'];
$pconfig['rcvbuf'] = $config['samba']['rcvbuf'];
$pconfig['enable'] = isset($config['samba']['enable']);
$pconfig['largereadwrite'] = isset($config['samba']['largereadwrite']);
$pconfig['easupport'] = isset($config['samba']['easupport']);
$pconfig['storedosattributes'] = isset($config['samba']['storedosattributes']);
$pconfig['mapdosattributes'] = isset($config['samba']['mapdosattributes']);
$pconfig['createmask'] = !empty($config['samba']['createmask']) ? $config['samba']['createmask'] : "";
$pconfig['directorymask'] = !empty($config['samba']['directorymask']) ? $config['samba']['directorymask'] : "";
$pconfig['guestaccount'] = $config['samba']['guestaccount'];
$pconfig['maptoguest'] = $config['samba']['maptoguest'];
$pconfig['nullpasswords'] = isset($config['samba']['nullpasswords']);
$pconfig['aio'] = isset($config['samba']['aio']);
$pconfig['aiorsize'] = $config['samba']['aiorsize'];
$pconfig['aiowsize'] = $config['samba']['aiowsize'];
$pconfig['aiowbehind'] = $config['samba']['aiowbehind'];
if(is_array($config['samba']['auxparam'])):
	$pconfig['auxparam'] = implode("\n", $config['samba']['auxparam']);
endif;

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	if (isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = ['security','netbiosname','workgroup'];
		$reqdfieldsn = [gtext('Authentication'),gtext('NetBIOS Name'),gtext('Workgroup')];
		$reqdfieldst = ['string','domain','workgroup'];

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		// Do additional input type validation.
		$reqdfields = ['sndbuf','rcvbuf'];
		$reqdfieldsn = [gtext('Send Buffer'),gtext('Receive Buffer')];
		$reqdfieldst = ['numericint','numericint'];

		// samba 4+ does not have "share". you can delete this in future.
		if ($_POST['security'] == 'share') {
			if(preg_match('/^SMB[23]/',$_POST['maxprotocol']) || preg_match('/^SMB[23]/',$_POST['minprotocol'])) {
				$input_errors[] = gtext('Anonymous access has been deprecated starting from SMB2.');
			}
		}
		if (!empty($_POST['createmask']) || !empty($_POST['directorymask'])) {
			$reqdfields = array_merge($reqdfields, ['createmask','directorymask']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Create Mask'),gtext('Directory Mask')]);
			$reqdfieldst = array_merge($reqdfieldst, ['filemode','filemode']);
		}
		if (!empty($_POST['pwdsrv'])) {
			$reqdfields = array_merge($reqdfields, ['pwdsrv']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('Password Server')]);
			$reqdfieldst = array_merge($reqdfieldst, ['string']);
		}
		if (!empty($_POST['winssrv'])) {
			$reqdfields = array_merge($reqdfields, ['winssrv']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('WINS Server')]);
			$reqdfieldst = array_merge($reqdfieldst, ['ipaddr']);
		}
		if (isset($_POST['aio']) && $_POST['aio']) {
			$reqdfields = array_merge($reqdfields, ['aiorsize','aiowsize']);
			$reqdfieldsn = array_merge($reqdfieldsn, [gtext('AIO Read Size'),gtext('AIO Write Size')]);
			$reqdfieldst = array_merge($reqdfieldst, ['numericint','numericint']);
		}

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
	}

	if (empty($input_errors)) {
		$config['samba']['enable'] = isset($_POST['enable']) ? true : false;
		$config['samba']['netbiosname'] = $_POST['netbiosname'];
		$config['samba']['workgroup'] = $_POST['workgroup'];
		$config['samba']['serverdesc'] = $_POST['serverdesc'];
		$config['samba']['security'] = $_POST['security'];
		if ($_POST['security'] == 'share') {
			$config['samba']['maxprotocol'] = 'NT1';
		} else {
			$config['samba']['maxprotocol'] = $_POST['maxprotocol'] ?? '';
		}
		if ($_POST['security'] == 'share') {
			$config['samba']['minprotocol'] = 'NT1';
		} else {
			$config['samba']['minprotocol'] = $_POST['minprotocol'] ?? '';
		}
		$config['samba']['clientmaxprotocol'] = $_POST['clientmaxprotocol'] ?? '';
		$config['samba']['clientminprotocol'] = $_POST['clientminprotocol'] ?? '';
		$config['samba']['if'] = $_POST['if'];
		$config['samba']['localmaster'] = $_POST['localmaster'];
		$config['samba']['pwdsrv'] = $_POST['pwdsrv'];
		$config['samba']['winssrv'] = $_POST['winssrv'];
		$config['samba']['timesrv'] = $_POST['timesrv'];
		$config['samba']['trusteddomains'] = isset($_POST['trusteddomains']) ? true : false;
		$config['samba']['doscharset'] = $_POST['doscharset'];
		$config['samba']['unixcharset'] = $_POST['unixcharset'];
		$config['samba']['loglevel'] = $_POST['loglevel'];
		$config['samba']['sndbuf'] = $_POST['sndbuf'];
		$config['samba']['rcvbuf'] = $_POST['rcvbuf'];
		$config['samba']['largereadwrite'] = isset($_POST['largereadwrite']) ? true : false;
		$config['samba']['easupport'] = isset($_POST['easupport']) ? true : false;
		$config['samba']['storedosattributes'] = isset($_POST['storedosattributes']) ? true : false;
		$config['samba']['mapdosattributes'] = isset($_POST['mapdosattributes']) ? true : false;
		if (!empty($_POST['createmask']))
			$config['samba']['createmask'] = $_POST['createmask'];
		else
			unset($config['samba']['createmask']);
		if (!empty($_POST['directorymask']))
			$config['samba']['directorymask'] = $_POST['directorymask'];
		else
			unset($config['samba']['directorymask']);
		if (!empty($_POST['guestaccount']))
			$config['samba']['guestaccount'] = $_POST['guestaccount'];
		else
			unset($config['samba']['guestaccount']);
		$config['samba']['maptoguest'] = $_POST['maptoguest'];
		$config['samba']['nullpasswords'] = isset($_POST['nullpasswords']) ? true : false;
		$config['samba']['aio'] = isset($_POST['aio']) ? true : false;
		if (isset($_POST['aio']) && $_POST['aio']) {
			$config['samba']['aiorsize'] = $_POST['aiorsize'];
			$config['samba']['aiowsize'] = $_POST['aiowsize'];
			$config['samba']['aiowbehind'] = '';
		}

		# Write additional parameters.
		unset($config['samba']['auxparam']);
		foreach (explode("\n", $_POST['auxparam']) as $auxparam) {
			$auxparam = trim($auxparam, "\t\n\r");
			if (!empty($auxparam))
				$config['samba']['auxparam'][] = $auxparam;
		}

		write_config();

		$retval = 0;
		if (!file_exists($d_sysrebootreqd_path)) {
			config_lock();
			$retval |= rc_update_service("samba");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}

		$savemsg = get_std_save_message($retval);
	}
}
$l_protocol = [
	'' => gtext('Default'),
	'SMB3' => gtext('SMB3'),
	'SMB3_11' => gtext('SMB3_11 (Windows 10)'),
	'SMB3_02' => gtext('SMB3 (Windows 8.1)'),
	'SMB3_00' => gtext('SMB3 (Windows 8)'),
	'SMB2' => gtext('SMB2'),
	'NT1' => gtext('NT1 (CIFS)')
];
$desc_anyanyprot = gtext('Normally this option should not be set as the automatic negotiation phase in the SMB protocol takes care of choosing the appropriate protocol.');
$desc_srvmaxprot = gtext('This parameter sets the highest protocol level that will be supported by the server.');
$desc_srvminprot = gtext('This setting controls the minimum protocol version that the server will allow the client to use.');
$desc_climaxprot = gtext('This parameter sets the highest protocol level that will be supported by the client.');
$desc_climinprot = gtext('This setting controls the minimum protocol version that the client will attempt to use.');
$pgtitle = [gtext('Services'),gtext('CIFS/SMB'),gtext('Settings')];
?>
<?php include 'fbegin.inc';?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.netbiosname.disabled = endis;
	document.iform.workgroup.disabled = endis;
	document.iform.localmaster.disabled = endis;
	document.iform.pwdsrv.disabled = endis;
	document.iform.winssrv.disabled = endis;
	document.iform.timesrv.disabled = endis;
	document.iform.trusteddomains.disabled = endis;
	document.iform.serverdesc.disabled = endis;
	document.iform.doscharset.disabled = endis;
	document.iform.unixcharset.disabled = endis;
	document.iform.loglevel.disabled = endis;
	document.iform.sndbuf.disabled = endis;
	document.iform.rcvbuf.disabled = endis;
	document.iform.security.disabled = endis;
	document.iform.maxprotocol.disabled = endis;
	document.iform.minprotocol.disabled = endis;
	document.iform.clientmaxprotocol.disabled = endis;
	document.iform.clientminprotocol.disabled = endis;
	document.iform.if.disabled = endis;
	document.iform.largereadwrite.disabled = endis;
	document.iform.easupport.disabled = endis;
	document.iform.storedosattributes.disabled = endis;
	document.iform.mapdosattributes.disabled = endis;
	document.iform.createmask.disabled = endis;
	document.iform.directorymask.disabled = endis;
	document.iform.guestaccount.disabled = endis;
	document.iform.maptoguest.disabled = endis;
	document.iform.nullpasswords.disabled = endis;
	document.iform.aio.disabled = endis;
	document.iform.aiorsize.disabled = endis;
	document.iform.aiowsize.disabled = endis;
	document.iform.auxparam.disabled = endis;
}

function authentication_change() {
	switch(document.iform.security.value) {
		case "share":
			showElementById('createmask_tr','show');
			showElementById('directorymask_tr','show');
			showElementById('pwdsrv_tr','hide');
			showElementById('winssrv_tr','hide');
			showElementById('trusteddomains_tr','hide');
			break;
		case "ads":
			showElementById('createmask_tr','hide');
			showElementById('directorymask_tr','hide');
			showElementById('pwdsrv_tr','show');
			showElementById('winssrv_tr','show');
			showElementById('trusteddomains_tr','show');
			break;
		default:
			showElementById('createmask_tr','hide');
			showElementById('directorymask_tr','hide');
			showElementById('pwdsrv_tr','hide');
			showElementById('winssrv_tr','hide');
			showElementById('trusteddomains_tr','hide');
			break;
	}
}

function aio_change() {
	switch (document.iform.aio.checked) {
		case true:
			showElementById('aiorsize_tr','show');
			showElementById('aiowsize_tr','show');
			break;

		case false:
			showElementById('aiorsize_tr','hide');
			showElementById('aiowsize_tr','hide');
			break;
	}
}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="services_samba.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Settings");?></span></a></li>
				<li class="tabinact"><a href="services_samba_share.php"><span><?=gtext("Shares");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="services_samba.php" method="post" name="iform" id="iform" onsubmit="spinner()">
				<?php if (!empty($input_errors)) print_input_errors($input_errors);?>
				<?php if (!empty($savemsg)) print_info_box($savemsg);?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Common Internet File System"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
					<?php html_combobox("security", gtext("Authentication"), $pconfig['security'], ['user' => gtext('Local User'),'ads' => gtext('Active Directory')], "", true, false, "authentication_change()");?>
					<?php 
					html_combobox("maxprotocol", gtext('Server Max. Protocol'), $pconfig['maxprotocol'], $l_protocol, sprintf('%s %s',$desc_srvmaxprot,$desc_anyanyprot),false,false,'');
					html_combobox("minprotocol", gtext('Server Min. Protocol'), $pconfig['minprotocol'], $l_protocol, sprintf("%s %s",$desc_srvminprot,$desc_anyanyprot),false,false,'');
					html_combobox("clientmaxprotocol", gtext('Client Max. Protocol'), $pconfig['clientmaxprotocol'], $l_protocol, sprintf("%s %s",$desc_climaxprot,$desc_anyanyprot),false,false,'');
					html_combobox("clientminprotocol", gtext('Client Min. Protocol'), $pconfig['clientminprotocol'], $l_protocol, sprintf("%s %s",$desc_climinprot,$desc_anyanyprot),false,false,'');
					?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("NetBIOS Name");?></td>
						<td width="78%" class="vtable">
							<input name="netbiosname" type="text" class="formfld" id="netbiosname" size="30" value="<?=htmlspecialchars($pconfig['netbiosname']);?>" />
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Workgroup") ; ?></td>
						<td width="78%" class="vtable">
							<input name="workgroup" type="text" class="formfld" id="workgroup" size="30" value="<?=htmlspecialchars($pconfig['workgroup']);?>" />
							<br /><?=gtext("The workgroup in which the server will appear when queried by Windows or SMB clients. (maximum 15 characters).");?>
						</td>
					</tr>
					<?php html_combobox("if", gtext("Interface Selection"), $pconfig['if'], array("" => gtext("ALL Interfaces"), "lan" => gtext("LAN Only"), "opt" => gtext("OPT Only"), "carp" => gtext("CARP only")), "", false);?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Description") ;?></td>
						<td width="78%" class="vtable">
							<input name="serverdesc" type="text" class="formfld" id="serverdesc" size="30" value="<?=htmlspecialchars($pconfig['serverdesc']);?>" />
							<br /><?=gtext("Server description. This can usually be left blank.") ;?>
						</td>
					</tr>
					<?php html_combobox("doscharset", gtext("Dos Charset"), $pconfig['doscharset'], ['CP437' => gtext('CP437 (Latin US)'), 'CP850' => gtext('CP850 (Latin 1)'), 'CP852' => gtext('CP852 (Latin 2)'), 'CP866' => gtext('CP866 (Cyrillic CIS 1)'), 'CP932' => gtext('CP932 (Japanese Shift-JIS)'), 'CP936' => gtext('CP936 (Simplified Chinese GBK)'), 'CP949' => gtext('CP949 (Korean)'), 'CP950' => gtext('CP950 (Traditional Chinese Big5)'), 'CP1251' => gtext('CP1251 (Cyrillic)'), 'CP1252' => gtext('CP1252 (Latin 1)'), 'ASCII' => 'ASCII'], "", false);?>
					<?php html_combobox("unixcharset", gtext("Unix Charset"), $pconfig['unixcharset'], ['UTF-8' => 'UTF-8', 'iso-8859-1' => 'ISO-8859-1', 'iso-8859-15' => 'ISO-8859-15', 'gb2312' => 'GB2312', 'EUC-JP' => 'EUC-JP', 'ASCII' => 'ASCII'], "", false);?>
					<?php html_combobox("loglevel", gtext("Log Level"), $pconfig['loglevel'], ['0' => gtext('Disabled'), '1' => gtext('Minimum'), '2' => gtext('Normal'), '3' => gtext('Full'), '10' => gtext('Debug')], "", false);?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Local Master Browser"); ?></td>
						<td width="78%" class="vtable">
							<select name="localmaster" class="formfld" id="localmaster">
								<?php $types = [gtext('Yes'),gtext('No')]; $vals = explode(" ", "yes no");?>
								<?php $j = 0; for ($j = 0; $j < count($vals); $j++): ?>
									<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['localmaster']) echo "selected=\"selected\"";?>>
									<?=htmlspecialchars($types[$j]);?>
									</option>
								<?php endfor; ?>
							</select>
							<br /><?php echo sprintf(gtext("Allows the server to try and become a local master browser."));?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Time Server"); ?></td>
						<td width="78%" class="vtable">
							<select name="timesrv" class="formfld" id="timesrv">
								<?php $types = [gtext('Yes'),gtext('No')]; $vals = explode(" ", "yes no");?>
								<?php $j = 0; for ($j = 0; $j < count($vals); $j++): ?>
									<option value="<?=$vals[$j];?>" <?php if ($vals[$j] == $pconfig['timesrv']) echo "selected=\"selected\"";?>>
										<?=htmlspecialchars($types[$j]);?>
									</option>
								<?php endfor; ?>
							</select>
							<br /><?php echo sprintf(gtext("The server advertises itself as a time server to Windows clients."));?>
						</td>
					</tr>
					<tr id="pwdsrv_tr">
						<td width="22%" valign="top" class="vncell"><?=gtext("Password Server"); ?></td>
						<td width="78%" class="vtable">
							<input name="pwdsrv" type="text" class="formfld" id="pwdsrv" size="30" value="<?=htmlspecialchars($pconfig['pwdsrv']);?>" />
							<br /><?=gtext("Password server name or IP address (e.g. Active Directory domain controller).");?>
						</td>
					</tr>
					<tr id="winssrv_tr">
						<td width="22%" valign="top" class="vncell"><?=gtext("WINS Server"); ?></td>
						<td width="78%" class="vtable">
							<input name="winssrv" type="text" class="formfld" id="winssrv" size="30" value="<?=htmlspecialchars($pconfig['winssrv']);?>" />
							<br /><?=gtext("WINS server IP address (e.g. from MS Active Directory server).");?>
						</td>
					</tr>
					<?php html_checkbox("trusteddomains", gtext("Trusted Domains"), !empty($pconfig['trusteddomains']) ? true : false, gtext("Allow trusted domains."), gtext("If allowed, a user of the trusted domains can access the share."), false);?>
					<tr>
						<td colspan="2" class="list" height="12"></td>
					</tr>
					<tr>
						<td colspan="2" valign="top" class="listtopic"><?=gtext("Advanced Settings");?></td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Guest Account");?></td>
						<td width="78%" class="vtable">
							<input name="guestaccount" type="text" class="formfld" id="guestaccount" size="30" value="<?=htmlspecialchars($pconfig['guestaccount']);?>" />
							<br /><?=gtext("Use this option to override the username ('ftp' by default) which will be used for access to services which are specified as guest. Whatever privileges this user has will be available to any client connecting to the guest service. This user must exist in the password file, but does not require a valid login.");?>
						</td>
					</tr>
					<?php html_combobox("maptoguest", gtext("Map to Guest"), $pconfig['maptoguest'], ['Never' => gtext('Never - (Default)'), 'Bad User' => gtext('Bad User - (Non Existing Users)')], "", false, false, "");?>
					<tr id="createmask_tr">
						<td width="22%" valign="top" class="vncell"><?=gtext("Create Mask"); ?></td>
						<td width="78%" class="vtable">
							<input name="createmask" type="text" class="formfld" id="createmask" size="30" value="<?=htmlspecialchars($pconfig['createmask']);?>" />
							<br /><?=gtext("Use this option to override the file creation mask (0666 by default).");?>
						</td>
					</tr>
					<tr id="directorymask_tr">
						<td width="22%" valign="top" class="vncell"><?=gtext("Directory Mask"); ?></td>
						<td width="78%" class="vtable">
							<input name="directorymask" type="text" class="formfld" id="directorymask" size="30" value="<?=htmlspecialchars($pconfig['directorymask']);?>" />
							<br /><?=gtext("Use this option to override the directory creation mask (0777 by default).");?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Send Buffer"); ?></td>
						<td width="78%" class="vtable">
							<input name="sndbuf" type="text" class="formfld" id="sndbuf" size="30" value="<?=htmlspecialchars($pconfig['sndbuf']);?>" />
							<br /><?=sprintf(gtext("Size of send buffer (%d by default)."), 65536); ?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Receive Buffer") ; ?></td>
						<td width="78%" class="vtable">
							<input name="rcvbuf" type="text" class="formfld" id="rcvbuf" size="30" value="<?=htmlspecialchars($pconfig['rcvbuf']);?>" />
							<br /><?=sprintf(gtext("Size of receive buffer (%d by default)."), 65536); ?>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Large Read/Write");?></td>
						<td width="78%" class="vtable">
							<input name="largereadwrite" type="checkbox" id="largereadwrite" value="yes" <?php if (isset($pconfig['largereadwrite']) && $pconfig['largereadwrite']) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable large read/write.");?><span class="vexpl"><br />
							<?=gtext("Use the new 64k streaming read and write variant SMB requests introduced with Windows 2000.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("EA Support");?></td>
						<td width="78%" class="vtable">
							<input name="easupport" type="checkbox" id="easupport" value="yes" <?php if (isset($pconfig['easupport']) && $pconfig['easupport']) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable extended attribute support.");?><span class="vexpl"><br />
							<?=gtext("Allow clients to attempt to store OS/2 style extended attributes on a share.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Store DOS Attributes");?></td>
						<td width="78%" class="vtable">
							<input name="storedosattributes" type="checkbox" id="storedosattributes" value="yes" <?php if (isset($pconfig['storedosattributes']) && $pconfig['storedosattributes']) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable store DOS attributes.");?><span class="vexpl"><br />
							<?=gtext("If this parameter is set, Samba attempts to first read DOS attributes (SYSTEM, HIDDEN, ARCHIVE or READ-ONLY) from a filesystem extended attribute, before mapping DOS attributes to UNIX permission bits. When set, DOS attributes will be stored onto an extended attribute in the UNIX filesystem, associated with the file or directory.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Mapping DOS Attributes");?></td>
						<td width="78%" class="vtable">
							<input name="mapdosattributes" type="checkbox" id="mapdosattributes" value="yes" <?php if (isset($pconfig['mapdosattributes']) && $pconfig['mapdosattributes']) echo "checked=\"checked\""; ?> />
							<?=gtext("Enable mapping DOS attributes.");?><span class="vexpl"><br />
							<?=gtext("Convert DOS attributes to UNIX execution bits when Store DOS attributes is disabled.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Null Passwords");?></td>
						<td width="78%" class="vtable">
							<input name="nullpasswords" type="checkbox" id="nullpasswords" value="yes" <?php if (isset($pconfig['nullpasswords']) && $pconfig['nullpasswords']) echo "checked=\"checked\""; ?> />
							<?=gtext("Allow client access to accounts that have null passwords.");?>
						</td>
					</tr>
					<?php
					html_checkbox("aio", gtext("Asynchronous I/O"), !empty($pconfig['aio']) ? true : false, gtext("Enable Asynchronous I/O. (AIO)."), "", false, "aio_change()");
					html_inputbox("aiorsize", gtext("AIO Read Size"), $pconfig['aiorsize'], sprintf(gtext("Samba will read from file asynchronously when size of request is bigger than this value. (%d by default)."), 1024), true, 30);
					html_inputbox("aiowsize", gtext("AIO Write Size"), $pconfig['aiowsize'], sprintf(gtext("Samba will write to file asynchronously when size of request is bigger than this value. (%d by default)."), 1024), true, 30);
					/*html_inputbox("aiowbehind", gtext("AIO write behind"), $pconfig['aiowbehind'], "", false, 60);*/
					$helpinghand = '<a href="'
						. 'http://us1.samba.org/samba/docs/man/manpages-3/smb.conf.5.html'
						. '" target="_blank">'
						. gtext('Please check the documentation')
						. '</a>.';
					html_textarea("auxparam", gtext("Additional Parameters"), $pconfig['auxparam'], sprintf(gtext("These parameters are added to [Global] section of %s."), "smb4.conf") . " " . $helpinghand, false, 65, 5, false, false);
					?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
				</div>
				<div id="remarks">
					<?php
					$helpinghand = gtext('To increase CIFS performance try the following:')
						. '<div id="enumeration"><ul>'
						. '<li>' . gtext("Enable 'Asynchronous I/O (AIO).' switch.") . '</li>'
						. '<li>' . gtext("Enable 'Large read/write' switch.") . '</li>'
						. '<li>' . '<a href="' . 'system_advanced.php' . '">' . gtext('Enable tuning switch') . '</a>.' . '</li>'
						. '</ul></div>';
					html_remark("note", gtext('Note'), $helpinghand );
					?>
				</div>
				<?php include 'formend.inc';?>
			</form>
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_change(false);
authentication_change();
aio_change();
//-->
</script>
<?php include 'fend.inc';?>
