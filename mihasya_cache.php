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
    protected function _getRealKey($key) {
        //TODO: store the key so that we don't have to run m5 every time
        //TODO: make this modifiable so that the user can define own hash/salt
        return md5($key);
    }
}

class apcCacheHdl extends cacheHdl{
    function __construct($initArgs) {
        //TODO: maybe set a default ttl?
        if (!function_exists('apc_fetch')) {
            throw new Exception ("APC does not appear to be enabled");
        }
    }
    function get($key) {
        return apc_fetch($this->_getRealKey($key));
    }
    function set($key, $value, $ttl=null) {
        return apc_store($this->_getRealKey($key), $value, $ttl);
    }
}
class fileCacheHdl extends cacheHdl{
    private $_path;
    private $_ttl;
    function __construct($initArgs) {
        //TODO: check for empty fields
        $this->_path = $initArgs['path'];
        $this->_ttl = $initArgs['ttl'];
    }
    function get($key) {
        //TODO: use $this->ttl and file's mtime to check if it's expired
    }
    function set($key, $value, $ttl=null) {
        $realKey = $this->_getRealKey($key);
        $ttl = $ttl ? time()+$ttl : 0;
        $f = fopen($this->path.'/'.$realKey, 'w');
        fwrite($f, $value);
        fclose($f);
    }
}
?>
