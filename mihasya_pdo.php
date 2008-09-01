<?php
abstract class PDONamespace {
  public static $connections;
  public static $conf = array();
}
#grab a pdo connection; create it if needed;
function pdo($db) {
  if (!PDONamespace::$connections[$db]) {
    if (!PDONamespace::$conf[$db]) return false; //init hasnt been run
    //go on and create the connection
    $conf = PDONamespace::$conf[$db];
    $dsn = $conf['dsn']; //some sort of PDO weirdness
    PDONamespace::$connections[$db] = new PDO($dsn, $conf['user'], $conf['pwd']);
  }
  return PDONamespace::$connections[$db];
}
#initialize the given configuration file
function pdo_init($conn_name, $conf_array) {
  $newConf = array($conn_name=>$conf_array);
  PDONamespace::$conf = array_merge(PDONamespace::$conf, $newConf);
  return true;
}
#abstracts getting configuration from a file (checks apc first)
#TODO: move this to a conf library;
function fetch_conf_file($fname) {
  $apc = function_exists('apc_fetch'); //do we have apc?
  $key = 'pdo_conf_'.md5(fname);
  if ($apc) {
    $apc_value = apc_fetch($key);
    if ($apc_value) return unserialize($apc_value);
  }
  $fcontents = parse_ini_file($fname, true);
  if ($apc) {
    apc_store($key, serialize($fcontents));
  }
  return $fcontents;
}
?>
