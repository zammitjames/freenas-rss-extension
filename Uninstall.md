# Introduction #

There currently is no uninstall file to automate the process, but uninstallation is not overly difficult.


# Details #
  1. `rm /usr/local/www/extension_rss_*`
  1. `rm -rf /usr/local/www/ext/RSS/`
  1. `rm -rf YOUR_INSTALL_DIR`
  1. Delete the cron job in **System|Advanced|cron**
  1. In **Advanced|Execute** enter the following command in the PHP command textbox:
```
unset($config['rss']);
write_config();
```