<?php
// prevent the server from timing out
set_time_limit(0);

// include the web sockets server script (the server is started at the far bottom of this file)
require 'class.PHPWebSocket.php';
//require 'index.php';
require 'functions.php';
//mysql_select_db("android_db") or die ("no database");
//mysql_query("set chats utf8");mysql_query("set chat utf8");mysql_query('SET CHARACTER SET utf8');
clear_chat();
// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {
	global $Server;	
	$ip = long2ip( $Server->wsClients[$clientID][6] );
	fetch_user_request($Server,$clientID,$message,$ip);
//  perform_msg_to_clients($clientID,$ip, $message,$messageLength);



}
function perform_msg_to_clients($clientID,$ip, $message,$messageLength){	
// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}

	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) == 1 )
		$Server->wsSend($clientID, "There isn't anyone else in the room, but I'll still listen to you. --Your Trusty Server");
	else
		//Send the message to everyone but the person who said it
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID )
				$Server->wsSend($id, "Visitor $clientID ($ip) said \"$message\"");}
// when a client connects
function redirect_to_user($Server,$clientID,$msg){//request call_back
			
				$Server->wsSend($clientID, $msg);
			}

function declear_to_target($Server,$tcid,$mycid,$msg){//pm_msg
			
				$Server->wsSend($tcid, $msg);
				//$Server->wsSend($mycid, $msg);
			}
function declear_to_users($Server,$clientID,$msg){//send to all but me
			foreach ( $Server->wsClients as $Cid => $client )
			if ( $Cid != $clientID )
				$Server->wsSend($Cid, $msg);
			
}
function declear_to_AllUsers($Server,$clientID,$msg){//send to every connected client
	foreach ( $Server->wsClients as $Cid => $client )
			
			{$Server->wsSend($Cid, $msg);}
}

function wsOnOpen($clientID)
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has connected." );

	//Send a join notice to everyone but the person who joined
	foreach ( $Server->wsClients as $id => $client )
		if ( $id != $clientID )
			$Server->wsSend($id, "Visitor $clientID ($ip) has joined the room.");
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );
	$uid=get_UidBy_CS_id($clientID);$nick=get_nick($uid);
	 $power=get_PowerBy_CS_id($clientID);
	$no_room="#";
	if($uid!=="USER_NOT_EXIT" ){
	$ar= array('header' => 'SERVER_ALERT','data' =>"ALERT_EXIT_CHAT&".$uid ,'room' =>$no_room);
	$x= json_encode($ar);
	declear_to_AllUsers($Server,$clientID,$x);}
	if($power>0){mysql_query("update `members` set session='inactive' where nick='$nick' ")or die (mysql_error());}
    delete_user($clientID);
	$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
	$a=json_encode($arr);
	declear_to_AllUsers($Server,$clientID,$a);
	//$Server->log( "$ip ($clientID) has disconnected." );

	//Send a user left notice to everyone in the room
	//foreach ( $Server->wsClients as $id => $client )
	//	$Server->wsSend($id, "Visitor $clientID ($ip) has left the room.");
}


function fetch_user_request($Server,$clientID,$data,$ip){
	$now=date('Y-m-d H:i:s');	
	$data=explode(',,,', $data);
	$request=$data[0];
	$no_room="#";
switch ($request){
	case "startup":
	    
		$res=startup($data[1],$ip,$data[2])	;
		add_CS_id(Get_Id($data[1]),$clientID);
		$arr1 = array('header' => $request,'data' => $res);
		$a=json_encode($arr1);
		redirect_to_user($Server,$clientID,$a);
		redirect_to_user($Server,$clientID,"WEB_USERS&".Get_Id($data[1])."&".get_current_room($data[1],"nick"));//just for test for web clients//not procces by android app
		//
		$arr2 = array('header' => 'SERVER_ALERT' ,'data' => ALERT_USER_SETTENGS(Get_Id($data[1])),'room' =>$no_room );		
		$c=json_encode($arr2);
		declear_to_users($Server,$clientID,$c);
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_users($Server,$clientID,$x);
		//
        break;
    case "get_rooms_names":
	     $room=get_current_room($data[1],"id");		
	    $arr1 = array('header' => $request,'data' => get_rooms_names());
		$a=json_encode($arr1);
	    redirect_to_user($Server,$clientID,$a);
		//
		$arr2 = array('header' => 'SERVER_ALERT','data' =>ALERT_ENTER_CHAT($data[1]) ,'room' =>$room);
		$b= json_encode($arr2);
		declear_to_AllUsers($Server,$clientID,$b);

		//sleep(300);
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);
		//
        break; 
	case "kick_user":	
		$arr = array('header' => 'SERVER_ALERT','data' => kick_user($data[1],$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);		
        break; 
	case "band_user":	
		$arr = array('header' => 'SERVER_ALERT','data' => band_user(get_nick($data[1]),$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);		
        break; 
	case "unband_user":	
		$arr = array('header' => 'SERVER_ALERT','data' => unband_user(get_nick($data[1]),$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  
        break; 		
	case "change_room":	
	    $result = json_decode(change_room($data[1],$data[2],$data[3],$data[4],$data[5],$now));
		$ALERT_LEAVE_ROOM =$result->{'msg1'};$ALERT_ENTER_ROOM =$result->{'msg2'};$USERS_ids =$result->{'USERS_ids'};
		$arr3 = array('header' => 'change_room','data' => $USERS_ids,'current_room' => $data[3],'new_room' => $data[2]);//call_back
		$c=json_encode($arr3);
	    redirect_to_user($Server,$clientID,$c); 
		if($ALERT_LEAVE_ROOM !=='null' && $ALERT_ENTER_ROOM !=='null' )
		{
	     $arr1 = array('header' => 'SERVER_ALERT','data' => $ALERT_LEAVE_ROOM,'room' =>$data[3]);
		 $a=json_encode($arr1);
	     declear_to_AllUsers($Server,$clientID,$a); 

	     $arr2 = array('header' => 'SERVER_ALERT','data' => $ALERT_ENTER_ROOM,'room' =>$data[2]);
		 $b=json_encode($arr2);
	     declear_to_AllUsers($Server,$clientID,$b); 
		}		                                                    
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);		
        break; 
	case "send_new_msg":	
	     $room=get_current_room($data[2],"id");
	    $arr = array('header' => 'send_new_msg','data' => $data[1],'uid' => $data[2],'room' =>$room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);
        break; 
	case "send_pm_msg":	//text
	     $cid=get_CSid_by_uid($data[3]);
	    $arr = array('header' => 'new_pm_msg','data' => $data[1],'SenderID' => $data[2],'RecieverID' => $data[3]);
		$x=json_encode($arr);
	    declear_to_target($Server,$cid,$clientID,$x);
        break; 	
	case "send_pm_voice":	//audio
	     $cid=get_CSid_by_uid($data[3]);
	    $arr = array('header' => 'new_pm_voice','data' => $data[1],'SenderID' => $data[2],'RecieverID' => $data[3],'duration'=> $data[4]);
		$x=json_encode($arr);
	    declear_to_target($Server,$cid,$clientID,$x);
        break; 	
	case "send_pm_picture":	//pic 
	     $cid=get_CSid_by_uid($data[3]);
	    $arr = array('header' => 'new_pm_picture','data' => $data[1],'SenderID' => $data[2],'RecieverID' => $data[3]);
		$x=json_encode($arr);
	    declear_to_target($Server,$cid,$clientID,$x);
        break; 				
		
	case "send_pm_video":	//video 
	     $cid=get_CSid_by_uid($data[3]);
	    $arr = array('header' => 'new_pm_video','data' => $data[1],'SenderID' => $data[2],'RecieverID' => $data[3]);
		$x=json_encode($arr);
	    declear_to_target($Server,$cid,$clientID,$x);
        break; 				
    case "freeze_user":
		$arr = array('header' => 'SERVER_ALERT','data' => freeze_user($data[1],$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);		
        break;         
    case "unfreeze_user":
		$arr = array('header' => 'SERVER_ALERT','data' => unfreeze_user($data[1],$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);	
         break;
    case "level_up_user":
       	$arr = array('header' => 'SERVER_ALERT','data' => level_up_user($data[1],$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);	        
        break;
    case "level_down_user":
       	$arr = array('header' => 'SERVER_ALERT','data' => level_down_user($data[1],$data[2],$now),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);	        
        break;
    case "ignore_user":
       	$arr = array('header' => $request,'data' => ignore_user($data[1],$data[2]),'tid' =>$data[2]);
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);	        
        break;
    case "release_user":
       	$arr = array('header' =>  $request,'data' => release_user($data[1],$data[2]),'tid' =>$data[2]);
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  
		$arr = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
	    declear_to_AllUsers($Server,$clientID,$x);	        
        break;
    case "get_ips":
       	$arr = array('header' =>  $request,'data' => get_IPs($data[2],$data[3],$data[5],$data[4]),"uid"=>$data[1]);
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);       
        break;
	case "G_freeze":	
	  $t_ids=htmlspecialchars($data[2]);//
	  $Users=explode('&',$t_ids);      
      foreach ($Users as $tid)                    {
		$arr = array('header' => 'SERVER_ALERT','data' => freeze_user($data[1],$tid,$now));
		$x=json_encode($arr);
      declear_to_AllUsers($Server,$clientID,$x);  }
	  $arr = array('header' =>  $request,'data' => "FREEZED","uid"=>$data[1]);
	  $x=json_encode($arr);
	  redirect_to_user($Server,$clientID,$x); 
	  $arr1 = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
      $x=json_encode($arr1);
	  declear_to_AllUsers($Server,$clientID,$x);
      break; 		
	case "G_unfreeze":	
	   $t_ids=htmlspecialchars($data[2]);//
	   $Users=explode('&',$t_ids);      
       foreach ($Users as $tid)                    {
		$arr = array('header' => 'SERVER_ALERT','data' => unfreeze_user($data[1],$tid,$now));
		$x=json_encode($arr);
        declear_to_AllUsers($Server,$clientID,$x);  }
	   $arr = array('header' =>  $request,'data' => "UNFREEZED","uid"=>$data[1]);
	   $x=json_encode($arr);
	   redirect_to_user($Server,$clientID,$x);
	   $arr1 = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
	   $x=json_encode($arr1);
	   declear_to_AllUsers($Server,$clientID,$x);
       break; 		
	case "G_kick":	
	   $t_ids=htmlspecialchars($data[2]);//
	   $Users=explode('&',$t_ids);      
       foreach ($Users as $tid)                    {
		$arr = array('header' => 'SERVER_ALERT','data' => kick_user($data[1],$tid,$now));
		$x=json_encode($arr);
        declear_to_AllUsers($Server,$clientID,$x);  }
	   $arr = array('header' =>  $request,'data' => "KICKED","uid"=>$data[1]);
	   $x=json_encode($arr);
	   redirect_to_user($Server,$clientID,$x);
	   $arr1 = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
	   $x=json_encode($arr1);
	   declear_to_AllUsers($Server,$clientID,$x);
       break; 	
//user_info_class requests
	case "find_room":
       	$arr = array('header' =>  $request,'data' => find_room($data[1]));
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  	        
        break; 
		
    case "find_ip":
       	$arr = array('header' =>  $request,'data' => find_ip($data[1]));
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  	        
        break; 	
	case "find_location":
       	$arr = array('header' =>  $request,'data' => find_location($data[1]));
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  	        
        break; 
//show_user_properties class requests
	case "new_settings":
	  $uid=$data[1];$uid=htmlspecialchars($uid);//
	  $tid=$data[2];$tid=htmlspecialchars($tid);//
	  $tNick=$data[3];$tNick=htmlspecialchars($tNick);//
	  $fg=$data[4];$fg=htmlspecialchars($fg);//
	  $bg=$data[5];$bg=htmlspecialchars($bg);//
	  $pic=$data[6];$pic=htmlspecialchars($pic);//
	  $mark=$data[7];$mark=htmlspecialchars($mark);//
	  $pm_st=$data[8];$pm_st=htmlspecialchars($pm_st);//
	  $wc=$data[9];$wc=htmlspecialchars($wc);//
	  $inv=$data[10];$inv=htmlspecialchars($inv);//
      $result=new_settings($uid,$tid,$tNick,$fg,$bg,$mark,$pic,$wc,$pm_st,$inv,$now);
       	$arr = array('header' =>  'SERVER_ALERT','data' => $result,'room'=>$no_room);
		$a=json_encode($arr);
		declear_to_AllUsers($Server,$clientID,$a);
	   $arr2 = array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
	   $b=json_encode($arr2);
	   declear_to_AllUsers($Server,$clientID,$b);		
	  //$arr1 = array('header' =>  $request,'data' => "SUCCESS");
	  //$x=json_encode($arr1);
	  //redirect_to_user($Server,$clientID,$x);
	
        break; 				
//move_users class requests		
	case "get_room_users_android":
       	$arr = array('header' =>  $request,'data' => get_room_users_android($data[2]));
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  	        
        break; 	
	case "move_users":
	  $uids=$data[2];$uids=htmlspecialchars($uids);//
	  $new_room=$data[3];$new_room=htmlspecialchars($new_room);//
      $UIDS=explode('&',$uids);
      $pass=get_room_pass($new_room);
      foreach ($UIDS as $uid){
		  $uid=str_replace("&","",$uid);
		  if(strlen($uid)>0){
	    $msg='ALERT_MOVE'.'&'.$uid.'&'.$new_room.'&'.$pass;
       	$arr = array('header' =>  'SERVER_ALERT','data' => $msg,'room'=>$no_room);
		$x=json_encode($arr);
		  declear_to_AllUsers($Server,$clientID,$x);}
		                   }
	  $arr1 = array('header' =>  $request,'data' => "SUCCESS");
	  $x=json_encode($arr1);
	  redirect_to_user($Server,$clientID,$x);
	
        break; 		
//add_room class requests		
	case "create_room":
	    $result= add_room($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$now);
       	$arr = array('header' =>  $request,'data' => $result);
		$x=json_encode($arr);
		redirect_to_user($Server,$clientID,$x);  
		if($result=="ROOM_CREATED"){
	    $msg='ALERT_NEW_ROOM'.'&'.$data[2].'&'.$data[3].'&'.$data[4].'&'.$data[6].'&'.$data[7];		
		$arr = array('header' =>  'SERVER_ALERT','data' => $msg,'room'=>$no_room);
		$x=json_encode($arr);
		 declear_to_AllUsers($Server,$clientID,$x);
		 $arr= array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
		 declear_to_AllUsers($Server,$clientID,$x);
		}
        break; 			
		//edit_room class requests		
	case "edit_room":

	    $result= edit_room($data[1],$data[2],$data[3],$data[4],$data[6],$data[5],$data[7],$now);
       	$arr = array('header' =>  $request,'data' => $result);
		$x=json_encode($arr);
	    redirect_to_user($Server,$clientID,$x);  
		if($result=="ROOM_EDITED"){
	     $arr= array('header' => 'SERVER_ALERT','data' => ALERT_ROOMS_INFO(),'room' =>$no_room);
		$x=json_encode($arr);
		  declear_to_AllUsers($Server,$clientID,$x);
		}
        break; 			
    default:        

       // code to be executed if n is different from all labels;
}
}


// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
$Server->wsStartServer('192.168.137.1', 9300);
//$Server->wsStartServer('127.0.0.1', 9300);



?>