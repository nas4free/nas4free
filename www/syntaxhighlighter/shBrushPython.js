/*
	shBrushPython.js

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
	either expressed or implied, of the FreeBSD Project.

 * JsMin
 * Javascript Compressor
 * http://www.crockford.com/
 * http://www.smallsharptools.com/
*/

dp.sh.Brushes.Python=function()
{var keywords='and assert break class continue def del elif else '+'except exec finally for from global if import in is '+'lambda not or pass print raise return try yield while';var special='None True False self cls class_'
this.regexList=[{regex:dp.sh.RegexLib.SingleLinePerlComments,css:'comment'},{regex:new RegExp("^\\s*@\\w+",'gm'),css:'decorator'},{regex:new RegExp("(['\"]{3})([^\\1])*?\\1",'gm'),css:'comment'},{regex:new RegExp('"(?!")(?:\\.|\\\\\\"|[^\\""\\n\\r])*"','gm'),css:'string'},{regex:new RegExp("'(?!')*(?:\\.|(\\\\\\')|[^\\''\\n\\r])*'",'gm'),css:'string'},{regex:new RegExp("\\b\\d+\\.?\\w*",'g'),css:'number'},{regex:new RegExp(this.GetKeywords(keywords),'gm'),css:'keyword'},{regex:new RegExp(this.GetKeywords(special),'gm'),css:'special'}];this.CssClass='dp-py';this.Style='.dp-py .builtins { color: #ff1493; }'+'.dp-py .magicmethods { color: #808080; }'+'.dp-py .exceptions { color: brown; }'+'.dp-py .types { color: brown; font-style: italic; }'+'.dp-py .commonlibs { color: #8A2BE2; font-style: italic; }';}
dp.sh.Brushes.Python.prototype=new dp.sh.Highlighter();dp.sh.Brushes.Python.Aliases=['py','python'];
