/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-cpu_temp_${GRAPH_NAME}.png 2>> /tmp/rrdgraphs-error.log \
--start $START_TIME \
--end -0h \
--title "$TITLE_STRING" \
--vertical-label "Temperature [°C]" \
$LEFT_AXIS_FORMAT "-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
--slope-mode $BACKGROUND $EXTENDED_OPTIONS \
"DEF:cpu=$STORAGE_PATH/rrd/cpu_temp.rrd:core0:AVERAGE" \
"LINE1:cpu#00CF00:" \
"VDEF:maxC=cpu,MAXIMUM" \
"VDEF:minC=cpu,MINIMUM" \
"VDEF:avgC=cpu,AVERAGE" \
"VDEF:lastC=cpu,LAST" \
"GPRINT:minC:Min\\: %2.1lf" \
"GPRINT:maxC:Max\\: %2.1lf" \
"GPRINT:avgC:Avg\\: %2.1lf" \
"GPRINT:lastC:Last\\: %2.1lf \t\t" \
"COMMENT: Last update\: $LAST_UPDATE"
