#!/usr/local/bin/php
<?php
require('ext/RSS/rss_functions.inc');
require_once("XML/Unserializer.php");

function get_download($item) {
    if (isset($item['enclosure']) && isset($item['enclosure']['attributes'])  && $item['enclosure']['attributes']['type'] == 'application/x-bittorrent')
        return $item['enclosure']['attributes']['url'];
    return $item['link'];
}

// We don't have any feeds, just exit.  Filters are not required.
if (!isset($config['rss']) || !isset($config['rss']['feeds']) || !isset($config['rss']['feeds']['rule'])) {
    if (isset($config['rss']['debug'])) write_log("RSS: No valid configuration");
    exit();
}

$a_feeds = &$config['rss']['feeds']['rule'];
if (isset($config['rss']) && isset($config['rss']['filters']))
    $a_filters = &$config['rss']['filters']['rule'];
else
    $a_filters = array();

$options = array(
    'parseAttributes' => TRUE,
    'attributesArray' => 'attributes'
);
$Unserializer = &new XML_Unserializer($options);

foreach ($a_feeds as &$feed) {
    if(!isset($feed['enabled'])) continue;

    if (isset($config['rss']['debug'])) write_log("RSS: Getting feed {$feed['name']}");
    
    $status = $Unserializer->unserialize($feed['_url'], true);
    if (PEAR::isError($status)) die($status->getMessage());
    
    $data = $Unserializer->getUnserializedData();
    if (!is_array($feed['history'])) $feed['history'] = array('rule' => array());

    foreach ($data['channel']['item'] as $item) {
        foreach ($feed['history']['rule'] as $entry) {
            if ($item['guid']['_content'] == $entry['guid']) {
                continue 2;
            }
        }
        
        if ($feed['subscribe']) {
            if (add_torrent(get_download($item), $feed['directory'])) $item['downloaded'] = true;
        } else {
            foreach ($a_filters as $filter) {
                if (!isset($filter['enabled'])) continue;
                if ($filter['feed'] != -1 && $filter['feed'] != $feed['uuid']) continue;
                
                if (preg_match('/'.$filter['filter'].'/i', $item['title']))
                {
                    if (isset($config['rss']['debug'])) write_log("RSS: {$item['title']} matches {$filter['filter']}");
                    if (add_torrent(get_download($item), !empty($filter['directory']) ? $filter['directory'] : $feed['directory']) == 0) {
                        $item['filter'] = $filter['uuid'];
                        $item['downloaded'] = true;
                    }
                    else if (isset($config['rss']['debug'])) write_log("RSS: Unable to add {$item['title']} from " . get_download($item));
                }
            }
        }
        
        $feed['history']['rule'][] = array(
            'title' => $item['title'],
            'guid' => $item['guid']['_content'],
            'description' => $item['description'],
            'pubDate' => $item['pubDate'],
            'link' => get_download($item),
            'downloaded' => (isset($item['downloaded']) ? true : false),
            'feed' => $feed['uuid'],
            'filter' => $item['uuid']
        );
    }   
}

if (isset($config['rss']['debug'])) write_log("RSS: Saving data");
write_config();
