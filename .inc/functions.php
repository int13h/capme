<?php

include_once 'config.php';
global $dbHost,$dbName,$dbUser,$dbPass;
$db = mysql_connect($dbHost,$dbUser,$dbPass) or die(mysql_error());
mysql_select_db($dbName,$db) or die();

function h2s($x) {
  $s='';
  foreach(explode("\n",trim(chunk_split($x,2))) as $h) $s.=chr(hexdec($h));
  return($s);
}

function s2h($x) {
  $s='';
  foreach(str_split($x) as $c) $s.=sprintf("%02X",ord($c));
  return($s);
}

?>

