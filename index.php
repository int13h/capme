<?php

//
//
//      Copyright (C) 2012 Paul Halliday <paul.halliday@gmail.com>
//
//      This program is free software: you can redistribute it and/or modify
//      it under the terms of the GNU General Public License as published by
//      the Free Software Foundation, either version 3 of the License, or
//      (at your option) any later version.
//
//      This program is distributed in the hope that it will be useful,
//      but WITHOUT ANY WARRANTY; without even the implied warranty of
//      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//      GNU General Public License for more details.
//
//      You should have received a copy of the GNU General Public License
//      along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//

include '.inc/functions.php';
// Argument counters
$s = 0;

// Argument defaults
$sip = $spt = $dip = $dpt = $stime = $etime = $usr = $pwd = $sancp = $event = '';
// Grab any arguments provided in URI
if (isset($_REQUEST['sip']))      { $sip    = $_REQUEST['sip'];      $s++; }
if (isset($_REQUEST['spt']))      { $spt    = $_REQUEST['spt'];      $s++; }
if (isset($_REQUEST['dip']))      { $dip    = $_REQUEST['dip'];      $s++; }
if (isset($_REQUEST['dpt']))      { $dpt    = $_REQUEST['dpt'];      $s++; }
if (isset($_REQUEST['stime']))    { $stime  = $_REQUEST['stime'];    $s++; }
if (isset($_REQUEST['etime']))    { $etime  = $_REQUEST['etime'];    $s++; }
if (isset($_REQUEST['user']))     { $usr    = $_REQUEST['user'];     $s++; }
if (isset($_REQUEST['password'])) { $pwd    = $_REQUEST['password']; $s++; }
if (isset($_REQUEST['filename'])) { 
    $event = " checked";
} else {
    $sancp = " checked";
}
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
