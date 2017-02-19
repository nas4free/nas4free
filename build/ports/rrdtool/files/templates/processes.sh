/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Processes" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--alt-autoscale-max" \
"DEF:total=$STORAGE_PATH/rrd/processes.rrd:total:AVERAGE" \
"DEF:running=$STORAGE_PATH/rrd/processes.rrd:running:AVERAGE" \
"DEF:sleeping=$STORAGE_PATH/rrd/processes.rrd:sleeping:AVERAGE" \
"DEF:waiting=$STORAGE_PATH/rrd/processes.rrd:waiting:AVERAGE" \
"DEF:starting=$STORAGE_PATH/rrd/processes.rrd:starting:AVERAGE" \
"DEF:stopped=$STORAGE_PATH/rrd/processes.rrd:stopped:AVERAGE" \
"DEF:zombie=$STORAGE_PATH/rrd/processes.rrd:zombie:AVERAGE" \
"AREA:stopped#0000007F:Stopped " \
"GPRINT:stopped:MIN:Min\\:%6.1lf" \
"GPRINT:stopped:MAX:Max\\:%6.1lf" \
"GPRINT:stopped:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:stopped:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"STACK:starting#FF62F37F:Starting" \
"GPRINT:starting:MIN:Min\\:%6.1lf" \
"GPRINT:starting:MAX:Max\\:%6.1lf" \
"GPRINT:starting:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:starting:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"STACK:waiting#3AD9E77F:Waiting " \
"GPRINT:waiting:MIN:Min\\:%6.1lf" \
"GPRINT:waiting:MAX:Max\\:%6.1lf" \
"GPRINT:waiting:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:waiting:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"STACK:zombie#DF00007F:Zombie  " \
"GPRINT:zombie:MIN:Min\\:%6.1lf" \
"GPRINT:zombie:MAX:Max\\:%6.1lf" \
"GPRINT:zombie:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:zombie:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"STACK:sleeping#FFC96C7F:Sleeping" \
"GPRINT:sleeping:MIN:Min\\:%6.1lf" \
"GPRINT:sleeping:MAX:Max\\:%6.1lf" \
"GPRINT:sleeping:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:sleeping:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"STACK:running#10BB0D7F:Running " \
"GPRINT:running:MIN:Min\\:%6.1lf" \
"GPRINT:running:MAX:Max\\:%6.1lf" \
"GPRINT:running:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:running:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE:stopped#00000000" \
"STACK:starting#FF62F3" \
"STACK:waiting#3AD9E7" \
"STACK:zombie#DF0000" \
"STACK:sleeping#FFC96C" \
"STACK:running#10BB0D" \
"GPRINT:total:LAST:Processes total\\:%6.1lf" \
"COMMENT:\tLast update\: $LAST_UPDATE"
