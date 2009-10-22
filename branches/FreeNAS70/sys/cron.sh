#! /bin/sh
SCRIPT_NAME=rss_cron.php
# for /bin/bash in case /bin/sh ever disappears
#SCRIPT_FILENAME="${0/cron.sh/$SCRIPT_NAME}"
SCRIPT_FILENAME="${0%/*}/$SCRIPT_NAME"
/usr/local/bin/php "$SCRIPT_FILENAME"