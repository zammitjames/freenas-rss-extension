# Introduction #

I love FreeNAS, but there are things that drive me nuts about it.  This is my documentation of what I do to make my life with FreeNAS more pain free.  Also included are things that I attempted that didn't work, just so others do not have to suffer the same fate ;)

# Details #

## CIFS/SMB + Transmission = File Permission Hell ##
Under System|Advanced|rc.conf and the following value to make transmission, APF, SMB/CIFS all use the same "guest" user:
|Variable|Value|Comment|
|:-------|:----|:------|
|transmission\_user|ftp  |Set the transmission user to FTP|

**Note** If you used my previous tweak to have CIFS/SMB use the transmission user, you'll need to `chown -R ftp .` your shared directories to make sure everything is playing nice.

## settings.json blocklist\_enabled ##

By default (for whatever reason) FreeNAS disables the use of blocklists in transmission.  Simply editing the settings.json won't fix it, since it's the rc.d script telling transmission not to.  To solve this we need to add a new rc.conf variable in System|Advanced|rc.conf:
|Variable|Value|Comment|
|:-------|:----|:------|
|transmission\_blocklist|YES  |Use blocklists|

### Updating the Transmission Blocklist ###
Add the following to System|Advanced|rc.conf
|Variable|Value|Comment|
|:-------|:----|:------|
|transmission\_blocklist\_url|http://www.bluetack.co.uk/config/nipfilter.dat.gz|Use the normal ipfilter instead of just Level 1|

Add the following to System|Advanced|Cron
|Command|Who|Description|
|:------|:--|:----------|
|/etc/rc.d/transmission updateblocklist|root|Update transmission blocklist|

Under Schedule Time:
  * Change the Minutes radio button from "All" to "Selected".  Select "0".
  * Change the Hours radio button from "All" to "Selected". Select "0".

This will update your block list every day at midnight.  You can fine tune this to meet your needs.  I update once a week by selecting a specific day.

## watcher.sh ##
```
#! /bin/bash

if [ -z "$(pgrep transmission-daemon)" ]
then
        rm /home/transmission/*.core
        /etc/rc.d/transmission start
fi
```

Transmission crashes on me a lot so I use this to clean up the core file and restart transmission.  I have it set to run every 5 minutes in a cron job.

**Note** I am running 0.70 at this time, but haven't checked to see if transmission is still crashing