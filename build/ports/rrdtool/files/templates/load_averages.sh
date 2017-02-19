/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-v Load" \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"-X 0" \
"-l 0" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:CPU=$STORAGE_PATH/rrd/load_averages.rrd:CPU:AVERAGE" \
"DEF:CPU5=$STORAGE_PATH/rrd/load_averages.rrd:CPU5:AVERAGE" \
"DEF:CPU15=$STORAGE_PATH/rrd/load_averages.rrd:CPU15:AVERAGE" \
"LINE1:CPU#FF0000:Load  1 minute " \
"VDEF:minC=CPU,MINIMUM" \
"VDEF:maxC=CPU,MAXIMUM" \
"VDEF:avgC=CPU,AVERAGE" \
"VDEF:lastC=CPU,LAST" \
"GPRINT:minC:Min\\: %5.2lf" \
"GPRINT:maxC:Max\\: %5.2lf" \
"GPRINT:avgC:Avg\\: %5.2lf" \
"GPRINT:lastC:Last\\: %5.2lf\\n" \
"LINE1:CPU5#00CF00D0:Load  5 minutes" \
"VDEF:minC5=CPU5,MINIMUM" \
"VDEF:maxC5=CPU5,MAXIMUM" \
"VDEF:avgC5=CPU5,AVERAGE" \
"VDEF:lastC5=CPU5,LAST" \
"GPRINT:minC5:Min\\: %5.2lf" \
"GPRINT:maxC5:Max\\: %5.2lf" \
"GPRINT:avgC5:Avg\\: %5.2lf" \
"GPRINT:lastC5:Last\\: %5.2lf\\n" \
"LINE1:CPU15#0000FF:Load 15 minutes" \
"VDEF:minC15=CPU15,MINIMUM" \
"VDEF:maxC15=CPU15,MAXIMUM" \
"VDEF:avgC15=CPU15,AVERAGE" \
"VDEF:lastC15=CPU15,LAST" \
"GPRINT:minC15:Min\\: %5.2lf" \
"GPRINT:maxC15:Max\\: %5.2lf" \
"GPRINT:avgC15:Avg\\: %5.2lf" \
"GPRINT:lastC15:Last\\: %5.2lf\\n" \
"TEXTALIGN:right" "COMMENT: Last update\: $LAST_UPDATE"
