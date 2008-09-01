<?php
abstract class CacheNamespace {
  public static $handlers;
  public static $conf = array();
}

function cache($hdlName) {
  if (!CacheNamespace::$handlers[$hdlName]) {
    if (!CacheNamespace::$conf[$hdlName]) return false; //init hasnt been run
    //go on and create the connection
    $conf = CacheNamespace::$conf[$hdlName];
    $className = $conf['type'].'CacheHdl';
    $hdl = new $className($conf['initArgs']);
    CacheNamespace::$handlers[$hdlName] = $hdl;
  }
  return CacheNamespace::$handlers[$hdlName];
}

function cache_init($hdlName, $type='file', $initArgs = array()) {
  $conf = array(
    'type'=>$type,
    'initArgs'=>$initArgs
  );
  CacheNamespace::$conf[$hdlName] = $conf;
}

abstract class cacheHdl {
  //TODO: add initArgs validation to __construct; iterate over $this->_requiredArgs 
  //and set those in the handler class definitions
  abstract function __construct($initArgs);
  abstract function get($key);
  abstract function set($key, $value, $ttl=null);
}

class fileCacheHdl extends cacheHdl{
  function __construct($initArgs) {
    $this->path = $initArgs['path'];
  }
  //break this out into its own function incase I need to hash differently
  function getRealKey($key) {
    return md5($key);
  }
  function get($key) {
    $realKey = $this->getRealKey($key);
    $path = $this->path.'/'.$realKey;
    if (!file_exists($path)) return false;
    $contents = file_get_contents($path);
    $matches = array();
    $m = preg_match("/([0-9]+),/",$contents, $matches);
    if ($matches[1]==0 || $matches[1] < time()) {
      //TODO: actually strip out the ttl and return the value
      echo $contents;
    } else {
      unlink($path);
      return false;
    }
  }
  function set($key, $value, $ttl=null) {
    $realKey = $this->getRealKey($key);
    $ttl = $ttl ? time()+$ttl : 0;
    $toStore = is_string($value) ? $value : serialize($value);
    $toStore = $ttl.','.$toStore;
    $f = fopen($this->path.'/'.$realKey, 'w');
    fwrite($f, $toStore);
    fclose($f);
  }
}
?>
