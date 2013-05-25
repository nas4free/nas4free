<?php
/*
	ptbr.php

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
// Português - Brasil Language Module

$GLOBALS["charset"] = "UTF-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d/m/Y H:i";
$GLOBALS["error_msg"] = array(

	// error
	"error"		=> "ERRO(S)",
	"back"			=> "Voltar",
	
	// root
	"home"			=> "A pasta padrão não existe. Entre em contato com o administrador.",
	"abovehome"		=> "A pasta atual não existe. Entre em contato com o administrador..",
	"targetabovehome"	=> "A pasta destino não existe.",
	
	// exist
	"direxist"		=> "Esta pasta não existe.",
	"filedoesexist"	=> "Este arquivo já existe.",
	"fileexist"		=> "Este arquivo não existe.",
	"itemdoesexist"	=> "Item já existente.",
	"itemexist"		=> "Este item não existe.",
	"targetexist"		=> "A pasta destino não existe.",
	"targetdoesexist"	=> "A pasta destino já existe.",
	
	// open
	"opendir"		=> "Erro ao abrir a pasta.",
	"readdir"		=> "Erro ao ler a pasta.",
	
	// access
	"accessdir"		=> "Você não tem permissão para acessar esta pasta.",
	"accessfile"		=> "Você não tem permissão para acessar este arquivo.",
	"accessitem"		=> "Você não tem permissão para acessar este item.",
	"accessfunc"		=> "Você não tem permissão para acessar esta função.",
	"accesstarget"	=> "Você não tem permissão para acessar esta pasta.",
	
	// actions
	"permread"		=> "Sem permissão.",
	"permchange"		=> "Sem permissão.",
	"openfile"		=> "Erro ao abrir arquivo.",
	"savefile"		=> "Erro ao salvar arquivo.",
	"createfile"		=> "Erro na criação do arquivo.",
	"createdir"		=> "Erro na criação da pasta.",
	"uploadfile"		=> "Erro no upload.",
	"copyitem"		=> "Erro ao copiar.",
	"moveitem"		=> "Erro ao mover.",
	"delitem"		=> "Erro ao deletar.",
	"chpass"		=> "Erro na troca de senha.",
	"deluser"		=> "Erro ao remover usuário.",
	"adduser"		=> "Erro ao adicionar usuário.",
	"saveuser"		=> "Erro ao salvar usuário.",
	"searchnothing"	=> "Digite algo para buscar.",
	
	// misc
	"miscnofunc"		=> "Função indisponível.",
	"miscfilesize"	=> "Arquivo excedeu tamanho máximo permitido.",
	"miscfilepart"	=> "Arquivo enviado parcialmente.",
	"miscnoname"		=> "Você deve indicar um nome.",
	"miscselitems"	=> "Não houve seleção de item(s).",
	"miscdelitems"	=> "Deseja realmente apagar \"+num+\" item(s)?",
	"miscdeluser"		=> "Deseja realmente remover o usuário '\"+user+\"'?",
	"miscnopassdiff"	=> "A nova senha é igual a atual.",
	"miscnopassmatch"	=> "As senhas não correspondem.",
	"miscfieldmissed"	=> "Você esqueceu um campo importante.",
	"miscnouserpass"	=> "Usuário ou senha incorretos.",
	"miscselfremove"	=> "Você não pode remover.",
	"miscuserexist"	=> "Usuário já existente.",
	"miscnofinduser"	=> "Usuário não encontrado.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ALTERAR PERMISSÕES",
	"editlink"		=> "EDITAR",
	"downlink"		=> "DOWNLOAD",
	"download_selected"	=> "DOWNLOAD SELECTED FILES",
	"uplink"		=> "ACIMA",
	"homelink"		=> "INÍCIO",
	"reloadlink"		=> "ATUALIZAR",
	"copylink"		=> "COPIAR",
	"movelink"		=> "MOVER",
	"dellink"		=> "REMOVER",
	"comprlink"		=> "COMPACTAR",
	"adminlink"		=> "ADMINISTRAÇÃO",
	"logoutlink"		=> "SAIR",
	"uploadlink"		=> "ENVIAR",
	"searchlink"		=> "BUSCAR",
	"unziplink"		=> "UNZIP",
	
	// list
	"nameheader"		=> "Nome",
	"sizeheader"		=> "Tamanho",
	"typeheader"		=> "Tipo",
	"modifheader"		=> "Modificado",
	"permheader"		=> "Permissões",
	"actionheader"	=> "Ações",
	"pathheader"		=> "Caminho",
	
	// buttons
	"btncancel"		=> "Cancelar",
	"btnsave"		=> "Salvar",
	"btnchange"		=> "Modificar",
	"btnreset"		=> "Reset",
	"btnclose"		=> "Fechar",
	"btncreate"		=> "Criar",
	"btnsearch"		=> "Buscar",
	"btnupload"		=> "Enviar",
	"btncopy"		=> "Copiar",
	"btnmove"		=> "Mover",
	"btnlogin"		=> "Login",
	"btnlogout"		=> "Logout",
	"btnadd"		=> "Adicionar",
	"btnedit"		=> "Editar",
	"btnremove"		=> "Remover",
	"btnunzip"		=> "Unzip",
	
	// actions
	"actdir"		=> "Pasta",
	"actperms"		=> "Modificar permissões",
	"actedit"		=> "Editar arquivos",
	"actsearchresults"	=> "Resultados da busca",
	"actcopyitems"	=> "Item(s) copiado(s)",
	"actcopyfrom"		=> "Copiar de /%s pa /%s ",
	"actmoveitems"	=> "Mover item(s)",
	"actmovefrom"		=> "Mover de /%s para /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Login -  Disco Virtual",
	"actadmin"		=> "Administração",
	"actchpwd"		=> "Alterar senha",
	"actusers"		=> "Usuários",
	"actarchive"		=> "Compactar item(s)",
	"actunzipitem"	=> "Extracting",
	"actupload"		=> "Enviar arqiuvo(s)",
	
	// misc
	"miscitems"		=> "Item(s)",
	"miscfree"		=> "Livre",
	"miscusername"	=> "Usuário",
	"miscpassword"	=> "Senha",
	"miscoldpass"		=> "Senha antiga",
	"miscnewpass"		=> "Senha nova",
	"miscconfpass"	=> "Confirme senha",
	"miscconfnewpass"	=> "Confirme nova senha",
	"miscchpass"		=> "Alterar senha",
	"mischomedir"		=> "Pasta padrão",
	"mischomeurl"		=> "Local URL",
	"miscshowhidden"	=> "Exibir itens ocultos",
	"mischidepattern"	=> "Hide pattern",
	"miscperms"		=> "Permissões",
	"miscuseritems"	=> "(nome, pasta padrão, exibir itens ocultos, permissões, ativo)",
	"miscadduser"		=> "Adicionar usuário",
	"miscedituser"	=> "Editar usuário '%s'",
	"miscactive"		=> "Ativo",
	"misclang"		=> "Idioma",
	"miscnoresult"	=> "Sem resultados.",
	"miscsubdirs"		=> "Buscar sub-pastas",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"	=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Sim","Não","S","N"),
	"miscchmod"		=> array("Usuário", "Grupo", "Público"),
);
?>