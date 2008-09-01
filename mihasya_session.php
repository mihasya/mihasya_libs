<?php
//simulate a namespace to keep things tidy
//the session should pretty much never have to be referenced, so the long name should be ok
abstract class SessionNamespace {
  public static $handlers;
  public static $conf = array();
}
//initialize and return a session handler instance if it doesn't already exist
//pass whatever you need to $initArgs
function sess($hdlName) {
  if (!isset(SessionNamespace::$handlers[$hdlName])){
    if(!isset(SessionNamespace::$conf[$hdlName])) {
      throw new Exception('Session Handler '.$hdlName.' was not initialized properly.');
    }
    $conf = SessionNamespace::$conf[$hdlName];
    $className = $conf['type'].'SessionHdl';
    $hdl = new $className($conf['initArgs']);
    SessionNamespace::$handlers[$hdlName] = $hdl;
  }
  return SessionNamespace::$handlers[$hdlName];
}
function sess_init($hdlName, $type='default', $initArgs=null) {
  $conf = array(
    'type'=>$type,
    'initArgs'=>$initArgs
  );
  SessionNamespace::$conf[$hdlName] = $conf;
}
//what a session handler should look like
abstract class sessionHdl {
  protected $session_id;
  protected $cookie_name = 'PHPSESSID';
  protected $ttl = 0;
  //TODO: add initArgs validation to __construct; iterate over $this->_requiredArgs 
  //and set those in the handler class definitions
  abstract function __construct($initArgs);
  abstract function get($key);
  abstract function set($key, $value);
  abstract function getAll();
  abstract function setBatch($kvPairs);
  abstract function destroy();
  abstract function generate_id();
  function reset_id() {
    $currentValues = $this->getAll();
    $this->destroy();
    $this->generate_id();
    $this->setBatch($currentValues);
  }
  function write_cookie() {
    $ttl = ($this->ttl==0) ? 0 : time()+$this->ttl;
    setcookie($this->cookie_name, $this->session_id, $ttl);
  }
  function getID() {
    return $this->session_id;
  }
}
//a wrapper for the default sessions handler
class defaultSessionHdl extends sessionHdl {
  function __construct($initArgs) {
    session_start();
    $this->session_id = session_id();
    if (!isset($_COOKIE['PHPSESSID'])) $this->write_cookie();
  }
  function get($key) { return $_SESSION[$key]; }
  function set($key, $value) { $_SESSION[$key] = $value; }
  function getAll() { return $_SESSION; }
  function setBatch($kvPairs) { $_SESSION = $kvPairs; }
  function destroy() { session_destroy(); }
  function generate_id() {
    session_regenerate_id();
    $this->session_id = session_id();
  }
  //reset_id is overwritten to not do extra work since session_regenerate_id does all the work
  function reset_id() {
    $this->generate_id();
    $this->write_cookie();
  }
}
?>
