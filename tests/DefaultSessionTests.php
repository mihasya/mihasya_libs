<?php
require_once('../mihasya_session.php');
require_once('PHPUnit/Framework.php');



class DefaultSessionTests extends PHPUnit_Framework_TestCase {
  public function setup () {
    sess_init('foo');
    sess_init('bar', 'default', array());
    $this->s = @sess('foo');
  }
  public function testSessFunction() {
    $this->assertTrue(is_object($this->s));
    $this->assertEquals(get_class($this->s), 'defaultSessionHdl');
    $s2 = @sess('bar');
    $this->assertTrue(is_object($s2));
    $this->assertEquals(get_class($s2), 'defaultSessionHdl');
  }
  public function testDefaultSet() {
    $this->s->set('foo', 'bar');
    $this->assertEquals($_SESSION['foo'], 'bar');
  }
  public function testDefaultGet() {
    $_SESSION['foo'] = 'bar';
    $this->assertEquals($this->s->get('foo'), 'bar');
  }
  public function testGetAll() {
    $test_sess = array('foo'=>'bar', 'eff'=>'ehh');
    $_SESSION = $test_sess;
    $this->assertEquals($this->s->getAll(), $test_sess);
  }
  public function testSetBatch() {
    $test_sess = array('foo'=>'bar', 'eff'=>'ehh');
    $this->s->setBatch($test_sess);
    $this->assertEquals($this->s->getAll(), $test_sess);
    $this->assertEquals($_SESSION, $test_sess);
  }
  /*public function testDestroy() {
    $this->s->destroy();
    $this->assertEquals($_SESSION, array());
  } TODO: figure out why this fails; most likely b/c headers are never sent
    so $_SESSION is just a dumb variable, session_destroy() doesn't affect it*/
  /*public function testGenerateID() {
    $oldID = $this->s->getID();
    @$this->s->generate_id();
    if ($this->s->getID() == $oldID) $this->fail();
  } can't test this either b/c of headers; new id never generated*/
}
?>
