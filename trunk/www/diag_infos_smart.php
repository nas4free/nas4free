<?php
/*
	diag_infos_smart.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of freenas (http://www.freenas.org).
	Copyright (c) 2005-2011 by Olivier Cochard <olivier@freenas.org>.
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

$pgtitle = array(gettext("Diagnostics"), gettext("Information"), gettext("S.M.A.R.T."));
$a_disk = get_physical_disks_list();

$smartValueInfo = array(
	"1" => array(False,"",gettext("(Vendor specific raw value.) Stores data related to the rate of hardware read errors that occurred when reading data from a disk surface. The raw value has different structure for different vendors and is often not meaningful as a decimal number.")),
	"2" => array(False,"",gettext("Overall (general) throughput performance of a hard disk drive. If the value of this attribute is decreasing there is a high probability that there is a problem with the disk.")),
	"3" => array(False,"",gettext("Average time of spindle spin up (from zero RPM to fully operational).")),
	"4" => array(False,"",gettext("A tally of spindle start/stop cycles. The spindle turns on, and hence the count is increased, both when the hard disk is turned on after having before been turned entirely off (disconnected from power source) and when the hard disk returns from having previously been put to sleep mode.")),
	"5" => array(True,gettext("Consider replacing this drive"),gettext("Count of reallocated sectors. When the hard drive finds a read/write/verification error, it marks that sector as 'reallocated' and transfers data to a special reserved area (spare area). This process is also known as remapping, and reallocated sectors are called 'remaps'. The raw value normally represents a count of the bad sectors that have been found and remapped. Thus, the higher the attribute value, the more sectors the drive has had to reallocate. This allows a drive with bad sectors to continue operation; however, a drive which has had any reallocations at all is significantly more likely to fail in the near future. While primarily used as a metric of the life expectancy of the drive, this number also affects performance. As the count of reallocated sectors increases, the read/write speed tends to become worse because the drive head is forced to seek to the reserved area whenever a remap is accessed. If sequential access speed is critical, the remapped sectors can be manually marked as bad blocks in the file system in order to prevent their use.")),
	"6" => array(False,"",gettext("Margin of a channel while reading data. The function of this attribute is not specified.")),
	"7" => array(False,"",gettext("(Vendor specific raw value.) Rate of seek errors of the magnetic heads. If there is a partial failure in the mechanical positioning system, then seek errors will arise. Such a failure may be due to numerous factors, such as damage to a servo, or thermal widening of the hard disk. The raw value has different structure for different vendors and is often not meaningful as a decimal number.")),
	"8" => array(False,"",gettext("Average performance of seek operations of the magnetic heads. If this attribute is decreasing, it is a sign of problems in the mechanical subsystem.")),
	"9" => array(False,"",gettext("Count of hours in power-on state. The raw value of this attribute shows total count of hours (or minutes, or seconds, depending on manufacturer) in power-on state.")),
	"10" => array(False,"",gettext("Count of retry of spin start attempts. This attribute stores a total count of the spin start attempts to reach the fully operational speed (under the condition that the first attempt was unsuccessful). An increase of this attribute value is a sign of problems in the hard disk mechanical subsystem.")),
	"11" => array(False,"",gettext("This attribute indicates the count that recalibration was requested (under the condition that the first attempt was unsuccessful). An increase of this attribute value is a sign of problems in the hard disk mechanical subsystem.")),
	"12" => array(False,"",gettext("This attribute indicates the count of full hard disk power on/off cycles.")),
	"13" => array(False,"",gettext("Uncorrected read errors reported to the operating system.")),
	"22" => array(False,"",gettext("Specific to He8 drives from HGST. The Helium, so says the literature, allows the drives to run cooler and quieter, and reduces power consumption. This is the status of the Helium in the drive. It is a pre-fail attribute that trips once the drive detects that the internal environment is out of specification.")),
	"170" => array(False,"",gettext("See attribute E8.")),
	"171" => array(False,"",gettext("(Kingston)Counts the number of flash program failures. This Attribute returns the total number of Flash program operation failures since the drive was deployed. This attribute is identical to attribute 181.")),
	"172" => array(False,"",gettext("(Kingston)Counts the number of flash erase failures. This Attribute returns the total number of Flash erase operation failures since the drive was deployed. This Attribute is identical to Attribute 182.")),
	"173" => array(False,"",gettext("Counts the maximum worst erase count on any block.")),
	"174" => array(False,"",gettext("Also known as 'Power-off Retract Count' per conventional HDD terminology. Raw value reports the number of unclean shutdowns, cumulative over the life of an SSD, where an 'unclean shutdown' is the removal of power without STANDBY IMMEDIATE as the last command (regardless of PLI activity using capacitor power). Normalized value is always 100.")),
	"175" => array(False,"",gettext("Last test result as microseconds to discharge cap, saturated at its maximum value. Also logs minutes since last test and lifetime number of tests. Raw value contains the following data: Bytes 0-1: Last test result as microseconds to discharge cap, saturates at max value. Test result expected in range 25 <= result <= 5000000, lower indicates specific error code. Bytes 2-3: Minutes since last test, saturates at max value. Bytes 4-5: Lifetime number of tests, not incremented on power cycle, saturates at max value. Normalized value is set to one on test failure or 11 if the capacitor has been tested in an excessive temperature condition, otherwise 100.")),
	"177" => array(False,"",gettext("Delta between most-worn and least-worn Flash blocks. It describes how good/bad the wearleveling of the SSD works on a more technical way.")),
	"179" => array(False,"",gettext("Pre-Fail' Attribute used at least in Samsung devices.")),
	"180" => array(False,"",gettext("Pre-Fail' Attribute used at least in HP devices.")),
	"181" => array(False,"",gettext("Total number of Flash program operation failures since the drive was deployed.")),
	"182" => array(False,"",gettext("Pre-Fail' Attribute used at least in Samsung devices.")),
	"183" => array(False,"",gettext("Western Digital, Samsung or Seagate attribute: Total number of data blocks with detected, uncorrectable errors encountered during normal operation.")),
	"184" => array(False,"",gettext("This attribute is a part of Hewlett-Packard's SMART IV technology, as well as part of other vendors' IO Error Detection and Correction schemas, and it contains a count of parity errors which occur in the data path to the media via the drive's cache RAM.")),
	"185" => array(False,"",gettext("Western Digital attribute.")),
	"186" => array(False,"",gettext("Western Digital attribute.")),
	"187" => array(True,gettext("Consider replacing this drive"),gettext("The count of errors that could not be recovered using hardware ECC (see attribute 195).")),
	"188" => array(True,gettext("Consider replacing this drive"),gettext("The count of aborted operations due to HDD timeout. Normally this attribute value should be equal to zero and if the value is far above zero, then most likely there will be some serious problems with power supply or an oxidized data cable.")),
	"189" => array(False,"",gettext("HDD producers implement a Fly Height Monitor that attempts to provide additional protections for write operations by detecting when a recording head is flying outside its normal operating range. If an unsafe fly height condition is encountered, the write process is stopped, and the information is rewritten or reallocated to a safe region of the hard drive. This attribute indicates the count of these errors detected over the lifetime of the drive. This feature is implemented in most modern Seagate drives.")),
	"190" => array(False,"",gettext("Airflow temperature on Western Digital HDs (Same as temp. , but current value is 50 less for some models. Marked as obsolete.)")),
	"190" => array(False,"",gettext("Value is equal to (100-temp. &deg;C), allowing manufacturer to set a minimum threshold which corresponds to a maximum temperature.")),
	"191" => array(False,"",gettext("The count of errors resulting from externally induced shock & vibration.")),
	"192" => array(False,"",gettext("Count of times the heads are loaded off the media. Heads can be unloaded without actually powering off.")),
	"193" => array(False,"",gettext("Count of load/unload cycles into head landing zone position.")),
	"194" => array(False,"",gettext("Current internal temperature.")),
	"195" => array(False,"",gettext("(Vendor-specific raw value.) The raw value has different structure for different vendors and is often not meaningful as a decimal number.")),
	"196" => array(False,"",gettext("Count of remap operations. The raw value of this attribute shows the total count of attempts to transfer data from reallocated sectors to a spare area. Both successful & unsuccessful attempts are counted.")),
	"197" => array(True,gettext("Consider replacing this drive"),gettext("Count of 'unstable' sectors (waiting to be remapped, because of unrecoverable read errors). If an unstable sector is subsequently read successfully, the sector is remapped and this value is decreased. Read errors on a sector will not remap the sector immediately (since the correct value cannot be read and so the value to remap is not known, and also it might become readable later); instead, the drive firmware remembers that the sector needs to be remapped, and will remap it the next time it's written. However some drives will not immediately remap such sectors when written; instead the drive will first attempt to write to the problem sector and if the write operation is successful then the sector will be marked good (in this case, the 'Reallocation Event Count' (0xC4) will not be increased). This is a serious shortcoming, for if such a drive contains marginal sectors that consistently fail only after some time has passed following a successful write operation, then the drive will never remap these problem sectors.")),
	"198" => array(True,gettext("Consider replacing this drive"),gettext("The total count of uncorrectable errors when reading/writing a sector. A rise in the value of this attribute indicates defects of the disk surface and/or problems in the mechanical subsystem.")),
	"199" => array(True,gettext("Check and replace cable"),gettext("The count of errors in data transfer via the interface cable as determined by ICRC (Interface Cyclic Redundancy Check).")),
	"200" => array(False,"",gettext("The count of errors found when writing a sector. The higher the value, the worse the disk's mechanical condition is.")),
	"200" => array(False,"",gettext("The total count of errors when writing a sector.")),
	"201" => array(False,"",gettext("Count of off-track errors.")),
	"202" => array(False,"",gettext("Count of Data Address Mark errors (or vendor-specific).")),
	"203" => array(False,"",gettext("The number of errors caused by incorrect checksum during the error correction.")),
	"204" => array(False,"",gettext("Count of errors corrected by software ECC.")),
	"205" => array(False,"",gettext("Count of errors due to high temperature.")),
	"206" => array(False,"",gettext("Height of heads above the disk surface. A flying height that's too low increases the chances of a head crash while a flying height that's too high increases the chances of a read/write error.")),
	"207" => array(False,"",gettext("Amount of surge current used to spin up the drive.")),
	"208" => array(False,"",gettext("Count of buzz routines needed to spin up the drive due to insufficient power.")),
	"209" => array(False,"",gettext("Drive's seek performance during its internal tests.")),
	"210" => array(False,"",gettext("(Found in a Maxtor 6B200M0 200GB and Maxtor 2R015H1 15GB disks).")),
	"211" => array(False,"",gettext("Vibration During Write.")),
	"212" => array(False,"",gettext("Shock During Write.")),
	"220" => array(False,"",gettext("Distance the disk has shifted relative to the spindle (usually due to shock or temperature). Unit of measure is unknown.")),
	"221" => array(False,"",gettext("The count of errors resulting from externally induced shock & vibration.")),
	"222" => array(False,"",gettext("Time spent operating under data load (movement of magnetic head armature).")),
	"223" => array(False,"",gettext("Count of times head changes position.")),
	"224" => array(False,"",gettext("Resistance caused by friction in mechanical parts while operating.")),
	"225" => array(False,"",gettext("Total count of load cycles.")),
	"226" => array(False,"",gettext("Total time of loading on the magnetic heads actuator (time not spent in parking area).")),
	"227" => array(False,"",gettext("Count of attempts to compensate for platter speed variations.")),
	"228" => array(False,"",gettext("The count of times the magnetic armature was retracted automatically as a result of cutting power.")),
	"230" => array(False,"",gettext("Amplitude of 'thrashing' (distance of repetitive forward/reverse head motion).")),
	"230" => array(False,"",gettext("Current state of drive operation based upon the Life Curve.")),
	"231" => array(False,"",gettext("Drive Temperature.")),
	"231" => array(False,"",gettext("Indicates the approximate SSD life left, in terms of program/erase cycles or Flash blocks currently available for use.")),
	"232" => array(False,"",gettext("Number of physical erase cycles completed on the drive as a percentage of the maximum physical erase cycles the drive is designed to endure.")),
	"232" => array(False,"",gettext("Intel SSD reports the number of available reserved space as a percentage of reserved space in a brand new SSD.")),
	"233" => array(False,"",gettext("Number of hours elapsed in the power-on state.")),
	"233" => array(False,"",gettext("Intel SSD reports a normalized value of 100 (when the SSD is new) and declines to a minimum value of 1. It decreases while the NAND erase cycles increase from 0 to the maximum-rated cycles.")),
	"234" => array(False,"",gettext("Decoded as: byte 0-1-2 = average erase count (big endian) and byte 3-4-5 = max erase count (big endian).")),
	"235" => array(False,"",gettext("decoded as: byte 0-1-2 = good block count (big endian) and byte 3-4 = system(free) block count.")),
	"240" => array(False,"",gettext("Time spent during the positioning of the drive heads.")),
	"240a" => array(False,"",gettext("Count of times the link is reset during a data transfer.")),
	"241" => array(False,"",gettext("Total count of LBAs written.")),
	"242" => array(False,"",gettext("Total count of LBAs read. Some S.M.A.R.T. utilities will report a negative number for the raw value since in reality it has 48 bits rather than 32.")),
	"249" => array(False,"",gettext("Total NAND Writes. Raw value reports the number of writes to NAND in 1GB increments.")),
	"250" => array(False,"",gettext("Count of errors while reading from a disk.")),
	"251" => array(False,"",gettext("The Minimum Spares Remaining attribute indicates the number of remaining spare blocks as a percentage of the total number of spare blocks available.")),
	"252" => array(False,"",gettext("The Newly Added Bad Flash Block attribute indicates the total number of bad flash blocks the drive detected since it was first initialized in manufacturing.")),
	"254" => array(False,"",gettext("Count of 'Free Fall Events' detected."))
);

include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="diag_infos.php"><span><?=gettext("Disks");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ata.php"><span><?=gettext("Disks (ATA)");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_part.php"><span><?=gettext("Partitions");?></span></a></li>
				<li class="tabact"><a href="diag_infos_smart.php" title="<?=gettext("Reload page");?>"><span><?=gettext("S.M.A.R.T.");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_space.php"><span><?=gettext("Space Used");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_mount.php"><span><?=gettext("Mounts");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_raid.php"><span><?=gettext("Software RAID");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav2">
				<li class="tabinact"><a href="diag_infos_iscsi.php"><span><?=gettext("iSCSI Initiator");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ad.php"><span><?=gettext("MS Domain");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_samba.php"><span><?=gettext("CIFS/SMB");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ftpd.php"><span><?=gettext("FTP");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_rsync_client.php"><span><?=gettext("RSYNC Client");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_swap.php"><span><?=gettext("Swap");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_sockets.php"><span><?=gettext("Sockets");?></span></a></li>
				<li class="tabinact"><a href="diag_infos_ups.php"><span><?=gettext("UPS");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
		    <table width="100%" border="0">
			  <?php foreach($a_disk as $diskk => $diskv) { ?>
				<?php html_titleline(sprintf(gettext("Device /dev/%s - %s"), $diskk, $diskv['desc']));?>
				<tr>
					<td>
						<pre><?php
						$devicetype_arg = (!empty($diskv['smart']['devicetypearg']))
							? sprintf('-d %s',$diskv['smart']['devicetypearg'])
							: "";
						exec ("/usr/local/sbin/smartctl -i {$diskv['smart']['devicefilepath']} {$devicetype_arg}",$rawdata);
						$rawdata = array_slice($rawdata,3);
						echo htmlspecialchars(implode("\n", $rawdata));
						unset($rawdata);
						?></pre>
						<?php $hasdata = False;
							$devicetype_arg = (!empty($diskv['smart']['devicetypearg']))
							? sprintf('-d %s',$diskv['smart']['devicetypearg'])
							: '';
						exec ("/usr/local/sbin/smartctl -a {$diskv['smart']['devicefilepath']} {$devicetype_arg}",$rawdata);
							$rawdata = array_slice($rawdata, 3);
							$regex = '/^\s*(\d+)\s+([A-Za-z0-9_\-]+)\s+(0x[0-9a-fA-F]+)\s+(\d+)\s+(\d+)\s+(\d+).*\s+\-\s+(\d+)/';?>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td class="listhdrlr" width="10%"><?=gettext("ID");?></td>
								<td class="listhdrlr" width="10%"><?=gettext("ATTRIBUTE NAME");?></td>
								<td class="listhdrlr" width="10%"><?=gettext("RAW VALUE");?></td>
								<td class="listhdrlr" width="70%"><?=gettext("DESCRIPTION");?></td>
							</tr>
						<?php $hasdata = false;
							foreach($rawdata as $line) {
								if(preg_match($regex,$line,$match)!==1)
								{ continue; }
								$hasdata      = true;
								$info         = $smartValueInfo[$match[1]][2];
								$haserror     = $smartValueInfo[$match[1]][0] && $match[7] >0;
								$showRedValue = ($haserror)
								? 'listbg errortext'
								: 'listbg';
								?>
							<tr>
								<td class="listlr"><?= $match[1]; ?></td>
								<td class="listr"><?= $match[2]; ?></td>
								<td class="<?= $showRedValue; ?>"><?= $match[7]; ?></td>
								<td class="listr"><?= $info; ?></td>
						<?php if($haserror) { ?>
							</tr>
						<tr>
						<td class="listlr"></td>
						<td colspan="3" class="listbg errortext"><?= $smartValueInfo[$match[1]][1]; ?></td><?php } ?>
							</tr>
						<?php }
							if(!$hasdata) { ?>
						<tr>
						<td colspan="4">no data</td>
							</tr>
						<?php } ?>
						</table>
						<?php unset($rawdata); ?>
						<pre><?php
						$devicetype_arg = (!empty($diskv['smart']['devicetypearg']))
							? sprintf('-d %s',$diskv['smart']['devicetypearg'])
							: '';
						exec("/usr/local/sbin/smartctl -AcH -l selftest -l error -l selective {$diskv['smart']['devicefilepath']} {$devicetype_arg}",$rawdata);
						$rawdata = array_slice($rawdata, 3);
						echo htmlspecialchars(implode("\n", $rawdata));
						unset($rawdata);
						?></pre>
					</td>
				</tr>
			<?php } ?>
		</table>
	   </td>
	</tr>
</table>
<?php include("fend.inc");