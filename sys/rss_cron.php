#!/usr/local/bin/php
<?php
require_once('ext/RSS/rss_class_history.php');
require_once('ext/RSS/rss_functions.inc');
require_once('XML/Unserializer.php');

function get_download($item) {
    if (isset($item['enclosure']) && isset($item['enclosure']['attributes'])  && $item['enclosure']['attributes']['type'] == 'application/x-bittorrent')
        return $item['enclosure']['attributes']['url'];
    return $item['link'];
}

function get_guid($item) {
    if (!isset($item['guid']) || empty($item['guid']))
        return get_download($item);

    if (is_array($item['guid']))
        return $item['guid']['_content'];
    return $item['guid'];
}

function add_item($feed, $item) {
    global $History;
    
    $History->add($feed['uuid'], array(
        'title' => $item['title'],
        'guid' => get_guid($item),
        'description' => $item['description'],
        'pubDate' => $item['pubDate'],
        'link' => get_download($item),
        'downloaded' => (isset($item['downloaded']) ? true : false),
        'filter' => (isset($item['filter']) ? $item['filter'] : false)
    ));
}

// We don't have any feeds, just exit.  Filters are not required.
if (!isset($config['rss']) || !isset($config['rss']['feeds']) || !isset($config['rss']['feeds']['rule'])) {
    rss_log('No valid configuration', VERBOSE_ERROR);
    exit();
}

$a_feeds = &$config['rss']['feeds']['rule'];
if (isset($config['rss']) && isset($config['rss']['filters']))
    $a_filters = &$config['rss']['filters']['rule'];
else
    $a_filters = array();

$History = new History($config['rss']);
$History->read();

$options = array(
    'parseAttributes' => TRUE,
    'attributesArray' => 'attributes'
);
$Unserializer = &new XML_Unserializer($options);

foreach ($a_feeds as &$feed) {
    if(!isset($feed['enabled'])) continue;

    rss_log("Getting feed {$feed['name']}", VERBOSE_EXTRA);
    
    $status = $Unserializer->unserialize($feed['_url'], true);
    if (PEAR::isError($status)) die($status->getMessage());
    
    $data = $Unserializer->getUnserializedData();
    if ($data == false) {
        rss_log("Unable to unserialize {$feed['name']}", VERBOSE_EXTRA);
        continue;
    }
    
    if (!isset($data['channel']['item'])) {
        rss_log("No item data", VERBOSE_EXTRA);
        continue;
    }
    
    if (!isset($data['channel']['item'][0]))
      $data['channel']['item'] = array($data['channel']['item']);

    foreach ($data['channel']['item'] as $item) {
        if (!is_array($item)) {
            rss_log("Invalid feed data for {$feed['name']}", VERBOSE_EXTRA);
            continue;
        }
        
        if ($History->find($feed['uuid'], get_guid($item))) { rss_log("{$item['title']} found", VERBOSE_EXTRA); continue; }

        if ($feed['subscribe']) {
            if (add_torrent(get_download($item), $feed['directory']) == 0) $item['downloaded'] = true;
        } else {
            foreach ($a_filters as &$filter) {
                if (!isset($filter['enabled'])) continue;
                if ($filter['feed'] != -1 && $filter['feed'] != $feed['uuid']) continue;
                
                if (preg_match('/'.$filter['filter'].'/i', $item['title']))
                {
                    rss_log("{$item['title']} matches {$filter['filter']}", VERBOSE_EXTRA);
                    $item['filter'] = $filter['uuid'];

                    if (isset($filter['smart'])) {
                        if(!preg_match('/\W(?:S(\d+)E(\d+)|(\d+)x(\d+)(?:\.(\d+))?)\W/', $item['title'], $match))
                            continue;
                        
                        $id = implode('x', array_slice($match, 3));
                        if (is_array($filter['episodes']) && is_array($filter['episodes']['rule'])) {
                            if (in_array($id, $filter['episodes']['rule'])) {
                                rss_log("Already have episode $id", VERBOSE_EXTRA);
                                add_item(&$feed, $item);
                                continue 2;
                            }
                            $filter['episodes']['rule'][] = $id;
                        }
                        else
                            $filter['episodes'] = array('rule' => array($id));
                            
                        rss_log("New epidose $id", VERBOSE_EXTRA);
                    }
                    
                    if (add_torrent(get_download($item), !empty($filter['directory']) ? $filter['directory'] : $feed['directory']) == 0) {
                        $item['downloaded'] = true;
                    }
                    else rss_log("Unable to add {$item['title']} from " . get_download($item), VERBOSE_ERROR);
                }
            }
        }
        
        add_item(&$feed, $item);
    }   
}

rss_log('Saving data', VERBOSE_EXTRA);

$History->write();
write_config();
