<?php
/*
	co_geom_info.inc

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
require_once 'config.inc';
require_once 'util.inc';

class co_geom_info {
	private $a_xml;
	
	public function __construct() {
		$this->scan();
		return true;
	}
	public function scan() {
		$c_xml = '';
		$cmd = 'sysctl -b kern.geom.confxml';
		$a_row = [];
		$retval = 0;
		mwexec2($cmd, $a_row, $retval);
		$c_xml = implode('', $a_row);
		$this->a_xml = new SimpleXMLElement($c_xml);
		return true;
	}
	public function get_disk(&$a_ret = []) {
		$r_ret = [];
		$x_disk = '/mesh/class[name="DISK"]/geom';
		if(false !== ($a_disk = $this->a_xml->xpath($x_disk))) {
			foreach($a_disk as $r_disk) {
				$r_ret['geom_id'] = (string)$r_disk['id'];
				$r_ret['rank'] = (int)$r_disk->rank;
				$r_ret['name'] = (string)$r_disk->name;
				$a_ret[(string)$r_disk['id']] = $r_ret;
			}
		}
		return $a_ret;
	}
	public function get_dev(&$a_ret = []) {
		$r_ret = [];
		$x_dev = '/mesh/class[name="DEV"]/geom';
		if(false !== ($a_dev = $this->a_xml->xpath($x_dev))) {
			foreach($a_dev as $r_dev) {
				$r_ret['geom.id'] = (string)$r_dev['id'];
				$r_ret['rank'] = (int)$r_dev->rank;
				$r_ret['name'] = (string)$r_dev->name;
				$a_ret[(string)$r_dev['id']] = $r_ret;
			}
		}
		return $a_ret;
	}
	public function get_part(&$a_ret = []) {
		$r_ret = [];
		$x_part = '/mesh/class[name="PART"]/geom';
		if(false !== ($a_result = $this->a_xml->xpath($x_part))) {
			foreach($a_part as $r_part) {
				$r_ret['geom.id'] = (string)$r_part['id'];
				$r_ret['rank'] = (int)$r_part->rank;
				$r_ret['name'] = (string)$r_part->name;
				$a_ret[(string)$r_part['id']] = $r_ret;
			}
		}
		return $a_ret;
	}
	public function get_provider(&$a_ret = []) {
		$r_ret = [];
		$x_provider = '//provider[@id]';
		if(false !== ($a_provider = $this->a_xml->xpath($x_provider))) {
			foreach($a_provider as $r_provider) {
				$r_ret['provider.id'] = (string)$r_provider['id'];
				$r_ret['name'] = (string)$r_provider->name;
				$r_ret['mediasize'] = (int)$r_provider->mediasize;
				$r_ret['sectorsize'] = (int)$r_provider->sectorsize;
				$r_ret['stripesize'] = (int)$r_provider->stripesize;
				$r_ret['stripeoffset'] = (int)$r_provider->stripeoffset;
				$a_ret[(string)$r_provider['id']] = $r_ret;
			}
		}
		return $a_ret;
	}
}
