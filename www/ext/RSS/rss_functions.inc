<?php
require_once('util.inc');
require_once('config.inc');
require_once('history.class.php');
require_once('torrent.class.php');

define('VERBOSE_OFF', 0);
define('VERBOSE_ERROR', 1);
define('VERBOSE_SUCCESS', 2);
define('VERBOSE_EXTRA', 3);

define('SUCCESS', 0);
define('ERROR_NODATA', -1);
define('ERROR_TRANSMISSION', -2);
define('ERROR_NOWRITE', -3);

$TRANSMISSION = array(
    'user' => (isset($config['bittorrent']['username']) ? $config['bittorrent']['username'] : 'admin'),
    'pass' => $config['bittorrent']['password'],
    'downloaddir' => $config['bittorrent']['downloaddir'],
    'port' => $config['bittorrent']['port']
);

function rss_log($message, $err_level) {
    global $config;
    if (isset($config['rss']['debug']) && $config['rss']['debug'] >= $err_level)
    {
        if (isset($config['rss']['debuglog'])) {
            $fp = fopen("{$config['rss']['path']}/rss.log", 'a');
            if (!$fp) return;
            $log = sprintf("[%s] %s\r\n", date('d M Y H:i:s'), $message);
            fwrite($fp, $log);
            fclose($fp);
        }
        else
            write_log("RSS: $message");
    }
}

function rss_download($url, $cookie) {
  $cntx = stream_context_create(array(
    'http' => array(
      'header' => "Cookie: $cookie\r\n" .
        "User-Agent: FreeNAS RSS Extension/1.0 (FreeNAS; FreeBSD)\r\n",
    )
  ));

  $data = file_get_contents($url, false, $cntx);
  $cntx = null;
  return $data;
}

function transmission_command($command) {
    global $config, $TRANSMISSION;
    $cmd = "transmission-remote {$TRANSMISSION['port']} --auth={$TRANSMISSION['user']}:{$TRANSMISSION['pass']} $command 2>&1";
    mwexec2($cmd, $output, $retVal);
        
    return array($retVal, $output);
}

function add_torrent($torrent, $folder, $cookie='', $start_paused=false) {
    global $config, $TRANSMISSION;
    
    $file = '/tmp/' . md5($torrent) . '.torrent';
    $torrent_data = rss_download($torrent, $cookie);
    
    if ($torrent_data === false) {
        rss_log("Unable to download $torrent", VERBOSE_ERROR);
        return ERROR_NODATA;
    } else if (file_put_contents($file, $torrent_data) === false) {
        rss_log("Uable to write to $file", VERBOSE_ERROR);
        return ERROR_NOWRITE;
    }
    else {
        transmission_command('--download-dir=' . escapeshellarg($folder));
        $ret = transmission_command("-a $file");
        if ($ret[0] == 0 && $start_paused)
        {
            $t = new Torrent($file);
            $t = transmission_command("-t {$t->hash_info()} --stop");
            if ($t[0] == 1) rss_log("Unable to pause $torrent");
        }
        
        // just to be safe, set everything back to the default download directory
        transmission_command('--download-dir=' . escapeshellarg($TRANSMISSION['downloaddir']));
        
        unlink($file);
        if($ret[0] == 1) {
            if (strpos($ret[1][0], 'duplicate torrent') !== FALSE) {
                rss_log("Download $torrent is already added", VERBOSE_SUCCESS);
                return SUCCESS;
            }
            rss_log("Transmission error on $torrent: {$ret[1][0]}", VERBOSE_ERROR);
            return ERROR_TRANSMISSION;
        }
    }
    
    rss_log("Downloaded $torrent", VERBOSE_SUCCESS);
    return SUCCESS;
}

function get_by_uuid($array, $uuid, $field = null) {
  $i = 0;
  if (!is_array($array)) return null;
	
  foreach ($array as $item) {
    if ($item['uuid'] == $uuid) {
      if ($field == null) return $item;
      else if ($field == 'id') return $i;
      else if (isset($field) && isset($item[$field])) return $item[$field];
      break;
    }
    $i++;
  }
  return null;
}

function usort_by_pubdate($a, $b) {
  return strtotime($a['pubDate']) < strtotime($b['pubDate']);
}