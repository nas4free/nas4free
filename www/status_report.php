<?php
/*
	status_report.php.

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
require("email.inc");
require("report.inc");

$pgtitle = array(gtext("Status"), gtext("Email Report"));

if (!isset($config['statusreport']) || !is_array($config['statusreport']))
	$config['statusreport'] = array();

$pconfig['enable'] = isset($config['statusreport']['enable']);
$pconfig['to'] = $config['statusreport']['to'];
$pconfig['subject'] = $config['statusreport']['subject'];
$pconfig['report'] = $config['statusreport']['report'];
$pconfig['report_scriptname'] = $config['statusreport']['report_scriptname'];
$pconfig['minute'] = $config['statusreport']['minute'];
$pconfig['hour'] = $config['statusreport']['hour'];
$pconfig['day'] = $config['statusreport']['day'];
$pconfig['month'] = $config['statusreport']['month'];
$pconfig['weekday'] = $config['statusreport']['weekday'];
$pconfig['all_mins'] = $config['statusreport']['all_mins'];
$pconfig['all_hours'] = $config['statusreport']['all_hours'];
$pconfig['all_days'] = $config['statusreport']['all_days'];
$pconfig['all_months'] = $config['statusreport']['all_months'];
$pconfig['all_weekdays'] = $config['statusreport']['all_weekdays'];

$a_months = explode(" ",gtext("January February March April May June July August September October November December"));
$a_weekdays = explode(" ",gtext("Sunday Monday Tuesday Wednesday Thursday Friday Saturday"));

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	// Input validation.
	if(isset($_POST['enable']) && $_POST['enable']) {
		$reqdfields = explode(" ", "to");
		$reqdfieldsn = array(gtext("To e-mail"));
		$reqdfieldst = explode(" ", "string");

		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		if (isset($_POST["Submit"]) && $_POST["Submit"]) {
			// Validate synchronization time
			do_input_validate_synctime($_POST, $input_errors);
		}

		// custom script
		if (is_array($_POST['report']) && in_array("script", $_POST['report'])) {
			if ($_POST['report_scriptname'] == '') {
				$input_errors[] = gtext("Custom script is required.");
			} else if (!file_exists($_POST['report_scriptname'])) {
				$input_errors[] = gtext("Custom script is not found.");
			}
		}
	}

	if (empty($input_errors)) {
		$config['statusreport']['enable'] = isset($_POST['enable']) ? true : false;
		$config['statusreport']['to'] = $_POST['to'];
		$config['statusreport']['subject'] = $_POST['subject'];
		$config['statusreport']['report'] = $_POST['report'];
		$config['statusreport']['report_scriptname'] = $_POST['report_scriptname'];
		$config['statusreport']['minute'] = !empty($_POST['minute']) ? $_POST['minute'] : null;
		$config['statusreport']['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$config['statusreport']['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$config['statusreport']['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$config['statusreport']['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$config['statusreport']['all_mins'] = $_POST['all_mins'];
		$config['statusreport']['all_hours'] = $_POST['all_hours'];
		$config['statusreport']['all_days'] = $_POST['all_days'];
		$config['statusreport']['all_months'] = $_POST['all_months'];
		$config['statusreport']['all_weekdays'] = $_POST['all_weekdays'];

		write_config();

		if(isset($_POST['SendReportNow']) && $_POST['SendReportNow']) {
			// Send an email status report now.
			$retval = @report_send_mail();
			if (0 == $retval) {
				$savemsg = gtext("Status report successfully sent.");
			} else {
				$failmsg = gtext('Failed to send status report.')
					. ' '
					. '<a href="' . 'diag_log.php' . '">'
					. gtext('Please check the log files')
					. '</a>.';
			}
		} else {
			// Configure cron job.
			if (!file_exists($d_sysrebootreqd_path)) {
				config_lock();
				$retval = rc_update_service("cron");
				config_unlock();
			}

			$savemsg = get_std_save_message($retval);
		}
	}
}
?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}

function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.to.disabled = endis;
	document.iform.subject.disabled = endis;
	document.iform.report_systeminfo.disabled = endis;
	document.iform.report_dmesg.disabled = endis;
	document.iform.report_systemlog.disabled = endis;
	document.iform.report_ftplog.disabled = endis;
	document.iform.report_rsynclog.disabled = endis;
	document.iform.report_sshdlog.disabled = endis;
	document.iform.report_smartdlog.disabled = endis;
	document.iform.report_daemonlog.disabled = endis;
	document.iform.report_script.disabled = endis;
	document.iform.report_scriptname.disabled = endis;
	document.iform.report_scriptnamebrowsebtn.disabled = endis;
	document.iform.minutes1.disabled = endis;
	document.iform.minutes2.disabled = endis;
	document.iform.minutes3.disabled = endis;
	document.iform.minutes4.disabled = endis;
	document.iform.minutes5.disabled = endis;
	document.iform.hours1.disabled = endis;
	document.iform.hours2.disabled = endis;
	document.iform.days1.disabled = endis;
	document.iform.days2.disabled = endis;
	document.iform.days3.disabled = endis;
	document.iform.months.disabled = endis;
	document.iform.weekdays.disabled = endis;
	document.iform.all_mins1.disabled = endis;
	document.iform.all_mins2.disabled = endis;
	document.iform.all_hours1.disabled = endis;
	document.iform.all_hours2.disabled = endis;
	document.iform.all_days1.disabled = endis;
	document.iform.all_days2.disabled = endis;
	document.iform.all_months1.disabled = endis;
	document.iform.all_months2.disabled = endis;
	document.iform.all_weekdays1.disabled = endis;
	document.iform.all_weekdays2.disabled = endis;
	document.iform.sendnow.disabled = endis;
}
//-->
</script>
<form action="status_report.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td class="tabcont">
	    	<?php
			if (0 !== email_validate_settings()) {
				$helpinghand = '<a href="' . 'system_email.php' . '">'
					. gtext('Make sure you have already configured your email settings')
					. '</a>.';
				print_error_box($helpinghand);
			}
			if (!empty($input_errors)) {
				print_input_errors($input_errors);
			}
			if (!empty($savemsg)) {
				print_info_box($savemsg);
			}
			if (!empty($failmsg)) {
				print_error_box($failmsg);
			}
			?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_titleline_checkbox("enable", gtext("Email Report"), !empty($pconfig['enable']) ? true : false, gtext("Enable"), "enable_change(false)");?>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("To email");?></td>
						<td width="78%" class="vtable">
							<input name="to" type="text" class="formfld" id="to" size="74" value="<?=htmlspecialchars($pconfig['to']);?>" /><br />
							<span class="vexpl"><?=gtext("Destination email address.");?> <?=gtext("Separate email addresses by semi-colon.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gtext("Subject");?></td>
						<td width="78%" class="vtable">
							<input name="subject" type="text" class="formfld" id="subject" size="74" value="<?=htmlspecialchars($pconfig['subject']);?>" /><br />
							<?php
							$helpinghand = '<div id="enumeration"><ul><li>%d - ' . gtext('Date') . '</li><li>%h - ' . gtext('Hostname') . '</li></ul></div>';
							?>
							<span class="vexpl"><?=gtext('The subject of the email.') . ' ' . gtext('You can use the following parameters for substitution:');?></span><?=$helpinghand;?>
						</td>
					</tr>
					<tr>
				    <td width="22%" valign="top" class="vncell"><?=gtext("Reports");?></td>
			      <td width="78%" class="vtable">
			      	<table>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_systeminfo" value="systeminfo" <?php if (is_array($pconfig['report']) && in_array("systeminfo", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("System info");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_dmesg" value="dmesg" <?php if (is_array($pconfig['report']) && in_array("dmesg", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("System message buffer");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_systemlog" value="systemlog" <?php if (is_array($pconfig['report']) && in_array("systemlog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("System log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_ftplog" value="ftplog" <?php if (is_array($pconfig['report']) && in_array("ftplog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("FTP log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_rsynclog" value="rsynclog" <?php if (is_array($pconfig['report']) && in_array("rsynclog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("RSYNC log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_sshdlog" value="sshdlog" <?php if (is_array($pconfig['report']) && in_array("sshdlog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("SSHD log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_smartdlog" value="smartdlog" <?php if (is_array($pconfig['report']) && in_array("smartdlog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("S.M.A.R.T. log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_daemonlog" value="daemonlog" <?php if (is_array($pconfig['report']) && in_array("daemonlog", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("Daemon log");?></td></tr>
								<tr><td><input name="report[]" type="checkbox" class="formfld" id="report_script" value="script" <?php if (is_array($pconfig['report']) && in_array("script", $pconfig['report'])):?>checked="checked"<?php endif;?> /><?=gtext("Custom script");?></td></tr>
								<tr><td>
<?php
	$scriptname = $pconfig['report_scriptname'];
	$scriptpath = "/mnt";
	$ctrl = new HTMLFileChooser("report_scriptname", "", "$scriptname", "", 65);
	$ctrl->SetRequired(false);
	$ctrl->SetReadOnly(false);
	$ctrl->SetPath($scriptpath);
	$ctrl->RenderCtrl();
?>
								</td></tr>
			        </table>
			      </td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncellreq"><?=gtext("Polling time");?></td>
						<td width="78%" class="vtable">
							<table width="100%" border="0" cellpadding="5" cellspacing="0">
								<tr>
									<td class="listhdrlr"><?=gtext("Minutes");?></td>
									<td class="listhdrr"><?=gtext("Hours");?></td>
									<td class="listhdrr"><?=gtext("Days");?></td>
									<td class="listhdrr"><?=gtext("Months");?></td>
									<td class="listhdrr"><?=gtext("Week days");?></td>
								</tr>
								<tr>
									<td class="listlr">
										<input type="radio" name="all_mins" id="all_mins1" value="1" <?php if (1 == $pconfig['all_mins']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_mins" id="all_mins2" value="0" <?php if (1 != $pconfig['all_mins']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes1" onchange="set_selected('all_mins')">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes2" onchange="set_selected('all_mins')">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes3" onchange="set_selected('all_mins')">
														<?php for ($i = 24; $i <= 35; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes4" onchange="set_selected('all_mins')">
														<?php for ($i = 36; $i <= 47; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="minute[]" id="minutes5" onchange="set_selected('all_mins')">
														<?php for ($i = 48; $i <= 59; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['minute']) && is_array($pconfig['minute']) && in_array("$i", $pconfig['minute'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
										<br />
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_hours" id="all_hours1" value="1" <?php if (1 == $pconfig['all_hours']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_hours" id="all_hours2" value="0" <?php if (1 != $pconfig['all_hours']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="hour[]" id="hours1" onchange="set_selected('all_hours')">
														<?php for ($i = 0; $i <= 11; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['hour']) && is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="hour[]" id="hours2" onchange="set_selected('all_hours')">
														<?php for ($i = 12; $i <= 23; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['hour']) && is_array($pconfig['hour']) && in_array("$i", $pconfig['hour'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_days" id="all_days1" value="1" <?php if (1 == $pconfig['all_days']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_days" id="all_days2" value="0" <?php if (1 != $pconfig['all_days']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="day[]" id="days1" onchange="set_selected('all_days')">
														<?php for ($i = 1; $i <= 12; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="12" name="day[]" id="days2" onchange="set_selected('all_days')">
														<?php for ($i = 13; $i <= 24; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
												<td valign="top">
													<select multiple="multiple" size="7" name="day[]" id="days3" onchange="set_selected('all_days')">
														<?php for ($i = 25; $i <= 31; $i++):?>
														<option value="<?=$i;?>" <?php if (!empty($pconfig['day']) && is_array($pconfig['day']) && in_array("$i", $pconfig['day'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($i);?></option>
														<?php endfor;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_months" id="all_months1" value="1" <?php if (1 == $pconfig['all_months']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_months" id="all_months2" value="0" <?php if (1 != $pconfig['all_months']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="12" name="month[]" id="months" onchange="set_selected('all_months')">
														<?php $i = 1; foreach ($a_months as $month):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['month']) && in_array("$i", $pconfig['month'])) echo "selected=\"selected\"";?>><?=htmlspecialchars($month);?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
									<td class="listr" valign="top">
										<input type="radio" name="all_weekdays" id="all_weekdays1" value="1" <?php if (1 == $pconfig['all_weekdays']) echo "checked=\"checked\"";?> />
										<?=gtext("All");?><br />
										<input type="radio" name="all_weekdays" id="all_weekdays2" value="0" <?php if (1 != $pconfig['all_weekdays']) echo "checked=\"checked\"";?> />
										<?=gtext("Selected");?> ..<br />
										<table>
											<tr>
												<td valign="top">
													<select multiple="multiple" size="7" name="weekday[]" id="weekdays" onchange="set_selected('all_weekdays')">
														<?php $i = 0; foreach ($a_weekdays as $day):?>
														<option value="<?=$i;?>" <?php if (isset($pconfig['weekday']) && in_array("$i", $pconfig['weekday'])) echo "selected=\"selected\"";?>><?=$day;?></option>
														<?php $i++; endforeach;?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<span class="vexpl"><?=gtext("Note: Ctrl-click (or command-click on the Mac) to select and de-select minutes, hours, days and months.");?></span>
						</td>
					</tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
					<input name="SendReportNow" id="sendnow" type="Submit" class="formbtn" value="<?=gtext("Send Now");?>" />
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
