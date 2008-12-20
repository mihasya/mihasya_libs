<?php
require_once('../mihasya_cache.php');
require_once('PHPUnit/Framework.php');

class FileCacheTests extends PHPUnit_Framework_TestCase {
    public function setup() {
        cache_init('fileCache');
        cache_init('fileCache2', 'file', array('path'=>'.'));
    }
    public function testCacheFunction() {
        $this->c = cache('fileCache');
        $this->assertTrue(is_object($this->c));
        $this->assertEquals(get_class($this->c), 'fileCacheHdl');
        $this->c = cache('fileCache2');
        $this->assertTrue(is_object($this->c));
        $this->assertEquals(get_class($this->c), 'fileCacheHdl');
    }
    
    public function testSet() {
        $this->c = cache('fileCache2');
        $this->c->set('foo', 'this is a test value');
        $realKey = md5('foo');
        $this->assertTrue(file_exists('./'.$realKey));
        unlink('./'.$realKey);
    }

    public function testGet() {
        $c = cache('fileCache2');
        //try getting a value without having set it
        $val = $c->get('foo');
        $this->assertEquals($val, false);
        $c->set('foo', 15);  
        $val = $c->get('foo');
        $this->assertEquals($val, 15);
        $realKey = md5('foo');
        unlink('./'.$realKey);
    }
  
    public function testDelete() {
        $c = cache('fileCache2');
        $c->set('foo', 15);
        $this->assertTrue(file_exists('./'.md5('foo')));
        $this->assertTrue($c->delete('foo'));
        $this->assertTrue(!file_exists('./'.md5('foo')));
    }
}
