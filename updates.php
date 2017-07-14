<?
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$now=date('Y-m-d H:i:s');
$user_ip = $_SERVER['REMOTE_ADDR']; 
$para=$_POST['para'];

///////////////////////////////////////////////////////////////////////////////////////////
function select_contents_update($current_chat_date)   {
    $id_array=array();
    $sql = mysql_query("SELECT date, id FROM `chat_updates` where date !='$current_chat_date'"); //get all lines dose not inserted on $current_chat_date
    mysql_query("commit;");
    while($row = mysql_fetch_array($sql)) 
     {
     $selected_date = $row['date']; 
     $id = $row['id'];
     if ( strtotime($selected_date)-strtotime($current_chat_date) >0){array_push($id_array,$id);}
     }
            return $id_array     ; }

////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
function read_contents_update($ids)   {
    $updates=array();
    foreach ($ids as $id)
     {
         $sql = mysql_query("SELECT * FROM `chat_updates` where id ='$id'"); //read all selected lines
         mysql_query("commit;");
       while($row = mysql_fetch_array($sql)) 
         {
          $a = $row['action']; 
          $p = $row['pic'];    if (strlen($p)==0){$p='None';}
         $i = $row['icon'];    if (strlen($i)==0){$i='None';}
         $w = $row['wlc'];     if (strlen($w)==0){$w='None';}
         $e = $row['emo'];     if (strlen($e)==0){$e='None';}
         
         array_push($updates,$a.'&'.$p.'&'.$i.'&'.$w.'&'.$e);
          }
     }
     //////////////
     if (count($updates)>0){
        $sql = mysql_query("SELECT distinct date FROM `chat_updates` order by date") ;
        while($row = mysql_fetch_array($sql)){$date=$row['date'];}
        array_push($updates,'&&'.$date);
                            }
     ///////////////   
           if (count($updates)>0  ){
            $Updates=join(',', $updates);
            return $Updates   ;        }
            else{return 'UP_TO_DATE';}
                                   ; }
//array_sum()
//strlen()
/////////////////////////////////////////////////////////////////////////////////
function check_version($current_version)
{   $arr1=split('.',$current_version,2);
    $sql = mysql_query("SELECT DISTINCT version FROM `ver_updates` order by date "); //get all lines dose not inserted on $current_chat_date
    mysql_query("commit;");
    while($row = mysql_fetch_array($sql)) 
     {
     $selected_version = $row['version'];
     }
     $arr2=split('.',$selected_version,2);
     
        if ($arr1[0]==$arr2[0] )
                {
               if ($arr1[1]==$arr2[1] )
                        {
                        if ($arr1[2]==$arr2[2] )
                                {
                                return 0;  //current version is up to date   
                                }
                        else{return 1;}        
                        }
                else{return 1;}        
                }
        else{return 1;} //there is a new version to download       
        
    
}


////////////////////////////////////////////////////////////////////////////////////////////////
if ($para=='ver')
    {   $ver=$_POST['v'];
        echo check_version($ver);
    }
else
{
    $date=$_POST['date'];
    echo read_contents_update(select_contents_update($date));
}
?>