<?php
/*
	license.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE NAS4Free PROJECT ``AS IS'' AND ANY
	EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE NAS4Free PROJECT OR ITS CONTRIBUTORS BE LIABLE FOR
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
// Configure page permission
$pgperm['allowuser'] = TRUE;

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gtext("Help"), gtext("License & Credits"));
?>
<?php include("fbegin.inc");?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php html_titleline(gtext("License"));?>
			<tr>
<td class="listt">
	     <p><strong>NAS4Free is Copyright &copy; 2012-2017 The NAS4Free Project
              (<a href="mailto:info@nas4free.org">info@nas4free.org</a>).<br />
              All rights reserved.</strong></p>

	     <p>The compilation of software, code and documentation known as NAS4Free is distributed under the following terms:</p>
             <p>Redistribution and use in source and binary forms, with or without<br />
                modification, are permitted provided that the following conditions are met:</p>

              1. Redistributions of source code must retain the above copyright notice,<br />
                 this list of conditions and the following disclaimer.<br />
              <br />
              2. Redistributions in binary form must reproduce the above copyright<br />
                 notice, this list of conditions and the following disclaimer in the<br />
                 documentation and/or other materials provided with the distribution.<br />
              <br />

              <strong>THIS SOFTWARE IS PROVIDED BY THE NAS4Free PROJECT ``AS IS'' AND ANY<br />
              EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED<br />
              WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.<br />
              IN NO EVENT SHALL THE NAS4Free PROJECT OR ITS CONTRIBUTORS BE LIABLE FOR<br />
              ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES<br /> 
              (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;<br />
              LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON<br />
              ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT<br />
              (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF<br />
	      THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</strong><br />
	      <br />
              <p>The views and conclusions contained in the software and documentation are those of the authors and should<br /> 
               not be interpreted as representing official policies, either expressed or implied, of the NAS4Free Project.</p>
	</td>
							
</tr>
            <?php html_separator();?>
            <?php html_titleline(gtext("Credits"));?>
            <tr>
            	<td class="listt">
            <p>The following persons have contributed to NAS4Free code:</p>
	    <div>Daisuke Aoyama (<a href="mailto:aoyama@nas4free.org">aoyama@nas4free.org</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Developer & Project leader</font></em></div><br />

	    <div>Michael Schneider (<a href="mailto:ms49434@nas4free.org">ms49434@nas4free.org</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Developer</font></em></div><br />

	    <div>Michael Zoon (<a href="mailto:zoon1@nas4free.org">zoon1@nas4free.org</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Developer & Project leader</font></em></div><br />

	    <hr size="1" />
	    <p>The following persons have contributed to NAS4Free support:</p>
	    <div>Tony Cat (<a href="mailto:tony1@nas4free.org">tony1@nas4free.org</a>) irc alias tony1<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">User guide and Live support on irc #nas4free|IRC Freenode <a href="http://webchat.freenode.net/?channels=#nas4free">http://webchat.freenode.net</a></font></em></div><br />
	    <div>Rhett Hillary (<a href="mailto:siftu@nas4free.org">siftu@nas4free.org</a>) irc alias SIFTU<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">User guide and Live support on irc #nas4free|IRC Freenode <a href="http://webchat.freenode.net/?channels=#nas4free">http://webchat.freenode.net</a></font></em></div><br />

	    <hr size="1" />
	    <p>The following persons have contributed to NAS4Free documentation and/or webgui translations:</p>
	    <div>Alex Lin (<a href="mailto:linuxant@gmail.com">linuxant@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Chinese translator of the WebGUI</font></em></div><br />

	    <div>Pavel Borecki (<a href="mailto:pavel.borecki@gmail.com">pavel.borecki@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Czech translator of the WebGUI</font></em></div><br />

	    <div>Carsten Vinkler (<a href="mailto:carsten@indysign.dk">carsten@indysign.dk</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Danish translator of the WebGUI</font></em></div><br />

	    <div>Christophe Lherieau (<a href="mailto:skimpax@gmail.com">skimpax@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">French translator of the WebGUI</font></em></div><br />

	    <div>Edouard Richard (<a href="mailto:richard.edouard84@gmail.com">richard.edouard84@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">French translator of the WebGUI</font></em></div><br />

	    <div>Dominik Plaszewski (<a href="mailto:domme555@gmx.net">domme555@gmx.net</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">German translator of the WebGUI</font></em></div><br />

	    <div>Chris Kanatas (<a href="mailto:ckanatas@gmail.com">ckanatas@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Greek translator of the WebGUI</font></em></div><br />

	    <div>Petros Kyladitis (<a href="mailto:petros.kyladitis@gmail.com">petros.kyladitis@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Greek translator of the WebGUI</font></em></div><br />

	    <div>Kiss-Kálmán Dániel (<a href="mailto:kisskalmandaniel@gmail.com">kisskalmandaniel@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Hungarian translator of the WebGUI</font></em></div><br />

	    <div>Christian Sulmoni (<a href="mailto:csulmoni@gmail.com">csulmoni@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Italian translator of the WebGUI and QuiXplorer</font></em></div><br />

	    <div>Frederico Tavares (<a href="mailto:frederico-tavares@sapo.pt">frederico-tavares@sapo.pt</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Portuguese translator of the WebGUI</font></em></div><br />

	    <div>Laurentiu Bubuianu (<a href="mailto:laurfb@yahoo.com">laurfb@yahoo.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Romanian translator of the WebGUI</font></em></div><br />

	    <div>Raul Fernandez Garcia (<a href="mailto:raulfg3@gmail.com">raulfg3@gmail.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Spanish translator of the WebGUI</font></em></div><br />

	    <div>Mucahid Zeyrek (<a href="mailto:mucahid.zeyrek@dhl.com">mucahid.zeyrek@dhl.com</a>)<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">Turkish translator of the WebGUI</font></em></div><br />

	    <hr size="1" />
	      <p>The following persons have contributed to NAS4Free in the past:</p>
	    <div>Samuel Tunis (<a href="mailto:killermist@gmail.com">killermist@gmail.com</a>) irc alias killermist<br />
	      &nbsp;&nbsp;&nbsp;&nbsp;<em><font color="#666666">User guide and Live support on irc #nas4free|IRC Freenode <a href="http://webchat.freenode.net/?channels=#nas4free">http://webchat.freenode.net</a></font></em></div><br />
	</td>
</tr>
	    <?php html_separator();?>
	    <?php html_titleline(gtext("Software Used"));?>
	    <tr>
	    <td class="listt">
	    <p>NAS4Free is based upon/includes various free software packages, listed below.<br />
	    The authors of NAS4Free would like to thank the authors of these software
	    for their efforts.</p>
	    <p> FreeBSD (<a href="http://www.freebsd.org" target="_blank">http://www.freebsd.org</a>)<br />
	    Copyright &copy; 1995-2016 The FreeBSD Project. All Rights Reserved.</p>

	    <p> GEOM RAID5 module (<a href="http://www.wgboome.org/geom_raid5-html" target="_blank">http://www.wgboome.org/geom_raid5-html</a> & (<a href="http://lev.serebryakov.spb.ru/download/graid5/" target="_blank">http://lev.serebryakov.spb.ru/download/graid5</a>)<br />
	    Copyright &copy; 2006-2010 Originally written by Arne Woerner (<a href="mailto:graid5@wgboome.org">graid5@wgboome.org</a>).<br />
	    Copyright &copy; 2010-2014 Now maintained by Lev Serebryakov (<a href="mailto:lev@FreeBSD.org">lev@FreeBSD.org</a>).</p>

	    <p> PHP - An server-side scripting language (<a href="http://www.php.net" target="_blank">http://www.php.net</a>)<br />
	    Copyright &copy; 1999-2017 The PHP Group. All Rights Reserved.</p>

	    <p> jQuery - An fast, small, and feature-rich JavaScript library (<a href="http://jquery.com" target="_blank">http://jquery.com</a>).<br />
	    Copyright &copy; 2016 jQuery Foundation. All Rights Reserved.</p>

	    <p> Lighttpd -An lighty fast webserver (<a href="http://www.lighttpd.net" target="_blank">http://www.lighttpd.net</a>)<br />
	    Copyright &copy; 2004 Jan Kneschke (<a href="mailto:jan@kneschke.de">jan@kneschke.de</a>). All Rights Reserved.</p>

	    <p> OpenSSH (<a href="http://www.openssh.com" target="_blank">http://www.openssh.com</a>)<br />
	    Copyright &copy; 1999-2009 OpenBSD. All Rights Reserved.</p>

	    <p> Samba - Suite providing secure, stable and fast file services for all clients using the SMB/CIFS protocol (<a href="http://www.samba.org" target="_blank">http://www.samba.org</a>)<br />
	    Copyright &copy; 2007 Free Software Foundation. All Rights Reserved.</p>

	    <p> Python - An programming language (<a href="http://www.python.org" target="_blank">http://www.python.org</a>)<br />
	    Copyright &copy; 2001-2016 Python Software Foundation. All Rights Reserved.</p>

	    <p> Rsync - Utility that provides fast incremental file transfer. (<a href="http://www.samba.org/rsync" target="_blank">http://www.samba.org/rsync</a>)<br />
	    Copyright &copy; 2007 Free Software Foundation. All Rights Reserved.</p>

	    <p> ProFTPD - Highly configurable FTP server (<a href="http://www.proftpd.org" target="_blank">http://www.proftpd.org</a>)<br />
	    Copyright &copy; 1999, 2000-2017 The ProFTPD Project. All Rights Reserved.</p>

	    <p> TFTPD-HPA - TFTPD-HPA (Trivial File Transfer Protocol Server) (<a href="http://www.kernel.org/pub/software/network/tftp" target="_blank">http://www.kernel.org/pub/software/network/tftp</a>)<br />
	    Copyright &copy; 1999, 2000-2009 The tftp-hpa series is maintained by H. Peter Anvin. <hpa@zytor.com>All Rights Reserved.</p>

	    <p> Netatalk - Netatalk is a freely-available Open Source AFP fileserver (<a href="http://netatalk.sourceforge.net" target="_blank">http://netatalk.sourceforge.net</a>)<br />
	    Copyright &copy; 1990,1996 Regents of The University of Michigan. All Rights Reserved.</p>

	    <p> Apple Bonjour - Bonjour, known as zero-configuration networking, using multicast Domain Name System (mDNS) records (<a href="http://developer.apple.com/networking/bonjour" target="_blank">http://developer.apple.com/networking/bonjour</a>)<br />
	    Copyright &copy; Apple Public Source License. All Rights Reserved.</p>

	    <p> syslogd - Circular log support for FreeBSD syslogd<br />
	    Copyright &copy; 2001 Jeff Wheelhouse (<a href="mailto:jdw@wheelhouse.org">jdw@wheelhouse.org</a>).</p>

	    <p> ataidle - Sets the idle timer on ATA (IDE) hard drives (<a href="http://bluestop.org/ataidle/" target="_blank">http://bluestop.org/ataidle</a>)<br />
	    Copyright &copy; 2004-2005 Bruce Cran (<a href="mailto:bruce@cran.org.uk">bruce@cran.org.uk</a>). All Rights Reserved.</p>

	    <p> smartmontools - Utility programs (smartctl, smartd) to control/monitor storage systems (<a href="http://sourceforge.net/projects/smartmontools/" target="_blank">http://sourceforge.net/projects/smartmontools</a>)<br />
	    Copyright &copy; 2002-2016 Bruce Allen, Christian Franke. All Rights Reserved.</p>

	    <p> iSCSI initiator (<a href="ftp://ftp.cs.huji.ac.il/users/danny/freebsd" target="_blank">ftp://ftp.cs.huji.ac.il/users/danny/freebsd</a>)<br />
	    Copyright &copy; 2005-2011 Daniel Braniss (<a href="mailto:danny@cs.huji.ac.il">danny@cs.huji.ac.il</a>). All Rights Reserved.</p>

	    <p> istgt - iSCSI target for FreeBSD (<a href="http://shell.peach.ne.jp/aoyama" target="_blank">http://shell.peach.ne.jp/aoyama</a>)<br />
	    Copyright &copy; 2008-2016 Daisuke Aoyama (<a href="mailto:aoyama@peach.ne.jp">aoyama@peach.ne.jp</a>). All Rights Reserved.</p>

	    <p> FUPPES - Free UPnP Entertainment Service (<a href="http://fuppes.ulrich-voelkel.de" target="_blank">http://fuppes.ulrich-voelkel.de</a>)<br />
	    Copyright &copy; 2005 - 2011 Ulrich V&ouml;lkel (<a href="mailto:mail@ulrich-voelkel.de">mail@ulrich-voelkel.de</a>). All Rights Reserved.</p>

	    <p> MiniDLNA - Media server software, with the aim of being fully compliant with DLNA/UPnP-AV clients. (<a href="https://sourceforge.net/projects/minidlna/" target="_blank">https://sourceforge.net/projects/minidlna</a>)<br />
	    Copyright &copy; 2008-2015  Justin Maggard. All Rights Reserved.</p>


	    <p> mt-daapd - Multithread daapd Apple iTunes server (<a href="http://www.fireflymediaserver.org" target="_blank">http://www.fireflymediaserver.org</a>)<br />
	    Copyright &copy; 2003 Ron Pedde (<a href="mailto:ron@pedde.com">ron@pedde.com</a>). All Rights Reserved.</p>

	    <p> NTFS-3G - NTFS-3G is a NTFS driver (<a href="http://www.tuxera.com/community/open-source-ntfs-3g/" target="_blank">http://www.tuxera.com/community/open-source-ntfs-3g</a>)<br />
	    Copyright &copy; 2008-2016 Tuxera Inc. All Rights Reserved.</p>

	    <p> ext4fuse - EXT4 implementation for FUSE (<a href="https://github.com/gerard/ext4fuse" target="_blank">https://github.com/gerard/ext4fuse</a>)<br />
	    Copyright &copy; 1989, 1991 Free Software Foundation. All Rights Reserved.</p>

	    <p> Fuse - Filesystem in Userspace (<a href="https://github.com/libfuse/libfuse" target="_blank">https://github.com/libfuse/libfuse</a>)<br />
	    Copyright &copy; GNU General Public License. All Rights Reserved.</p>

	    <p> e2fsprogs (<a href="http://e2fsprogs.sourceforge.net" target="_blank">http://e2fsprogs.sourceforge.net</a>)<br />
	    Copyright &copy; 2007 Theodore Ts'o. All Rights Reserved.</p>

	    <p> inadyn-mt - Simple Dynamic DNS client (<a href="http://sourceforge.net/projects/inadyn-mt" target="_blank">http://sourceforge.net/projects/inadyn-mt</a>)<br />
	    Inadyn Copyright &copy; 2003-2004 Narcis Ilisei. All Rights Reserved.<br />
	    Inadyn-mt Copyright &copy; 2007 Bryan Hoover (<a href="mailto:bhoover@wecs.com">bhoover@wecs.com</a>). All Rights Reserved.</p>

	    <p> XMLStarlet - Command Line XML Toolkit (<a href="http://xmlstar.sourceforge.net" target="_blank">http://xmlstar.sourceforge.net</a>)<br />
	    Copyright &copy; 2002 Mikhail Grushinskiy. All Rights Reserved.</p>

	    <p> sipcalc (<a href="http://www.routemeister.net/projects/sipcalc/" target="_blank">http://www.routemeister.net/projects/sipcalc</a>)<br />
	    Copyright &copy; 2003 Simon Ekstrand. All Rights Reserved.</p>

	    <p> msmtp - An SMTP client with a sendmail compatible interface (<a href="http://msmtp.sourceforge.net" target="_blank">http://msmtp.sourceforge.net</a>)<br />
	    Copyright &copy; 2008 Martin Lambers and others. All Rights Reserved.</p>

	    <p> cdialog - Display simple dialog boxes from shell scripts (<a href="http://invisible-island.net/dialog/" target="_blank">http://invisible-island.net/dialog</a>)<br />
	    Copyright &copy; 2000-2006, 2007 Thomas E. Dickey. All Rights Reserved.</p>

	    <p> host - An utility to query DNS servers<br />
	    Copyright &copy; Rewritten by Eric Wassenaar, Nikhef-H, (<a href="mailto:e07@nikhef.nl">e07@nikhef.nl</a>). All Rights Reserved.</p>

	    <p> Transmission - An fast, easy, and free multi-platform BitTorrent client (<a href="http://www.transmissionbt.com" target="_blank">http://www.transmissionbt.com</a>)<br />
	    Copyright &copy; 2008-2016 Transmission Project. All Rights Reserved.</p>

	    <p> QuiXplorer - An Web-based file-management browser (<a href="https://github.com/realtimeprojects/quixplorer" target="_blank">https://github.com/realtimeprojects/quixplorer</a>)<br />
	    Copyright &copy; Felix C. Stegerman. All Rights Reserved.</p>

	    <p> Freenas 7 - NAS4Free legally use Freenas 7 code too (<a href="https://github.com/freenas/freenas7" target="_blank">https://github.com/freenas/freenas7</a>)<br />
	    Copyright &copy; 2005-2011 by Olivier Cochard (olivier@freenas.org). All Rights Reserved.</p>

	    <p> pfSense - NAS4Free use some pfSense code too (<a href="http://www.pfsense.com" target="_blank">http://www.pfsense.com</a>)<br />
	    Copyright &copy; 2004, 2005, 2006 Scott Ullrich. All Rights Reserved.</p>

	    <p> m0n0wall - NAS4Free use some m0n0wall code too (<a href="http://m0n0.ch/wall/index.php" target="_blank">http://m0n0.ch/wall/index.php</a>)<br />
	    Copyright &copy; 2002-2006 by Manuel Kasper. All Rights Reserved.</p>

	    <p> VMXNET3 - An NIC driver for FreeBSD (<a href="http://www.vmware.com" target="_blank">http://www.vmware.com</a>)<br />
	    Copyright &copy; 2010 VMware, Inc. All Rights Reserved.</p>

	    <p> Open Virtual Machine Tools - Virtualization utilities and drivers (<a href="http://sourceforge.net/projects/open-vm-tools/" target="_blank">http://sourceforge.net/projects/open-vm-tools</a>)<br />
	    Copyright &copy; 2007-2015 VMware Inc. All rights reserved.</p>

	    <p> VirtualBox Open Source Edition (OSE) & (Guest Additions) (<a href="http://www.virtualbox.org" target="_blank">http://www.virtualbox.org</a>)<br />
	    Copyright &copy; 2010-2017, Oracle and/or its affiliates. All Rights Reserved.</p>

	    <p> phpVirtualBox (<a href="http://sourceforge.net/projects/phpvirtualbox/" target="_blank">http://sourceforge.net/projects/phpvirtualbox</a>)<br />
	    Copyright &copy; 2011-2016 Ian Moore, Inc. All Rights Reserved.</p>

	    <p> noVNC (<a href="http://kanaka.github.io/noVNC/" target="_blank">http://kanaka.github.io/noVNC/</a>)<br />
	    Copyright &copy; 2011-2017 Joel Martin (<a href="mailto:github@martintribe.org">github@martintribe.org</a>) Inc. All Rights Reserved.</p>

	    <p> LCDproc - An client/server suite for LCD devices (<a href="http://lcdproc.org" target="_blank">http://lcdproc.org</a>)<br />
	    Copyright &copy; 1998-2006 William Ferrell, Selene Scriven and many other contributors. All Rights Reserved.</p>

	    <p> tmux - An terminal multiplexer. (<a href="http://tmux.github.io/" target="_blank">http://tmux.github.io</a>)<br />
	    Copyright &copy; 2010 Nicholas Marriott. All Rights Reserved.</p>

	    <p> iperf3 - An tool to measure TCP and UDP bandwidth. (<a href="http://software.es.net/iperf/" target="_blank">http://software.es.net/iperf</a>)<br />
	    Copyright &copy; 2014-2017 ESnet. All Rights Reserved.</p>

	    <p> sudo - An tool to allow a sysadmin to give limited root privileges. (<a href="http://www.sudo.ws" target="_blank">http://www.sudo.ws</a>)<br />
	    Copyright &copy; 1994-1996, 1998-2017 Todd C. Miller. All Rights Reserved.</p>

	    <p> ipmitool - IPMItool provides a simple command-line interface to v1.5 & v2.0 IPMI-enabled devices. (<a href="http://sourceforge.net/projects/ipmitool/" target="_blank">http://sourceforge.net/projects/ipmitool</a>)<br />
	    Copyright &copy; 2003 Sun Microsystems. All Rights Reserved.</p>

	    <p> Syncthing - Syncthing replaces proprietary sync and cloud services with something open, trustworthy and decentralized. (<a href="https://syncthing.net" target="_blank">https://syncthing.net</a>)<br />
	    Copyright &copy; Syncthing Development Team. All Rights Reserved.</p></p>

	    <p>Some of the software used for NAS4Free are under the <a href="third-party_licenses/gpl-license.txt">GNU General Public License</a> (<a href="third-party_licenses/gpl-license.txt">GPLv2</a>, <a href="third-party_licenses/gpl3-license.txt">GPLv3</a>), <a href="third-party_licenses/lgpl-license.txt">GNU Lesser General Public License (LGPL)</a>, <a href="third-party_licenses/mpl2-license.txt">Mozilla Public License Version 2.0 (MPLv2)</a>, <a href="third-party_licenses/apple-license.txt">Apple Public Source License</a> and <a href="third-party_licenses/php-license.txt">PHP License</a>.</p>
	</td>
      </tr>
   </table>
  </td>
</tr>
</table>
<?php include("fend.inc");?>
