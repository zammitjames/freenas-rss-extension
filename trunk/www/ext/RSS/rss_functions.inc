<?php
require_once("util.inc");
require_once("config.inc");

$TRANSMISSION = array(
    'user' => 'admin',
    'pass' => $config['bittorrent']['password'],
    'downloaddir' => $config['bittorrent']['downloaddir'],
    'port' => $config['bittorrent']['port']
);

function rss_log($message) {
    global $config;
    if (isset($config['rss']['debug']))
        write_log("RSS: $message");
}

function add_torrent($torrent, $folder = '') {
    global $config, $TRANSMISSION;
    
    $file = '/tmp/' . md5($torrent) . '.torrent';
    
    if (copy($torrent, $file) !== TRUE) {
        if (isset($config['rss']['debug'])) write_log('RSS: Downloading failed.');
        return -1;
    }
    
    $start = "transmission-remote {$TRANSMISSION['port']} --auth={$TRANSMISSION['user']}:{$TRANSMISSION['pass']}";
    $cmd = $start;
    
    if (!empty($folder) && is_dir($folder))
        $cmd .= ' --download-dir=' . escapeshellarg($folder);
    
    $cmd .= " -a $file 2>&1";
    mwexec2($cmd, $output, $retVal);
    
    preg_match('/ responded: "([^"]+)"/i', $output[!empty($folder) && is_dir($folder) ? 1 : 0], $match);
    if ($match[1] != "success") $retVal = "-1 {$match[1]}";

    // just to be safe, set everything back to the default download directory
    $cmd = $start . ' --download-dir=' . escapeshellarg($TRANSMISSION['downloaddir']). ' 2>&1';
    exec($cmd);
    unlink($file);
    if ($retVal != 0) {
        if (isset($config['rss']['debug'])) write_log("RSS: Unable to add to transmission: $retVal");
        return $retVal;
    }
}

function get_by_uuid($array, $uuid, $field = null) {
  $i = 0;
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
  return strtotime($a['pubdate']) < strtotime($b['pubdate']);
}