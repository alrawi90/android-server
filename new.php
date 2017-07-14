<?php 
// place your DB_host, UserName, Password, and DB_Name below where shown
mysql_connect("localhost","root","") or die ("Could not connect.");
mysql_select_db("android_db") or die ("no database");mysql_query("set chats utf8");mysql_query("set chat utf8");mysql_query('SET CHARACTER SET utf8');

function get_nick($id)
{  $nick="";
   $sql_1=mysql_query("select username from `records` where id='$id'") or die (mysql_error()); 
   while($res_1=mysql_fetch_array($sql_1))
   {  $nick=$res_1['username'];   }
   mysql_query("commit;");
  return $nick;
                 
}
//------------------------------------------------------------------------------
function user_is_exit($id)
{  
   $sql_1=mysql_query("select username from `records` where id='$id'") or die (mysql_error()); 
   $res=mysql_num_rows($sql_1);
   mysql_query("commit;");
  if($res>0){return true;}
   else{return false;}
                 
}
 
//-------------------------new solution to prevent misscalculation the real number of user each room------------------
function set_nou_room($room)
{

    $SQL=mysql_query("select id from `records`  where room='$room'") or die (mysql_error());// 
    $nou=mysql_num_rows($SQL);
    mysql_query("commit;");
    mysql_query("update  `rooms` set num_uesrs= '$nou' where room='$room'") or die (mysql_error());//
    mysql_query("commit;");   

}
//-----------------------------
function get_banned_users($ip)
{   $_nicks=array();
    $sql_string="select user_id,user_nick from `band_list` where ip='$ip'";
    $sql=mysql_query($sql_string) or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){return 'NO_RESULT';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               $info=$r['user_nick'] ;
               array_push($_nicks,$info) ;
              }
      $users=join('&',$_nicks);
      }
    return $ip.'&&'.$users;
}
//---------------------
function get_allowed_users($ip,$ip_group,$ctrl_by,$room)
{   $_nicks=array();
  if($ctrl_by=="By IP address"){
    if($ip_group=="show all"){$sql_string="select id from `records` where ip='$ip'";}
    else if($ip_group=="show frozen"){$sql_string="select id from `records` where ip='$ip' and id in (select user_id from `frozen_list`)";}
    else{$sql_string="select id from `records` where ip='$ip' and id not in (select user_id from `frozen_list`)";}//unfrozen
                  }
  else{//By Room location
                 
    if($ip_group=="show all"){$sql_string="select id from `records` where ip='$ip' and room='$room'";}
    else if($ip_group=="show frozen"){$sql_string="select id from `records` where ip='$ip' and id in (select user_id from `frozen_list`) and room='$room'";}
    else{$sql_string="select id from `records` where ip='$ip' and id not in (select user_id from `frozen_list`) and room='$room'";}//unfrozen
       }
    $sql=mysql_query($sql_string) or die (mysql_error());
    mysql_query("commit;");
    if  (mysql_num_rows($sql)<1){return 'NO_RESULT';}//
    else
    {
       while ($r=mysql_fetch_array($sql))
              {
               $info=$r['id'] ;
               array_push($_nicks,$info) ;
              }
      $users=join('&',$_nicks);
      }
    return $ip.'&&'.$users;
}
//---------------------
function get_frozen_users()
{  $frozen_users=array();
    $frozen_users[0]="0";//to prevent empty array that crash app on register_position class
    $SQL=mysql_query("select user_id from `frozen_list` ") or die (mysql_error());// 
    $nou=mysql_num_rows($SQL);
    if($nou >0){
         while($res_1=mysql_fetch_array($SQL))
       {  $uid=$res_1['user_id'];
        array_push($frozen_users,$uid);
        }
    mysql_query("commit;");
$result=join(',',$frozen_users);
return $result;  

}
}
function get_session($u){
      $SQL=mysql_query("select * from `members` where username='$u' ") or die (mysql_error());//
          mysql_query("commit;");
      //$num_rows=mysql_num_rows($SQL)   ;
     // if($num_rows>0){
      while($res_1=mysql_fetch_array($SQL)){$s=$res_1['session'];}
      return $s;      //}else{return "inactive";}
                       }
function set_session($u,$s){
      $SQL=mysql_query("update `members` set session='$s' where username='$u' ") or die (mysql_error());//
          mysql_query("commit;");
      if($SQL){return true;}else{return false;}
      }

?>