# $FreeBSD: src/etc/root/dot.cshrc 242850 2012-11-10 06:05:04Z eadler $
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2014 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#
# Portions of freenas (http://www.freenas.org).
# Copyright (c) 2005-2011 Olivier Cochard-Labbe <olivier@freenas.org>.
# All rights reserved.
#
# .cshrc - csh resource script, read at beginning of execution by each shell
#

alias h		history 25
alias j		jobs -l
alias la	ls -a
alias lf	ls -FA
alias ll	ls -lAF
alias ls      ls -G

# A righteous umask
umask 22

set path = (/sbin /bin /usr/sbin /usr/bin /usr/local/sbin /usr/local/bin $HOME/bin)

setenv	LANG	 en_US.UTF-8
setenv	LANGUAGE en_US.UTF-8
setenv	LC_ALL	 en_US.UTF-8

setenv	PAGER	more
setenv	BLOCKSIZE	K
setenv	EDITOR nano

if ($?prompt) then
	# An interactive shell -- set some stuff up
	set prompt="%{\033[1;32m%}%m: %{\033[1;32m%}%. %{\033[0m%}%# "
	set promptchars = "%#"
	set filec
	set autolist
	set history = 100
	set savehist = 100
	if ( $?tcsh ) then
		bindkey "^W" backward-delete-word
		bindkey -k up history-search-backward
		bindkey -k down history-search-forward
	endif
endif

# Display console menu (only on ttyv0/ttyd0).
if ( "ttyv0" == "$tty" || "ttyu0" == "$tty" ) then
	/etc/rc.banner
	/etc/rc.initial
endif
