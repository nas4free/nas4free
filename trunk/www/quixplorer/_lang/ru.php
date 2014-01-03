<?php
/*
	ru.php

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
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
// Russian Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

      // error
      "error"                => "Ошибка",
      "back"                 => "Назад",
      
      // root
      "home"                 => "Домашний каталог пользователя не существует, проверьте ваши настройки...",
      "abovehome"            => "Текущий каталог не может находиться выше домашнего каталога пользователя.",
      "targetabovehome"      => "Целевой каталог не может находиться выше домашнего каталога пользователя.",

      // exist
      "direxist"             => "Этот каталог не существует",
      //"filedoesexist"      => "Этот файл уже существует",
      "fileexist"            => "Этого файла не существует",
      "itemdoesexist"        => "Этот объект уже существует",
      "itemexist"            => "Этого объекта не существует",
      "targetexist"          => "Целевого каталога не существует",
      "targetdoesexist"      => "Целевой объект уже существует",
      
      // open
      "opendir"              => "Невозможно открыть каталог",
      "readdir"              => "Невозможно прочесть содержимое каталога",

      // access
      "accessdir"            => "Вам не разрешен доступ к этому каталогу",
      "accessfile"           => "Вам не разрешен доступ к этому файлу",
      "accessitem"           => "Вам не разрешен доступ к этому объекту",
      "accessfunc"           => "Вам не разрешено использовать эту функцию",
      "accesstarget"         => "Вам не разрешен доступ к целевому каталогу",

      // actions
	"chmod_not_allowed"  => 'Changing Permissions to NONE is not allowed!',
      "permread"             => "Ошибка получения прав доступа",
      "permchange"           => "Ошибка смены прав доступа",
      "openfile"             => "Ошибка открытия файла",
      "savefile"             => "Ошибка сохранения файла",
      "createfile"           => "Ошибка создания файла",
      "createdir"            => "Ошибка создания каталога",
      "uploadfile"           => "Ошибка загрузки файла",
      "copyitem"             => "Ошибка копирования",
      "moveitem"             => "Ошибка перемещения",
      "delitem"              => "Ошибка удаления",
      "chpass"               => "Ошибка смены пароля",
      "deluser"              => "Ошибка удаления пользователя",
      "adduser"              => "Ошибка добавления пользователя",
      "saveuser"             => "Ошибка сохранения пользователя",
      "searchnothing"        => "Строка поиска не должна быть пустой",
      
      // misc
      "miscnofunc"           => "Функция недоступна",
      "miscfilesize"         => "Размер файла превышает максимальный",
      "miscfilepart"         => "Файл был загружен частично",
      "miscnoname"           => "Вы должны дать задать имя",
      "miscselitems"         => "Вы не выбрали объекты",
      "miscdelitems"         => "Вы уверены, что хотите удалить эти объекты (\"+num+\" шт.) ?",
      "miscdeluser"          => "Вы уверены, что хотите удалить пользователя '\"+user+\"' ?",
      "miscnopassdiff"       => "Новый пароль совпадает с текущим",
      "miscnopassmatch"      => "Пароли не совпадают",
      "miscfieldmissed"      => "Все важные поля должны быть заполнены",
      "miscnouserpass"       => "Неправильные имя пользователя или пароль",
      "miscselfremove"       => "Вы не можете удалять свою учетную запись",
      "miscuserexist"        => "Такой пользователь уже существует",
      "miscnofinduser"       => "Невозможно найти пользователя",
);
$GLOBALS["messages"] = array(
      // links
      "permlink"             => "Изменить права доступа",
      "editlink"             => "Редактировать",
      "downlink"             => "Скачать",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
      "uplink"               => "Наверх",
      "homelink"             => "Домой",
      "reloadlink"           => "Обновить",
      "copylink"             => "Копировать",
      "movelink"             => "Переместить",
      "dellink"              => "Удалить",
      "comprlink"            => "Архивировать",
      "adminlink"            => "Администрирование",
      "logoutlink"           => "Выход",
      "uploadlink"           => "Закачать",
      "searchlink"           => "Поиск",
	"unziplink"		=> "UNZIP",
      
      // list
      "nameheader"           => "Файл",
      "sizeheader"           => "Размер",
      "typeheader"           => "Тип",
      "modifheader"          => "Изменен",
      "permheader"           => "Права доступа",
      "actionheader"         => "Действия",
      "pathheader"           => "Путь",
      
      // buttons
      "btncancel"            => "Отменить",
      "btnsave"              => "Сохранить",
      "btnchange"            => "Изменить",
      "btnreset"             => "Очистить",
      "btnclose"             => "Закрыть",
      "btncreate"            => "Создать",
      "btnsearch"            => "Поиск",
      "btnupload"            => "Закачать",
      "btncopy"              => "Копировать",
      "btnmove"              => "Переместить",
      "btnlogin"             => "Вход",
      "btnlogout"            => "Выход",
      "btnadd"               => "Добавить",
      "btnedit"              => "Редактировать",
      "btnremove"            => "Удалить",
	"btnunzip"		=> "Unzip",
      
      // actions
      "actdir"               => "Каталог",
      "actperms"             => "Поменять права доступа",
      "actedit"              => "Редактировать файл",
      "actsearchresults"     => "Результаты поиска",
      "actcopyitems"         => "Копировать объекты",
      "actcopyfrom"          => "Копировать из /%s в /%s ",
      "actmoveitems"         => "Переместить объекты",
      "actmovefrom"          => "Переместить из /%s в /%s ",
      "actlogin"             => "Войти",
      "actloginheader"       => "Добро пожаловать в QuiXplorer!",
      "actadmin"             => "Администрирование",
      "actchpwd"             => "Изменение пароля",
      "actusers"             => "Пользователи",
      "actarchive"           => "Архивировать объекты",
	"actunzipitem"	=> "Extracting",
      "actupload"            => "Закачать файлы",
      
      // misc
      "miscitems"            => "Объекты",
      "miscfree"             => "Свободно",
      "miscusername"         => "Имя пользователя",
      "miscpassword"         => "Пароль",
      "miscoldpass"          => "Старый пароль",
      "miscnewpass"          => "Новый пароль",
      "miscconfpass"         => "Подтверждение пароля",
      "miscconfnewpass"      => "Подтверждение нового пароля",
      "miscchpass"           => "Сменить пароль",
      "mischomedir"          => "Домашний каталог",
      "mischomeurl"          => "Домашний URL",
      "miscshowhidden"       => "Показывать скрытые объекты",
      "mischidepattern"      => "Скрытые объекты",
      "miscperms"            => "Права доступа",
      "miscuseritems"        => "(имя, домашний каталог, показывать скрытые объекты, права доступа, активность)",
      "miscadduser"          => "добавление пользователя",
      "miscedituser"         => "редактирование свойств пользователя '%s'",
      "miscactive"           => "Активен",
      "misclang"             => "Язык",
      "miscnoresult"         => "Ничего не найдено",
      "miscsubdirs"          => "Искать в подкаталогах",
      "miscpermissions"	 => array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
      "miscyesno"            => array("Да","Нет","Yes","No"),
      "miscchmod"            => array("Владелец", "Группа", "Общий"),
);
?>
