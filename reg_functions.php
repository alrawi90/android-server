<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$active_users=('');

$now=date('Y-m-d H:i:s');
function get_current_room($user,$factor){
	$room;
	if($factor=="nick"){
	$sql_3=mysql_query("select room from `records` where username ='$user' ") or die (mysql_error());}
	else{$sql_3=mysql_query("select room from `records` where id ='$user' ") or die (mysql_error());}

    while($res3=mysql_fetch_array($sql_3)){
		return $res3['room'];
        mysql_query("commit;");
        break;
                                          }
}
function select_room_1(){
$sql_3=mysql_query("select room from `rooms` where size > num_uesrs and state='general'") or die (mysql_error());

while($res3=mysql_fetch_array($sql_3)){
if( mysql_num_rows($sql_3)>0){
return $res3['room'];
mysql_query("commit;");
break;}
else {return false;mysql_query("commit;");}
}}
///////////////////////////////////////////////////////////////////////////////////////////
function get_latest_inserted_msg_id(){
    $sql = mysql_query("SELECT id FROM `chat` ORDER BY id DESC LIMIT 1"); //get last inserted row of msgs
    while($row = mysql_fetch_array($sql)) { $latest_id = $row["id"]; }
            return $latest_id      ; }

////////////////////////////////////////////////////////////////////////////////////////////////
function check_if_banned($user_ip){
   $SQL=mysql_query("select * from `band_list` where ip ='$user_ip'") or die (mysql_error());//check if user banned
  if (mysql_num_rows($SQL)<1){return false;}
  else{return true;}      }
/////////////////////////////////////////////////////////////////////////////////////////////////
function clear_inactive_users($sql,$user,$now){
    while($res1=mysql_fetch_array($sql ))                             {//while clause start
            $last_seen=$res1['last_seen'];
            $room=$res1['room'];
            $username=$res1['username'];
      if (   strtotime($now)-strtotime($last_seen) >=60){//if user inactive for 1 minute..this mean he is disconncected so delete him
     $work = mysql_query("DELETE  FROM `records` WHERE  last_seen='$last_seen 'and username !='SERVER' ")  or die(mysql_error()); mysql_query("commit;");
      mysql_query("update   `rooms` set num_uesrs=(num_uesrs-1) WHERE room='$room' ") or die(mysql_error()); mysql_query("commit;") ;
       mysql_query("DELETE  FROM `chat` WHERE user='$user' ")  or die(mysql_error());mysql_query("commit;") ;}
                                                                      }//while clause end
                                              mysql_query("commit;"); 

                                }
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function check_if_nick_is_token($user){
            $sql_2=mysql_query("select username from `records` where username='$user'") or die (mysql_error());
            mysql_query("commit;");
            $sql_2_1=mysql_query("select nick from `members` where nick='$user'") or die (mysql_error());
            mysql_query("commit;");
            $res2=mysql_fetch_array($sql_2);$res2_1=mysql_fetch_array($sql_2_1);
            
            if( mysql_num_rows($sql_2)>0 || mysql_num_rows($sql_2_1)>0){return true;}//after deleting inactive(not connected) users.. we check if this nick name token by active user (guest or member)                       
            else{return false;}
                                 }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function get_proper_user_settings($user,$now,$latest_id,$try_regiser_in_room,$user_ip,$power){
            $fg;$bg;$pic;$mark;$pm_state;$wc;$inv;
              $sql=mysql_query("select * from `settings` where value ='$user'") or die (mysql_error());
              // check wheather user has custom or default settings..to assign proper settings
              mysql_query("commit;");
              if (mysql_num_rows($sql)>0){
                while($r=mysql_fetch_array($sql)){
                  $settings=$r['fg'].','.$r['bg'].','.$r['pic'].','.$r['mark'].'&'.$r['pm'].'&'.$power.'&'.$r['wc'].'&'.$r['invisible'];
                  $fg=$r['fg'];$bg=$r['bg'];$pic=$r['pic'];$mark=$r['mark'];$pm_state=$r['pm'];$wc=$r['wc'];$inv=$r['invisible'];
				  //break;
                                                 }//this membber has custom settings
                        register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$pm_state,$wc,$user_ip,$power,$inv);
                        
                                          }
              else {$sql=mysql_query("select * from `settings` where value ='default'") or die (mysql_error());
                      mysql_query("commit;");
                    while($r=mysql_fetch_array($sql)){
                    $settings='DEFAULT'.','.$r['fg'].','.$r['bg'].','.$r['pic'].','.$r['mark'].'&'.$r['pm'].'&'.$power.'$'.$r['wc'];
                    $fg=$r['fg'];$bg=$r['bg'];$pic=$r['pic'];$mark=$r['mark'];$pm_state=$r['pm'];$wc=$r['wc'];$inv=$r['invisible'];
                                                     }               
           register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$pm_state,$wc,$user_ip,$power,$inv);
		                       } // this membber has default settings    

              //insert_server_messges($user,$fg,$bg,$pic,$mark,$pm_state,$try_regiser_in_room,$now,$power,$wc,$inv);
			$id=Get_Id($user);
             make_pm_dir($id);  
           return $settings;   }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$pm_state,$wc,$user_ip,$power,$inv)
                 {
           $x=mysql_query("INSERT INTO `records` (username, last_seen,last_msg_id,room,fg,bg,pic,mark,pm,wc,ip,power,invisible)  VALUES('$user','$now','$latest_id','$try_regiser_in_room','$fg','$bg','$pic','$mark','$pm_state','$wc','$user_ip','$power','$inv')") or die (mysql_error());
            mysql_query("commit;");//start registring steps
            //mysql_query("update   `rooms` set num_uesrs=(num_uesrs+1) WHERE room='$try_regiser_in_room' ")  or die(mysql_error());
            set_nou_room($try_regiser_in_room);
			mysql_query("commit;");//register in the selected room by server
			mysql_query("INSERT INTO `socket_record` (ClientSocket) VALUES('$user')") or die (mysql_error());

                   }


////////////////////////////////////////////////////////////////////////
function get_privileges($power){
              $sql_=mysql_query("select *  from `privileges` where power ='$power'") or die (mysql_error());
              // select privileges related to guests
              while ($r=mysql_fetch_array($sql_)){
                $priv=$r['current_room']."&".$r['ignore']."&".$r['kick']."&".$r['forebide']."&".$r['set_fg-color']."&".$r['set_bg-color'].
              "&".$r['freeze']."&".$r['get_ip']."&".$r['get_country']."&".$r['set_marks']."&".$r['set_nick']."&".$r['set_rooms']."&".$r['invisible']
              ."&".$r['set_write_color']."&".$r['move_users']."&".$r['general_msg']."&".$r['set_vip']."&".$r['edit_rooms']."&".$r['Gactions'].
              "&".$r['see_invisible']."&".$r['set_invisible'];}
              mysql_query("commit;");
           return $priv;
                         }
/////////////////////////////////////////////////////////////////////////////////////////////////////
function get_users_this_room($try_regiser_in_room){
  $users_thisROOM=array();
    $sql_3=mysql_query("select username,fg,bg,pic,mark ,pm,power,wc,invisible from `records` where room='$try_regiser_in_room' and username !='SERVER'") or die (mysql_error());//select users this room
    mysql_query("commit;");//get other users in the same room
      while($res_3=mysql_fetch_array($sql_3)){array_push($users_thisROOM,$res_3['username'].'&'.$res_3['fg'].'&'.$res_3['bg'].'&'.$res_3['pic'].'&'.$res_3['mark'].'&'.$res_3['pm'].'&'.$res_3['power'].'&'.$res_3['wc'].$res_3['invisible']);}//excract users from complex array to simple one
       $_users_thisROOM=join(',',$users_thisROOM) ;//convert simple array into string mode
       return $_users_thisROOM;}
/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////
function get_all_users_chat(){
  $allUSERS=array();
    $sql_4=mysql_query("select id,username,fg,bg,pic,mark,pm,power,wc,invisible ,room from `records` where username !='SERVER'") or die (mysql_error()); // select all users in the chat
    mysql_query("commit;");// get all users in the chat
         while($res_4=mysql_fetch_array($sql_4)){array_push($allUSERS,$res_4['username'].'&'.$res_4['fg'].'&'.$res_4['bg'].'&'.$res_4['pic'].'&'.$res_4['mark'].'&'.$res_4['pm'].'&'.$res_4['power'].'&'.$res_4['wc'].'&'.$res_4['invisible'].'&'.$res_4['id'].'&'.$res_4['room']);}//excract users from complex array to simple one
         $_allUSERS=join(',',$allUSERS) ;//convert simple array into string mode
         return $_allUSERS   ;
                               }
/////////////////////////////////////////////////////////////////////////////////////////////////
function Get_Id($u){
    $q=mysql_query("select id from  `records` where username='$u'") or die (mysql_error());
    mysql_query("commit;");//
    while($r=mysql_fetch_array($q)){return $r['id'];break;}
                     }
function make_pm_dir($dir)   {
	 if( ! file_exists($dir)){mkdir('src/temp/'.$dir, 0755, true);}
	}
function get_reg_TIME($u)   {
    
    $q=mysql_query("select last_seen from  `records` where username='$u'") or die (mysql_error());
    mysql_query("commit;");//
    while($r=mysql_fetch_array($q)){return $r['last_seen'];}
                     
                               }

function insert_server_messges($user,$fg,$bg,$pic,$mark,$pm_state,$try_regiser_in_room,$now,$power,$wc,$inv){ //daprecated
                      $id=Get_Id($user);
                      make_pm_dir($id);
                      
                      $server_msg_settings='ALERT_USER_SETTENGS'.'&'.$user.'&'.$fg.'&'.$bg.'&'.$pic.'&'.$mark.'&'.$pm_state.'&'.$power.'&'.$wc.'&'.$inv.'&'.$id.'&'.$try_regiser_in_room;
                      mysql_query("INSERT INTO `chat` (user,date_time, msg,room)  VALUES('SERVER','$now','$server_msg_settings','#')") or die (mysql_error());
                      //msg from server notify all users that this user  has this settings  
                     mysql_query("commit;");  
                       
                      $server_msg='ALERT_ENTER_CHAT&'.$user."&".$id;
                      mysql_query("INSERT INTO `chat` (user,date_time, msg,room)  VALUES('SERVER','$now','$server_msg','#')")or die (mysql_error());
                      //gloabal msg from server notify users to append new user to thier allusers gloabal var(even he is not in the current room).// marked by # symble as room name
                      mysql_query("commit;");
                   
                      mysql_query("INSERT INTO `chat` (user,date_time, msg,room)  VALUES('SERVER','$now','$server_msg','$try_regiser_in_room')") or die (mysql_error());
                      //msg from server notify current room users that this user  entered chat 
                      mysql_query("commit;");                
                                       

                                }

//  new/modified functions:
function ALERT_USER_SETTENGS($uid){
     $sql = mysql_query("SELECT * FROM `records` where id='$uid'") or die (mysql_error());
     mysql_query("commit;");
    if(mysql_num_rows($sql)>0){
        while($row = mysql_fetch_array($sql)) { 
                                            $fg=$row['fg'];$bg=$row['bg'];$pic=$row['pic'];$mark=$row['mark'];
                                           $pm_state=$row['pm'];$power=$row['power'];$wc=$row['wc'];
                                            $id=$row['id'];$room=$row['room'];$inv=$row['invisible'];$nick=$row['username'];


 // make_pm_dir($id);                                          }
  return 'ALERT_USER_SETTENGS'.'&'.$nick.'&'.$fg.'&'.$bg.'&'.$pic.'&'.$mark.'&'.$pm_state.'&'.$power.'&'.$wc.'&'.$inv.'&'.$id.'&'.$room;	
	}
		 }
    else{return "USER_NOT_EXIT";} 
}
 function ALERT_ENTER_CHAT($uid){
                      $nick=get_nick($uid);
                      //make_pm_dir($id);
                      return 'ALERT_ENTER_CHAT&'.$nick."&".$uid;
                                }

function get_users_of_room($room){
  $users_thisROOM=array();
    $sql_3=mysql_query("select id from `records` where room='$room' and username !='SERVER'") or die (mysql_error());//select users this room
    mysql_query("commit;");//get other users in the same room
      while($res_3=mysql_fetch_array($sql_3)){array_push($users_thisROOM,$res_3['id']);}
//excract users from complex array to simple one
       $_users_thisROOM=join(',',$users_thisROOM) ;//convert simple array into string mode
       return $_users_thisROOM;
	                             }
function add_CS_id($uid,$cid){
	$sql=mysql_query("update  `records` set CS_ID='$cid' where id='$uid'") or die (mysql_error());
    if($sql){return true;}else{return false;}
}
function getRoomBy_CS_id($cid){
	$result;
	$sql=mysql_query("select room  from `records` where CS_ID='$cid'  ") or die (mysql_error());
	$num_rows=mysql_num_rows($sql);
	if($num_rows>0){
	while($res=mysql_fetch_array($sql)){$result=$res['room'];}
	}
	else{$result= "USER_NOT_EXIT";}
	return $result;
     
}
function get_UidBy_CS_id($cid){
		$result;
	$sql=mysql_query("select id  from `records` where CS_ID='$cid'  ") or die (mysql_error());
	$num_rows=mysql_num_rows($sql);
	if($num_rows>0){
	while($res=mysql_fetch_array($sql)){$result=$res['id'];  }	
	               }
	else{$result= "USER_NOT_EXIT";}
	return $result;
}
function get_PowerBy_CS_id($cid){
	$sql=mysql_query("select power  from `records` where CS_ID='$cid'  ") or die (mysql_error());
	$num_rows=mysql_num_rows($sql);
	if($num_rows>0){
	while($res=mysql_fetch_array($sql)){$result=$res['power'];  }	
	               }
	else{$result= "USER_NOT_EXIT";}
	return $result;
}
function delete_user($cid){
	$sql=mysql_query("delete  from `records` where CS_ID='$cid' ") or die (mysql_error());
    if($sql){return true;}else{return false;}
} 
function get_CSid_by_uid($uid){
			$result;
	$sql=mysql_query("select CS_ID  from `records` where id='$uid'  ") or die (mysql_error());
	$num_rows=mysql_num_rows($sql);
	if($num_rows>0){
	while($res=mysql_fetch_array($sql)){$result=$res['CS_ID'];  }	
	               }
	else{$result= "USER_NOT_EXIT";}
	return $result;
}

function clear_chat(){
	$sql=mysql_query("delete  from `records` where username !='SERVER' ") or die (mysql_error());
    $sql=mysql_query("update  `members` set session='inactive' ") or die (mysql_error());
                 
					 }
function get_room_pass($room){
    $sql=mysql_query("select password from  `rooms` where room='$room' ") or die (mysql_error());
      	while($res=mysql_fetch_array($sql)){$result=$res['password'];  }	
        return $result;
					 }
						 
?>