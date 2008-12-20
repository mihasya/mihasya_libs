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
    abstract function __construct($initArgs);
    abstract function get($key);
    abstract function set($key, $value, $ttl=null);
    abstract function delete($key, $timeout=null);
    protected function _getRealKey($key) {
        //TODO: make this modifiable so user can define own hash/salt
        return md5($key);
    }
}

class apcCacheHdl extends cacheHdl{
    function __construct($initArgs) {
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
    function delete($key, $timeout=null) {
        if (!$timeout) {
            return apc_delete($this->_getRealKey($key));
        } else {
            return apc_store($this->_getRealKey($key), $value, $timeout);
        }
    }
}
class fileCacheHdl extends cacheHdl{
    private $_path;

    function __construct($initArgs) {
        $this->_path = $initArgs['path'] ? $initArgs['path'] : '.';
    }
    function get($key) {
        $realKey = $this->_getRealKey($key);
        $keyPath = $this->_path.'/'.$realKey;
        try {
            $fcontent = @fread(fopen($keyPath, 'r'), filesize($keyPath));
        } catch (Exception $e) {
            return false;
        }
        if (!$fcontent) { return false; }
        $content = unserialize($fcontent);
        if ($content->ttl < time() && $content->ttl != 0) {
            unlink($keyPath);
            return false;
        }
        return $content->value;
    }
    function set($key, $value, $ttl=null) {
        $realKey = $this->_getRealKey($key);
        $ttl = $ttl ? time()+$ttl : 0;
        $content = new stdClass();
        $content->ttl = $ttl;
        $content->value = $value;
        $f = fopen($this->_path.'/'.$realKey, 'w+');
        $result = @fwrite($f, serialize($content));
        fclose($f);
        return $result;
    }
    function delete($key, $timeout=null) {
        if (!$timeout) {
            return unlink($this->_path.'/'.$this->_getRealKey($key));
        } else {
            if (!$value = $this->get($key)) {
                return false;
            }
            return $this->set($key, $value, $timeout);
        }
    }
}
?>
