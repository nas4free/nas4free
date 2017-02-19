# ARC: 712K Total, 146K MFU, 347K MRU, 16K Anon, 12K Header, 190K Other
/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Bytes" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"DEF:Total=$STORAGE_PATH/rrd/${GRAPH}.rrd:Total:AVERAGE" \
"DEF:MFU=$STORAGE_PATH/rrd/${GRAPH}.rrd:MFU:AVERAGE" \
"DEF:MRU=$STORAGE_PATH/rrd/${GRAPH}.rrd:MRU:AVERAGE" \
"DEF:Anon=$STORAGE_PATH/rrd/${GRAPH}.rrd:Anon:AVERAGE" \
"DEF:Header=$STORAGE_PATH/rrd/${GRAPH}.rrd:Header:AVERAGE" \
"DEF:Other=$STORAGE_PATH/rrd/${GRAPH}.rrd:Other:AVERAGE" \
"CDEF:cTotal=MFU,MRU,Anon,Header,Other,+,+,+,+" \
"LINE1:cTotal#0000CF:Total " \
"GPRINT:cTotal:MIN:Min\\:%6.1lf %s" \
"GPRINT:cTotal:MAX:Max\\:%6.1lf %s" \
"GPRINT:cTotal:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:cTotal:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"AREA:MFU#3AD9E7BF:MFU   " \
"GPRINT:MFU:MIN:Min\\:%6.1lf %s" \
"GPRINT:MFU:MAX:Max\\:%6.1lf %s" \
"GPRINT:MFU:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:MFU:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:MRU#EEFF337F:MRU   " \
"GPRINT:MRU:MIN:Min\\:%6.1lf %s" \
"GPRINT:MRU:MAX:Max\\:%6.1lf %s" \
"GPRINT:MRU:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:MRU:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:Anon#10BB0D7F:Anon  " \
"GPRINT:Anon:MIN:Min\\:%6.1lf %s" \
"GPRINT:Anon:MAX:Max\\:%6.1lf %s" \
"GPRINT:Anon:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:Anon:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:Header#0A7C08BF:Header" \
"GPRINT:Header:MIN:Min\\:%6.1lf %s" \
"GPRINT:Header:MAX:Max\\:%6.1lf %s" \
"GPRINT:Header:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:Header:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:Other#EC00EC7F:Other " \
"GPRINT:Other:MIN:Min\\:%6.1lf %s" \
"GPRINT:Other:MAX:Max\\:%6.1lf %s" \
"GPRINT:Other:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:Other:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"TEXTALIGN:right" "COMMENT:Last update\: $LAST_UPDATE"
