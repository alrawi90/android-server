<?php
require_once "new.php";
require "reg_functions.php";
date_default_timezone_set('Asia/baghdad');
$active_users=('');

$now=date('Y-m-d H:i:s');

function startup($user,$user_ip,$power )// user just entered chat and established socket connection.
  {  
    //$power=0;
    $allUSERS=array();
    $users_thisROOM=array();
    $now=date('Y-m-d H:i:s');
    // $user_ip = $_SERVER['REMOTE_ADDR']; 
     $fg="";$bg="";$pic="";$mark="";$settings="";$pm_state="";
   $latest_id =1000;//get_latest_inserted_msg_id();
   
   $banned_check=check_if_banned($user_ip);
  if ($banned_check==false)
     {
    
      $sql_1=mysql_query("select * from `records` where username !='SERVER'") or die (mysql_error());
      mysql_query("commit;");
	  $token_nick;
    if(mysql_num_rows($sql_1)>0)
    {
        //clear_inactive_users($sql_1,$user,$now); 
		//now check if the picked nick by 'guest' is availble
		if($power>0){$token_nick=false;}//if this is a member
		else{// or he/she is geust
        $token_nick=check_if_nick_is_token($user);
		    }
        if ($token_nick==true) {return "ERR_0";}
        else
        {  // room code to add here               // if it is not token then it is ok and we can go to find a place on some room
                $try_regiser_in_room=select_room();        // select room or say all rooms are full..try again later..
            if( $try_regiser_in_room!=false){
                //$_users_thisROOM='';
                //$_allUSERS='';
                $settings=get_proper_user_settings($user,$now,$latest_id,$try_regiser_in_room,$user_ip,$power);
                
                $priv=get_privileges($power);
                $_users_thisROOM=get_users_this_room($try_regiser_in_room);
                $_allUSERS=get_all_users_chat();
                
                $reg_time=get_reg_TIME($user);
                $frozen=get_frozen_users();
				if(strlen($frozen)<2){$frozen=null;}
                return $try_regiser_in_room."&&".$_users_thisROOM."&&".$_allUSERS."&&"."success"."&&".$settings."&&".$priv."&&".$reg_time."&&".$frozen;//
                                    
                                            }//sucessfully registered and return room name
             else{return 'ERR_3' ;}// if select_room()return false means no empty room  failed to register   
        } //full register user attempt

    }        
  else //if no body in the chat then just register 
    {
          
 //           $sql_2_1=mysql_query("select nick from `members` where nick='$user'") or die (mysql_error());
 //           mysql_query("commit;");
 //           $res2_1=mysql_fetch_array($sql_2_1);
            
//    if( mysql_num_rows($sql_2_1)>0){return "ERR_0";}
			if($power>0){$token_nick=false;}else{
        $token_nick=check_if_nick_is_token($user);
		                           }
        if ($token_nick==true) {return "ERR_0";}//we check if this nick name token by  not logged in member                        
    else{
          
          $try_regiser_in_room=select_room();
            if( $try_regiser_in_room!=false)
            {
                $settings=get_proper_user_settings($user,$now,$latest_id,$try_regiser_in_room,$user_ip,$power);
               // register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$user_ip);
                $priv=get_privileges($power);
                $_users_thisROOM=get_users_this_room($try_regiser_in_room);
                $_allUSERS=get_all_users_chat();
                $reg_time=get_reg_TIME($user);
                $frozen=get_frozen_users();
				if(strlen($frozen)<2){$frozen=null;}
                return $try_regiser_in_room."&&".$_users_thisROOM."&&".$_allUSERS."&&"."success"."&&".$settings."&&".$priv."&&".$reg_time."&&".$frozen;//
                //insert_server_messges();  
             /////////////////////////////////////////////////////////////////////////////// 
                      
            }//sucessfully registered and return room name
            else{return 'ERR_3' ;}// if select_room()return false means no empty room  failed to register   //but here this is not possible //no one in the chat
        } //full register user attempt

                      
    }
  }
  else{ return 'ERR_4' ;}    //user has been banned 
         
  }


function ALERT_ROOMS_INFO()
{$rooms_info=array();
   $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
      mysql_query("commit;");

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
   $rooms_updates=join('!!!',$rooms_info) ;
   $server_msg='ALERT_ROOMS_INFO'.'-&'.$rooms_updates.'!!!';
   return $server_msg;
                 
}
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
///////////////////////////////////////////////////


function get_rooms_names()
{
  $names=array();  
 $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
     array_push($names,$r);
    
    }
mysql_query("commit;"); 
$_names=join(',',$names) ;
return "GET_ROOMS_NAMES"."&".$_names;
    
}
function get_rooms()
{
    
 $sql_1=mysql_query("select * from `rooms` order by num_uesrs") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1))
   {  $r=$res_1['room'];
      $n=$res_1['num_uesrs'];
      $s=$res_1['size'];
      $st=$res_1['state'];
      $bg=$res_1['roomBG'];

      return $r."&".$n.'&'.$s.'&'.$st.'&'.$bg.",";
    
    }
   mysql_query("commit;"); 
    
    
}


 function get_room_users_android($room)// this is only for android app untill we modify python app
{   $names=array(); 
 $sql_1=mysql_query("select id from `records` where room='$room' ") or die (mysql_error()); 
   mysql_query("commit;");  
   if( mysql_num_rows($sql_1)>0){
   while($res_1=mysql_fetch_array($sql_1))
   {       array_push($names,$res_1['id']);
   }  
      $_names=join(',',$names) ;
      return "SUCCESS&".$_names;
      }
	else{return "EMPTY_ROOM";}
   
}
function find_location($uid)
{
 $sql_1=mysql_query("select ip from `records` where id='$uid'") or die (mysql_error());   
   while($res_1=mysql_fetch_array($sql_1)){$ip_adrr=$res_1['ip'];}


//$ip_adrr=$_POST['ip'];
$details = ip_details($ip_adrr);
if(count($details )>1){return "FOUND&".$details->country."-".$details->city;}
else{return "NO_RESULT";}
//return $details->city;     // => Mountain View
//return $details->country;  // => US
//return $details->org;      // => AS15169 Google Inc.
//return $details->hostname; // => google-public-dns-a.google.com
}

function change_room($exception,$newROOM,$currentROOM,$pass,$uid,$now)
{
 $exception=htmlspecialchars($exception);//
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
              
    set_nou_room($newROOM);// +1 number of users in the new room
    set_nou_room($currentROOM);// -1 number of users in the old room
   $msg1='ALERT_LEAVE_ROOM'.'&'.$uid.'&'.$newROOM;
   $msg2='ALERT_ENTER_ROOM&'.$uid;
     $USERS_ids=get_users_of_room($newROOM);
	  $arr=array('msg1' => $msg1,'msg2' => $msg2,'USERS_ids' => $USERS_ids);
      return json_encode($arr); 
     
               }
    else{//full room
	$arr=array('msg1' => 'null','msg2' => 'null','USERS_ids' => 'FULLroom');
      return json_encode($arr);
	
	}
  }
  else{//password is wrong 
  	$arr=array('msg1' => 'null','msg2' => 'null','USERS_ids' => 'WRONG_PASS');
      return json_encode($arr);
    }

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

      $USERS_ids=get_users_of_room($newROOM);
	  $arr=array('msg1' => $msg1,'msg2' => $msg2,'USERS_ids' => $USERS_ids);
      return json_encode($arr);  	

  }
else{$arr=array('msg1' => 'null','msg2' => 'null','USERS_ids' => 'NOT_EXIT');
      return json_encode($arr);}//incase room deleted..
}
}


function kick_user($uid,$tid,$now)
{
   $tid=htmlspecialchars($tid);//
    if (user_is_exit($tid))                               {

    $msg='ALERT_KICK'.'&'.$tid;
    //$w=mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
   // alert msg to kick some user out chat
    //mysql_query("commit;");
    $sql=mysql_query("select room from `records`  where id='$tid'") or die (mysql_error());// change from current to new
    while($res=mysql_fetch_array($sql)){$room=$res['room'];}
    mysql_query("commit;");
    $w=mysql_query("delete from `records`  where id='$tid'") or die (mysql_error());// change from current to new
    mysql_query("commit;");
   // mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());
	set_nou_room($room);
	// change from current to new
    //mysql_query("commit;");
    
    
    if ($w){return $msg;}
    else{return 'FAILED_KICK_USER';}
                                                           }else{return "NOT_EXIT";}
    
  
}

function new_settings($uid,$tid,$newNICK,$newFG,$newBG,$newMARK,$newPIC,$newWC,$pm_state,$inv,$now)
{
//$NICK=$_POST['NICK']; list($uid,$newNICK) = explode(',', $NICK);
$tid=htmlspecialchars($tid);//
$newNICK=htmlspecialchars($newNICK);//
$newFG=htmlspecialchars($newFG);//
$newBG=htmlspecialchars($newBG);//
$newMARK=htmlspecialchars($newMARK);//
$newPIC=htmlspecialchars($newPIC);//
$pm_state=htmlspecialchars($pm_state);//
$newWC=htmlspecialchars($newWC);//
$inv=htmlspecialchars($inv);//

    if (user_is_exit($tid))                               {
   $sql=mysql_query("update   `records` set username='$newNICK', bg='$newBG' ,fg='$newFG',pic='$newPIC',mark='$newMARK' ,pm='$pm_state',wc='$newWC',invisible='$inv' where id='$tid' ") or die (mysql_error());    
   mysql_query("commit;");
    $msg='ALERT_NEW_SETTINGS'.'&'.$tid.'&'.$newNICK.'&'.$newFG.'&'.$newBG.'&'.$newPIC.'&'.$newMARK.'&'.$pm_state.'&'.$newWC.'&'.$inv;
   // mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
   // mysql_query("commit;");
   if($sql){return $msg;}//'SET';} 
    else{return 'CANNOT_SET';}                              
                                                          }else{return "NOT_EXIT";}
    
}

function ignore_user($uid,$tid)
{
 $uid=htmlspecialchars($uid);//
 $tid=htmlspecialchars($tid);//

    if (user_is_exit($tid))                               {

 $sql_1=mysql_query("select *  from `ignore_list` where user_id='$uid' and ignored_id='$tid'") or die (mysql_error());
 mysql_query("commit;");
   if (mysql_num_rows($sql_1) <1){ //if this user not in my ignore list.. add him
    
        $sql=mysql_query("insert into `ignore_list` (user_id,ignored_id) values('$uid' ,'$tid') ") or die (mysql_error());
        mysql_query("commit;");
        if ($sql){return 'IGNORED';}
        else{return 'CANNOT_IGNORE';}       }
                                                            }else{return "NOT_EXIT";}
 }                                
                                   
   
function release_user($uid,$tid)
{
$uid=htmlspecialchars($uid);//
$tid=htmlspecialchars($tid);//
 $sql_1=mysql_query("delete   from `ignore_list` where user_id='$uid' and ignored_id='$tid'") or die (mysql_error());
 mysql_query("commit;");  
if($sql_1){ return 'RELEASED'  ; }
else{return 'CANNOT_RELEASE';}  
    
}

function find_room($uid)
{
$uid=htmlspecialchars($uid);//
 $sql=mysql_query("select room   from `records` where id='$uid' ") or die (mysql_error());
 mysql_query("commit;");
 if (mysql_num_rows($sql)>0){
    while ($res=mysql_fetch_array($sql)){       
        
    $room=$res['room'];
                           
  
 return 'FOUND'.'&'.$room  ;              }
                             }
else{return "NO_RESULT";}
}
function find_ip($uid)
{
$uid=htmlspecialchars($uid);//
 $sql=mysql_query("select ip   from `records` where id='$uid' ") or die (mysql_error());
 mysql_query("commit;");
 if (mysql_num_rows($sql)>0){
    while ($res=mysql_fetch_array($sql)){        
    $ip=$res['ip'];  
 return 'FOUND'.'&'.$ip  ;              }
                             }
else{return "NO_RESULT";}

}
function band_user($owner_nick,$tid,$now)
{
$owner_nick=htmlspecialchars($owner_nick);//
$tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select ip,room ,username from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");

 if (mysql_num_rows($SQL)>0){
    while($r=mysql_fetch_array($SQL)){$ip=$r['ip'];$room=$r['room'];$nick=$r['username']; }
    $sql_1=mysql_query("insert into `band_list` (ip,user_id,user_nick,owner_nick,time)  values('$ip','$tid','$nick','$owner_nick','$now')  ") or die (mysql_error());
    mysql_query("commit;");
    $server_msg='ALERT_BAND_USER'.'&'.$tid;
    //$sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     //mysql_query("commit;");
    $w=mysql_query("delete from `records`  where id='$tid'") or die (mysql_error());// 
    mysql_query("commit;");
    //mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());//
    //mysql_query("commit;");
	set_nou_room($room);
   if($sql_1 && $w){return $server_msg;//"BANNED";
   }
   else{return "CAN_NOT_BAN_USER";}
                          }
 
}
function exit_chat($uid,$now)
{
$uid=htmlspecialchars($uid);//
    if (user_is_exit($uid))                               {
    $server_msg='ALERT_EXIT_CHAT'.'&'.$uid;
 $sql=mysql_query("select room   from `records` where id='$user_id' ") or die (mysql_error());
while($r=mysql_fetch_array($sql)){$room=$r['room'];}
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
   $w= mysql_query("delete from `records`  where id='$uid'") or die (mysql_error());// 
    mysql_query("commit;");
    mysql_query("update  `rooms` set num_uesrs= num_uesrs-1 where room='$room'") or die (mysql_error());//
    mysql_query("commit;");

  if ($w)    { return $uid.'&'.' SIGNED_OUT' ;}            
  else{return 'ERROR';}
                                                           }else{return "NOT_EXIT";}

}
function send_msg($uid,$msg,$now)
{
$uid=htmlspecialchars($uid);//
$msg=htmlspecialchars($msg);//
    
    
    $server_msg='CAUTION_MSG'.'&'.$msg.'&'.$uid;

    $sql=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");


  if ($sql)    { return 'SENT' ;}            
  else{return 'ERROR';}

}

function freeze_user($uid,$tid,$now)
{
 $tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $server_msg='ALERT_FREEZE_USER'.'&'.$tid;
    //$sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
    //mysql_query("commit;");
    $sql_3=mysql_query("insert into `frozen_list` (user_id,time)  values('$tid','$now')  ") or die (mysql_error());
     mysql_query("commit;");

if($sql_3){return $server_msg; //"FROZEN";
} else{return "CAN_NOT_FREEZE";}
                          }
 
}
function unfreeze_user($uid,$tid,$now)
{
 $tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){
    $server_msg='ALERT_UNFREEZE_USER'.'&'.$tid;
    //$sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
     $sql_3=mysql_query("delete from `frozen_list` where user_id='$tid' ") or die (mysql_error()); 
    mysql_query("commit;");
if($sql_3){return $server_msg;//"UNFROZEN";
} else{return "CAN_NOT_UNFREEZE";}
                          }
 
}
function level_up_user($uid,$tid,$now)
{
 $tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){       while($r=mysql_fetch_array($SQL)){$p=$r['power'];}
   if ($p==0){
    $sql_=mysql_query("update   `records` set power='-1' where id='$tid'") or die (mysql_error());
    $server_msg='ALERT_LEVELUP'.'&'.$tid;
    //$sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
       if ($sql_)    { return $server_msg;//'DONE' ;
	                     }  
	   }       
       else{return 'ERROR';}  }
 
}
function level_down_user($uid,$tid,$now)
{
 $tid=htmlspecialchars($tid);//
 $SQL=mysql_query("select * from `records` where id='$tid'") or die (mysql_error());
  mysql_query("commit;");
 if (mysql_num_rows($SQL)>0){       while($r=mysql_fetch_array($SQL)){$p=$r['power'];}
   if ($p<0){
    $sql_=mysql_query("update   `records` set power='0' where id='$tid'") or die (mysql_error());
    $server_msg='ALERT_LEVELDOWN'.'&'.$tid;
    //$sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
       if ($sql_)    { return $server_msg;//'DONE' ;
	   }  
	   }       
       else{return 'ERROR';}  }
 
}

function add_room($uid,$room,$size,$state,$pass,$bg,$factor,$now)
{     $room=htmlspecialchars($room);//
      $size=htmlspecialchars($size);//
      $state=htmlspecialchars($state);//
      $pass=htmlspecialchars($pass);//
      $bg=htmlspecialchars($bg);//
      $factor=htmlspecialchars($factor);//

   // alert msg to kick some user out chat
    mysql_query("commit;");
    $sql=mysql_query("select room from `rooms`  where room='$room'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)>0){return 'ROOM_EXIST';}//room name alreay exist
    else
    {
   //$work= mysql_query("insert into  `rooms` ( room,size,state,roomBG,password) values('$room','$size','$state','$bg','$pass')") or die (mysql_error());// change from current to new
   $work= mysql_query("insert into  `rooms` ( room,size,state,bg,password,factor) values('$room','$size','$state','$bg','$pass','$factor')") or die (mysql_error());// change from current to new
  
 mysql_query("commit;");
    
    
    if ($work)
    {return 'ROOM_CREATED';
    //$msg='ALERT_NEW_ROOM'.'&'.$room.'&'.$size.'&'.$state.'&'.$bg.'&'.$factor;
    //$w=mysql_query("insert into  `chat`  (user,msg,date_time,room) values('SERVER','$msg','$now','#' )") or die (mysql_error());
    
    }
    else{return 'ERR_ROOM_ADDITION';}
    }
}    

function edit_room($uid,$name,$size,$state,$current_pass,$new_pass,$bg,$now)
{     $name=htmlspecialchars($name);//
      $size=htmlspecialchars($size);//
      $state=htmlspecialchars($state);//
      $current_pass=htmlspecialchars($current_pass);//
      $new_pass=htmlspecialchars($new_pass);//
      $bg=htmlspecialchars($bg);

    mysql_query("commit;");
    $sql=mysql_query("select room from `rooms`  where room='$name' and password='$current_pass'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){return 'ROOM_NOT_FOUND_OR_WRONG_PASS';}//
    else
    {
   //$work= mysql_query("update   `rooms` set size='$size',state='$state',roomBG='$bg',password='$new_pass' where room='$room'") or die (mysql_error());// change from current to new
    $work= mysql_query("update   `rooms` set size='$size',state='$state',bg='$bg',password='$new_pass' where room='$room'") or die (mysql_error());// change from current to new
   
   mysql_query("commit;");
    
    
    if ($work)
    {return 'ROOM_EDITED';

    }
    else{return 'ERR_ROOM_EDITION';}
    }
} 
function get_room_users($room)
{     $room=htmlspecialchars($room);//
      $users=array();
    mysql_query("commit;");
    $sql=mysql_query("select id from `records`  where room='$room'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){return 'EMPTY_ROOM';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users);
       return $list;
    }
} 
function move_users($uids,$new_room,$now)//change room for one user or group of users by some member who has the ability to move them
{  
	 $uids=htmlspecialchars($uids);//
      $UIDS=explode('&',$uids);
      $new_room=htmlspecialchars($new_room);//
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
    if  (mysql_num_rows($sql)<1){return 'EMPTY_ROOM';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users);
       return $list;
    }
} 

function get_IPs($ip_state,$ip_group,$room,$ctrl_by_value)             //        x_control related
{    $ip_state=htmlspecialchars($ip_state);//
      $ip_group=htmlspecialchars($ip_group);//
      $room=htmlspecialchars($room);//
      $ctrl_by_value=htmlspecialchars($ctrl_by_value);//by ip or by room
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
    if  (mysql_num_rows($sql)<1){return 'NO_RESULT';}//
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
       return $list;
    }
}

function unban($owner_nick,$ips,$now)
{    $ips=htmlspecialchars($ips);//
    $IPs=explode('&',$ips);      
     $owner_nick=$_POST['owner']  ;$owner_nick=htmlspecialchars($owner_nick);////not used yet
    foreach ($IPs as $ip)                                    {
    $sql_string="select * from `band_list` where ip='$ip'";
    $sql=mysql_query($sql_string) or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){}//return 'NO_RESULT';}
    else
    {
       $sql=mysql_query("delete from `band_list` where ip='$ip' ") or die (mysql_error());
       mysql_query("commit;");
      if($sql){ return 'UNBANNED';  }
      else{return 'CANNOT_UNBAN';}
    }                                                         }
}
function get_users_by_ip($ip)
{    $ip=htmlspecialchars($ip);//
      $users_ids=array()   ;
    $sql=mysql_query("select id from `records`  where ip='$ip'") or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){return 'NO_RESULT';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               array_push($users,$r['id']) ;
              }
       $list=join(',',$users_ids);
       return $list;
    }
}
function Gkick($users_ids,$now)
{   $users_ids=htmlspecialchars($users_ids);//
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
    if ($w){return 'KICKED';}
    else{return 'FAILED';}                                                     }
}
function Gdefreez($users_ids,$now)
{    $users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids);      

    foreach ($Users as $uid)                                    {

 if (user_is_exit($uid)){
    $server_msg='ALERT_UNFREEZE_USER'.'&'.$uid;
    $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
     mysql_query("commit;");
     $sql_3=mysql_query("delete from `frozen_list` where user_id='$uid' ") or die (mysql_error()); 
    mysql_query("commit;");
                          }
    
    if($sql_3){return "UNFREEZED";}else{return "FAILED_UNFREEZE";}  

                                                                   }
}
function Gfreez($action_owner,$users_ids,$now)
{   $users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids);      

    foreach ($Users as $uid)                                    {

 if (user_is_exit($uid)){
    $server_msg='ALERT_FREEZE_USER'.'&'.$uid;
   // $sql_2=mysql_query("insert into `chat` (user,msg,room,date_time)  values('SERVER','$server_msg','#','$now')  ") or die (mysql_error());
    // mysql_query("commit;");

$sql_3=mysql_query("insert into `frozen_list` (user_id,time)  values('$uid','$now')  ") or die (mysql_error());
     mysql_query("commit;");
                          }    
          if($sql_3){return $server_msg;//"FREEZED";
		  }
		  else{return "FAILED";}                }
}
function Gban($users_ids,$owner_nick,$now)
{  $users_ids=htmlspecialchars($users_ids);//
    $Users=explode('&',$users_ids); 
    $owner_nick=htmlspecialchars($owner_nick);//

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
    if($w){return "BANNED";}else{return "FAILED";}                                    
   
                          }                                        }
}

function GOinvisible($uid,$now)
{     $uid=htmlspecialchars($uid);//
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
function GOvisible($uid,$now)
{    $uid=htmlspecialchars($uid);//
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



?>