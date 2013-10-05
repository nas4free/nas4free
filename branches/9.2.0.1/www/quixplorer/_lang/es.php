<?php
/*
	en.php

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
// Spanish Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "ERROR(ES)",
	"back"			=> "Ir Atrás",
	
	// root
	"home"			=> "El directorio home no existe, revise su configuración.",
	"abovehome"		=> "El directorio actual no puede estar arriba del directorio home.",
	"targetabovehome"	=> "El directorio objetivo no puede estar arriba del directorio home.",
	
	// exist
	"direxist"		=> "Este directorio no existe.",
	//"filedoesexist"	=>  "Este archivo ya existe.",
	"fileexist"		=> "Este archivo no existe.",
	"itemdoesexist"	=> "Este artículo ya existe.",
	"itemexist"		=> "Este artículo no existe.",
	"targetexist"		=> "El directorio objetivo no existe.",
	"targetdoesexist"	=> "El artículo objetivo ya existe.",
	
	// open
	"opendir"		=> "Incapaz de abrir directorio.",
	"readdir"		=> "Incapaz de leer directorio.",
	
	// access
	"accessdir"		=> "Ud. no está permitido accesar este directorio.",
	"accessfile"		=> "Ud. no está permitido accesar a este archivo.",
	"accessitem"		=> "Ud. no está permitido accesar a este artículo.",
	"accessfunc"		=> "Ud. no está permitido usar esta funcion.",
	"accesstarget"	=> "Ud. no está permitido accesar al directorio objetivo.",
	
	// actions
	"chmod_not_allowed"  => 'Changing Permissions to NONE is not allowed!',
	"permread"		=> "Fracaso reuniendo permisos.",
	"permchange"		=> "Fracaso en Cambio de permisos.",
	"openfile"		=> "Fracaso abriendo archivo.",
	"savefile"		=> "Fracaso guardando archivo.",
	"createfile"		=> "Fracaso creando archivo.",
	"createdir"		=> "Fracaso creando Directorio.",
	"uploadfile"		=> "Fracaso subiendo archivo.",
	"copyitem"		=> "Fracaso Copiando.",
	"moveitem"		=> "Fracaso Moviendo.",
	"delitem"		=> "Fracaso Borrando.",
	"chpass"		=> "Fracaso Cambiando password.",
	"deluser"		=> "Fracaso Removiendo usuario.",
	"adduser"		=> "Fracaso Agragando usuario.",
	"saveuser"		=> "Fracaso Guardadno usuario.",
	"searchnothing"	=> "Ud. debe suministrar algo para la busqueda.",
	
	// misc
	"miscnofunc"		=> "Función no disponible.",
	"miscfilesize"	=> "Archivo excede maximo tamaño.",
	"miscfilepart"	=> "Archivo fue parcialmente subido.",
	"miscnoname"		=> "Ud. debe suministrar un nombre.",
	"miscselitems"	=> "Ud. no tiene seleccionado(s) ningun artículo.",
	"miscdelitems"	=> "Está seguro de querer borrar este(os) \"+num+\" artículo(s)?",
	"miscdeluser"		=> "Está seguro de querer borrar usuario '\"+user+\"'?",
	"miscnopassdiff"	=> "Nuevo password no difiere del actual.",
	"miscnopassmatch"	=> "No coinciden los Passwords.",
	"miscfieldmissed"	=> "Ud. falló en un importante campo.",
	"miscnouserpass"	=> "Usuario o password incorrecto.",
	"miscselfremove"	=> "Ud. no puede borrarse a si mismo.",
	"miscuserexist"	=> "Usuario ya existe.",
	"miscnofinduser"	=> "No se puede encontrar usuario.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "PORMISOS CAMBIADOS",
	"editlink"		=> "EDITAR",
	"downlink"		=> "DESCARGAR",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "ARRIBA",
	"homelink"		=> "HOME",
	"reloadlink"		=> "RECARGAR",
	"copylink"		=> "COPIAR",
	"movelink"		=> "MOVER",
	"dellink"		=> "BORRAR",
	"comprlink"		=> "ARCHIVAR",
	"adminlink"		=> "ADMINISTRAR",
	"logoutlink"		=> "SALIR",
	"uploadlink"		=> "SUBIR",
	"searchlink"		=> "BÚSQUEDA",
	"unziplink"		=> "UNZIP",
	
	// list
	"nameheader"		=> "Nombre",
	"sizeheader"		=> "Tamaño",
	"typeheader"		=> "Tipo",
	"modifheader"		=> "Modificado",
	"permheader"		=> "Permisos",
	"actionheader"	=> "Acciones",
	"pathheader"		=> "Ruta",
	
	// buttons
	"btncancel"		=> "Cancelar",
	"btnsave"		=> "Grabar",
	"btnchange"		=> "Cambiar",
	"btnreset"		=> "Restablecer",
	"btnclose"		=> "Cerrar",
	"btncreate"		=> "Crear",
	"btnsearch"		=> "Buscar",
	"btnupload"		=> "Subir",
	"btncopy"		=> "Copiar",
	"btnmove"		=> "Mover",
	"btnlogin"		=> "Login",
	"btnlogout"		=> "Salir",
	"btnadd"		=> "Añadir",
	"btnedit"		=> "Editar",
	"btnremove"		=> "Remover",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Directorio",
	"actperms"		=> "Cambiar permisos",
	"actedit"		=> "Editar archivo",
	"actsearchresults"	=> "Resultado de busqueda.",
	"actcopyitems"	=> "Copiar artículos(s)",
	"actcopyfrom"		=> "Copia de /%s a /%s ",
	"actmoveitems"	=> "Mover artículo(s)",
	"actmovefrom"		=> "Mover de /%s a /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Login para usar QuiXplorer",
	"actadmin"		=> "Administración",
	"actchpwd"		=> "Cambiar password",
	"actusers"		=> "Usuarios",
	"actarchive"		=> "Archivar item(s)",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Subir Archivo(s)",
	
	// misc
	"miscitems"		=> "Artículo(s)",
	"miscfree"		=> "Libre",
	"miscusername"	=> "Nombre de usuario",
	"miscpassword"	=> "Password",
	"miscoldpass"		=> "Password Antiguo",
	"miscnewpass"		=> "Password Nuevo",
	"miscconfpass"	=> "Confirmar password",
	"miscconfnewpass"	=> "Confirmar nuevo password",
	"miscchpass"		=> "Cambiar password",
	"mischomedir"		=> "Directorio Home",
	"mischomeurl"		=> "URL Home",
	"miscshowhidden"	=> "Mostrar artículos ocultos",
	"mischidepattern"	=> "Ocultar patrón",
	"miscperms"		=> "Permisos",
	"miscuseritems"	=> "(nombre, directorio home, mostrar artículos ocultos, permisos, activar)",
	"miscadduser"		=> "añadir usuario",
	"miscedituser"	=> "editar usario '%s'",
	"miscactive"		=> "Activar",
	"misclang"		=> "Lenguaje",
	"miscnoresult"	=> "Resultado(s) no disponible(s).",
	"miscsubdirs"		=> "Búsqueda de subdirectorios",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Si","No","S","N"),
	"miscchmod"		=> array("Propietario", "Grupo", "Público"),
);
?>
