<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$new_lines=('');

$now=date('Y-m-d H:i:s');

$req=$_POST['para'];

$uid=$_POST['uid'];

$lid=$_POST['lid'];



//$user=get_nick($uid);//not used
$exp=false;//not used(not effect)
function get_rooms_updates()
{$rooms_info=array();
   $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
      $n=$res_1['num_uesrs'];
      $s=$res_1['size'];
      $st=$res_1['state'];
      $bg=$res_1['bg'];
      $factor=$res_1['factor'];
      $line= $r."&".$n.'&'.$s.'&'.$st.'&'.$bg.'&'.$factor;//.",";
      array_push($rooms_info,$line);
    
    }
   mysql_query("commit;");
  return $rooms_info;
                 
}

mysql_query("START TRANSACTION"); 
if ($req=="check_news")

                                                                           {
                                                                              
      $REG_TIME=$_POST['REG_TIME'];

   //$work = mysql_query("UPDATE `records`  SET last_seen = '$now'    where username='$user'") or die(mysql_error());  mysql_query("commit;");
     $work = mysql_query("UPDATE `records`  SET last_seen = '$now'    where id='$uid'") or die(mysql_error());  mysql_query("commit;");
   $w=mysql_query("select last_seen from `records` where username='SERVER'");//check the last time server generated random message
   while($res=mysql_fetch_array($w)){$last_seen=$res['last_seen'] ;}         
   if (strtotime($now)-strtotime($last_seen) >=120){     //if it is genetated randam message more than 2 minutes age..generate new message..
                   mysql_query("UPDATE `records`  SET last_seen = '$now'    where username='SERVER'") or die(mysql_error());mysql_query("commit;");
                   
                   $msg='RANDOM_MSG&'.rand(1,4);
                   mysql_query("INSERT INTO `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#')") or die(mysql_error());mysql_query("commit;");
                   
                                                    
                                                    }
//-----------------------------------------------------------------------------------------
   $rooms_info=get_rooms_updates();//get and insert rooms info (number of users on each room) into database as server alert

   $rooms_updates=join('!!!',$rooms_info) ;
   $server_msg='ALERT_ROOMS_INFO'.'-&'.$rooms_updates.'!!!';
   mysql_query("INSERT INTO `chat` (user,date_time, msg,room)  VALUES('SERVER','$now','$server_msg','#')")or die (mysql_error());
   mysql_query("commit;");
   
   //-----------------------------------------------------------------
    //$SQL_get_current_room = mysql_query("SELECT room FROM `records` where username ='$user'  "    )   or die(mysql_error());mysql_query("commit;");
      $SQL_get_current_room = mysql_query("SELECT room FROM `records` where id='$uid'  "    )   or die(mysql_error());mysql_query("commit;");
    $room;
    while($row = mysql_fetch_array($SQL_get_current_room))
    {
                $room=  $row['room'];
    }
    $sql = mysql_query("SELECT id FROM `chat` ORDER BY id DESC LIMIT 1") or die(mysql_error());mysql_query("commit;"); 
   while($row = mysql_fetch_array($sql)) { $latest_id = $row["id"];   } 
   if ($lid==0){
                  $stored_id = $latest_id;
                  //$str_of_rgstieration_time=strtotime($REG_TIME) ;
 $sql1="select id,user, msg, date_time ,room from `chat` where date_time >= '$REG_TIME' AND (room='$room' OR room='#' ) ";
       
                 }
   else {$stored_id = $lid;$sql1="select id,user, msg, date_time,room from `chat` where id > '$stored_id' AND (room='$room' OR room='#'  ) ";
        }
    

   mysql_query("commit;");
     if($latest_id >$stored_id | $exp=true){

        $SQL= mysql_query($sql1) or die(mysql_error());  
        mysql_query("commit;");
        $j=0;
        while($row = mysql_fetch_array($SQL)) { 
                                              $id=$row['id'];
                                              $otherUSERs = $row['user']; //other users and SERVER also but not the current user
                                                $msg = $row['msg'];
                                                $T= $row['date_time'];
                                                $Room=$row['room'];
                                              echo $msg.','.$T.','.$otherUSERs.','.$id.','.$Room.'&&';
                                                 
                                               }

       }

    mysql_query("commit;");
                                                                         }

mysql_close(); 

?>