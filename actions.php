<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$active_users=('');

$now=date('Y-m-d H:i:s');

$req=$_POST['para'];$req=htmlspecialchars($req);
$pass=$_POST['pass'];$pass=htmlspecialchars($pass);
$newROOM=$_POST['requestedR'];$newROOM=htmlspecialchars($newROOM);//for change_rooms req only
$currentROOM=$_POST['currentR'];$currentROOM=htmlspecialchars($currentROOM);//for change_rooms req only
$uid=$_POST['uid'];$uid=htmlspecialchars($uid);//
//$user=$_POST['para3'];//for change_rooms req only
//$enmy=$_POST['kick'];//kick_user only

function select_room(){
$sql_3=mysql_query("select room from `rooms` where size > num_uesrs") or die (mysql_error());

while($res3=mysql_fetch_array($sql_3)){
if( mysql_num_rows($sql_3)>0){
return $res3['room'];
mysql_query("commit;");
break;}
else {return false;mysql_query("commit;");}
}}

function ip_details($ip) {
    //$v="95.159.90.240";
    $json = file_get_contents("http://ipinfo.io/{$ip}");
    $details = json_decode($json);
    return $details;
}

if ($req=="get_rooms_names")
{
  $names=array();  
 $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
     array_push($names,$r);
    
    }
mysql_query("commit;"); 
$_names=join(',',$names) ;
echo "SUCCESS"."&".$_names;
   
    
    
}
if ($req=="get_rooms")
{
    
 $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
      $n=$res_1['num_uesrs'];
      $s=$res_1['size'];
      $st=$res_1['state'];
      $bg=$res_1['roomBG'];

      echo $r."&".$n.'&'.$s.'&'.$st.'&'.$bg.",";
    
    }
   mysql_query("commit;"); 
    
    
}
if ($req=="get_room_users_android")// this is only for android app untill we modify python app
{   $names=array(); 
    $room=$_POST['room'];$room=htmlspecialchars($room);//
 $sql_1=mysql_query("select id from `records` where room='$room'") or die (mysql_error());   
   if( mysql_num_rows($sql_1)>0){
   while($res_1=mysql_fetch_array($sql_1))
   {  $id=$res_1['id'];

     array_push($names,$id);
    
    }
      $_names=join(',',$names) ;
      echo "SUCCESS&",$_names;

                               }else{echo"EMPTY_ROOM";}
   mysql_query("commit;"); 
    
    
}
else if($req=="get_location")
{
 $sql_1=mysql_query("select ip from `records` where id='$uid'") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1)){$ip_adrr=$res_1['ip'];}


//$ip_adrr=$_POST['ip'];
$details = ip_details($ip_adrr);
if(count($details )>1){echo "FOUND&".$details->country."-".$details->city;}
else{echo "ERROR";}
//echo $details->city;     // => Mountain View
//echo $details->country;  // => US
//echo $details->org;      // => AS15169 Google Inc.
//echo $details->hostname; // => google-public-dns-a.google.com
}

else if ($req=="change_rooms")
{ $exception=$_POST['exception'];$exception=htmlspecialchars($exception);//
if($exception=="" || $exception=="False")
 {
  $sql_1=mysql_query("select room ,num_uesrs,size from `rooms` where room= '$newROOM' and password='$pass' ") or die (mysql_error());
  if (mysql_num_rows($sql_1)>0)#if access is ok(true password):register to the new room
  {
      while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
      $n=$res_1['num_uesrs'];
      $s=$res_1['size'];
    
    }
    mysql_query("commit;");
   if ($s>$n) {// if there is empty place in the room
   mysql_query("update  `records` set room= '$newROOM' where id='$uid'") or die (mysql_error());// change from current to new
    mysql_query("commit;");
              
   mysql_query("update  `rooms` set num_uesrs= num_uesrs+1 where room='$newROOM'") or die (mysql_error()); // +1 number of users in the new room
   mysql_query("commit;"); 
   mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$currentROOM'") or die (mysql_error());// -1 number of users in the old room
   mysql_query("commit;");
   $msg1='ALERT_LEAVE_ROOM'.'&'.$uid.'&'.$newROOM;
   $msg2='ALERT_ENTER_ROOM&'.$uid;
   mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg1','$now','$currentROOM' )") or die (mysql_error());
   // alert users this users left room to new room
    mysql_query("commit;");

   mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg2','$now','$newROOM' )") or die (mysql_error());
   
     mysql_query("commit;");//alert users this users enter room

      $sql_users_inTHEroom=mysql_query("select id from `records` where room= '$newROOM' and username !='SERVER'") or die (mysql_error());
      while($res=mysql_fetch_array($sql_users_inTHEroom))
   {  
      $USERS_ids=$res['id'];
      echo $USERS_ids.',';
    
    }
    mysql_query("commit;");
     
               }
    else{echo 'FULLroom'.'&';}
  }
  else{echo "WRONG_PASS";}

 }
else{//exception=True
$sql_1=mysql_query("select room ,num_uesrs,size from `rooms` where room= '$newROOM'  ") or die (mysql_error());
  if (mysql_num_rows($sql_1)>0)#if access is ok(true password):register to the new room
  {
      while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
      $n=$res_1['num_uesrs'];
      $s=$res_1['size'];
    
    }
    mysql_query("commit;");
   mysql_query("update  `records` set room= '$newROOM' where id='$uid'") or die (mysql_error());// change from current to new
    mysql_query("commit;");
              
    set_nou_room($newROOM);// +1 number of users in the new room
    set_nou_room($currentROOM);// -1 number of users in the old room
   $msg1='ALERT_LEAVE_ROOM'.'&'.$uid.'&'.$newROOM;
   $msg2='ALERT_ENTER_ROOM&'.$uid;
   mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg1','$now','$currentROOM' )") or die (mysql_error());
   // alert users this users left room to new room
    mysql_query("commit;");

   mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg2','$now','$newROOM' )") or die (mysql_error());
   
     mysql_query("commit;");//alert users this users enter room

      $sql_users_inTHEroom=mysql_query("select id from `records` where room= '$newROOM' and username !='SERVER'") or die (mysql_error());
      while($res=mysql_fetch_array($sql_users_inTHEroom))
   {  
      $USERS_ids=$res['id'];
      echo $USERS_ids.',';
    
    }
    mysql_query("commit;");
     
               

  }
else{echo "NOT_EXIT";}//incase room deleted..
}
}



else if ($req=="kick_user")
{
   $tid=$_POST['tid'];$tid=htmlspecialchars($tid);//
    if (user_is_exit($tid))                               {

    $msg='ALERT_KICK'.'&'.$tid;
    $w=mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
   // alert msg to kick some user out chat
    mysql_query("commit;");
    $sql=mysql_query("select room from `records`  where id='$tid'") or die (mysql_error());// change from current to new
    while($res=mysql_fetch_array($sql)){$room=$res['room'];}
    mysql_query("commit;");
    mysql_query("delete from `records`  where id='$tid'") or die (mysql_error());// change from current to new
    mysql_query("commit;");
    mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());// change from current to new
    mysql_query("commit;");
    
    
    if ($w){echo 'kicked';}
    else{echo 'failed to kick user';}
                                                           }else{echo "NOT_EXIT";}
    
  
}

else if($req=='new_settings')
{
//$NICK=$_POST['NICK']; list($uid,$newNICK) = explode(',', $NICK);
$tid=$_POST['tid'];$tid=htmlspecialchars($tid);//
$newNICK=$_POST['newnick'];$newNICK=htmlspecialchars($newNICK);//
$newFG=$_POST['FG'];$newFG=htmlspecialchars($newFG);//
$newBG=$_POST['BG'];$newBG=htmlspecialchars($newBG);//
$newMARK=$_POST['MARK'];$newMARK=htmlspecialchars($newMARK);//
$newPIC=$_POST['PIC'];$newPIC=htmlspecialchars($newPIC);//
$pm_state=$_POST['pm_state'];$pm_state=htmlspecialchars($pm_state);//
$newWC=$_POST['WC'];$newWC=htmlspecialchars($newWC);//
$inv=$_POST['INV'];$inv=htmlspecialchars($inv);//

    if (user_is_exit($tid))                               {
   $sql=mysql_query("update   `records` set username='$newNICK', bg='$newBG' ,fg='$newFG',pic='$newPIC',mark='$newMARK' ,pm='$pm_state',wc='$newWC',invisible='$inv' where id='$tid' ") or die (mysql_error());    
   mysql_query("commit;");
    $msg='ALERT_NEW_SETTINGS'.'&'.$tid.'&'.$newNICK.'&'.$newFG.'&'.$newBG.'&'.$newPIC.'&'.$newMARK.'&'.$pm_state.'&'.$newWC.'&'.$inv;
    mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
    mysql_query("commit;");

 

   if($sql){echo 'SET';} 
    else{echo 'CANNOT_SET';}                              
                                                          }else{echo "NOT_EXIT";}
    
}

else if ($req=="ignore_user")
{
 $uid=$_POST['uid']  ;$uid=htmlspecialchars($uid);//
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//

    if (user_is_exit($tid))                               {

 $sql_1=mysql_query("select *  from `ignore_list` where user_id='$uid' and ignored_id='$tid'") or die (mysql_error());
 mysql_query("commit;");
   if (mysql_num_rows($sql_1) <1){ //if this user not in my ignore list.. add him
    
        $sql=mysql_query("insert into `ignore_list` (user_id,ignored_id) values('$uid' ,'$tid') ") or die (mysql_error());
        mysql_query("commit;");
        if ($sql){echo 'IGNORED';}
        else{echo 'CANNOT_IGNORE';}       }
                                                            }else{echo "NOT_EXIT";}
 }                                
                                   
   
else if ($req=="release_user")
{
 $uid=$_POST['uid']  ;$uid=htmlspecialchars($uid);//
 $tid=$_POST['tid']  ;   $tid=htmlspecialchars($tid);//
 $sql_1=mysql_query("delete   from `ignore_list` where user_id='$uid' and ignored_id='$tid'") or die (mysql_error());
 mysql_query("commit;");  
if($sql_1){ echo 'RELEASED'  ; }
else{echo 'CANNOT_RELEASE';}  
    
}

else if ($req=="find_room_user")
{
 $uid=$_POST['uid']  ;   $uid=htmlspecialchars($uid);//
 $sql=mysql_query("select room   from `records` where id='$uid' ") or die (mysql_error());
 mysql_query("commit;");
 if (mysql_num_rows($sql)>0){
    while ($res=mysql_fetch_array($sql)){       
        
    $room=$res['room'];
                           
  
 echo 'FOUND'.'&'.$room  ;              }
                             }
}
else if ($req=="find_user_ip")
{
 $uid=$_POST['uid']  ;   $uid=htmlspecialchars($uid);//
 $sql=mysql_query("select ip   from `records` where id='$uid' ") or die (mysql_error());
 mysql_query("commit;");
 if (mysql_num_rows($sql)>0){
    while ($res=mysql_fetch_array($sql)){       
        
    $ip=$res['ip'];
                           
  
 echo 'FOUND'.'&'.$ip  ;              }
                             }
}
else if ($req=="band_user")
{
  $owner_nick=$_POST['owner'];$owner_nick=htmlspecialchars($owner_nick);//
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select ip,room ,username from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");

 if (mysql_num_rows($SQL)>0){
    while($r=mysql_fetch_array($SQL)){$ip=$r['ip'];$room=$r['room'];$nick=$r['username']; }
    $sql_1=mysql_query("insert into `band_list` (ip,user_id,user_nick,owner_nick,time)  values('$ip','$tid','$nick','$owner_nick','$now')  ") or die (mysql_error());
    mysql_query("commit;");
    $server_msg='ALERT_BAND_USER'.'&'.$tid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
    $w=mysql_query("delete from `records`  where id='$tid'") or die (mysql_error());// 
    mysql_query("commit;");
    mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());//
    mysql_query("commit;");
   if($sql_1  && $sql_1 && $w){echo "BANNED";}
   else{echo "CAN_NOT_BAN_USER";}
                          }
 
}
else if ($req=="exit_chat")
{
 $user_id=$_POST['uid'];$user_id=htmlspecialchars($user_id);//
    if (user_is_exit($tid))                               {
    $server_msg='ALERT_EXIT_CHAT'.'&'.$user_id;
 $sql=mysql_query("select room   from `records` where id='$user_id' ") or die (mysql_error());
while($r=mysql_fetch_array($sql)){$room=$r['room'];}
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
   $w= mysql_query("delete from `records`  where id='$user_id'") or die (mysql_error());// 
    mysql_query("commit;");
    mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());//
    mysql_query("commit;");

  if ($w)    { echo $user_id.'&'.' SIGNED_OUT' ;}            
  else{echo 'ERROR';}
                                                           }else{echo "NOT_EXIT";}

}
else if ($req=="send_msg")
{
 $uid=$_POST['uid'];$uid=htmlspecialchars($uid);//
$msg=$_POST['msg'];$msg=htmlspecialchars($msg);//
    
    
    $server_msg='CAUTION_MSG'.'&'.$msg.'&'.$uid;

    $sql=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");


  if ($sql)    { echo 'SENT' ;}            
  else{echo 'ERROR';}

}

else if ($req=="freeze_user")
{
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $server_msg='ALERT_FREEZE_USER'.'&'.$tid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
    mysql_query("commit;");
    $sql_3=mysql_query("insert into `frozen_list` (user_id,time)  values('$tid','$now')  ") or die (mysql_error());
     mysql_query("commit;");

if($sql_2){echo "FROZEN";} else{echo "CAN_NOT_FREEZE";}
                          }
 
}
else if ($req=="unfreeze_user")
{
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $server_msg='ALERT_UNFREEZE_USER'.'&'.$tid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
     $sql_3=mysql_query("delete from `frozen_list` where user_id='$tid' ") or die (mysql_error()); 
    mysql_query("commit;");
if($sql_2){echo "UNFROZEN";} else{echo "CAN_NOT_UNFREEZE";}
                          }
 
}
else if ($req=="level_up_user")
{
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){       while($r=mysql_fetch_array($SQL)){$p=$r['power'];}
   if ($p==0){
    mysql_query("update   `records` set power='-1' where id='$tid'") or die (mysql_error());
    $server_msg='ALERT_LEVELUP'.'&'.$tid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
       if ($sql_2)    { echo 'DONE' ;}  }       
       else{echo 'ERROR';}  }
 
}
else if ($req=="level_down_user")
{
 $tid=$_POST['tid']  ;$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){       while($r=mysql_fetch_array($SQL)){$p=$r['power'];}
   if ($p<0){
    mysql_query("update   `records` set power='0' where id='$tid'") or die (mysql_error());
    $server_msg='ALERT_LEVELDOWN'.'&'.$tid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
       if ($sql_2)    { echo 'DONE' ;}  }       
       else{echo 'ERROR';}  }
 
}

else if ($req=="add_room")
{     $room=$_POST['name'];$room=htmlspecialchars($room);//
      $size=$_POST['size'];$size=htmlspecialchars($size);//
      $state=$_POST['state'];$state=htmlspecialchars($state);//
      $pass=$_POST['pass'];$pass=htmlspecialchars($pass);//
      $bg=$_POST['bg'];$bg=htmlspecialchars($bg);//
      $factor=$_POST['factor'];$factor=htmlspecialchars($factor);//

   // alert msg to kick some user out chat
    mysql_query("commit;");
    $sql=mysql_query("select room from `rooms`  where room='$room'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)>0){echo 'ROOM_EXIST';}//room name alreay exist
    else
    {
   //$work= mysql_query("insert into  `rooms` ( room,size,state,roomBG,password) values('$room','$size','$state','$bg','$pass')") or die (mysql_error());// change from current to new
   $work= mysql_query("insert into  `rooms` ( room,size,state,bg,password,factor) values('$room','$size','$state','$bg','$pass','$factor')") or die (mysql_error());// change from current to new
  
 mysql_query("commit;");
    
    
    if ($work)
    {echo 'ROOM_CREATED';
    $msg='ALERT_NEW_ROOM'.'&'.$room.'&'.$size.'&'.$state.'&'.$bg.'&'.$factor;
    $w=mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
    
    }
    else{echo 'ERR_ROOM_ADDITION';}
    }
}    

else if ($req=="edit_room")
{     $room=$_POST['name'];$name=htmlspecialchars($name);//
      $size=$_POST['size'];$size=htmlspecialchars($size);//
      $state=$_POST['state'];$state=htmlspecialchars($state);//
      $current_pass=$_POST['Cpass'];$current_pass=htmlspecialchars($current_pass);//
      $new_pass=$_POST['Npass'];$new_pass=htmlspecialchars($new_pass);//
      $bg=$_POST['bg'];$bg=htmlspecialchars($bg);//

    mysql_query("commit;");
    $sql=mysql_query("select room from `rooms`  where room='$room' and password='$current_pass'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){echo 'ROOM_NOT_FOUND_OR_WRONG_PASS';}//
    else
    {
   //$work= mysql_query("update   `rooms` set size='$size',state='$state',roomBG='$bg',password='$new_pass' where room='$room'") or die (mysql_error());// change from current to new
    $work= mysql_query("update   `rooms` set size='$size',state='$state',bg='$bg',password='$new_pass' where room='$room'") or die (mysql_error());// change from current to new
   
   mysql_query("commit;");
    
    
    if ($work)
    {echo 'ROOM_EDITED';

    }
    else{echo 'ERR_ROOM_EDITION';}
    }
} 
else if ($req=="get_room_users")
{     $room=$_POST['room'];$room=htmlspecialchars($room);//
      $users=array();
    mysql_query("commit;");
    $sql=mysql_query("select id from `records`  where room='$room'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){echo 'EMPTY_ROOM';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users);
       echo $list;
    }
} 
else if ($req=="move_users")//change room for one user or group of users by some member who has the ability to move them
{     //$names=$_POST['names'];$Names=explode('&',$names);
      $uids=$_POST['uids'];$uids=htmlspecialchars($uids);//
      $UIDS=explode('&',$uids);
      $new_room=$_POST['new_room'];$new_room=htmlspecialchars($new_room);//
      $users_ids=array();
    //mysql_query("commit;");
    foreach ($UIDS as $uid)
    {
     $sql=mysql_query("select room from `records`  where id='$uid'") or die (mysql_error());mysql_query("commit;");
     if (mysql_num_rows($sql)<1){}
     else
     {while($r=mysql_fetch_array($sql)){$currnt_room=$r['room'];}
     $w1=mysql_query("update `records` set room='$new_room'  where id='$uid'") or die (mysql_error());mysql_query("commit;");
     
      set_nou_room($currnt_room);
      set_nou_room($new_room);
        $msg1='ALERT_LEAVE_ROOM'.'&'.$uid.'&'.$new_room;
        $msg2='ALERT_ENTER_ROOM&'.$uid;
        $msg3='ALERT_LOAD_ROOM_USERS_AFTER_MOVE'.'&'.$uid.'&'.$new_room;
 
       mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg1','$now','$currnt_room' )") or die (mysql_error());
   // alert users this users left room to new room
       mysql_query("commit;");

       mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg2','$now','$new_room' )") or die (mysql_error());
           mysql_query("commit;");

       mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg3','$now','$new_room' )") or die (mysql_error());
     }
     
    }
    
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){echo 'EMPTY_ROOM';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users);
       echo $list;
    }
} 

else if ($req=="get_IPs")             //        x_control related
{     $ip_state=$_POST['ip_state'];$ip_state=htmlspecialchars($ip_state);//
      $ip_group=$_POST['ip_group'];$ip_group=htmlspecialchars($ip_group);//
      $room=$_POST['room'];$room=htmlspecialchars($room);//
      $ctrl_by_value=$_POST['ctrl_by_value'];$ctrl_by_value=htmlspecialchars($ctrl_by_value);//by ip or by room
      if ($ip_state=='banned'){$sql_string="select distinct ip from `band_list`";}
      else{//allowed
          if($ctrl_by_value=="By IP address"){
                            if($ip_group=="show all"){$sql_string="select  distinct ip from `records` where username !='SERVER'";}
                            else if($ip_group=="show frozen"){$sql_string="select  distinct ip from `records` where id in (select user_id from `frozen_list`) and username !='SERVER'";}
                            else{//unfrozen
                                 $sql_string="select  distinct ip from `records`  where id not in (select user_id from `frozen_list`) and username !='SERVER'";}
                                   }
          else{//By Room location
                            if($ip_group=="show all"){$sql_string="select  distinct ip from `records` where username !='SERVER' and room='$room'";}
                            else if($ip_group=="show frozen"){$sql_string="select  distinct ip from `records` where id in (select user_id from `frozen_list`) and username !='SERVER' and room='$room'";}
                            else{$sql_string="select  distinct ip from `records` where id in (select user_id from `frozen_list`) and username !='SERVER' and room='$room'";}
                                 }//unfrozen
               
          }
      $IPs=array();
      $_uids=array();
      $_nicks=array();
    $sql=mysql_query($sql_string) or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){echo 'NO_RESULT';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {

              if ($ip_state=='banned')                 {
               $row=get_banned_users($r['ip']);  //      format : users=nick1&nick2&....; ip&&users;
               array_push($IPs,$row) ;
                                                       }
              else{
               $row=get_allowed_users($r['ip'],$ip_group,$ctrl_by_value,$room);  //      format : users=nick1&nick2&....; ip&&users;
               array_push($IPs,$row) ;
                  }
               }
              
       $list=join(',',$IPs);
       echo $list;
    }
}
else if ($req=="unban")
{     $ips=$_POST['IPs'];$ips=htmlspecialchars($ips);//
    $IPs=explode('&',$ips);      
     $owner_nick=$_POST['owner']  ;$owner_nick=htmlspecialchars($owner_nick);////not used yet
    foreach ($IPs as $ip)                                    {
    $sql_string="select * from `band_list` where ip='$ip'";
    $sql=mysql_query($sql_string) or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){}//echo 'NO_RESULT';}
    else
    {
       $sql=mysql_query("delete from `band_list` where ip='$ip' ") or die (mysql_error());
       mysql_query("commit;");
      if($sql){ echo 'UNBANNED';  }
      else{echo 'CANNOT_UNBAN';}
    }                                                         }
}
else if ($req=="get_users_by_ip")
{     $ip=$_POST['ip'];$ip=htmlspecialchars($ip);//
      $users_ids=array()   ;
    $sql=mysql_query("select id from `records`  where ip='$ip'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){echo 'NO_RESULT';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users_ids);
       echo $list;
    }
}
else if ($req=="Gkick")
{     $users_ids=$_POST['Users_ids'];$users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids);      
    
    foreach ($Users as $uid)                                    {
    $msg='ALERT_KICK'.'&'.$uid;
    $w=mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
   // alert msg to kick some user out chat
    mysql_query("commit;");
    $sql=mysql_query("select room from `records`  where id='$uid'") or die (mysql_error());// 
    while($res=mysql_fetch_array($sql)){$room=$res['room'];}
    mysql_query("commit;");
    mysql_query("delete from `records`  where id='$uid'") or die (mysql_error());//
    mysql_query("commit;");
    
        set_nou_room($room);
    if ($w){echo 'KICKED';}
    else{echo 'FAILED';}                                                     }
}
else if ($req=="Gdefreez")
{     $users_ids=$_POST['Users_ids'];$users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids);      

    foreach ($Users as $uid)                                    {

 if (user_is_exit($uid)){
    $server_msg='ALERT_UNFREEZE_USER'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
     $sql_3=mysql_query("delete from `frozen_list` where user_id='$uid' ") or die (mysql_error()); 
    mysql_query("commit;");
                          }
    
    if($sql_3){echo "UNFREEZED";}else{echo "FAILED_UNFREEZE";}  

                                                                   }
}
else if ($req=="Gfreez")
{     $users_ids=$_POST['Users_ids'];$users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids);      

    foreach ($Users as $uid)                                    {

 if (user_is_exit($uid)){
    $server_msg='ALERT_FREEZE_USER'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");

$sql_3=mysql_query("insert into `frozen_list` (user_id,time)  values('$uid','$now')  ") or die (mysql_error());
     mysql_query("commit;");
                          }
    
    
          if($sql_3){echo "FREEZED";}else{echo "FAILED";}                }
}
else if ($req=="Gban")
{     $users_ids=$_POST['Users_ids'];$users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids); 
    $owner_nick=$_POST['owner']   ;  $owner_nick=htmlspecialchars($owner_nick);//

    foreach ($Users as $uid)                                    {
 
 $SQL=mysql_query("select ip,room from `records` where id='$uid'") or die (mysql_error());
  mysql_query("commit;");

 if (mysql_num_rows($SQL)>0){
    $target_nick=get_nick($uid) ;
    while($r=mysql_fetch_array($SQL)){$ip=$r['ip'];$room=$r['room'];}
    $sql_1=mysql_query("insert into `band_list` (ip,user_id,user_nick,owner_nick,time)  values('$ip','$uid','$target_nick','$owner_nick','$now')  ") or die (mysql_error());
    mysql_query("commit;");
    $server_msg='ALERT_BAND_USER'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
    $w=mysql_query("delete from `records`  where id='$uid'") or die (mysql_error());// 
    mysql_query("commit;");
     set_nou_room($room);     
    if($w){echo "BANNED";}else{echo "FAILED";}                                    
   
                          }                                        }
}

else if ($req=="GOinvisible")
{     $uid=$_POST['uid'];$uid=htmlspecialchars($uid);//
      $user=get_nick($uid);      

 $SQL=mysql_query("select * from `records` where id='$uid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $sql_1=mysql_query("update `records` set invisible='1' where id='$uid'  ") or die (mysql_error());
    mysql_query("commit;");
    $server_msg='ALERT_GO_INVISIBLE'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");                                        
   
                          }                                        
}
else if ($req=="GOvisible")
{     $uid=$_POST['uid'];$uid=htmlspecialchars($uid);//
$user=get_nick($uid);    
 $SQL=mysql_query("select * from `records` where id='$uid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $sql_1=mysql_query("update `records` set invisible='0' where id='$uid'  ") or die (mysql_error());
    mysql_query("commit;");
    $server_msg='ALERT_GO_VISIBLE'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");                                        
   
                          }                                        
}
else{}
mysql_close(); 



?>