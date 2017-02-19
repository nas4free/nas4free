/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Uptime [minutes]" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:uptime=$STORAGE_PATH/rrd/${GRAPH}.rrd:uptime:AVERAGE" \
"VDEF:mintime=uptime,MINIMUM" \
"VDEF:avgtime=uptime,AVERAGE" \
"VDEF:maxtime=uptime,MAXIMUM" \
"AREA:uptime#FFCC556F" \
"HRULE:mintime#0000FF:Minimun\::dashes" \
"GPRINT:uptime:MIN:%1.0lf min" \
"HRULE:maxtime#FF0000:Maximum\::dashes" \
"GPRINT:uptime:MAX:%1.0lf min" \
"HRULE:avgtime#10BB0D:Average\::dashes" \
"GPRINT:uptime:AVERAGE:%1.0lf min" \
"LINE1:uptime#FFCC55:Current\:" \
"GPRINT:uptime:LAST:%1.0lf min" \
"COMMENT:\n" \
"COMMENT:Actual uptime\: $UT" \
"COMMENT:Last update\: $LAST_UPDATE"
