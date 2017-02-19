/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${INTERFACE0}_${GRAPH_NAME}.png \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
$SCALING \
$LOWER_LIMIT \
"-v ${BIT_STR}/sec" \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:in=$STORAGE_PATH/rrd/${INTERFACE0}.rrd:in:AVERAGE" \
"DEF:out=$STORAGE_PATH/rrd/${INTERFACE0}.rrd:out:AVERAGE" \
"CDEF:in_bits=in,${BIT_VAL},*" \
"CDEF:out_bits=out,${BIT_VAL},${YAXIS},*,*" \
"AREA:in_bits#00CF0050:Incoming" \
"LINE1:in_bits#009900" \
"GPRINT:in_bits:MAX:Max\\:%6.1lf %s" \
"GPRINT:in_bits:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:in_bits:LAST:Last\\:%6.1lf %s${BIT_STR}/sec\\n" \
"AREA:out_bits#002A9750:Outgoing" \
"LINE1:out_bits#002A97" \
"GPRINT:out_bits:${OUT_MAX}:Max\\:%6.1lf %s" \
"GPRINT:out_bits:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:out_bits:LAST:Last\\:%6.1lf %s${BIT_STR}/sec\\n" \
"TEXTALIGN:right" "COMMENT: Last update\: $LAST_UPDATE"
