<?php
/*
	zh_CN.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2015 The NAS4Free Project <info@nas4free.org>.
	All rights reserved.

	Portions of Quixplorer (http://quixplorer.sourceforge.net).
	Authors: quix@free.fr, ck@realtime-projects.com.
	The Initial Developer of the Original Code is The QuiX project.

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
// Chinese (Simplified) Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "错误",
	"back"			=> "返回",

	// root
	"home"			=> "主目录不存在，请检查您的设置。",
	"abovehome"		=> "当前目录可能不在主目录里。",
	"targetabovehome"	=> "目标目录可能不在主目录里。",

	// exist
	"direxist"		=> "此目录并不存在。",
	"fileexist"		=> "此文件并不存在。",
	"itemdoesexist"	=> "此项目已经存在。",
	"itemexist"		=> "此项目并不存在。",
	"targetexist"		=> "目标目录并不存在。",
	"targetdoesexist"	=> "目标项目已经存在。",

	// open
	"opendir"		=> "无法打开目录。",
	"readdir"		=> "无法读取目录。",

	// access
	"accessdir"		=> "您不允许访问此目录。",
	"accessfile"		=> "您不允许访问此文件。",
	"accessitem"		=> "您不允许使用此项目。",
	"accessfunc"		=> "您不允许使用这个功能。",
	"accesstarget"	=> "您不允许访问目标目录。",

	// actions
	"chmod_not_allowed" => '不允许更改权限为NONE！',
	"permread"		=> "无法获取权限。",
	"permchange"		=> "无法更改权限。",
	"openfile"		=> "无法打开文件。",
	"savefile"		=> "无法储存文件。",
	"createfile"		=> "无法创建文件。",
	"createdir"		=> "无法创建目录。",
	"uploadfile"		=> "无法上传文件。",
	"copyitem"		=> "无法复制。",
	"moveitem"		=> "无法移动。",
	"delitem"		=> "无法删除。",
	"chpass"		=> "无法更改密码。",
	"deluser"		=> "无法删除用户。",
	"adduser"		=> "无法添加用户。",
	"saveuser"		=> "无法保存用户。",
	"searchnothing"	=> "您必须提供搜索查找。",

	// misc
	"miscnofunc"		=> "功能不可使用。",
	"miscfilesize"	=> "文件超过最大大小。",
	"miscfilepart"	=> "只上传了部分文件。",
	"miscnoname"		=> "您必须提供一个名称。",
	"miscselitems"	=> "您还没有选择任何项目。",
	"miscdelitems"	=> "您确定要删除这些 \"+num+\" 项目？",
	"miscdeluser"		=> "您确定要删除用户 '\"+user+\"'?",
	"miscnopassdiff"	=> "新密码与当前密码是相同的。",
	"miscnopassmatch"	=> "密码不符合。",
	"miscfieldmissed"	=> "您遗漏了一个重要的字段。",
	"miscnouserpass"	=> "用户名或密码不正确。",
	"miscselfremove"	=> "您无法删除自己。",
	"miscuserexist"	=> "用户已经存在。",
	"miscnofinduser"	=> "找不到用户。",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "更改权限",
	"editlink"		=> "编辑",
	"downlink"		=> "下载",
	"download_selected"	=> "下载选中的文件",
	"uplink"		=> "上",
	"homelink"		=> "主目录",
	"reloadlink"		=> "更新",
	"copylink"		=> "复制",
	"movelink"		=> "移动",
	"dellink"		=> "删除",
	"comprlink"		=> "压缩",
	"adminlink"		=> "管理",
	"logoutlink"		=> "退出",
	"uploadlink"		=> "上载",
	"searchlink"		=> "搜索",
	"unziplink"		=> "解缩",

	// list
	"nameheader"		=> "名称",
	"sizeheader"		=> "大小",
	"typeheader"		=> "类型",
	"modifheader"		=> "更改",
	"permheader"		=> "权限",
	"actionheader"	=> "操作",
	"pathheader"		=> "路径",

	// buttons
	"btncancel"		=> "取消",
	"btnsave"		=> "储存",
	"btnchange"		=> "更改",
	"btnreset"		=> "重设",
	"btnclose"		=> "关闭",
	"btncreate"		=> "创建",
	"btnsearch"		=> "搜索",
	"btnupload"		=> "上载",
	"btncopy"		=> "复制",
	"btnmove"		=> "移动",
	"btnlogin"		=> "登录",
	"btnlogout"		=> "退出",
	"btnadd"		=> "添加",
	"btnedit"		=> "编辑",
	"btnremove"		=> "删除",
	"btnunzip"		=> "解缩",

	// actions
	"actdir"		=> "目录",
	"actperms"		=> "更改权限",
	"actedit"		=> "编辑文件",
	"actsearchresults"	=> "搜索结果",
	"actcopyitems"	=> "复制项目",
	"actcopyfrom"		=> "从 /%s 复制到 /%s ",
	"actmoveitems"	=> "移动项目",
	"actmovefrom"		=> "从 /%s 移动到 /%s ",
	"actlogin"		=> "登录",
	"actloginheader"	=> "登录使用文件管理器",
	"actadmin"		=> "管理",
	"actchpwd"		=> "更改密码",
	"actusers"		=> "用户",
	"actarchive"		=> "压缩项目",
    "actunzipitem"	=> "解缩中",
	"actupload"		=> "上载文件",

	// misc
	"miscitems"		=> "项目",
	"miscfree"		=> "可用",
	"miscusername"	=> "用户名",
	"miscpassword"	=> "密码",
	"miscoldpass"		=> "旧密码",
	"miscnewpass"		=> "新密码",
	"miscconfpass"	=> "确认密码",
	"miscconfnewpass"	=> "确认新密码",
	"miscchpass"		=> "更改密码",
	"mischomedir"		=> "主目录",
	"mischomeurl"		=> "主目录网址",
	"miscshowhidden"	=> "显示隐藏的项目",
	"mischidepattern"	=> "隐藏模式",
	"miscperms"		=> "权限",
	"miscuseritems"	=> "（名称，主目录，显示隐藏的项目，权限，活跃）",
	"miscadduser"		=> "添加用户",
	"miscedituser"	=> "编辑用户 '%s'",
	"miscactive"		=> "活跃",
	"misclang"		=> "语言",
	"miscnoresult"	=> "没有结果。",
	"miscsubdirs"		=> "搜索子目录",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "用户可以读取和下载文件"),
					"create" 	=> array("Write", "用户可以创建新文件"),
					"change"	=> array("Change", "用户可以更改（上传，修改）现有的文件"),
					"delete"	=> array("Delete", "用户可以删除现有的文件"),
					"password"	=> array("Change password", "用户可以更改密码"),
					"admin"		=> array("Administrator", "所有的权限"),
			),
	"miscyesno"		=> array("是","否","Y","N"),
	"miscchmod"		=> array("用户", "组", "其他用户"),
);

?>