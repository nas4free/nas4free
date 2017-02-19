# states:   active, inact, wired, cache, buf, free, swaptotal, swapused
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
"DEF:sused=$STORAGE_PATH/rrd/memory.rrd:used:AVERAGE" \
"CDEF:rfree=inact,free,+" \
"CDEF:rused=active,wired,cache,+,+" \
"CDEF:sfree=total,sused,-" \
"CDEF:swapused=total,sfree,-" \
"CDEF:rsfree=rfree,sfree,+" \
"CDEF:rsused=rused,sused,+" \
"AREA:sfree#10BB0DBF:Swap free" \
"GPRINT:sfree:MIN:Min\\:%6.1lf %s" \
"GPRINT:sfree:MAX:Max\\:%6.1lf %s" \
"GPRINT:sfree:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:sfree:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:rfree#4BCC49AF:RAM free " \
"GPRINT:rfree:MIN:Min\\:%6.1lf %s" \
"GPRINT:rfree:MAX:Max\\:%6.1lf %s" \
"GPRINT:rfree:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:rfree:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:swapused#FFFF00AF:Swap used" \
"GPRINT:swapused:MIN:Min\\:%6.1lf %s" \
"GPRINT:swapused:MAX:Max\\:%6.1lf %s" \
"GPRINT:swapused:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:swapused:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"STACK:rused#FFCC557F:RAM used " \
"GPRINT:rused:MIN:Min\\:%6.1lf %s" \
"GPRINT:rused:MAX:Max\\:%6.1lf %s" \
"GPRINT:rused:AVERAGE:Avg\\:%6.1lf %s" \
"GPRINT:rused:LAST:Last\\:%6.1lf %s" \
"COMMENT:\n" \
"LINE:sfree#10BB0D" \
"STACK:rfree#4BCC49" \
"STACK:sused#FFFF00" \
"GPRINT:rsfree:LAST:Total memory (RAM+Swap) free\\:%6.1lf %s" \
"COMMENT:\n" \
"GPRINT:rsused:LAST:Total memory (RAM+Swap) used\\:%6.1lf %s" \
"COMMENT:\tLast update\: $LAST_UPDATE"
