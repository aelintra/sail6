#!/bin/sh
#
# delete recordings older than $RECAGE days
#
RECAGE=`cat /opt/sark/.recage`
if ! [[ "$RECAGE" =~ ^[0-9]+$ ]]
    then
        RECAGE = "60"
fi
find /opt/sark/www/origrecs/recordings/*  -mtime +$RECAGE -type d -exec rm -rf {} +
