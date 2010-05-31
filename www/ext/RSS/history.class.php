<?php
require_once('config.inc');

class History {
  var $_history = array();
  var $_config = null;
  
  function History(&$settings) {
    global $config;

    $this->_config = $settings;
  }
  
  function __destruct() {
//    $this->write();
  }
  
  function write() {
    $data = serialize($this->_history);
    $fd = @fopen("{$this->_config['path']}/history.dat", 'wb');
    if ($fd) {
      fwrite($fd, $data);
      fclose($fd);
    }
  }
  
  function read() {
    global $config;
    
    if (file_exists("{$this->_config['path']}/history.dat")) {
      $this->_history = unserialize(file_get_contents("{$this->_config['path']}/history.dat"));
    }
    
    $haveHistory = false;
    foreach ($config['rss']['feeds']['rule'] as &$feed) {
      if (isset($feed['history'])) {    
        foreach ($feed['history']['rule'] as $entry) {
          if ($this->find($feed['uuid'], $entry) === false) {
            $entry['pubDate'] = $entry['pubdate'];
            
            if (isset($entry['downloaded'])) $entry['downloaded'] = true;
            else $entry['downloaded'] = false;
            
            unset($entry['pubdate']);
            $this->add($feed['uuid'], $entry);
          }
        }
        $haveHistory = true;
        unset($feed['history']);
      }
    }
    if ($haveHistory) {
      $this->write();
      write_config();
    }
  }
  
  function &find($feed_id, $item) {
    if (!isset($this->_history[$feed_id])) return false;
    
    $guid = is_array($item) ? $item['guid'] : $item;
    
    foreach ($this->_history[$feed_id] as &$entry) {
      if ($guid == $entry['guid']) return $entry;
    }
    
    return false;
  }
  
  function add($feed_id, $item) {
    if (!isset($this->_history[$feed_id])) $this->_history[$feed_id] = array();
    
    $this->_history[$feed_id][] = $item;
  }
  
  function delete($feed_id) {
    if (isset($this->_history[$feed_id]))
      unset($this->_history[$feed_id]);
  }
  
  function &full($feed_id) {
    return $this->_history[$feed_id];
  }
}
