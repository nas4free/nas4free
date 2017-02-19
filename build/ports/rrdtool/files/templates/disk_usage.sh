CLEAN_NAME=`echo -e ${DISK_NAME} | awk '{gsub("/","-"); print}'`
/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-mnt_${CLEAN_NAME}_${GRAPH_NAME}.png \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-v Bytes" \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"-l 0" \
"DEF:Used=$STORAGE_PATH/rrd/mnt_${CLEAN_NAME}.rrd:Used:AVERAGE" \
"DEF:Free=$STORAGE_PATH/rrd/mnt_${CLEAN_NAME}.rrd:Free:AVERAGE" \
"AREA:Used#FFCC559F:Used" \
"GPRINT:Used:MIN:Min\\:%7.2lf %s" \
"GPRINT:Used:MAX:Max\\:%7.2lf %s" \
"GPRINT:Used:AVERAGE:Avg\\:%7.2lf %s" \
"GPRINT:Used:LAST:Last\\:%7.2lf %s" \
"COMMENT:\n" \
"STACK:Free#00CF007F:Free" \
"GPRINT:Free:MIN:Min\\:%7.2lf %s" \
"GPRINT:Free:MAX:Max\\:%7.2lf %s" \
"GPRINT:Free:AVERAGE:Avg\\:%7.2lf %s" \
"GPRINT:Free:LAST:Last\\:%7.2lf %s" \
"COMMENT:\n" \
"TEXTALIGN:right" "COMMENT:Last update\: $LAST_UPDATE"
