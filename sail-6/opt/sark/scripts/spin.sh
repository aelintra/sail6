#!/bin/bash
#
# Spin off a backup  
#
#  cp  -a /opt/sark/db/sark.db /opt/sark/bkup/sark.db.`date +%s`

sysroot='/usr/share/'

/usr/sbin/slapcat > /tmp/sark.local.ldif 
/usr/bin/zip -r /opt/sark/bkup/sarkbak.`date +%s`.zip /opt/sark/db/sark.db $sysroot/asterisk/sounds/usergreet* $sysroot/asterisk/moh-* /var/spool/asterisk/voicemail /etc/asterisk /etc/shorewall /tmp/sark.local.ldif  >/dev/null 2>&1
if [  "$(ls -A /opt/sark/bkup)" ]; then
   /bin/ls -t /opt/sark/bkup/* | /bin/sed -e '1,9d' | /usr/bin/xargs -d '\n' /bin/rm
fi
rm /tmp/sark.local.ldif
