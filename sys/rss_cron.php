#!/usr/local/bin/php
<?php
require_once("config.inc");
require_once("XML/Unserializer.php");

function addTorrent($torrent, $folder = '') {
    global $config;
    
    $file = md5($torrent);
    exec("fetch -o /tmp/{$file}.torrent \"$torrent\"", $output, $retVal);
    
    $cmd = "transmission-remote --auth=admin:{$config['bittorrent']['password']}";
    
    if (!empty($folder) && is_dir($folder))
        $cmd .= " --download-dir=$folder";
    
    // just to be safe, set everything back to the default download directory
    $cmd .= " -a /tmp/{$file}.torrent --download-dir={$config['bittorrent']['downloaddir']} 2>&1";
    
    mwexec2($cmd, $output, $retVal);
}

// We don't have any feeds, just exit.  Filters are not required.
if (!isset($config['rss']) || !isset($config['rss']['feeds']) || !isset($config['rss']['feeds']['rule'])) exit();

$a_feeds = &$config['rss']['feeds']['rule'];
if (isset($config['rss']) && isset($config['rss']['filters']))
    $a_filters = &$config['rss']['filters']['rule'];
else
    $a_filters = array();

$Unserializer = &new XML_Unserializer();

foreach ($a_feeds as &$feed) {
    if(!isset($feed['enabled'])) continue;
    
    $status = $Unserializer->unserialize($feed['_url'], true);
    if (PEAR::isError($status)) die($status->getMessage());
    
    $data = $Unserializer->getUnserializedData();
    if (!is_array($feed['history'])) $feed['history'] = array('rule' => array());
    
    foreach ($data['channel']['item'] as $item) {
        foreach ($feed['history']['rule'] as $entry) {
            if ($item['guid'] == $entry['guid'])
                continue 2;
        }
        
        $item['feed'] = $feed['uuid'];
        if ($feed['subscribe']) {
            addTorrent($item['link'], $feed['directory']);
            $item['downloaded'] = true;
        } else {
            foreach ($a_filters as $filter) {
                if (!isset($filter['enabled'])) continue;
                if ($filter['feed'] != -1 && $filter['feed'] != $feed['uuid']) continue;
                
                if (preg_match('/'.$filter['filter'].'/i', $item['title']))
                {
                    addTorrent($item['link'], !empty($filter['directory']) ? $filter['directory'] : $feed['directory']);
                    $item['filter'] = $filter['uuid'];
                    $item['downloaded'] = true;
                }
            }
        }
        
        $feed['history']['rule'][] = $item;
    }   
}

write_config();