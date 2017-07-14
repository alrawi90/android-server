<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$active_users=('');

$now=date('Y-m-d H:i:s');


$req=$_POST['para'];

$msg=$_POST['msg'];

//$user=$_POST['user'];
$uid=$_POST['uid'];
$user=get_nick($uid);

if ($req=="send_new_msg"){


//the next action is necessery if we talk about last seen but we are not intersted in last msg id to be stored in records..so we may change this action next versions.
$work = mysql_query(" UPDATE `records`  SET last_seen = '$now' , last_msg_id ='$latest_id'   where id='$uid'") or die(mysql_error());
mysql_query("commit;");

$work = mysql_query(" SELECT room FROM `records`    where id='$uid'") or die(mysql_error());
while($row = mysql_fetch_array($work)) { $room = $row["room"]; }
mysql_query("commit;");
$sql = mysql_query("INSERT INTO `chat` (user, msg, date_time,room) VALUES('$uid','$msg','$now','$room')")  or die (mysql_error());

mysql_query("commit;");
echo "SENT" ;             }
else {echo "invalid";}
mysql_close(); 
?>