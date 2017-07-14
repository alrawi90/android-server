<?php
require_once "new.php";
date_default_timezone_set('Asia/baghdad');
$now=date('Y-m-d H:i:s');

 if($_SERVER['REQUEST_METHOD']=='POST'){
 $file_name = $_FILES['uploaded_file']['name'];
 $temp_name = $_FILES['uploaded_file']['tmp_name'];
 $file_size = $_FILES['uploaded_file']['size'];
$uid=$_POST['uid'];
 $tid=$_POST['tid'];

 $location = "src/temp/".$uid."/".$tid."/";
 if( ! file_exists($location)){mkdir("src/temp/".$uid."/".$tid."/", 0755, true);}
 move_uploaded_file($temp_name, $location.$file_name);
 echo "SUCCESS" ;
 }
else {
        echo "ERROR_ Sorry, there was an error uploading your file.";
    }


//$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
//$uploadOk = 1;
//$callBack_msg;
//$FileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
function is_image($file){//$_FILES["fileToUpload"]["tmp_name"]
    $check = getimagesize($file);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
function is_already_exist($target_file){
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
}
 // Check file size
 function is_big_size($file){//$_FILES["fileToUpload"]["size"] 
if ($file)> 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
 }
 }
// Allow certain file formats

//if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
//&& $imageFileType != "gif" ) {
//    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
//    $uploadOk = 0;
//}
// Check if $uploadOk is set to 0 by an error

?> 