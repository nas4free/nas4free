#!/bin/sh
#
# Part of NAS4Free (http://www.nas4free.org).
# Copyright (c) 2012-2017 The NAS4Free Project <info@nas4free.org>.
# All rights reserved.
#
WORKING_DIR="/var/run/rrdgraphs"
TEMPLATE_DIR="/usr/local/share/rrdgraphs"

if [ ! -d ${WORKING_DIR}/rrd ]; then
    mkdir -p ${WORKING_DIR}/rrd
fi
STORAGE_PATH=`/usr/local/bin/xml sel -t -v "//rrdgraphs/storage_path" /conf/config.xml`
. ${STORAGE_PATH}/rrd_config
LAST_UPDATE=`date +"%d.%m.%Y %H\:%M"`
if [ ${BACKGROUND_WHITE} -eq 1 ]; then
    BACKGROUND='-c CANVAS#FFFFFF'
else
    BACKGROUND='-c CANVAS#000000'
fi

# function parameters (mandatory): $1 = template_file_name, $2 = graph_title_string
CREATE_GRAPHS()
{
    GRAPH=${1}
    GRAPH_NAME="daily";    START_TIME="-1day";    TITLE_STRING="${2} - By Day (5 Minute Average)"
    . ${TEMPLATE_DIR}/templates/${1}.sh
    GRAPH_NAME="weekly";    START_TIME="-1week";    TITLE_STRING="${2} - By Week (30 Minute Average)"
    . ${TEMPLATE_DIR}/templates/${1}.sh
    GRAPH_NAME="monthly";    START_TIME="-1month";    TITLE_STRING="${2} - By Month (2 Hour Average)"
    . ${TEMPLATE_DIR}/templates/${1}.sh
    GRAPH_NAME="yearly";    START_TIME="-1year";    TITLE_STRING="${2} - By Year (12 Hour Average)"
    . ${TEMPLATE_DIR}/templates/${1}.sh
}

if [ "$1" == "traffic" ] || ( [ "$1" == "" ] && [ "${RUN_LAN}" == "1" ] ); then
    if [ "${LOGARITHMIC}" -eq 1 ]; then
        SCALING='-o --units=si'; LOWER_LIMIT='-l 1000'
    else
        SCALING=''; LOWER_LIMIT='--alt-autoscale-max'
    fi
    if [ "${AXIS}" -eq 1 ]; then
        YAXIS='-1'; OUT_MAX="MIN"
    else
        YAXIS='1'; OUT_MAX="MAX"
    fi
    if [ "${BYTE_SWITCH}" -eq 1 ]; then
        BIT_STR="Bytes"; BIT_VAL=1
    else
        BIT_STR="Bits"; BIT_VAL=8
    fi
    x=0
    while [ -e "${STORAGE_PATH}/rrd/${INTERFACE0}.rrd" ]
    do
        CREATE_GRAPHS "network_traffic" "Traffic Interface ${INTERFACE0}"
        x=$((x+1))
        INTERFACE0=`/usr/local/bin/xml sel -t -v "//interfaces/opt${x}/if" /conf/config.xml`
    done
fi

if [ "$1" == "load" ] || ( [ "$1" == "" ] && [ "${RUN_AVG}" == "1" ] ); then
    CREATE_GRAPHS "load_averages"    "Load Averages"
fi
if [ "$1" == "temperature" ] || ( [ "$1" == "" ] && [ "${RUN_TMP}" == "1" ] ); then
    CREATE_GRAPHS "cpu_temperature"    "CPU Temperature"
fi
if [ "$1" == "frequency" ] || ( [ "$1" == "" ] && [ "${RUN_FRQ}" == "1" ] ); then
    CREATE_GRAPHS "cpu_frequency"    "CPU Frequency"
fi
if [ "$1" == "processes" ] || ( [ "$1" == "" ] && [ "${RUN_PRO}" == "1" ] ); then
    CREATE_GRAPHS "processes"    "System Processes"
fi
if [ "$1" == "cpu" ] || ( [ "$1" == "" ] && [ "${RUN_CPU}" == "1" ] ); then
    CREATE_GRAPHS "cpu"    "CPU Usage"
fi

if [ "$1" == "disk_usage" ] || ( [ "$1" == "" ] && [ "${RUN_DUS}" == "1" ] ); then
    if [ "$2" == "" ]; then
        DA=`df -k | awk '!/jail/ && /\/mnt\// {gsub("/mnt/",""); print $6}' | awk '!/\// {print}'`      # all mountpoints but not jail
        for DISK_NAME in ${DA}; do CREATE_GRAPHS "disk_usage" "Disk Space Usage: ${DISK_NAME}"; done
        DA=`zfs list -H -t filesystem -o name`                                                          # all ZFS datasets
        for DISK_NAME in ${DA}; do CREATE_GRAPHS "disk_usage" "Disk Space Usage: ${DISK_NAME}"; done
    else
        DISK_NAME=$2
        CREATE_GRAPHS "disk_usage" "Disk Space Usage: ${DISK_NAME}"
    fi
fi

if [ "$1" == "memory" ] || ( [ "$1" == "" ] && [ "${RUN_MEM}" == "1" ] ); then
    CREATE_GRAPHS "memory"          "Memory Usage"
    CREATE_GRAPHS "memory-detailed" "Memory Usage - detailed"
fi
if [ "$1" == "zfs_arc" ] || ( [ "$1" == "" ] && [ "${RUN_ARC}" == "1" ] ); then
    CREATE_GRAPHS "zfs_arc" "ZFS ARC Usage"
fi
if [ "$1" == "ups" ] || ( [ "$1" == "" ] && [ "${RUN_UPS}" == "1" ] ); then
    CREATE_GRAPHS "ups"     "UPS ${UPS_AT}"
fi
if [ "$1" == "latency" ] || ( [ "$1" == "" ] && [ "${RUN_LAT}" == "1" ] ); then
    CREATE_GRAPHS "latency" "Destination Host $LATENCY_HOST"
fi
if [ "$1" == "uptime" ] || ( [ "$1" == "" ] && [ "${RUN_UPT}" == "1" ] ); then
    TOP=`top -bSItu`
    UT=`echo -e "$TOP" | awk '/averages:/ {gsub("[,+:]", " "); print $10" day(s), "$11" hour(s), "$12" minute(s)"; exit}'` CREATE_GRAPHS "uptime" "System Uptime"
fi

# symlinks to png files
cd ${WORKING_DIR}/rrd
PNGS=`ls -1 rrd-*.png`
for NAME in ${PNGS}; do
    if [ ! -L /usr/local/www/images/rrd/${NAME} ]; then
        ln -s ${WORKING_DIR}/rrd/${NAME} /usr/local/www/images/rrd/${NAME}
    fi
done
if [ -f /tmp/rrdgraphs-error.log ]; then
    logger -f /tmp/rrdgraphs-error.log
    rm /tmp/rrdgraphs-error.log
    exit 1
fi
