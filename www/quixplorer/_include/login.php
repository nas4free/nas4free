<?php
/*
	login.php

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
require_once "./_include/user.php";
require_once "./_include/debug.php";

user_load();

session_start();

function login_check ()
{
    _debug("checking login");
    global $require_login;

    // if no login is required, there is nothing to do
    if (!$require_login)
    {
        _debug("no login required..");
        return;
    }

    // if the user is already authenticated, we're done
    _debug("login required, checking login");
    login();
}

//FIXME update home_dir variable if user is logged in
function login ()
{
    if ( isset( $_SESSION["s_user"] ) )
    {
        if ( ! user_activate( $_SESSION["s_user"], $_SESSION["s_pass"] ))
        {
            _debug("Failed to activate user " . $_SESSION['s_user']);
            logout();
        }
    }
    else
    {
        if ( isset( $_POST["p_pass"] ) )
            $p_pass= $_POST["p_pass"];
        else
            $p_pass="";

        if ( isset( $_POST["p_user"] ) )
        {
            // Check Login
            if ( ! user_activate( stripslashes( $_POST["p_user"] ), md5( stripslashes( $p_pass ) ) ) )
            {
                _error( "failed to authenticate user " . $_POST["p_user"] );
                logout();
            }
            // authentication sucessfull
            _debug( "user '" . $_POST[ "p_user" ]  . "' successfully authenticated" );

            // set language
            $_SESSION['language'] = qx_request("lang", "en");
            return;
        } else {
		// Ask for Login
		show_header($GLOBALS["messages"]["actlogin"]);
		echo "<CENTER><BR><TABLE width=\"300\"><TR><TD colspan=\"2\" class=\"header\" nowrap><B>";
		echo $GLOBALS["messages"]["actloginheader"]."</B></TD></TR>\n<FORM name=\"login\" action=\"";
		echo make_link("login",NULL,NULL)."\" method=\"post\">\n";
		echo "<TR><TD>".$GLOBALS["messages"]["miscusername"].":</TD><TD align=\"right\">";
		echo "<INPUT name=\"p_user\" type=\"text\" size=\"25\"></TD></TR>\n";
		echo "<TR><TD>".$GLOBALS["messages"]["miscpassword"].":</TD><TD align=\"right\">";
		echo "<INPUT name=\"p_pass\" type=\"password\" size=\"25\"></TD></TR>\n";
		//Select box and auto language detection array
		echo "<TR><TD>".gettext("Detected Language:<br />(Change if needed)")."</TD><TD align=\"right\">";
		include('./_lang/_info.php');
		echo "<TR><TD colspan=\"2\" align=\"right\"><INPUT type=\"submit\" value=\"";
		echo $GLOBALS["messages"]["btnlogin"]."\"></TD></TR>\n</FORM></TABLE><BR></CENTER>\n";
            ?><script language="JavaScript1.2" type="text/javascript">
                <!--
                if(document.login) document.login.p_user.focus();
            // -->
            </script><?php
                show_footer();
            exit;
        }
    }
}

function login_is_user_logged_in()
{
    return isset( $_SESSION["s_user"] );
}

function logout ()
{
    global $_SESSION;

    _debug("logging out user " . $_SESSION["s_user"]);
	$_SESSION = array();
	session_destroy();
	header("location: ".$GLOBALS["script_name"]);
}

?>
