<?php
include '.inc/functions.php';
// Argument counters
$s = 0;

// Argument defaults
$sip = $spt = $dip = $dpt = $stime = $etime = $usr = $pwd = $sancp = $event = $elsa = $bro = $tcpflow = $pcap = '';
// Grab any arguments provided in URI
if (isset($_REQUEST['sip']))      { $sip    = $_REQUEST['sip'];      $s++; }
if (isset($_REQUEST['spt']))      { $spt    = $_REQUEST['spt'];      $s++; }
if (isset($_REQUEST['dip']))      { $dip    = $_REQUEST['dip'];      $s++; }
if (isset($_REQUEST['dpt']))      { $dpt    = $_REQUEST['dpt'];      $s++; }
if (isset($_REQUEST['stime']))    { $stime  = $_REQUEST['stime'];    $s++; }
if (isset($_REQUEST['etime']))    { $etime  = $_REQUEST['etime'];    $s++; }
if (isset($_REQUEST['user']))     { $usr    = $_REQUEST['user'];     $s++; }
if (isset($_REQUEST['password'])) { $pwd    = $_REQUEST['password']; $s++; }
// If we see a filename parameter, we know the request came from Snorby
// and if so we can just query the event table since Snorby just has NIDS alerts
// If the referer contains ":3154", then it's most likely a Security Onion user 
// pivoting from ELSA, so we should query using ELSA.
// If all else fails, query sancp.
if (isset($_REQUEST['filename'])) { 
    $event = " checked";
} elseif ( isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],":3154") !== false)) {
    $elsa  = " checked";
} else {
    $sancp = " checked";
}
$tcpflow = " checked";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>
capME!
</title>
<style type="text/css" media="screen">@import ".css/capme.css";</style>
<script type="text/javascript" src=".js/jq.js"></script>
<script type="text/javascript" src=".js/capme.js"></script>
</head>
<body class=capme_body>

<table class=capme_div align=center cellpadding=0 cellspacing=0>
<tr>
<td colspan=2 class=capme_logo>
<h2><span class=capme_l1>cap</span><span class=capme_l2>ME!</span></h2>
</td>
</tr>
<form name=capme_form>

<tr>
<td class=capme_left>Src IP / Port:</td>
<td class=capme_right>
<input type=text maxlength=15 id=sip class=capme_selb value="<?php echo $sip;?>" /> /
<input type=text maxlength=5 id=spt class=capme_sels value="<?php echo $spt;?>" />
</td>
</tr>

<tr>
<td class=capme_left>Dst IP / Port:</td>
<td class=capme_right>
<input type=text maxlength=15 id=dip class=capme_selb value="<?php echo $dip;?>" /> /
<input type=text maxlength=5 id=dpt class=capme_sels value="<?php echo $dpt;?>" />
</td>
</tr>

<tr>
<td class=capme_left>Start Time:</td>
<td class=capme_right><input type=text maxlength=19 id=stime class=capme_selb value="<?php echo $stime;?>" />
</td>
</tr>

<tr>
<td class=capme_left>End Time:</td>
<td class=capme_right><input type=text maxlength=19 id=etime class=capme_selb value="<?php echo $etime;?>" />
</td>
</tr>

<tr>
<td class=capme_left>Username:</td>
<td class=capme_right><input type=text maxlength=32 id=username class=capme_selb value="<?php echo $usr;?>" />
</td>
</tr>

<tr>
<td class=capme_left>Password:</td>
<td class=capme_right><input type=password maxlength=32 id=password class=capme_selb value="<?php echo $pwd;?>" />
</td>
</tr>

<tr>
<td class=capme_left>Sid Source:</td>
<td class=capme_right>
<input type=radio name=sidsrc class=capme_rad value="sancp"<?php echo $sancp;?>>sancp
<input type=radio name=sidsrc class=capme_rad value="event"<?php echo $event;?>>event
<input type=radio name=sidsrc class=capme_rad value="elsa"<?php echo $elsa;?>>elsa
</td>
</tr>

<tr>
<td class=capme_left>Output:</td>
<td class=capme_right>
<input type=radio name=xscript class=capme_rad value="tcpflow"<?php echo $tcpflow;?>>tcpflow
<input type=radio name=xscript class=capme_rad value="bro"<?php echo $bro;?>>bro
<input type=radio name=xscript class=capme_rad value="pcap"<?php echo $pcap;?>>pcap
</td>
</tr>

<tr>
<td colspan=2 class=capme_msg_cont>
<span class=capme_msg></span>
</td>
</tr>

<tr>
<td colspan=2 class=capme_button>
<div class=capme_submit>submit</div>
<input id=formargs type=hidden value="<?php echo $s;?>" />
</td>
</tr>
</form>
</table>
</body>
</html>
