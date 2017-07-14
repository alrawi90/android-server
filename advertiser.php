<?php

require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$arr=array();

$now=date('Y-m-d H:i:s');
$user_ip = $_SERVER['REMOTE_ADDR']; 

$para=$_POST['para'];
$previous_ad=$_POST['previous_ad'];

function select_ad($pre){
$sql=mysql_query("select ad_name,ad_type,ad_url,link,period from `ads` where state = 'ACTIVE' and ad_name !='$pre' order by time") or die (mysql_error());

while($res=mysql_fetch_array($sql)){
if( mysql_num_rows($sql)>0){
        $name=$res['ad_name'];
        $sql2=mysql_query("update  `ads` set views=views+1 where ad_name='$name' ") or die (mysql_error());
        mysql_query("commit;");
return $res['ad_name'].'&'.$res['ad_type'].'&'.$res['ad_url'].'&'.$res['link'].'&'.$res['period'];
mysql_query("commit;");
break;}
else {return false;mysql_query("commit;");}
}}

$ad=select_ad($previous_ad);
if($ad!=false){

    $sql=mysql_query("update  `ads` set state='EXPIRED' where views >= max_views ") or die (mysql_error());
        mysql_query("commit;");
        echo $ad;
     
              }
 else{echo 'NO_ADS';}             










?>