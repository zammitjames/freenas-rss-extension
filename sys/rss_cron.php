#!/usr/local/bin/php
<?php
require('ext/RSS/rss_functions.inc');
require_once("XML/Unserializer.php");

function get_download($item) {
    if (isset($item['enclosure']) && isset($item['enclosure']['attributes'])  && $item['enclosure']['attributes']['type'] == 'application/x-bittorrent')
        return $item['enclosure']['attributes']['url'];
    return $item['link'];
}

function get_guid($item) {
    if (is_array($item['guid']))
        return $item['guid']['_content'];
    return $item['guid'];
}

function add_item($feed, $item) {
    $feed['history']['rule'][] = array(
        'title' => $item['title'],
        'guid' => get_guid($item),
        'description' => $item['description'],
        'pubDate' => $item['pubDate'],
        'link' => get_download($item),
        'downloaded' => (isset($item['downloaded']) ? true : false),
        'filter' => (isset($item['filter']) ? $item['filter'] : false)
    );
}

// We don't have any feeds, just exit.  Filters are not required.
if (!isset($config['rss']) || !isset($config['rss']['feeds']) || !isset($config['rss']['feeds']['rule'])) {
    rss_log("No valid configuration");
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

    rss_log("Getting feed {$feed['name']}");
    
    $status = $Unserializer->unserialize($feed['_url'], true);
    if (PEAR::isError($status)) die($status->getMessage());
    
    $data = $Unserializer->getUnserializedData();
    if ($data == false) {
        rss_log("Unable to unserialize {$feed['name']}");
        continue;
    }
    
    if (!is_array($feed['history'])) $feed['history'] = array('rule' => array());
    
    if (!isset($data['channel']['item'])) {
        rss_log("No item data");
        continue;
    }

    foreach ($data['channel']['item'] as $item) {
        if (!is_array($item)) {
            rss_log("Invalid feed data for {$feed['name']}");
            continue;
        }
        
        foreach ($feed['history']['rule'] as $entry) {
            if (get_guid($item) == $entry['guid']) {
                continue 2;
            }
        }
        
        if ($feed['subscribe']) {
            if (add_torrent(get_download($item), $feed['directory'])) $item['downloaded'] = true;
        } else {
            foreach ($a_filters as &$filter) {
                if (!isset($filter['enabled'])) continue;
                if ($filter['feed'] != -1 && $filter['feed'] != $feed['uuid']) continue;
                
                if (preg_match('/'.$filter['filter'].'/i', $item['title']))
                {
                    rss_log("{$item['title']} matches {$filter['filter']}");
                    $item['filter'] = $filter['uuid'];

                    if (isset($filter['smart'])) {
                        if(!preg_match('/\W(?:S(\d+)E(\d+)|(\d+)x(\d+)(?:\.(\d+))?)\W/', $item['title'], $match))
                            continue;
                        
                        $id = implode('x', array_slice($match, 3));
                        if (is_array($filter['episodes']) && is_array($filter['episodes']['rule'])) {
                            if (in_array($id, $filter['episodes']['rule'])) {
                                rss_log("Already have episode $id");
                                add_item(&$feed, $item);
                                continue 2;
                            }
                            $filter['episodes']['rule'][] = $id;
                        }
                        else
                            $filter['episodes'] = array('rule' => array($id));
                            
                        rss_log("New epidose $id");
                    }
                    
                    if (add_torrent(get_download($item), !empty($filter['directory']) ? $filter['directory'] : $feed['directory']) == 0) {
                        $item['downloaded'] = true;
                    }
                    else rss_log("Unable to add {$item['title']} from " . get_download($item));
                }
            }
        }
        
        add_item(&$feed, $item);
    }   
}

rss_log("Saving data");
write_config();
