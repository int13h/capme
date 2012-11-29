<?php

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

// Sensor list
function mkSensor($active) {
    global $dbHost,$dbName,$dbUser,$dbPass;
    $db = mysql_connect($dbHost,$dbUser,$dbPass) or die(mysql_error());
    mysql_select_db($dbName,$db) or die();
    $query = "SELECT net_name, hostname, sid FROM sensor
              WHERE agent_type = 'pcap'
              ORDER BY hostname ASC";
    $sensors = mysql_query($query);

    while ($row = mysql_fetch_row($sensors)) {
        $nn = $row[0];
        $hn = $row[1];
        $si = $row[2];

        $selected = '';

        if ($si == $active) {
            $selected="selected=\"yes\"";
        }

        echo "<option data-sensorname=\"$hn\" value=\"$si\" $selected>$nn - $hn - $si</option>\n";

    }
}

?>

