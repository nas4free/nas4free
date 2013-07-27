#!/bin/sh
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2013 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#
# Portions of freenas (http://www.freenas.org).
# Copyright (c) 2005-2011 Olivier Cochard-Labbe <olivier@freenas.org>.
# All rights reserved.
#
. /etc/rc.subr

name="upgrade"

load_rc_config "$name"

# Custom commands
extra_commands="clean"
start_cmd=":"
stop_cmd=":"
clean_cmd="upgrade_clean"

# Defaults
upgrade_obsoletefiles=${upgrade_obsoletefiles:-"/etc/install/ObsoleteFiles.inc"}

upgrade_clean()
{
	local _path _file _filepath

	_path=$1

	if [ -n ${_path} ]; then
		for _file in $(cat ${upgrade_obsoletefiles} | grep -v "^#"); do
			_filepath="${_path}/${_file}"
			if [ -d "${_filepath}" ]; then
				rm -d -r ${_filepath}
				logger "Upgrade: Remove obsolete directory ${_file}."
			elif [ -f "${_filepath}" ]; then
				rm ${_filepath}
				logger "Upgrade: Remove obsolete file ${_file}."
			fi
		done
	fi
}

run_rc_command "$@"
