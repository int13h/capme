<?php

include_once 'functions.php';

if (!isset($_REQUEST['d'])) { 
    exit;
} else { 
    $d = $_REQUEST['d'];
}

$d = explode("-", $d);

$sip	= h2s($d[0]);
$spt	= h2s($d[1]);
$dip	= h2s($d[2]);
$dpt	= h2s($d[3]);
$st	= $d[4];
$et     = $d[5];
$usr	= h2s($d[6]);
$pwd	= h2s($d[7]);
$sidsrc = h2s($d[8]);

// Format timestamps
$st = gmdate("Y-m-d H:i:s", $st);
$et = gmdate("Y-m-d H:i:s", $et);

// Defaults
$err = 0;
$fmtd = $debug = $errMsg = '';

// Find appropriate sensor

$queries = array(
                 "sancp" => "SELECT sancp.start_time, s2.sid, s2.hostname
                             FROM sancp
                             LEFT JOIN sensor ON sancp.sid = sensor.sid
                             LEFT JOIN sensor AS s2 ON sensor.net_name = s2.hostname
                             WHERE sancp.start_time >=  '$st' AND sancp.end_time <= '$et'
                             AND ((src_ip = INET_ATON('$sip') AND src_port = $spt AND dst_ip = INET_ATON('$dip') AND dst_port = $dpt) OR (src_ip = INET_ATON('$dip') AND src_port = $dpt AND dst_ip = INET_ATON('$sip') AND dst_port = $spt))
                             AND s2.agent_type = 'pcap' LIMIT 1",

                 "event" => "SELECT event.timestamp AS start_time, s2.sid, s2.hostname
                             FROM event
                             LEFT JOIN sensor ON event.sid = sensor.sid
                             LEFT JOIN sensor AS s2 ON sensor.net_name = s2.hostname
                             WHERE timestamp BETWEEN '$st' AND '$et'
                             AND ((src_ip = INET_ATON('$sip') AND src_port = $spt AND dst_ip = INET_ATON('$dip') AND dst_port = $dpt) OR (src_ip = INET_ATON('$dip') AND src_port = $dpt AND dst_ip = INET_ATON('$sip') AND dst_port = $spt))
                             AND s2.agent_type = 'pcap' LIMIT 1");

$response = mysql_query($queries[$sidsrc]);

if (!$response) {
    $err = 1;
    $errMsg = "Error: The query failed, please verify database connectivity";
    $debug = $queries[$sidsrc];
} else if (mysql_num_rows($response) == 0) {
    $err = 1;
    $errMsg = "Failed to find a matching sid, please try again in a few seconds";
    $debug = $queries[$sidsrc];
} else {
    $row = mysql_fetch_assoc($response);
    $st	= $row["start_time"];
    $sensor = $row["hostname"]; 
    $sid    = $row["sid"];
}

if ($err == 1) {
    $result = array("tx"  => "0",
                    "dbg" => "$debug",
                    "err" => "$errMsg");

} else {

    // CLIscript command
    $cmd = "cliscript.tcl -sid $sid -sensor '$sensor' -timestamp '$st' -u '$usr' -pw '$pwd' -sip $sip -spt $spt -dip $dip -dpt $dpt";

    exec("../.scripts/$cmd",$raw);

    foreach ($raw as $line) {

        $line = htmlspecialchars($line);
        $type = substr($line, 0,3);

        switch ($type) {
            case "DEB": $debug .= preg_replace('/^DEBUG:.*$/', "<span class=txtext_dbg>$0</span>", $line) . "<br>"; $line = ''; break;
            case "HDR": $line = preg_replace('/(^HDR:)(.*$)/', "<span class=txtext_hdr>$2</span>", $line); break;
            case "DST": $line = preg_replace('/^DST:.*$/', "<span class=txtext_dst>$0</span>", $line); break;
            case "SRC": $line = preg_replace('/^SRC:.*$/', "<span class=txtext_src>$0</span>", $line); break;       
        }

        if (strlen($line) > 0) {
            $fmtd  .= $line . "<br>";
        }
    }

    // Add query to debug
    $debug .= "<span class=txtext_qry>QUERY: " . $queries[$sidsrc] . "</span>";

    $result = array("tx"  => "$fmtd",
                    "dbg" => "$debug",
                    "err" => "$errMsg");
}

$theJSON = json_encode($result);
echo $theJSON;
?>

