#!/bin/bash
#
# delete recordings older than $RECAGE days
# DO NOT USE
# this doesn't work for multitenant and is redundant in the new setup (6.2.38 onwards)
exit 0
RECAGE=`/usr/bin/sqlite3 /opt/sark/db/sark.db "select RECAGE from globals;"`

find /opt/sark/media/recordings  -mtime +$RECAGE -type d -exec rm -rf {} +

