<?php
/*
	zh_TW.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
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
// Chinese (Traditional) Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "錯誤",
	"back"			=> "返回",
	
	// root
	"home"			=> "主目錄不存在，請檢查您的設置。",
	"abovehome"		=> "當前目錄不在主目錄裡。",
	"targetabovehome"	=> "目標目錄不在主目錄裡。",
	
	// exist
	"direxist"		=> "此目錄並不存在。",
	"fileexist"		=> "此檔案並不存在。",
	"itemdoesexist"	=> "此項目已經存在。",
	"itemexist"		=> "此項目並不存在。",
	"targetexist"		=> "目標目錄並不存在。",
	"targetdoesexist"	=> "目標項目已經存在。",
	
	// open
	"opendir"		=> "無法打開目錄。",
	"readdir"		=> "無法讀取目錄。",
	
	// access
	"accessdir"		=> "您不允許訪問此目錄。",
	"accessfile"		=> "您不允許訪問此檔案。",
	"accessitem"		=> "您不允許使用此項目。",
	"accessfunc"		=> "您不允許使用此項功能。",
	"accesstarget"	=> "您不允許訪問目標目錄。",
	
	// actions
	"chmod_not_allowed" => '不允許更改權限為NONE！',
	"permread"		=> "無法獲取權限。",
	"permchange"		=> "無法更改權限。",
	"openfile"		=> "無法打開檔案。",
	"savefile"		=> "無法儲存檔案。",
	"createfile"		=> "無法創建檔案。",
	"createdir"		=> "無法創建目錄。",
	"uploadfile"		=> "無法上傳檔案。",
	"copyitem"		=> "無法複製。",
	"moveitem"		=> "無法移動。",
	"delitem"		=> "無法刪除。",
	"chpass"		=> "無法更改密碼。",
	"deluser"		=> "無法刪除用戶。",
	"adduser"		=> "無法添加用戶。",
	"saveuser"		=> "無法保存用戶。",
	"searchnothing"	=> "您必須提供搜索查找。",
	
	// misc
	"miscnofunc"		=> "無法使用此項功能。",
	"miscfilesize"	=> "檔案超過最大大小。",
	"miscfilepart"	=> "只上傳了部分檔案。",
	"miscnoname"		=> "您必須提供一個名稱。",
	"miscselitems"	=> "您還沒有選擇任何項目。",
	"miscdelitems"	=> "您確定要刪除這些 \"+num+\" 項目？",
	"miscdeluser"		=> "您確定要刪除用戶 '\"+user+\"'?",
	"miscnopassdiff"	=> "新密碼與當前密碼是相同的。",
	"miscnopassmatch"	=> "密碼不符合。",
	"miscfieldmissed"	=> "您遺漏了一個重要的字段。",
	"miscnouserpass"	=> "用戶名或密碼不正確。",
	"miscselfremove"	=> "您無法刪除自己。",
	"miscuserexist"	=> "用戶已經存在。",
	"miscnofinduser"	=> "找不到用戶。",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "更改權限",
	"editlink"		=> "編輯",
	"downlink"		=> "下載",
	"download_selected"	=> "下載選中的檔案",
	"uplink"		=> "上",
	"homelink"		=> "主目錄",
	"reloadlink"		=> "更新",
	"copylink"		=> "複製",
	"movelink"		=> "移動",
	"dellink"		=> "刪除",
	"comprlink"		=> "壓縮",
	"adminlink"		=> "管理",
	"logoutlink"		=> "退出",
	"uploadlink"		=> "上載",
	"searchlink"		=> "搜索",
	"unziplink"		=> "解縮",
	
	// list
	"nameheader"		=> "名稱",
	"sizeheader"		=> "大小",
	"typeheader"		=> "類型",
	"modifheader"		=> "更改",
	"permheader"		=> "權限",
	"actionheader"	=> "操作",
	"pathheader"		=> "路徑",
	
	// buttons
	"btncancel"		=> "取消",
	"btnsave"		=> "儲存",
	"btnchange"		=> "更改",
	"btnreset"		=> "重設",
	"btnclose"		=> "關閉",
	"btncreate"		=> "創建",
	"btnsearch"		=> "搜索",
	"btnupload"		=> "上載",
	"btncopy"		=> "複製",
	"btnmove"		=> "移動",
	"btnlogin"		=> "登錄",
	"btnlogout"		=> "退出",
	"btnadd"		=> "添加",
	"btnedit"		=> "編輯",
	"btnremove"		=> "刪除",
	"btnunzip"		=> "解壓",
	
	// actions
	"actdir"		=> "目錄",
	"actperms"		=> "更改權限",
	"actedit"		=> "編輯檔案",
	"actsearchresults"	=> "搜索結果",
	"actcopyitems"	=> "複製項目",
	"actcopyfrom"		=> "從 /%s 複製到 /%s ",
	"actmoveitems"	=> "移动项目",
	"actmovefrom"		=> "從 /%s 移動到 /%s ",
	"actlogin"		=> "登錄",
	"actloginheader"	=> "登錄使用檔案管理器",
	"actadmin"		=> "管理",
	"actchpwd"		=> "更改密碼",
	"actusers"		=> "用戶",
	"actarchive"		=> "壓縮項目",
    "actunzipitem"	=> "解壓中",
	"actupload"		=> "上載檔案",
	
	// misc
	"miscitems"		=> "項目",
	"miscfree"		=> "可用",
	"miscusername"	=> "用戶名",
	"miscpassword"	=> "密碼",
	"miscoldpass"		=> "舊密碼",
	"miscnewpass"		=> "新密碼",
	"miscconfpass"	=> "確認密碼",
	"miscconfnewpass"	=> "確認新密碼",
	"miscchpass"		=> "更改密碼",
	"mischomedir"		=> "主目錄",
	"mischomeurl"		=> "主目錄網址",
	"miscshowhidden"	=> "顯示隱藏的項目",
	"mischidepattern"	=> "隱藏模式",
	"miscperms"		=> "權限",
	"miscuseritems"	=> "（名稱，主目錄，顯示隱藏的項目，權限，活躍）",
	"miscadduser"		=> "添加用戶",
	"miscedituser"	=> "編輯用戶 '%s'",
	"miscactive"		=> "活躍",
	"misclang"		=> "語言",
	"miscnoresult"	=> "沒有結果。",
	"miscsubdirs"		=> "搜索子目錄",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "用戶可以讀取和下載檔案"),
					"create" 	=> array("Write", "用戶可以創建新檔案"),
					"change"	=> array("Change", "用戶可以更改（上傳，修改）現有的檔案"),
					"delete"	=> array("Delete", "用戶可以刪除現有的檔案"),
					"password"	=> array("Change password", "用戶可以更改密碼"),
					"admin"		=> array("Administrator", "所有的權限"),
			),
	"miscyesno"		=> array("是","否","Y","N"),
	"miscchmod"		=> array("用戶", "組", "其他用戶"),
);
?>