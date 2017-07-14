<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$active_users=('');

$now=date('Y-m-d H:i:s');


$req=$_POST['para'];
$uid=$_POST['uid'];//$user=get_nick($uid);
$tid=$_POST['tid'];//$target=get_nick($tid);
$msg=$_POST['msg'];


if ($req=="send_new_pm")          {
mysql_query("START TRANSACTION");
    $sql_ = mysql_query("SELECT user_id ,ignored_id FROM `ignore_list` where ignored_id='$uid' and user_id='$tid'") or die (mysql_error());
    if( mysql_num_rows($sql_)>0){ echo 'IGNORED' ; }// if that user(target) ignored me
    else                     {// if that user did not ignore me

$sql = mysql_query("INSERT INTO `private` (fromUSER, toUSER, pm,time,state) VALUES('$uid','$tid','$msg','$now','new')")  or die (mysql_error());

mysql_query("commit;");
if ($sql){echo "SENT" ; }     }    }


if ($req=="find_new_pm"){
    mysql_query("START TRANSACTION"); 
    $sql = mysql_query("SELECT pm,time,fromUSER,id FROM `private` where toUSER='$uid' and state='new'") or die (mysql_error());
     // $sql = mysql_query("SELECT pm,time,fromUSER,id FROM `private` where state='new' AND (toUSER='$uid' OR fromUSER='$uid') order by id" ) or die(mysql_error());
    mysql_query("commit;");
    if(mysql_num_rows($sql)>0){
    while($row = mysql_fetch_array($sql)) { $msg=$row['pm'];
                                            $T=$row['time'];
                                           $sender_id=$row['fromUSER'];
                                            $id=$row['id'];//msg id
                                            
                                           
                                            echo $msg.",".$sender_id."&&";
                                            //echo $msg.','.$T.','.$sender_id.','.$id.'&&';
    $work = mysql_query(" UPDATE `private`  SET state = 'old'   where fromUSER='$sender_id' and toUSER='$uid' ") or die(mysql_error());                                        
                                            }
        mysql_query("commit;"); }
    else{}                           
                          }


mysql_close(); 
?>