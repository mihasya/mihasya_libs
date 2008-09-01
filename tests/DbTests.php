<?php
require_once('../mihasya_db.php');
require_once('PHPUnit/Framework.php');

//fixtures
global $pdo;
$pdo = new PDO('mysql:host=localhost;dbname=', 'root', null);
$pdo->exec("DROP DATABASE IF EXISTS mihasya_db_test");
$pdo->exec("CREATE DATABASE IF NOT EXISTS mihasya_db_test");
$pdo->exec("USE mihasya_db_test");
$q = "
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` bigint(20) NOT NULL auto_increment,
  `username` varchar(32) character set latin1 NOT NULL,
  `email` varchar(255) character set latin1 NOT NULL,
  `shh` varchar(32) character set latin1 NOT NULL,
  `mtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ctime` timestamp NOT NULL default '0000-00-00 00:00:00',
  `user_agent` varchar(255) collate utf8_unicode_ci NOT NULL,
  `user_ip` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`,`email`),
  KEY `user_agent` (`user_agent`,`user_ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;
";
$pdo->exec($q);

class DbTests extends PHPUnit_Framework_TestCase {
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    public function testInsert() {
        $what = array (
            'username'=>'mihasya',
            'email'=>'adfadf@adfadfsf.com',
            'shh'=> array('func'=>'md5(?)', 'values'=>array('hello')), //function
            'ctime'=>date('Y/m/d H:i:s')
        );
        $result = db_insert($this->pdo, 'user', $what);
        $this->assertTrue(is_numeric($result));
    }
    public function testInsertDuplicate() {
        //same as first test
        $what = array (
            'username'=>'mihasya',
            'email'=>'adfadf@adfadfsf.com',
            'shh'=> array('func'=>'md5(?)', 'values'=>array('hello')),
            'ctime'=>date('Y/m/d H:i:s')
        );
        try {
            $result = db_insert($this->pdo, 'user', $what);
        } catch (Exception $e) {
            if ($e->getCode()=='1062') {
                return;
            }
        }
        $this->fail('Expecting an exception with code 1062 for a unique key violation');
    }
    public function testInsertBadColumn() {
        $what = array (
            'foo_bar'=>'mihasya', //column doenst exist
            'email'=>'adfadf@adfadfsf.com',
            'shh'=> array('func'=>'md5(?)', 'values'=>array('hello')),
            'ctime'=>date('Y/m/d H:i:s')
        );
        try {
            $result = db_insert($this->pdo, 'user', $what);
        } catch (Exception $e) {
            if (preg_match("/^Query Failed at prepare/", $e->getMessage())) {
                return;
            }
        }
        $this->fail('Expecting an exception with code 1062 for a unique key violation');
    }
    /*public function testUpdate() {
        $this->assertTrue(true);
    }
    public function testDelete() {
        $this->assertTrue(true);
    }*/
}
?>