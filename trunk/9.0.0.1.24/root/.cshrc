# $FreeBSD: src/etc/root/dot.cshrc,v 1.29 2004/04/01 19:28:00 krion Exp $
#
# .cshrc - csh resource script, read at beginning of execution by each shell
#
# see also csh(1), environ(7).
#

alias h		history 25
alias j		jobs -l
alias la	ls -a
alias lf	ls -FA
alias ll	ls -lA

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
	set prompt = "%m\:%~# "
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
