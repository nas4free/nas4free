<?php
/*
	ru_mimes.php

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
// Russian Mimes Module

$GLOBALS["mimes"]=array(

      // dir, exe, file
      "dir"       => "Каталог",
      "exe"       => "Файл",
      "file"      => "Файл",
      "link"	    => "Link",

      // text
      "text"      => "Текстовый файл",

      // programming
      "php"       => "PHP-скрипт",
      "sql"       => "SQL-файл",
      "perl"      => "PERL-скрипт",
      "html"      => "HTML-страница",
      "js"        => "JavaScript-файл",
      "css"       => "CSS-стиль",
      "cgi"       => "CGI-скрипт",
      // C++
      "cpps"      => "Исходный код C++",
      "cpph"      => "Заголовок кода C++",
      // Java
      "javas"     => "Исходный код Java",
      "javac"     => "Исходный код Java class",
      // Pascal
      "pas"       => "Исходный код Pascal",

      // images
      "gif"       => "Изображение GIF",
      "jpg"       => "Изображение JPG",
      "bmp"       => "Изображение BMP",
      "png"       => "Изображение PNG",

      // compressed
      "zip"       => "Архив ZIP",
      "tar"       => "Архив TAR",
      "gzip"      => "Архив GZIP",
      "bzip2"     => "Архив BZIP2",
      "rar"       => "Архив RAR",
	"iso"		=> "Архив ISO",
	"mds"		=> "Файл MDS",

      // music
      "mp3"       => "Файл MP3",
      "wav"       => "Файл WAV",
      "midi"      => "Файл MIDI",
      "real"      => "Файл RealAudio",
      "flac"       => "Файл FLAC",

      // movie
      "mpg"       => "Видеофайл MPG",
      "mov"       => "Видеофайл Movie",
      "avi"       => "Видеофайл AVI",
      "flash"     => "Файл Flash",
	"mkv"		=> "Видеофайл MKV",
	"vob"		=> "Видеофайл VOB",

	// Micosoft / Adobe
	"word"		=> "Документ Word",
	"excel"	=> "Документ Excel",
	"pdf"		=> "Файл PDF",
	"xml"		=> "Файл XML",
	"c"		=> "Файл C",
	"psd"		=> "Файл Photoshop",
	"point"	=> "Документ PowerPoint"
);

?>