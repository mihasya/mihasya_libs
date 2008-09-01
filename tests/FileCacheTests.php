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
    $this->assertTrue(file_exists($realKey));
  }

  public function testGet() {
    $this->c = cache('fileCache2');
    $this->c->get('foo');
  }
}
