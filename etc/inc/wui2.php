<?php
/*
	wui2.php

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
require_once 'array.inc';

class HTMLBaseControl2 {
	var $_ctrlname = "";
	var $_title = "";
	var $_description = "";
	var $_value;
	var $_required = false;
	var $_readonly = false;
	var $_altpadding = false;
	var $_classtag = 'celltag';
	var $_classdata = 'celldata';
	var $_classaddonrequired = 'req';
	var $_classaddonpadalt = 'alt';
	// constructor method
	public function __construct($ctrlname, $title, $value, $description = "") {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetDescription($description);
		$this->SetValue($value);
	}
	// get methods
	function GetCtrlName() { return $this->_ctrlname; }
	function GetTitle() { return $this->_title; }
	function GetDescription() { return $this->_description; }
	function GetValue() { return $this->_value; }
	function IsRequired() { return $this->_required; }
	function IsReadOnly() { return $this->_readonly; }
	function IsAltPadding() { return $this->_altpadding; }
	function GetClassTag() { return $this->_classtag; }
	function GetClassData() { return $this->_classdata; }
	function GetClassAddonRequired() { return $this->_classaddonrequired; }
	function GetClassAddonPadAlt() { return $this->_classaddonpadalt; }
	// set methods
	function SetCtrlName($name) { $this->_ctrlname = $name; }
	function SetTitle($title) { $this->_title = $title; }
	function SetDescription($description) { $this->_description = $description; }
	function SetValue($value) { $this->_value = $value; }
	function SetRequired($bool) { $this->_required = $bool; }
	function SetReadOnly($bool) { $this->_readonly = $bool; 	}
	function SetAltPadding($bool) { $this->_altpadding = $bool; }
	function SetClassTag($cssclass) { $this ->_classtag = $cssclass; }
	function SetClassData($cssclass) { $this->_classdata = $cssclass; }
	function SetClassAddonRequired($cssclass) { $this->_classaddonrequired = $cssclass; }
	function SetClassAddonPadAlt($cssclass) { $this->_classaddonpadalt = $cssclass; }
	// support methods
	function GetClassOfTag() {
		$class = $this->GetClassTag();
		if (true === $this->IsRequired()) { $class .= $this->GetClassAddonRequired(); }
		if (true === $this->IsAltPadding()) { $class .= $this->GetClassAddonPadAlt(); }
		return $class;
	}
	function GetClassOfData() {
		$class = $this->GetClassData();
		if (true === $this->IsRequired()) { $class .= $this->GetClassAddonRequired(); }
		if (true === $this->IsAltPadding()) { $class .= $this->GetClassAddonPadAlt(); }
		return $class;
	}
	function GetDescriptionOutput() {
		$description = $this->GetDescription();
		$description_output = '';
		$suppressbr = true;
		if (!empty($description)) { // string or array
			if (is_string($description)) {
				$description_output = $description;
			} elseif (is_array($description)) {
				foreach ($description as $description_row) {
					if (is_string($description_row)) {
						if ($suppressbr) {
							$description_output .= $description_row;
							$suppressbr = false;
						} else {
							$description_output .= ('<br />' . $description_row);
						}
					} elseif (is_array($description_row)) {
						switch (count($description_row)) {
							case 1:
								if ($suppressbr) {
									$suppressbr = false;
								} else {
									$description_output .= '<br />';
								}
								$description_output .= $description_row[0];
								break;
							case 3: // allow not to break
								$suppressbr = (is_bool($description_row[2])) ? $description_row[2] : $suppressbr;
							case 2:
								if ($suppressbr) {
									$suppressbr = false;
								} else {
									$description_output .= '<br />';
								}
								if (is_null($description_row[1])) {
									$description_output .= $description_row[0];
								} else {
									$description_output .= '<font color="' . $description_row[1] . '">' . $description_row[0] . '</font>';
								}
								break;
						}
					}
				}
			}
		}
		return $description_output;
	}
	function Render() {
		$ctrlname = $this->GetCtrlName();
		$title = $this->GetTitle();
		$classtag = $this->GetClassOfTag();
		$classdata = $this->GetClassOfData();
		$description = $this->GetDescriptionOutput();

		echo "<tr id='{$ctrlname}_tr'>\n";
		echo "	<td class='{$classtag}'><label for='$ctrlname'>{$title}</label></td>\n";
		echo "	<td class='{$classdata}'>\n";
		$this->RenderCtrl();
		if (!empty($description)) {	echo "		<br /><span class='tagabout'>{$description}</span>\n"; }
		echo "	</td>\n";
		echo "</tr>\n";
	}
	function RenderCtrl() {
	}
}
class HTMLBaseControlJS2 extends HTMLBaseControl2 {
	var $_onclick = "";
	// get methods
	function GetJSonClick() { return $this->_onclick; }
	// set methods
	function SetJSonClick($code) { $this->_onclick = $code; }
}
class HTMLEditBox2 extends HTMLBaseControl2 {
	var $_size = 40;
	var $_maxlength = 40;
	var $_placeholder = '';
	var $_classinputtext = 'formfld';
	var $_classinputtextro = 'formfldro';
	
	// constructor method
	function __construct($ctrlname, $title, $value, $description, $size) {
		parent::__construct($ctrlname, $title, $value, $description);
		$this->SetSize($size);
	}
	// get methods
	function GetSize() { return $this->_size; }
	function GetMaxLength() { return $this->_maxlength; }
	function GetPlaceholder() { return $this->_placeholder; }
	function GetClassInputText() { return $this->_classinputtext; }
	function GetClassInputTextRO() { return $this->_classinputtextro; }	
	// set methods
	function SetSize($size) {
		// maxlength is set to $size when _size == _maxlength 
		if($this->GetSize() == $this->GetMaxLength()) {
			$this->SetMaxLength($size);
		}
		$this->_size = $size;
	}
	function SetMaxLength($maxlength) {
		$this->_maxlength = $maxlength;
	}
	function SetPlaceholder(string $placeholder = '') {
		$this->_placeholder = $placeholder;
	}
	function SetClassInputText($param) { $this->_classinputtext = $param; }
	function SetClassInputTextRO($param) { $this->_classinputtextro = $param; }
	// support functions
	function GetParam() {
		$param = '';
		if (true === $this->IsReadOnly()) { 
			$param .= 'readonly="readonly" ';
		}
		if(preg_match('/\S/',$this->GetPlaceholder())) {
			$param .= sprintf('placeholder="%s" ',$this->GetPlaceholder());
		}
		return $param;
	}
	function GetClassOfInputText() {
		if (true === $this->IsReadOnly()) {
			return $this->GetClassInputTextRO();
		} else {
			return $this->GetClassInputText();
		}
	}
	function RenderCtrl() {
		echo '<input name="', $this->GetCtrlName(), '" ', 
			'type="text" ',
			'class="', $this->GetClassOfInputText(), '" ', 
			'id="', $this->GetCtrlName(), '" ', 
			'size="', $this->GetSize(), '" ', 
			'maxlength="', $this->GetMaxLength(), '" ',
			'value="', htmlspecialchars($this->GetValue(), ENT_QUOTES), '" ',
			$this->GetParam(),
			"/>\n";
	}
}
class HTMLPasswordBox2 extends HTMLEditBox2 {
	var $_classinputpassword = 'formfld';
	// constructor method
	function __construct($ctrlname, $title, $value, $description, $size) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetDescription($description);
		$this->SetSize($size);
	}
	// get methods
	function GetClassInputPassword() { return $this->_classinputpassword; }
	// set methods
	function SetClassInputPassword($cssclass) { $this->_classinputpassword = $cssclass; }
	// support methods
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$size = $this->GetSize();
		$param = $this->GetParam();
		$classinputpassword = $this->GetClassOfInputPassword();
		echo "		<input name='{$ctrlname}' type='password' class='{$classinputpassword}' id='{$ctrlname}' size='{$size}' value='{$value}' {$param} />\n";
	}
}
class HTMLPasswordConfBox2 extends HTMLEditBox2 {
	var $_ctrlnameconf = "";
	var $_valueconf = "";
	var $_classinputpassword = 'formfld';
	// constructor method
	function __construct($ctrlname, $ctrlnameconf, $title, $value, $valueconf, $description, $size) {
		$this->SetCtrlName($ctrlname);
		$this->SetCtrlNameConf($ctrlnameconf);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetValueConf($valueconf);
		$this->SetDescription($description);
		$this->SetSize($size);
	}
	// get methods
	function GetCtrlNameConf() { return $this->_ctrlnameconf; }
	function GetValueConf() { return $this->_valueconf; }
	function GetClassInputPassword() { return $this->_classinputpassword; }
	// set methods
	function SetCtrlNameConf($name) { $this->_ctrlnameconf = $name; }
	function SetValueConf($value) { $this->_valueconf = $value; }
	function SetClassInputPassword($cssclass) { $this->_classinputpassword = $cssclass; }
	// support methods
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$ctrlnameconf = $this->GetCtrlNameConf();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$valueconf = htmlspecialchars($this->GetValueConf(), ENT_QUOTES);
		$size = $this->GetSize();
		$param = $this->GetParam();
		$caption = gtext("Confirmation");
		$classinputpassword = $this->GetClassOfInputPassword();
		echo "		<input name='{$ctrlname}' type='password' class='{$classinputpassword}' id='{$ctrlname}' size='{$size}' value='{$value}' {$param} /><br />\n";
		echo "		<input name='{$ctrlnameconf}' type='password' class='{$classinputpassword}' id='{$ctrlnameconf}' size='{$size}' value='{$valueconf}' {$param} />&nbsp;({$caption})\n";
	}
}
class HTMLTextArea2 extends HTMLEditBox2 {
	var $_columns = 40;
	var $_rows = 5;
	var $_wrap = true;
	var $_classtextarea = 'formpre';
	var $_classtextarearo = 'formprero';
	// constructor method
	function __construct($ctrlname, $title, $value, $description, $columns, $rows) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetDescription($description);
		$this->SetColumns($columns);
		$this->SetRows($rows);
	}
	// get methods
	function GetColumns() { return $this->_columns; }
	function GetRows() { return $this->_rows; }
	function IsWrap() { return $this->_wrap; }
	function GetClassTextarea() { return $this->_classtextarea; }
	function GetClassTextareaRO() { return $this->_classtextarearo; }
	// set methods
	function SetColumns($columns) { $this->_columns = $columns; }
	function SetRows($rows) { $this->_rows = $rows; }
	function SetWrap($bool) { $this->_wrap = $bool; }
	function SetClasstextarea($cssclass) { $this->_classtextarea = $cssclass; }
	function SetClasstextareaRO($cssclass) { $this->_classtextarearo = $cssclass; }
	// support methods
	function GetParam() {
		$param = parent::GetParam();
		if (false === $this->IsWrap())
			$param .= " wrap='off'";
		return $param;
	}
	function GetClassOfTextarea() {
		return ($this->IsReadOnly() ? $this->GetClassTextareaRO() : $this->GetClassTextarea());
	}
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$columns = $this->GetColumns();
		$rows = $this->GetRows();
		$param = $this->GetParam();
		$classtextarea = $this->GetClassOfTextarea();
		echo "		<textarea name='{$ctrlname}' cols='{$columns}' rows='{$rows}' id='{$ctrlname}' class='{$classtextarea}' {$param}>{$value}</textarea>\n";
	}
}
class HTMLFileChooser2 extends HTMLEditBox2 {
	var $_path = "";

	function __construct($ctrlname, $title, $value, $description, $size = 60) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetDescription($description);
		$this->SetSize($size);
	}

	function GetPath() {
		return $this->_path;
	}

	function SetPath($path) {
		$this->_path = $path;
	}

	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$size = $this->GetSize();
		$param = $this->GetParam();
		$path = $this->GetPath();

		echo "		<input name='{$ctrlname}' type='text' class='formfld' id='{$ctrlname}' size='{$size}' value='{$value}' {$param} />\n";
		echo "		<input name='{$ctrlname}browsebtn' type='button' class='formbtn' id='{$ctrlname}browsebtn' onclick='{$ctrlname}ifield = form.{$ctrlname}; filechooser = window.open(\"filechooser.php?p=\"+encodeURIComponent({$ctrlname}ifield.value)+\"&amp;sd={$path}\", \"filechooser\", \"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300\"); filechooser.ifield = {$ctrlname}ifield; window.ifield = {$ctrlname}ifield;' value='...' />\n";
	}
}
class HTMLIPAddressBox2 extends HTMLEditBox2 {
	var $_ctrlnamenetmask = "";
	var $_valuenetmask = "";

	function __construct($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $description) {
		$this->SetCtrlName($ctrlname);
		$this->SetCtrlNameNetmask($ctrlnamenetmask);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetValueNetmask($valuenetmask);
		$this->SetDescription($description);
	}

	function GetCtrlNameNetmask() {
		return $this->_ctrlnamenetmask;
	}

	function SetCtrlNameNetmask($name) {
		$this->_ctrlnamenetmask = $name;
	}

	function GetValueNetmask() {
		return $this->_valuenetmask;
	}

	function SetValueNetmask($value) {
		$this->_valuenetmask = $value;
	}
}
class HTMLIPv4AddressBox2 extends HTMLIPAddressBox2 {
	function __construct($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $description) {
		parent::__construct($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $description);
		$this->SetSize(20);
	}

	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$valuenetmask = htmlspecialchars($this->GetValueNetmask(), ENT_QUOTES);
		$size = $this->GetSize();

		echo "    <input name='{$ctrlname}' type='text' class='formfld' id='{$ctrlname}' size='{$size}' value='{$value}' />\n";
		echo "    /\n";
		echo "    <select name='{$ctrlnamenetmask}' class='formfld' id='{$ctrlnamenetmask}'>\n";
		foreach (range(1, 32) as $netmask) {
			$optparam = "";
			if ($netmask == $valuenetmask)
				$optparam .= "selected=\"selected\" ";
			echo "      <option value='{$netmask}' {$optparam}>{$netmask}</option>\n";
		}
		echo "    </select>\n";
	}
}
class HTMLIPv6AddressBox2 extends HTMLIPAddressBox2 {
	function __construct($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $description) {
		parent::__construct($ctrlname, $ctrlnamenetmask, $title, $value, $valuenetmask, $description);
		$this->SetSize(30);
	}

	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$valuenetmask = htmlspecialchars($this->GetValueNetmask(), ENT_QUOTES);
		$size = $this->GetSize();

		echo "    <input name='{$ctrlname}' type='text' class='formfld' id='{$ctrlname}' size='{$size}' value='{$value}' />\n";
		echo "    /\n";
		echo "    <input name='{$ctrlnamenetmask}' type='text' class='formfld' id='{$ctrlnamenetmask}' size='2' value='{$valuenetmask}' />\n";
	}
}
class HTMLCheckBox2 extends HTMLBaseControlJS2 {
	var $_caption = "";
	var $_classcheckbox = 'celldatacheckbox';
	var $_classcheckboxro = 'celldatacheckbox';
	// constructor method
	function __construct($ctrlname, $title, $value, $caption, $description = "") {
		parent::__construct($ctrlname, $title, $value, $description);
		$this->SetCaption($caption);
	}
	// get methods
	function IsChecked() { return $this->GetValue(); }
	function GetCaption() { return $this->_caption; }
	function GetClassCheckbox() { return $this->_classcheckbox; }
	function GetClassCheckboxRO() { return $this->_classcheckboxro; }
	// set methods
	function SetChecked($bool) { $this->SetValue($bool); }
	function SetCaption($caption) { $this->_caption = $caption; }
	function SetClassCheckbox($cssclass) { $this->_classcheckbox = $cssclass; }
	function SetClassCheckboxRO($cssclass) { $this->_classcheckboxro = $cssclass; }
	// support methods
	function GetParam() {
		$param = "";
		if (true === $this->IsChecked()) { $param .= "checked=\"checked\" "; }
		if (true === $this->IsReadOnly()) { $param .= "disabled=\"disabled\" "; }
		$onclick = $this->GetJSonClick();
		if (!empty($onclick))
			$param .= "onclick='{$onclick}' ";
		return $param;
	}
	function GetClassOfCheckbox() {
		return ($this->IsReadOnly() ? $this->GetClassCheckboxRO() : $this->GetClassCheckbox());
	}
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$caption = $this->GetCaption();
		$param = $this->GetParam();
		$classcheckbox = $this->GetClassOfCheckbox();
		echo "<div class='{$classcheckbox}'>";
		echo "	<input name='{$ctrlname}' type='checkbox' id='{$ctrlname}' value='yes' {$param} />";
		echo "	<label for='{$ctrlname}'>{$caption}</label>";
		echo "</div>";
	}
}
class HTMLSelectControl2 extends HTMLBaseControlJS2 {
	var $_ctrlclass = "";
	var $_options = [];

	function __construct($ctrlclass, $ctrlname, $title, $value, $options, $description) {
		parent::__construct($ctrlname, $title, $value, $description);
		$this->SetCtrlClass($ctrlclass);
		$this->SetOptions($options);
	}

	function GetCtrlClass() {
		return $this->_ctrlclass;
	}

	function SetCtrlClass($ctrlclass) {
		$this->_ctrlclass = $ctrlclass;
	}

	function GetOptions() {
		return $this->_options;
	}

	function SetOptions($options) {
		$this->_options = $options;
		if (empty($this->_options)) {
			unset($this->_options);
			$this->_options = [];
		}
	}

	function GetParam() {
		$param = "";
		if (true === $this->IsReadOnly())
			$param .= "disabled=\"disabled\" ";
		$onclick = $this->GetJSonClick();
		if (!empty($onclick))
			$param .= "onclick='{$onclick}' ";
		return $param;
	}

	function RenderCtrl() {
		$ctrlclass = $this->GetCtrlClass();
		$ctrlname = $this->GetCtrlName();
		$value = htmlspecialchars($this->GetValue(), ENT_QUOTES);
		$param = $this->GetParam();
		$options = $this->GetOptions();

		echo "    <select name='{$ctrlname}' class='{$ctrlclass}' id='{$ctrlname}' {$param}>\n";
		foreach ($options as $optionk => $optionv) {
			$optparam = "";
			if ($value == $optionk)
				$optparam .= "selected=\"selected\" ";
			echo "      <option value='{$optionk}' {$optparam}>{$optionv}</option>\n";
		}
		echo "    </select>\n";
	}
}
class HTMLMultiSelectControl2 extends HTMLSelectControl2 {
	var $_size = 10;

	function __construct($ctrlclass, $ctrlname, $title, $value, $options, $description) {
		parent::__construct($ctrlclass, $ctrlname, $title, $value, $options, $description);
	}

	function GetSize() {
		return $this->_size;
	}

	function SetSize($size) {
		$this->_size = $size;
	}

	function RenderCtrl() {
		$ctrlclass = $this->GetCtrlClass();
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$param = $this->GetParam();
		$options = $this->GetOptions();
		$size = $this->GetSize();

		echo "    <select name='{$ctrlname}[]' class='{$ctrlclass}' multiple='multiple' id='{$ctrlname}' size='{$size}' {$param}>\n";
		foreach ($options as $optionk => $optionv) {
			$optparam = "";
			if (is_array($value) && in_array($optionk, $value))
				$optparam .= "selected=\"selected\" ";
			echo "      <option value='{$optionk}' {$optparam}>{$optionv}</option>\n";
		}
		echo "    </select>\n";
	}
}
class HTMLComboBox2 extends HTMLSelectControl2 {
	function __construct($ctrlname, $title, $value, $options, $description) {
		parent::__construct("formfld", $ctrlname, $title, $value, $options, $description);
	}
}
class HTMLMountComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname, $title, $value, $description) {
		global $config;

		// Generate options.
		array_make_branch($config,'mounts','mount');
		array_sort_key($config['mounts']['mount'],'devicespecialfile');

		$options = [];
		$options[""] = gtext("Must choose one");
		foreach ($config['mounts']['mount'] as $mountv) {
			$options[$mountv['uuid']] = $mountv['sharename'];
		}

		parent::__construct($ctrlname, $title, $value, $options, $description);
	}
}
class HTMLTimeZoneComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname, $title, $value, $description) {
		// Get time zone data.
		function is_timezone($elt) {
			return !preg_match("/\/$/", $elt);
		}

		exec('/usr/bin/tar -tf /usr/share/zoneinfo.txz', $timezonelist);
		$timezonelist = array_filter($timezonelist, 'is_timezone');
		sort($timezonelist);

		// Generate options.
		$options = [];
		foreach ($timezonelist as $tzv) {
			if (!empty($tzv)) {
				$tzv = substr($tzv, 2); // Remove leading './'
				$options[$tzv] = $tzv;
			}
		}

		parent::__construct($ctrlname, $title, $value, $options, $description);
	}
}
class HTMLLanguageComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname, $title, $value, $description) {
		global $g_languages;

		// Generate options.
		$options = [];
		foreach ($g_languages as $languagek => $languagev) {
			$options[$languagek] = gtext($languagev['desc']);
		}
		// Sort options alphabetically
		asort($options);

		parent::__construct($ctrlname, $title, $value, $options, $description);
	}
}
class HTMLInterfaceComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname, $title, $value, $description) {
		global $config;

		// Generate options.
		$options = array('lan' => 'LAN');
		for ($i = 1; isset($config['interfaces']['opt' . $i]); $i++) {
			if (isset($config['interfaces']['opt' . $i]['enable'])) {
				$options['opt' . $i] = $config['interfaces']['opt' . $i]['descr'];
			}
		}

		parent::__construct($ctrlname, $title, $value, $options, $description);
	}
}
class HTMLListBox2 extends HTMLMultiSelectControl2 {
	function __construct($ctrlname, $title, $value, $options, $description) {
		parent::__construct("formselect", $ctrlname, $title, $value, $options, $description);
	}
}
class HTMLSeparator2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_idname = '';
	var $_classseparator = 'list';
	// constructor method
	function __construct() {
	}
	// get methods
	function GetColSpan() { return $this->_colspan; }
	function GetClassSeparator() { return $this->_classseparator; }
	// set methods
	function SetColSpan($colspan) { $this->_colspan = $colspan; }
	function SetIdName($idname) { $this->_idname = $idname; }
	function SetClassSeparator($cssclass) { $this->_classseparator = $cssclass; }
	// support methods
	function GetClassOfSeparator() {
		return $this->GetClassSeparator();
	}
	function Render() {
		$colspan = $this->GetColSpan();
		$classseparator = $this->GetClassOfSeparator();
		echo ($this->_idname != '') ? "<tr id='{$this->_idname}'>\n" : "<tr>\n";
		echo "	<td colspan='{$colspan}' class='{$classseparator}' height='12'></td>\n";
		echo "</tr>\n";
	}
}
class HTMLTitleLine2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_idname = '';
	var $_classtopic = 'lhetop';
	// constructor method
	function __construct($title) {
		$this->SetTitle($title);
	}
	// get methods
	function GetColSpan() { return $this->_colspan; }
	function GetClassTopic() { return $this->_classtopic; }
	// set methods
	function SetColSpan($colspan) { $this->_colspan = $colspan; }
	function SetIdName($idname) { $this->_idname = $idname; }
	function SetClassTopic($cssclass) { $this->_classtopic = $cssclass; }
	// support functions
	function GetClassOfTopic() {
		return $this->GetClassTopic();
	}
	function Render() {
		$title = $this->GetTitle();
		$colspan = $this->GetColSpan();
		$classtopic = $this->GetClassOfTopic();
		echo ($this->_idname != '') ? "<tr id='{$this->_idname}'>\n" : "<tr>\n";
		echo "	<th colspan='{$colspan}' class='{$classtopic}'>{$title}</th>\n";
		echo "</tr>\n";
	}
}
class HTMLTitleLineCheckBox2 extends HTMLCheckBox2 {
	var $_colspan = 2;

	function __construct($ctrlname, $title, $value, $caption) {
		parent::__construct($ctrlname, $title, $value, $caption);
	}
	function GetColSpan() {
		return $this->_colspan;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function Render() {
		$ctrlname = $this->GetCtrlName();
		$caption = $this->GetCaption();
		$title = $this->GetTitle();
		$param = $this->GetParam();
		$colspan = $this->GetColSpan();

		echo "<tr id='{$ctrlname}_tr'>\n";
		echo "	<td colspan='{$colspan}' valign='top' class='optsect_t'>\n";
		echo "    <table border='0' cellspacing='0' cellpadding='0' width='100%'>\n";
		echo "      <tr>\n";
		echo "        <td class='optsect_s'><strong>{$title}</strong></td>\n";
		echo "        <td align='right' class='optsect_s'>\n";
		echo "          <input name='{$ctrlname}' type='checkbox' class='formfld' id='{$ctrlname}' value='yes' {$param} /><strong>{$caption}</strong>\n";
		echo "        </td>\n";
		echo "      </tr>\n";
		echo "    </table>\n";
		echo "  </td>\n";
		echo "</tr>\n";
	}
}
class HTMLText2 extends HTMLBaseControl2 {
	function __construct($ctrlname, $title, $text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}

	function RenderCtrl() {
		$text = $this->GetValue();

		echo "{$text}\n";
	}
}
class HTMLTextInfo2 extends HTMLBaseControl2 {
	function __construct($ctrlname, $title, $text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	function Render() {
		$ctrlname = $this->GetCtrlName();
		$title = $this->GetTitle();
		$classtag = $this->GetClassOfTag();
		$classdata = $this->GetClassOfData();
		$text = $this->GetValue();
		echo "<tr id='{$ctrlname}_tr'>\n";
		echo "	<td class='{$classtag}'>{$title}</td>\n";
		echo "	<td class='{$classdata}'><span id='{$ctrlname}'>{$text}</span></td>\n";
		echo "</tr>\n";
	}
}
class HTMLRemark2 extends HTMLBaseControl2 {
	function __construct($ctrlname, $title, $text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}

	function Render() {
		$ctrlname = $this->GetCtrlName();
		$title = $this->GetTitle();
		$text = $this->GetValue();

		echo "<div id='remark'>\n";
		if (!empty($title)) {
			echo "  <span class='red'>\n";
			echo "    <strong>{$title}:</strong>\n";
			echo "  </span><br />\n";
		}
		echo "  {$text}\n";
		echo "</div>\n";
	}
}
class HTMLFolderBox2 extends HTMLBaseControl2 {
	var $_path = "";

	function __construct($ctrlname, $title, $value, $description = "") {
		parent::__construct($ctrlname, $title, $value, $description);
	}

	function GetPath() {
		return $this->_path;
	}

	function SetPath($path) {
		$this->_path = $path;
	}

	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$path = $this->GetPath();

		echo "    <script type='text/javascript'>\n";
		echo "    //<![CDATA[\n";
		echo "    function onchange_{$ctrlname}() {\n";
		echo "      document.getElementById('{$ctrlname}data').value = document.getElementById('{$ctrlname}').value;\n";
		echo "    }\n";
		echo "    function onclick_add_{$ctrlname}() {\n";
		echo "      var value = document.getElementById('{$ctrlname}data').value;\n";
		echo "      if (value != '') {\n";
		echo "        var found = false;\n";
		echo "        var element = document.getElementById('{$ctrlname}');\n";
		echo "        for (var i = 0; i < element.length; i++) {\n";
		echo "          if (element.options[i].text == value) {\n";
		echo "            found = true;\n";
		echo "            break;\n";
		echo "          }\n";
		echo "        }\n";
		echo "        if (found != true) {\n";
		echo "          element.options[element.length] = new Option(value, value, false, true);\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_delete_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var msg = confirm('".gtext("Do you really want to remove the selected item from the list?")."');\n";
		echo "        if (msg == true) {\n";
		echo "          element.options[element.selectedIndex] = null;\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      } else {\n";
		echo "        alert('".gtext("Select item to remove from the list")."');\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_change_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var value = document.getElementById('{$ctrlname}data').value;\n";
		echo "        element.options[element.selectedIndex].text = value;\n";
		echo "        element.options[element.selectedIndex].value = value;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onsubmit_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      for (var i = 0; i < element.length; i++) {\n";
		echo "        if (element.options[i].value != '')\n";
		echo "          element.options[i].selected = true;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    //]]>\n";
		echo "    </script>\n";
		echo "    <select name='{$ctrlname}[]' class='formfld' id='{$ctrlname}' multiple='multiple' size='4' style='width: 350px' onchange='onchange_{$ctrlname}()'>\n";
		foreach ($value as $valuek => $valuev) {
			echo "      <option value='{$valuev}' {$optparam}>{$valuev}</option>\n";
		}
		echo "    </select>\n";
		echo "    <input name='{$ctrlname}deletebtn' type='button' class='formbtn' id='{$ctrlname}deletebtn' value='".gtext("Delete")."' onclick='onclick_delete_{$ctrlname}()' /><br />\n";
		echo "    <input name='{$ctrlname}data' type='text' class='formfld' id='{$ctrlname}data' size='60' value='' />\n";
		echo "    <input name='{$ctrlname}browsebtn' type='button' class='formbtn' id='{$ctrlname}browsebtn' onclick='ifield = form.{$ctrlname}data; filechooser = window.open(\"filechooser.php?p=\"+encodeURIComponent(ifield.value)+\"&amp;sd={$path}\", \"filechooser\", \"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300\"); filechooser.ifield = ifield; window.ifield = ifield;' value='...' />\n";
		echo "    <input name='{$ctrlname}addbtn' type='button' class='formbtn' id='{$ctrlname}addbtn' value='".gtext("Add")."' onclick='onclick_add_{$ctrlname}()' />\n";
		echo "    <input name='{$ctrlname}changebtn' type='button' class='formbtn' id='{$ctrlname}changebtn' value='".gtext("Change")."' onclick='onclick_change_{$ctrlname}()' />\n";
	}
}
class HTMLFolderBox12 extends HTMLFolderBox2 {
	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$path = $this->GetPath();

		echo "    <script type='text/javascript'>\n";
		echo "    //<![CDATA[\n";
		echo "    function onchange_{$ctrlname}() {\n";
		echo "      var value1 = document.getElementById('{$ctrlname}');\n";
		echo "      if (value1.value.charAt(0) != '/') {\n";
		echo "      document.getElementById('{$ctrlname}data').value = value1.value.substring(2,(value1.value.length));\n";
		echo "      document.getElementById('{$ctrlname}filetype').value = value1.value.charAt(0);\n";
		echo "        }else{\n";
		echo "      document.getElementById('{$ctrlname}data').value = document.getElementById('{$ctrlname}').value;\n";
		echo "      document.getElementById('{$ctrlname}filetype').value = '';\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_add_{$ctrlname}() {\n";
		echo "      var value1 = document.getElementById('{$ctrlname}data').value;\n";
		echo "      var valuetype = document.getElementById('{$ctrlname}filetype').value;\n";
		echo "      if (valuetype != '') {\n";
		echo "      var valuetype = valuetype + ',';\n";
		echo "          }\n";
		echo "      var value = valuetype +  value1;\n";
		echo "      if (value != '') {\n";
		echo "        var found = false;\n";
		echo "        var element = document.getElementById('{$ctrlname}');\n";
		echo "        for (var i = 0; i < element.length; i++) {\n";
		echo "          if (element.options[i].text == value) {\n";
		echo "            found = true;\n";
		echo "            break;\n";
		echo "          }\n";
		echo "        }\n";
		echo "        if (found != true) {\n";
		echo "          element.options[element.length] = new Option(value, value, false, true);\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_delete_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var msg = confirm('".gtext("Do you really want to remove the selected item from the list?")."');\n";
		echo "        if (msg == true) {\n";
		echo "          element.options[element.selectedIndex] = null;\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      } else {\n";
		echo "        alert('".gtext("Select item to remove from the list")."');\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_change_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var value1 = document.getElementById('{$ctrlname}data').value;\n";
		echo "      var valuetype = document.getElementById('{$ctrlname}filetype').value;\n";
		echo "      if (valuetype != '') {\n";
		echo "      var valuetype = valuetype + ',';\n";
		echo "          }\n";
		echo "      var value = valuetype +  value1;\n";
		echo "        element.options[element.selectedIndex].text = value;\n";
		echo "        element.options[element.selectedIndex].value = value;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onsubmit_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      for (var i = 0; i < element.length; i++) {\n";
		echo "        if (element.options[i].value != '')\n";
		echo "          element.options[i].selected = true;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    //]]>\n";
		echo "    </script>\n";
		echo "    <select name='{$ctrlname}[]' class='formfld' id='{$ctrlname}' multiple='multiple'  style='width: 350px' onchange='onchange_{$ctrlname}()'>\n";
		foreach ($value as $valuek => $valuev) {
			echo "      <option value='{$valuev}' {$optparam}>{$valuev}</option>\n";
		}
		echo "    </select>\n";
		echo "    <input name='{$ctrlname}deletebtn' type='button' class='formbtn' id='{$ctrlname}deletebtn' value='".gtext("Delete")."' onclick='onclick_delete_{$ctrlname}()' /><br />\n";
		echo "    <select name='{$ctrlname}filetype' class='formfld' id='{$ctrlname}filetype' > ";
		echo "			<option value=''>".gtext("All")."</option>";
		echo "			<option value='A'>".gtext("Audio")."</option>";
		echo "			<option value='V'>".gtext("Video")."</option>";
		echo "  		<option value='P'>".gtext("Pictures")."</option>";
		echo "    </select>";

		echo "    <input name='{$ctrlname}data' type='text' class='formfld' id='{$ctrlname}data' size='60' value='' />\n";
		echo "    <input name='{$ctrlname}browsebtn' type='button' class='formbtn' id='{$ctrlname}browsebtn' onclick='ifield = form.{$ctrlname}data; filechooser = window.open(\"filechooser.php?p=\"+encodeURIComponent(ifield.value)+\"&amp;sd={$path}\", \"filechooser\", \"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300\"); filechooser.ifield = ifield; window.ifield = ifield;' value='...' />\n";
		echo "    <input name='{$ctrlname}addbtn' type='button' class='formbtn' id='{$ctrlname}addbtn' value='".gtext("Add")."' onclick='onclick_add_{$ctrlname}()' />\n";
		echo "    <input name='{$ctrlname}changebtn' type='button' class='formbtn' id='{$ctrlname}changebtn' value='".gtext("Change")."' onclick='onclick_change_{$ctrlname}()' />\n";
	}
}
class co_DOMElement extends DOMElement {
	public function addAttributes($attributes = []) {
		foreach($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
		return $this;
	}
	public function addElement(string $name, array $attributes = [], string $value = NULL, string $namespaceURI = NULL) {
		$node = $this->appendChild(new co_DOMElement($name, $value, $namespaceURI));
		$node->addAttributes($attributes);
		return $node;
	}
}
class co_DOMDocument extends DOMDocument {
	public function __construct(string $version = '1.0', string $encoding = 'UTF-8') {
		parent::__construct($version, $encoding);
		$this->formatOutput = true;
		$this->registerNodeClass('DOMElement', 'co_DOMElement');
	}
	public function addElement(string $name, array $attributes = [], string $value = NULL, string $namespaceURI = NULL) {
		$node = $this->appendChild(new co_DOMElement($name, $value, $namespaceURI));
		$node->addAttributes($attributes);
		return $node;
	}
	public function render() {
		return $this->saveHTML();
	}
}
?>
