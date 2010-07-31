#! /usr/local/bin/php -f
<?php
require_once('ext/RSS/history.class.php');
require_once('ext/RSS/rss_functions.inc');
require_once('XML/Unserializer.php');
require_once('email.inc');

$added_torrents = array();

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
    global $History, $modified;
    
    $History->add($feed['uuid'], array(
        'title' => $item['title'],
        'guid' => get_guid($item),
        'description' => $item['description'],
        'pubDate' => $item['pubDate'],
        'link' => get_download($item),
        'downloaded' => (isset($item['downloaded']) ? true : false),
        'filter' => (isset($item['filter']) ? $item['filter'] : false)
    ));

    $modified['history'] = true;
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

$modified = array('filters' => false, 'history'=> false);
foreach ($a_feeds as &$feed) {
    if(!isset($feed['enabled'])) continue;

    rss_log("Getting feed {$feed['name']}", VERBOSE_EXTRA);
    
    $xml = rss_download($feed['_url'], $feed['cookie']);
    if ($xml === false) {
      rss_log("Unable to download {$feed['name']} feed", VERBOSE_ERROR);
      continue;
    }
    
    $status = $Unserializer->unserialize($xml, false);
    if (PEAR::isError($status)) {
      rss_log("Error unserializing {$feed['name']} feed: " . $status->getMessage(), VERBOSE_ERROR);
      continue;
    }
    
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

        if (isset($feed['subscribe'])) {
            if (add_torrent(get_download($item), $feed['directory'], $feed['cookie']) == 0) {
                $item['downloaded'] = true;
                $added_torrents[] = $item['title'];
            }
        } else {
            foreach ($a_filters as &$filter) {
                if (!isset($filter['enabled'])) continue;
                if ($filter['feed'] != -1 && $filter['feed'] != $feed['uuid']) continue;
                
                if (preg_match('/'.$filter['filter'].'/i', $item['title']))
                {
                    rss_log("{$item['title']} matches {$filter['filter']}", VERBOSE_EXTRA);
                    $item['filter'] = $filter['uuid'];

                    if (isset($filter['smart'])) {
                        if(!preg_match('/\W(?:S0*(\d+)E0*(\d+)|0*(\d+)x0*(\d+)(?:\.0*(\d+))?)\W/', $item['title'], $match))
                            continue;
                        
                        $id = implode('x', array_slice(array_diff($match, array("")), 1));
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
                            
                        $modified['filters'] = true;
                        rss_log("New epidose $id", VERBOSE_EXTRA);
                    }
                    
                    if (add_torrent(get_download($item), !empty($filter['directory']) ? $filter['directory'] : $feed['directory'], $feed['cookie'], isset($filter['start_paused'])) == 0) {
                        $item['downloaded'] = true;
                        $added_torrents[] = $item['title'];
                    }
                    else rss_log("Unable to add {$item['title']} from " . get_download($item), VERBOSE_ERROR);
                }
            }
        }
        
        add_item(&$feed, $item);
    }   
}

$total_torrents = count($added_torrents);
if ($total_torrents && isset($config['rss']['notifications'])) {
    $subject = $total_torrents > 1 ? "Multiple Torrents added" : "Torrent added: {$added_torrents[0]}";
    $body = sprintf("%s has added the following torrent%s to your queue:\r\n\r\n%s",
        $config['system']['hostname'], $total_torrents > 1 ? 's' : '', implode("\r\n", $added_torrents));
    email_send($config['statusreport']['to'], $subject, $body, $error);
}

rss_log('Saving data', VERBOSE_EXTRA);
if ($modified['history']) $History->write();
if ($modified['filters']) write_config();

rss_log('Completed job', VERBOSE_SUCCESS);