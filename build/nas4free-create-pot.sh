#!/bin/sh
# Extract gettext strings from source.
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (C) 2012 NAS4Free Team <info@nas4free.org>.
# All rights reserved.
#

# Global variables
NAS4FREE_ROOTDIR="/usr/local/nas4free"
NAS4FREE_SVNDIR="$NAS4FREE_ROOTDIR/svn"
NAS4FREE_PRODUCTNAME=$(cat ${NAS4FREE_SVNDIR}/etc/prd.name)

OUTPUT="$(echo ${NAS4FREE_PRODUCTNAME} | tr '[:upper:]' '[:lower:]').pot"
OUTPUTDIR="${NAS4FREE_SVNDIR}/locale"
PARAMETERS="--output-dir=${OUTPUTDIR} --output=${OUTPUT} \
--force-po --no-location --no-wrap --sort-output --omit-header"

cd ${NAS4FREE_SVNDIR}/www
xgettext ${PARAMETERS} *.*

cd ${NAS4FREE_SVNDIR}/www
xgettext ${PARAMETERS} --join-existing *.*

cd ${NAS4FREE_SVNDIR}/www/quixplorer/.include
xgettext ${PARAMETERS} --join-existing *.*

cd ${NAS4FREE_SVNDIR}/etc/inc
xgettext ${PARAMETERS} --join-existing *.*

#cd ${NAS4FREE_SVNDIR}/build/checkversion
#xgettext ${PARAMETERS} --join-existing *.*

DATE="$(date "+%Y-%m-%d %H:%M")+0000"
echo "msgid \"\"
msgstr \"\"
\"Project-Id-Version: ${NAS4FREE_PRODUCTNAME}\\n\"
\"POT-Creation-Date: ${DATE}\\n\"
\"PO-Revision-Date: \\n\"
\"Last-Translator: \\n\"
\"Language-Team: \\n\"
\"MIME-Version: 1.0\\n\"
\"Content-Type: text/plain; charset=iso-8859-1\\n\"
\"Content-Transfer-Encoding: 8bit\\n\"
" >${OUTPUTDIR}/${OUTPUT}.tmp

cat ${OUTPUTDIR}/${OUTPUT} >>${OUTPUTDIR}/${OUTPUT}.tmp
mv -f ${OUTPUTDIR}/${OUTPUT}.tmp ${OUTPUTDIR}/${OUTPUT}

echo "==> Translation file created: ${OUTPUTDIR}/${OUTPUT}"
