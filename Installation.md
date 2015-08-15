# Introduction #

Installation of FreeNAS RSS Extension for Transmission.  Installation is really straight forward, and everything from moving the files to setting up the cronjob is done for you.  Just follow the instructions below and you'll be good to go!


# Details #

## Clean Install ##
  1. Download [install.php](http://code.google.com/p/freenas-rss-extension/downloads/list?can=3&q=install*.php) and the latest [RSS-somedate.tgz](http://code.google.com/p/freenas-rss-extension/downloads/list?can=3&q=RSS*tgz)
  1. Rename the RSS-somedate.tgz to RSS.tgz
  1. Create a share ("Mount Point") following the FreeNAS instructions, make it available through the protocol of your choice (CIFS/SMB for Windows).
  1. Open the above share, create a sub folder named "RSS" and copy both files in there.
  1. Go to the FreeNAS administration web page.
  1. Open **Advanced|Command**
  1. Execute `chmod 777 /mnt/YourShareHere/RSS/install.php`
  1. Execute `/mnt/YourShareHere/RSS/install.php`.  There should be some output ;)
  1. Refresh your browser to see the new Extension menu item if it is not already there
  1. Select RSS from the Extension menu
  1. Customize your RSS feeds and filters

## Update the RSS.tgz ##
  1. Download the latest [RSS-somedate.tgz](http://code.google.com/p/freenas-rss-extension/downloads/list?can=3&q=RSS*tgz)
  1. Rename the RSS-somedate.tgz to RSS.tgz
  1. Copy the new RSS.tgz to FreeNAS replacing the previous RSS.tgz
  1. Go to the FreeNAS administration web page.
  1. Open **Advanced|Command**
  1. Execute `/mnt/YourShareHere/RSS/install.php`.  There should be some output ;)