/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-cpu_freq_${GRAPH_NAME}.png \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-v Frequency [MHz]" \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"-X 0" \
"--slope-mode" \
"--alt-autoscale" \
"DEF:cpu=$STORAGE_PATH/rrd/cpu_freq.rrd:core0:AVERAGE" \
"LINE1:cpu#00CF00:" \
"VDEF:minC=cpu,MINIMUM" \
"VDEF:maxC=cpu,MAXIMUM" \
"VDEF:avgC=cpu,AVERAGE" \
"VDEF:lastC=cpu,LAST" \
"GPRINT:minC:Min\: %0.0lf" \
"GPRINT:maxC:Max\: %0.0lf" \
"GPRINT:avgC:Avg\: %0.0lf" \
"GPRINT:lastC:Last\: %0.0lf" \
"COMMENT:\tLast update\: $LAST_UPDATE"
