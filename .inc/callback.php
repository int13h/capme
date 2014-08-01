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
$st_unix= $d[4];
$et_unix= $d[5];
$usr	= h2s($d[6]);
$pwd	= h2s($d[7]);
$sidsrc = h2s($d[8]);
$xscript = h2s($d[9]);

// Format timestamps
$st = date("Y-m-d H:i:s", $st_unix);
$et = date("Y-m-d H:i:s", $et_unix);

// Fix Snorby timezone
if ($sidsrc == "event") {

	// load the user's timezone setting
	include 'timezone.php';

	// convert the start time from the user's timezone to UTC/GMT
	$st = date_create($st, timezone_open($timezone));
	date_timezone_set($st, timezone_open('Etc/GMT'));
	$st = date_format($st, 'Y-m-d H:i:s');

	// convert the end time from the user's timezone to UTC/GMT
	$et = date_create($et, timezone_open($timezone));
	date_timezone_set($et, timezone_open('Etc/GMT'));
	$et = date_format($et, 'Y-m-d H:i:s');
}

// Defaults
$err = 0;
$fmtd = $debug = $errMsg = '';

/*
We need to determine 3 pieces of data:
sensor	- sensor name (for Security Onion this is HOSTNAME-INTERFACE)
st	- time of the event from the sensor's perspective (may be more accurate than what we were given), in Y-m-d H:i:s format
sid	- sensor id
*/

$sensor = "";
if ($sidsrc == "elsa") {
	/*
	If ELSA is enabled, then we need to:
	- construct the ELSA query and submit it via cli.pl
	- receive the response and parse out the sensor name (HOSTNAME-INTERFACE) and timestamp
	- convert the timestamp to the proper format
	NOTE: This requires that ELSA has access to Bro conn.log AND that the conn.log 
	has been extended to include the sensor name (HOSTNAME-INTERFACE).
	*/

	$elsa_query = "class=bro_conn start:'$st_unix' end:'$et_unix' +$sip +$spt +$dip +$dpt limit:1 timeout:0";
	$elsa_command = "perl /opt/elsa/web/cli.pl -q '$elsa_query' ";
	$elsa_response = shell_exec($elsa_command);

	// A successful query response looks like this:
	// timestamp    class   host    program msg     fields
	// 1372897204   BRO_CONN        127.0.0.1       bro_conn        original_timestamp|Many|Pipe|Delimited|Fields|etc|sensorIsOffset22

	// Explode the output into separate lines and pull out the data line
	$pieces = explode("\n", $elsa_response);

	// Sometimes the response contains a warning - this means that the
	// expected query response data is not located on the second line.
	// Iterate through until we find the header line - the next
	// line is the data line we want. See line 35 of /opt/elsa/web/cli.pl
	$data_line_n = 1;

	for ($n=0; $n<=count($pieces); $n++) {
		if ($pieces[$n] === "timestamp\tclass\thost\tprogram\tmsg\tfields") {
			$data_line_n = $n + 1;
			break;
		}
	}

	$elsa_response_data = $pieces[$data_line_n];

	// Explode the tab-delimited data line and pull out the pipe-delimited raw log
	$pieces = explode("\t", $elsa_response_data);
	$elsa_response_data_raw_log = $pieces[4];

	// Explode the pipe-delimited raw log and pull out the original timestamp and sensor name
	$pieces = explode("|", $elsa_response_data_raw_log);
	$elsa_response_data_raw_log_timestamp = $pieces[0];
	$elsa_response_data_raw_log_sensor = $pieces[22];

	// Convert timestamp to proper format
	$st = date("Y-m-d H:i:s", $elsa_response_data_raw_log_timestamp);

	// Clean up $sensor
	$sensor = rtrim($elsa_response_data_raw_log_sensor);
	
	// We now have 2 of the 3 pieces of data that we need.
	// Next, we'll use $sensor to look up the $sid in Sguil's sensor table.
}

/*
Query the Sguil database
If the user selected sancp or event, query those tables and get
the 3 pieces of data that we need.
*/
$queries = array(
                 "elsa" => "SELECT sid FROM sensor WHERE hostname='$sensor' AND agent_type='pcap' LIMIT 1",

                 "sancp" => "SELECT sancp.start_time, s2.sid, s2.hostname
                             FROM sancp
                             LEFT JOIN sensor ON sancp.sid = sensor.sid
                             LEFT JOIN sensor AS s2 ON sensor.net_name = s2.net_name
                             WHERE sancp.start_time >=  '$st' AND sancp.end_time <= '$et'
                             AND ((src_ip = INET_ATON('$sip') AND src_port = $spt AND dst_ip = INET_ATON('$dip') AND dst_port = $dpt) OR (src_ip = INET_ATON('$dip') AND src_port = $dpt AND dst_ip = INET_ATON('$sip') AND dst_port = $spt))
                             AND s2.agent_type = 'pcap' LIMIT 1",

                 "event" => "SELECT event.timestamp AS start_time, s2.sid, s2.hostname
                             FROM event
                             LEFT JOIN sensor ON event.sid = sensor.sid
                             LEFT JOIN sensor AS s2 ON sensor.net_name = s2.net_name
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
    $debug = $queries[$sidsrc];
    $errMsg = "Failed to find a matching sid, please try again in a few seconds";
    $response = mysql_query("select * from sensor where agent_type='pcap' and active='Y';");
    if (mysql_num_rows($response) == 0) {
    $errMsg = "Error: No pcap_agent found";
    }
} else {
    $row = mysql_fetch_assoc($response);
    // If using ELSA, we already set $st and $sensor above so don't overwrite that here
    if ($sidsrc != "elsa") {
        $st = $row["start_time"];
    	$sensor = $row["hostname"]; 
    }
    $sid    = $row["sid"];
}

if ($err == 1) {
    $result = array("tx"  => "0",
                    "dbg" => "$debug",
                    "err" => "$errMsg");

} else {

    // We have all the data we need, so pass the parameters to the correct cliscript
    $script = "cliscript.tcl";
    if ($xscript == "bro") {
	$script = "cliscriptbro.tcl";
    }
    $cmd = "$script -sid $sid -sensor '$sensor' -timestamp '$st' -u '$usr' -pw '$pwd' -sip $sip -spt $spt -dip $dip -dpt $dpt";

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

    // default to sending transcript
    $mytx = $fmtd;

    /*
    $debug EITHER looks like this:

    DEBUG: Using archived data: /nsm/server_data/securityonion/archive/2013-11-08/doug-virtual-machine-eth1/10.0.2.15:1066_192.168.56.50:80-6.raw

    OR it looks like this:

    DEBUG: Raw data request sent to doug-virtual-machine-eth1.
    DEBUG: Making a list of local log files.
    DEBUG: Looking in /nsm/sensor_data/doug-virtual-machine-eth1/dailylogs/2013-11-08.
    DEBUG: Making a list of local log files in /nsm/sensor_data/doug-virtual-machine-eth1/dailylogs/2013-11-08.
    DEBUG: Available log files:
    DEBUG: 1383910121
    DEBUG: Creating unique data file: /usr/sbin/tcpdump -r /nsm/sensor_data/doug-virtual-machine-eth1/dailylogs/2013-11-08/snort.log.1383910121 -w /tmp/10.0.2.15:1066_192.168.56.50:80-6.raw (ip and host 10.0.2.15 and host 192.168.56.50 and port 1066 and port 80 and proto 6) or (vlan and host 10.0.2.15 and host 192.168.56.50 and port 1066 and port 80 and proto 6)
    DEBUG: Receiving raw file from sensor.
    */

    // Find pcap
    $archive = '/DEBUG: Using archived data.*/';
    $unique = '/DEBUG: Creating unique data file.*/';
    $found_pcap = 0;
    if (preg_match($archive, $debug, $matches)) {
    	$found_pcap = 1;
	$match = str_replace("</span><br>", "", $matches[0]);
    	$pieces = explode(" ", $match);
    	$full_filename = $pieces[4];
    	$pieces = explode("/", $full_filename);
    	$filename = $pieces[7];
    } else if (preg_match($unique, $debug, $matches)) {
    	$found_pcap = 1;
	$match = str_replace("</span><br>", "", $matches[0]);
    	$pieces = explode(" ", $match);
    	$sensor_filename = $pieces[7];
    	$server_filename = $pieces[9];
    	$pieces = explode("/", $sensor_filename);
    	$sensorname = $pieces[3];
    	$dailylog = $pieces[5];
    	$pieces = explode("/", $server_filename);
    	$filename = $pieces[2];
    	$full_filename = "/nsm/server_data/securityonion/archive/$dailylog/$sensorname/$filename";
    }	

    // Add query to debug
    $debug .= "<span class=txtext_qry>QUERY: " . $queries[$sidsrc] . "</span>";

    // if we found the pcap, create a symlink in /var/www/capme/pcap/
    // and then create a hyperlink to that symlink
    if ($found_pcap == 1) {
      	$tmpstring = rand();
	$filename_random = str_replace(".raw", "", "$filename-$tmpstring");
	$filename_download = "$filename_random.pcap";
	$link = "/var/www/capme/pcap/$filename_download";
	symlink($full_filename, $link);
	$debug .= "<br><a href=\"/capme/pcap/$filename_download\">$filename_download</a>";
	$mytx = "<a href=\"/capme/pcap/$filename_download\">$filename_download</a><br><br>$mytx";
	// if the user requested pcap, send the pcap instead of the transcript
	if ($xscript == "pcap") {
	    	$mytx = $filename_download;
	}
    } else {
        $debug .= "<br>WARNING: Unable to find pcap.";
    }

    $result = array("tx"  => "$mytx",
                    "dbg" => "$debug",
                    "err" => "$errMsg");
}

$theJSON = json_encode($result);
echo $theJSON;
?>

