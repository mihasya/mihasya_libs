<?php
ini_set('display_errors', '1');
function db_insert($pdo, $table, $kvValues) {
  $keys = array_keys($kvValues);
  $values = $placeHolders = array(); //declare scope arrays
  $q='INSERT INTO `'.$table.'` (';
  foreach ($kvValues as $k=>$v) {
    if (is_array($v)) {
      $placeHolders[] = $v['func']; //plug the user supplied function instead of the ?
      $values = array_merge($values, $v['values']);
    } else {
      $placeHolders[] = '?';
      $values[] = $v;
    }
  }
  $q='INSERT INTO `'.$table.'` ('
      .implode(',', $keys).') VALUES ('
      .implode(',', $placeHolders).')';
  $stmt = $pdo->prepare($q);
  if (!$stmt) {
      $err = $pdo->errorInfo();
      throw new Exception('Query Failed at prepare! ' . $err[2], $err[1]);
  }
  $res = $stmt->execute($values); //execute the query, using the $values array to plug the placeholders
  if(!$res) {
      $err = $stmt->errorInfo();
      throw new Exception('Query Failed at execute! ' . $err[2], $err[1]);
  }
  else return $pdo->lastInsertId(); //return what's useful; this will never be 0 on success
}

function db_update($pdo, $table, $where, $what) {
  $values = array(); //declare scope arrays
  $whatStr = $whereStr = ''; //declare scope string.
  foreach ($what as $k=>$v) {
    if (is_array($v)) {
      //there's a function involved
      $whatStr.=$k.'='.$v['func'].',';
      $values = array_merge($values, $v['values']);
    } else {
      //just a value
      $whatStr.=$k.'=?,';
      $values[] = $v;
    }
  }
  foreach ($where as $k=>$v) {
    if (is_array($v)) {
      //there's a function involved
      $whereStr.=$k.'='.$v['func'].' AND ';
      $values = array_merge($values, $v['values']);
    } else {
      //just a value
      $whereStr.=$k.'=? AND ';
      $values[] = $v;
    }
  }
  $whatStr = rtrim($whatStr, ',');
  $whereStr = rtrim($whereStr, ' AND ');
  $q = 'UPDATE `'.$table.'` SET '.$whatStr.' WHERE '.$whereStr;
  $stmt = $pdo->prepare($q);
  $res = $stmt->execute($values); //execute the query, using the $values array to plug the placeholders
  if(!$res) return $stmt->errorInfo();
  else return $stmt->rowCount(); //return what's useful; this will never be 0 on success
}
//TODO: OR's in WHERE?
function db_get($pdo, $table, $where=null, $what=null, $postfix=null) {
  $whereStr = '';
  $whatStr ='';
  $values=array();
  if (!$what || $what=='*') {
    $whatStr = '*';
  } else {
    $whatStr = implode(',',$what);
  }
  if ($where) {
    $whereStr.=' WHERE ';
    if (is_numeric($where)) { //if an id has been supplied, use it.
      $whereStr.= $table.'_id=?';
      $values[] = $where;
    } else { //a key value array has been supplied for the lookup
      foreach ($where as $k=>$v) {
        if (is_array($v)) {
          //there's a function involved
          $whereStr.=$k.'='.$v['func'].' AND ';
          $values = array_merge($values, $v['values']);
        } else {
          //just a value
          $whereStr.=$k.'=? AND ';
          $values[] = $v;
        }
      }
    }
  }
  $whereStr = rtrim($whereStr, ' AND ');
  $q = 'SELECT '.$whatStr.' FROM '.$table.$whereStr;
  if ($postfix) {
    $q.=' '.$postfix;
  }
  $stmt = $pdo->prepare($q);
  $res = $stmt->execute($values); //execute the query, using the $values array to plug the placeholders
  if(!$res) return $stmt->errorInfo();
  else return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//TODO: recheck this and test it.
function db_delete($pdo, $table, $where, $postfix=null) {
  $whereStr.=' WHERE ';
  $values = array();
  if (is_numeric($where)) { //if an id has been supplied, use it.
    $whereStr.= $table.'_id=?';
    $values[] = $where;
  } else { //a key value array has been supplied for the lookup
    foreach ($where as $k=>$v) {
      if (is_array($v)) {
        //there's a function involved
        $whereStr.=$k.'='.$v['func'].' AND ';
        $values = array_merge($values, $v['values']);
      } else {
        //just a value
        $whereStr.=$k.'=? AND ';
        $values[] = $v;
      }
    }
  }
  $whereStr = rtrim($whereStr, ' AND ');
  $q = 'DELETE FROM '.$table.' '.$whereStr;
  if ($postfix) {
    $q.=' '.$postfix;
  }
  $stmt = $pdo->prepare($q);
  $res = $stmt->execute($values); //execute the query, using the $values array to plug the placeholders
  if(!$res) return $stmt->errorInfo();
  else return $stmt->rowCount();
}

//TODO: write a function to interpret unique key errors using
//use "show index from tablename" and a local copy of the database for performance
?>
