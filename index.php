<?php
include '.inc/config.php';
include '.inc/functions.php';
$s = 0;
if (!isset($_REQUEST['sensor'])) { $sensor = ''; } else { $sensor = $_REQUEST['sensor']; $s++; }
if (!isset($_REQUEST['sid']))    { $sid    = ''; } else { $sid    = $_REQUEST['sid'];    $s++; }
if (!isset($_REQUEST['sip']))    { $sip    = ''; } else { $sip    = $_REQUEST['sip'];    $s++; }
if (!isset($_REQUEST['spt']))    { $spt    = ''; } else { $spt    = $_REQUEST['spt'];    $s++; }
if (!isset($_REQUEST['dip']))    { $dip    = ''; } else { $dip    = $_REQUEST['dip'];    $s++; }
if (!isset($_REQUEST['dpt']))    { $dpt    = ''; } else { $dpt    = $_REQUEST['dpt'];    $s++; }
if (!isset($_REQUEST['ts']))     { $ts     = ''; } else { $ts     = $_REQUEST['ts'];     $s++; }
if (!isset($_REQUEST['usr']))    { $usr    = ''; } else { $usr    = $_REQUEST['usr'];    $s++; }
if (!isset($_REQUEST['pwd']))    { $pwd    = ''; } else { $pwd    = $_REQUEST['pwd'];    $s++; }
?>

<html>
<head>
<title>
capME!
</title>

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
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
<form id=capme_form>
<tr>
<td class=capme_left>Sensor:</td>
<td>
<SELECT id=capme_sid class=capme_select>
<?php
    if(!isset($_REQUEST['qSID'])) { $qSID = 1024; } else { $qSID = $_REQUEST['qSID']; }
    mkSensor($qSID);
?>
</SELECT>
</td>
</tr>

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
<td class=capme_left>Timestamp:</td>
<td class=capme_right><input type=text maxlength=19 id=timestamp class=capme_selb value="<?php echo $ts;?>" />
<span class=capme_ex>ex: 2012-10-24.01:02:03</span>
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
<td colspan=2 class=capme_msg_cont>
<span class=capme_msg></span>
</td>
</tr>
	
<tr>
<td colspan=2 class=capme_buttons>
<input class=capme_reset type=reset value=reset>
<input class=capme_submit type=button value=submit>
<input id=numargs type=hidden value="<?php echo $s;?>" />
</td>
</tr>
</form>
</table>
</body>
</html>
