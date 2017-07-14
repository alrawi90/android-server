<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$allUSERS=array();
$users_thisROOM=array();
$now=date('Y-m-d H:i:s');
$user_ip = $_SERVER['REMOTE_ADDR']; 
$fg="";$bg="";$pic="";$mark="";$settings="";$pm_state="";

$req=$_POST['para'];

$user=$_POST['nick'];
$power=0;
function select_room(){
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
            
              $sql=mysql_query("select * from `settings` where value ='$user'") or die (mysql_error());
              // check wheather user has custom or default settings..to assign proper settings
              mysql_query("commit;");
              if (mysql_num_rows($sql)>0){
                while($r=mysql_fetch_array($sql)){
                  $settings=$r['fg'].','.$r['bg'].','.$r['pic'].','.$r['mark'].'&'.$r['pm'].'&'.$power.'&'.$r['wc'].'&'.$r['invisible'];;
                  $fg=$r['fg'];$bg=$r['bg'];$pic=$r['pic'];$mark=$r['mark'];$pm_state=$r['pm'];$wc=$r['wc'];$inv=$r['invisible'];
                                                 }//this membber has custom settings
                                          }
              else {$sql=mysql_query("select * from `settings` where value ='default'") or die (mysql_error());
                      mysql_query("commit;");
                    while($r=mysql_fetch_array($sql)){
                    $settings='DEFAULT'.','.$r['fg'].','.$r['bg'].','.$r['pic'].','.$r['mark'].'&'.$r['pm'].'&'.$power.'$'.$r['wc'];
                    $fg=$r['fg'];$bg=$r['bg'];$pic=$r['pic'];$mark=$r['mark'];$pm_state=$r['pm'];$wc=$r['wc'];$inv=$r['invisible'];
                                                     }               
                    } // this membber has default settings    
           register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$pm_state,$wc,$user_ip,$power,$inv);
              insert_server_messges($user,$fg,$bg,$pic,$mark,$pm_state,$try_regiser_in_room,$now,$power,$wc,$inv);
           return $settings;   }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$pm_state,$wc,$user_ip,$power,$inv)
                 {
           mysql_query("INSERT INTO `records` (username, last_seen,last_msg_id,room,fg,bg,pic,mark,pm,wc,ip,power,invisible)  VALUES('$user','$now','$latest_id','$try_regiser_in_room','$fg','$bg','$pic','$mark','$pm_state','$wc','$user_ip','$power','$inv')") or die (mysql_error());
            mysql_query("commit;");//start registring steps
            mysql_query("update   `rooms` set num_uesrs=(num_uesrs+1) WHERE room='$try_regiser_in_room' ")  or die(mysql_error());
            mysql_query("commit;");//register in the selected room by server
                   }


////////////////////////////////////////////////////////////////////////
function get_privileges(){
              $sql_=mysql_query("select *  from `privileges` where power ='0'") or die (mysql_error());
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
    while($r=mysql_fetch_array($q)){return $r['id'];}
                     }
function make_pm_dir($dir)   {mkdir('src/temp/'.$dir, 0755, true);}
function get_reg_TIME($u)   {
    
    $q=mysql_query("select last_seen from  `records` where username='$u'") or die (mysql_error());
    mysql_query("commit;");//
    while($r=mysql_fetch_array($q)){return $r['last_seen'];}
                     
                               }

function insert_server_messges($user,$fg,$bg,$pic,$mark,$pm_state,$try_regiser_in_room,$now,$power,$wc,$inv){
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
///////////////////////////////////////////////////////////////////////////////////////////
if ($req=="startup")
  {
   $latest_id =get_latest_inserted_msg_id();
   
   $banned_check=check_if_banned($user_ip);
  if ($banned_check==false)
     {
    
    
      $sql_1=mysql_query("select * from `records` where username !='SERVER'") or die (mysql_error());
      mysql_query("commit;");
    if(mysql_num_rows($sql_1)>0)
    {
        //clear_inactive_users($sql_1,$user,$now); 
        $token_nick=check_if_nick_is_token($user);
        if ($token_nick==true) {echo "ERR_0";}
        else
        {  // room code to add here               // if it is not token then it is ok and we can go to find a place on some room
                $try_regiser_in_room=select_room();        // select room or say all rooms are full..try again later..
            if( $try_regiser_in_room!=false){
                //$_users_thisROOM='';
                //$_allUSERS='';
                $settings=get_proper_user_settings($user,$now,$latest_id,$try_regiser_in_room,$user_ip,$power);
                
                $priv=get_privileges();
                $_users_thisROOM=get_users_this_room($try_regiser_in_room);
                $_allUSERS=get_all_users_chat();
                
                $reg_time=get_reg_TIME($user);
                $frozen=get_frozen_users();
                echo $try_regiser_in_room."&&".$_users_thisROOM."&&".$_allUSERS."&&"."success"."&&".$settings."&&".$priv."&&".$reg_time."&&".$frozen;//
                                    
                                            }//sucessfully registered and return room name
             else{echo 'ERR_3' ;}// if select_room()return false means no empty room  failed to register   
        } //full register user attempt

    }        
  else //if no body in the chat then just register 
    {
          
            $sql_2_1=mysql_query("select nick from `members` where nick='$user'") or die (mysql_error());
            mysql_query("commit;");
            $res2_1=mysql_fetch_array($sql_2_1);
            
    if( mysql_num_rows($sql_2_1)>0){echo "ERR_0";}//we check if this nick name token by  not logged in member                        
    else{
          
          $try_regiser_in_room=select_room();
            if( $try_regiser_in_room!=false)
            {
                $settings=get_proper_user_settings($user,$now,$latest_id,$try_regiser_in_room,$user_ip,$power);
               // register($user,$now,$latest_id,$try_regiser_in_room,$fg,$bg,$pic,$mark,$user_ip);
                $priv=get_privileges();
                $_users_thisROOM=get_users_this_room($try_regiser_in_room);
                $_allUSERS=get_all_users_chat();
                $reg_time=get_reg_TIME($user);
                $frozn=get_frozen_users();
                echo $try_regiser_in_room."&&".$_users_thisROOM."&&".$_allUSERS."&&"."success"."&&".$settings."&&".$priv."&&".$reg_time."&&".$frozen;//
                //insert_server_messges();  
             /////////////////////////////////////////////////////////////////////////////// 
                      
            }//sucessfully registered and return room name
            else{echo 'ERR_3' ;}// if select_room()return false means no empty room  failed to register   //but here this is not possible //no one in the chat
        } //full register user attempt

                      
    }
  }
  else{ echo 'ERR_4' ;}    //user has been banned 
         
  }




mysql_close(); 

?>
