# round-trip min/avg/max/stddev 
/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Round-trip time [ms]" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:min=$STORAGE_PATH/rrd/${GRAPH}.rrd:min:AVERAGE" \
"DEF:avg=$STORAGE_PATH/rrd/${GRAPH}.rrd:avg:AVERAGE" \
"DEF:max=$STORAGE_PATH/rrd/${GRAPH}.rrd:max:AVERAGE" \
"DEF:stddev=$STORAGE_PATH/rrd/${GRAPH}.rrd:stddev:AVERAGE" \
"CDEF:avgmin=avg,min,-" \
"CDEF:maxavg=max,avg,-" \
"LINE1:min#0000FF:Minimum" \
"GPRINT:min:MIN:Min\\:%6.1lf" \
"GPRINT:min:MAX:Max\\:%6.1lf" \
"GPRINT:min:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:min:LAST:Last\\:%6.1lf" \
"COMMENT:\tSource IP\: $LATENCY_INTERFACE_IP" \
"COMMENT:\n" \
"AREA:avgmin#00CF0050::STACK" \
"AREA:maxavg#00CF0050::STACK" \
"LINE1:avg#009900:Average" \
"GPRINT:avg:MIN:Min\\:%6.1lf" \
"GPRINT:avg:MAX:Max\\:%6.1lf" \
"GPRINT:avg:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:avg:LAST:Last\\:%6.1lf" \
"COMMENT:\tPing count\: $LATENCY_COUNT" \
"COMMENT:\n" \
"LINE1:max#FF0000:Maximum" \
"GPRINT:max:MIN:Min\\:%6.1lf" \
"GPRINT:max:MAX:Max\\:%6.1lf" \
"GPRINT:max:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:max:LAST:Last\\:%6.1lf" \
"COMMENT:\tAuxiliary parameters\:" \
"COMMENT:\n" \
"LINE1:stddev#EC00EC:Stddev " \
"GPRINT:stddev:MIN:Min\\:%6.1lf" \
"GPRINT:stddev:MAX:Max\\:%6.1lf" \
"GPRINT:stddev:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:stddev:LAST:Last\\:%6.1lf" \
"COMMENT:\t$LATENCY_PARAMETERS" \
"COMMENT:\n" \
"TEXTALIGN:right" "COMMENT:Last update\: $LAST_UPDATE"
