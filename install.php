#! /usr/local/bin/php -q
<?php
ini_set('html_errors', false);
ini_set('max_execution_time', 0);

require_once('config.inc');
require_once('updatenotify.inc');

$WWWPATH = '/usr/local/www/';
$INSTALLPATH = getcwd() . '/';

echo 'Extracting RSS.tgz', "\n";
exec('tar -xzf RSS.tgz');

if (!is_dir($WWWPATH.'ext')) mkdir($WWWPATH.'ext');

foreach (glob('www/*.php') as $filename) {
    echo 'Linking ', $filename, ' as ', $WWWPATH, 'extension_', basename($filename), "\n";
    if (!file_exists($WWWPATH . 'extension_' . basename($filename))) symlink($INSTALLPATH . $filename, $WWWPATH . 'extension_' . basename($filename));
}

echo 'Linking ext directory', "\n";
if (!is_dir($WWWPATH.'ext/RSS')) symlink($INSTALLPATH . 'www/ext/RSS', $WWWPATH.'ext/RSS');

$min = date('i');
if ($min > 14) $min %= 15;

if (!is_array($config['cron']['job']))
    $config['cron']['job'] = array();

echo 'Looking for cron job...';
$found = false;
foreach ($config['cron']['job'] as $job) {
    if (preg_match('/(?:^|\s)RSS(?:$|\s)/i', $job['desc'])) {
        echo 'found', "\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo 'creating', "\n";
    $cronjob = array();
    $cronjob['enable'] = true;
    $cronjob['uuid'] = uuid();
    $cronjob['desc'] = 'RSS Cron Job';
    $cronjob['minute'] = array( $min, $min + 15, $min + 30, $min + 45);
    $cronjob['hour'] = array();
    $cronjob['day'] = array();
    $cronjob['month'] = array();
    $cronjob['weekday'] = array();
    $cronjob['all_mins'] = '0';
    $cronjob['all_hours'] = '1';
    $cronjob['all_days'] = '1';
    $cronjob['all_months'] = '1';
    $cronjob['all_weekdays'] = '1';
    $cronjob['who'] = 'root';
    $cronjob['command'] = '/usr/local/bin/php ' . $INSTALLPATH . 'sys/rss_cron.php';

    $config['cron']['job'][] = $cronjob;
}

if (!is_array($config['rss'])) {
    echo 'Initializing configuration', "\n";
    $config['rss'] = array(
        'feeds' => array('rule' => array()),
        'filters' => array('rule' => array())
    );
}

echo 'Saving configuration changes', "\n";
write_config();

if (!file_exists($g['varrun_path'] . '/sysreboot.reqd') && $found == false) {
    echo 'Updating cron service', "\n";
    $retval |= updatenotify_process('cronjob', 'cronjob_process_updatenotification');
    config_lock();
    $retval |= rc_update_service('cron');
    config_unlock();
}
else if ($found == false)
    echo 'FreeNAS is requesting a reboot before starting new cron jobs', "\n";

echo "

Finished installing the RSS extension for transmission.  Please bear
in mind that this extension pulls most of transmission's settings
from FreeNAS' XML configuration file.  If you have modified the
transmission installation, you may need to modify
{$INSTALLPATH}www/ext/rss_functions.inc
to reflect your changes.  Look at the \$TRANSMISSION variable at the
top of the file.";