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
. /etc/email.subr

name="smartdreport"

load_rc_config "${name}"

# Send output of smartctl -a to as message.
_message=`cat`
_report=`/usr/local/sbin/smartctl -a -d ${SMARTD_DEVICETYPE} ${SMARTD_DEVICE}`

# Send email.
send_email "${SMARTD_ADDRESS}" "${SMARTD_SUBJECT}" "${_message} ${_report}"
