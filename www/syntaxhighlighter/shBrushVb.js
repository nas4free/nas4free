/*
	shBrushVb.js

	Part of NAS4Free (http://www.nas4free.org).
	Copyright (c) 2012 The NAS4Free Project <info@nas4free.org>.
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

dp.sh.Brushes.Vb=function()
{var keywords='AddHandler AddressOf AndAlso Alias And Ansi As Assembly Auto '+'Boolean ByRef Byte ByVal Call Case Catch CBool CByte CChar CDate '+'CDec CDbl Char CInt Class CLng CObj Const CShort CSng CStr CType '+'Date Decimal Declare Default Delegate Dim DirectCast Do Double Each '+'Else ElseIf End Enum Erase Error Event Exit False Finally For Friend '+'Function Get GetType GoSub GoTo Handles If Implements Imports In '+'Inherits Integer Interface Is Let Lib Like Long Loop Me Mod Module '+'MustInherit MustOverride MyBase MyClass Namespace New Next Not Nothing '+'NotInheritable NotOverridable Object On Option Optional Or OrElse '+'Overloads Overridable Overrides ParamArray Preserve Private Property '+'Protected Public RaiseEvent ReadOnly ReDim REM RemoveHandler Resume '+'Return Select Set Shadows Shared Short Single Static Step Stop String '+'Structure Sub SyncLock Then Throw To True Try TypeOf Unicode Until '+'Variant When While With WithEvents WriteOnly Xor';this.regexList=[{regex:new RegExp('\'.*$','gm'),css:'comment'},{regex:dp.sh.RegexLib.DoubleQuotedString,css:'string'},{regex:new RegExp('^\\s*#.*','gm'),css:'preprocessor'},{regex:new RegExp(this.GetKeywords(keywords),'gm'),css:'keyword'}];this.CssClass='dp-vb';}
dp.sh.Brushes.Vb.prototype=new dp.sh.Highlighter();dp.sh.Brushes.Vb.Aliases=['vb','vb.net'];
