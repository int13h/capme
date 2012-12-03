<?php

include_once 'functions.php';

if (!isset($_REQUEST['d'])) { 
    exit;
} else { 
    $d = $_REQUEST['d'];
}

$d = explode("-", $d);

$sensor = h2s($d[0]);
$sid	= h2s($d[1]);
$sip	= h2s($d[2]);
$spt	= h2s($d[3]);
$dip	= h2s($d[4]);
$dpt	= h2s($d[5]);
$st	= $d[6];
$et     = $d[7];
$usr	= h2s($d[8]);
$pwd	= h2s($d[9]);

// Format timestamps
$st = date("Y-m-d H:i:s", $st);
$et = date("Y-m-d H:i:s", $et);

// Defaults
$err = 0;
$fmtd = $debug = $errMsg = '';

// Find appropriate sensor
if ($sid == "00") {

    $query = "SELECT s2.sid, s2.hostname
              FROM sancp
              LEFT JOIN sensor ON sancp.sid = sensor.sid
              LEFT JOIN sensor AS s2 ON sensor.hostname = s2.hostname
              WHERE sancp.start_time >=  '$st' AND sancp.end_time <= '$et'
              AND ((src_ip = INET_ATON('$sip') AND src_port = $spt AND dst_ip = INET_ATON('$dip') AND dst_port = $dpt) OR (src_ip = INET_ATON('$dip') AND src_port = $dpt AND dst_ip = INET_ATON('$sip') AND dst_port = $spt))
              AND s2.agent_type = 'pcap' LIMIT 1";

    $response = mysql_query($query);

    if (!$response) {
        $err = 1;
        $errMsg = "The query failed";
    } else if (mysql_num_rows($response) == 0) {
        $err = 1;
        $errMsg = "Failed to find a matching sid, please try again in a few seconds";
    } else {
        $row = mysql_fetch_assoc($response);
        $sensor = $row["hostname"]; 
        $sid    = $row["sid"];
    }
}

if ($err == 1) {

    $result = array("tx"  => "$fmtd",
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

    $result = array("tx"  => "$fmtd",
                    "dbg" => "$debug",
                    "err" => "$errMsg");
}

$theJSON = json_encode($result);
echo $theJSON;
?>

