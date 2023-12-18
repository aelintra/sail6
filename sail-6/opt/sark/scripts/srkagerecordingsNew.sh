#!/bin/bash
#
# delete recordings older than $RECAGE days
#
# this doesn't work for multitenant
exit 0
RECAGE=`/usr/bin/sqlite3 /opt/sark/db/sark.db "select RECAGE from globals;"`
RECAGEARRAY = `sqlite3 /opt/sark/db/sark.db "select pkey,recmaxage,recmaxsize from cluster;"`

for row_str in $RECAGEARRAY; do
    IFS='|' read -r -a COLS <<< "$row_str"
    echo "${COLS[1]}"
    echo "${COLS[]}"
done

find /opt/sark/media/recordings  -mtime +$RECAGE -type d -exec rm -rf {} +


