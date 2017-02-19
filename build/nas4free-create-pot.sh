#!/bin/sh
#
# Extract gettext and gtext strings from source files and create new pot file.
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#

# Global variables
NAS4FREE_ROOTDIR="/usr/local/nas4free"
NAS4FREE_SVNDIR="$NAS4FREE_ROOTDIR/svn"
NAS4FREE_PRODUCTNAME=$(cat ${NAS4FREE_SVNDIR}/etc/prd.name)

OUTPUT="$(echo ${NAS4FREE_PRODUCTNAME} | tr '[:upper:]' '[:lower:]').pot"
OUTPUTDIR="${NAS4FREE_SVNDIR}/locale"
PARAMETERS="--output-dir=${OUTPUTDIR} --output=${OUTPUT} --language=PHP \
--force-po --no-location --no-wrap --sort-output --omit-header --keyword="gtext""

cd ${NAS4FREE_SVNDIR}/www
xgettext ${PARAMETERS} *.*

cd ${NAS4FREE_SVNDIR}/www
xgettext ${PARAMETERS} --join-existing *.*

cd ${NAS4FREE_SVNDIR}/www/quixplorer/_include
xgettext ${PARAMETERS} --join-existing *.*

cd ${NAS4FREE_SVNDIR}/etc/inc
xgettext ${PARAMETERS} --join-existing *.*

DATE="$(date "+%Y-%m-%d %H:%M")+0000"
echo "msgid \"\"
msgstr \"\"
\"Project-Id-Version: ${NAS4FREE_PRODUCTNAME}\\n\"
\"POT-Creation-Date: ${DATE}\\n\"
\"PO-Revision-Date: \\n\"
\"Last-Translator: \\n\"
\"Language-Team: \\n\"
\"MIME-Version: 1.0\\n\"
\"Content-Type: text/plain; charset=UTF-8\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"
" >${OUTPUTDIR}/${OUTPUT}.tmp

cat ${OUTPUTDIR}/${OUTPUT} >>${OUTPUTDIR}/${OUTPUT}.tmp
mv -f ${OUTPUTDIR}/${OUTPUT}.tmp ${OUTPUTDIR}/${OUTPUT}

echo -e "\033\\033[32m";
echo -e "==> Translation file created into:""\033[37m ${OUTPUTDIR}/${OUTPUT}\033[37m";
