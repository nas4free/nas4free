# states:   active, inact, wired, cache, Buf, free
/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v Bytes" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w" "600" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:active=$STORAGE_PATH/rrd/memory.rrd:active:AVERAGE" \
"DEF:inact=$STORAGE_PATH/rrd/memory.rrd:inact:AVERAGE" \
"DEF:wired=$STORAGE_PATH/rrd/memory.rrd:wired:AVERAGE" \
"DEF:cache=$STORAGE_PATH/rrd/memory.rrd:cache:AVERAGE" \
"DEF:buf=$STORAGE_PATH/rrd/memory.rrd:buf:AVERAGE" \
"DEF:free=$STORAGE_PATH/rrd/memory.rrd:free:AVERAGE" \
"DEF:total=$STORAGE_PATH/rrd/memory.rrd:total:AVERAGE" \
"DEF:used=$STORAGE_PATH/rrd/memory.rrd:used:AVERAGE" \
"CDEF:mtotal=active,inact,wired,cache,free,+,+,+,+" \
"VDEF:stotal=total,MAXIMUM" \
"AREA:wired#3AD9E77F:Wired     " \
"GPRINT:wired:MIN:Min\\:%6.1lf %s" \
"GPRINT:wired:MAX:Max\\:%6.1lf %s" \
"GPRINT:wired:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:wired:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:inact#EEFF33BF:Inactive  " \
"GPRINT:inact:MIN:Min\\:%6.1lf %s" \
"GPRINT:inact:MAX:Max\\:%6.1lf %s" \
"GPRINT:inact:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:inact:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:active#10BB0D7F:Active    " \
"GPRINT:active:MIN:Min\\:%6.1lf %s" \
"GPRINT:active:MAX:Max\\:%6.1lf %s" \
"GPRINT:active:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:active:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:free#0A7C087F:Free      " \
"GPRINT:free:MIN:Min\\:%6.1lf %s" \
"GPRINT:free:MAX:Max\\:%6.1lf %s" \
"GPRINT:free:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:free:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:cache#BF993F7F:Cache     " \
"GPRINT:cache:MIN:Min\\:%6.1lf %s" \
"GPRINT:cache:MAX:Max\\:%6.1lf %s" \
"GPRINT:cache:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:cache:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:buf#FFCC557F:Buffer    " \
"GPRINT:buf:MIN:Min\\:%6.1lf %s" \
"GPRINT:buf:MAX:Max\\:%6.1lf %s" \
"GPRINT:buf:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:buf:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"LINE1:total#0000FF7F:Swap total:dashes" \
"GPRINT:total:MIN:Min\\:%6.1lf %s" \
"GPRINT:total:MAX:Max\\:%6.1lf %s" \
"GPRINT:total:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:total:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"LINE1:used#0000FF:Swap used " \
"GPRINT:used:MIN:Min\\:%6.1lf %s" \
"GPRINT:used:MAX:Max\\:%6.1lf %s" \
"GPRINT:used:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:used:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"LINE:wired#3AD9E7" \
"STACK:inact#EEFF33" \
"STACK:active#10BB0D" \
"STACK:free#0A7C08" \
"STACK:cache#BF993F" \
"STACK:buf#FFCC55" \
"GPRINT:mtotal:LAST:RAM total\\:%6.1lf %s" \
"COMMENT:\tLast update\: $LAST_UPDATE"
