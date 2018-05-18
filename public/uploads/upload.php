<?php 
header('Content-type: text/html; charset=UTF-8');
require_once("inc/functions.php");

error_reporting(0);

$valid_exts = array(// allowed extensions
                'gif',
                'jpeg',
                'jpg',
                'JPG',
                'png',
            );
$valid_types = array(
                'image/gif',
                'image/jpeg',
                'image/jpg',
                'image/pjpeg',
                'image/x-png',
                'image/png',
            );


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	$key = !empty( $_POST['key']) ? decryptIt($_POST['key']) : "";
	$action = substr($key , '0' , strripos($key , "_"));
	$inputKind = !empty( $_POST['kind']) ? decryptIt($_POST['kind']) : ""; 	//bizphoto OR avatar OR  userUpload
	$kind = substr($inputKind , '0' , strripos($inputKind , "_"));
	$user = !empty( $_POST['user']) ? decryptIt($_POST['user']) : "";
	
	
	$isRobot = isset( $_POST['isRobot']) ? $_POST['isRobot'] : false;	
	$url = isset( $_POST['url']) ? base64_decode($_POST['url']) : "";
	
	
	if($action == "uploadFile"){
		if (isset($_FILES['file'])) {
			$myFile = $_FILES['file'];
			if($myFile["error"] == 0 ) {
				$filename = basename($myFile["name"]);
				$ext = substr($filename, strrpos($filename, '.') + 1);
				if ( in_array($myFile["type"], $valid_types) && in_array($ext , $valid_exts) && $myFile["size"] < 5550000) {
					$randomname = random_string(5) ;
					if($kind == 'bizphoto'){
						$citycode = !empty( $_POST['citycode']) ? $_POST['citycode'] : "";
						$filename2 = $user.'_'.$randomname.'.'.strtolower($ext);
						$dir = 'media/bizPhotos/';
						
						if (!file_exists($dir.$citycode)) {
						    mkdir($dir.$citycode , 0755, true);
						}
						$newname = dirname(__FILE__).'/'.$dir.$citycode.'/'.$filename2;
						$filead = $dir.$citycode.'/'.$filename2;
						
					}else if($kind == 'bProducts'){
						$bid = !empty( $_POST['bid']) ? $_POST['bid'] : "";
						$filename2 = $user.'_'.$randomname.'.'.strtolower($ext);
						$dir = 'media/bizProducts/';
						
						if (!file_exists($dir.$bid)) {
						    mkdir($dir.$bid, 0755, true);
						}
						$newname = dirname(__FILE__).'/'.$dir.$bid.'/'.$filename2;
						$filead = $dir.$bid.'/'.$filename2;
						
					}else if($kind == 'usrAvatar'){
						$lastAvatar = !empty( $_POST['lastAvatar']) ? decryptIt($_POST['lastAvatar']) : "";
						$UserAvatarCount = 0; 
						$dir = 'media/avatars/';
						
						$files = glob($dir.$user."_*");
						if ($files){
							$UserAvatarCount = count($files);
						}
						if($UserAvatarCount > 3){
							$deleted = 0;
							$extraAvatars = $UserAvatarCount - 3; 
							array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_ASC, $files);
							foreach($files as $file){
								if($file !== $lastAvatar){
									if(unlink($file))
										$deleted += 1;
									if($deleted == $extraAvatars)
										break;
								}
							}
						}
						$filename2 = $user.'_'.$randomname.'.'.strtolower($ext);
						$newname = dirname(__FILE__).'/'.$dir.$filename2;
						$filead = $dir.$filename2;
						
					}else if($kind == 'support'){
						$lastAvatar = !empty( $_POST['lastAvatar']) ? decryptIt($_POST['lastAvatar']) : "";
						$UserAvatarCount = 0; 
						$dir = 'media/support/';
						
						$filename2 = $user.'_'.$randomname.'.'.strtolower($ext);
						$newname = dirname(__FILE__).'/'.$dir.$filename2;
						$filead = $dir.$filename2;
						
					}else if($kind == 'userUpload'){
						$biz = !empty( $_POST['biz']) ? decryptIt($_POST['biz']) : "";
						$user = !empty( $_POST['user']) ? decryptIt($_POST['user']) : "";
						$dir = 'media/usrUpload/';
						
						$filename2 = $user.'_'.$randomname.'.'.strtolower($ext);
						if (!file_exists($dir.$biz )) {
						    mkdir($dir.$biz , 0755, true);
						}
						$newname = dirname(__FILE__).'/usrUpload/'.$biz.'/'.$filename2;
						$filead = $dir.$biz.'/'.$filename2;
					}
					if ((move_uploaded_file($myFile['tmp_name'],$newname))) {
						$deletekey = encryptIt("dm_".$filead );
						
						if($kind == 'bProducts'){$filead = str_replace("bizProducts/" , "" , $filead );}
						
						
						echo "<span id='success'>تصویر با موفقیت آپلود شد ... !</span><br/>";
						echo "<br/><b>نام فایل :</b> " . $myFile["name"] . "<br>";
						echo "<b>فرمت :</b> " . $myFile["type"] . "<br>";
						echo "<b>حجم :</b> " . floor($myFile["size"] / 1024) . " kB<br>
						<code style='display:none;'>".$filead."</code>";
						if($kind == 'userUpload'){
							echo "<key style='display:none;'>". base64_encode($filead)."</key>";
						}else{
							echo "<key style='display:none;'>".$deletekey."</key>";
						}
					} else {
						echo "سرور موقتا دچار مشکل است . بعدا تلاش کنید"; 
					}
				} else {
					echo  "خطایی در سایز یا فرمت فایل رخ داده است.";
				}
			}
	                
		}elseif($isRobot !== false && $url !== ""){
			
			// Robot Upload
			
			$citycode = !empty( $_POST['citycode']) ? $_POST['citycode'] : "";
			
			$ext = substr($url, strrpos($url, '.') + 1);
			$randomname = random_string(5) ;
			
			$filename2 = '1_'.$randomname.'.'.strtolower($ext);
			$dir = 'media/bizPhotos/';
			
			if (!file_exists($dir.$citycode)) {
			    mkdir($dir.$citycode , 0755, true);
			}
			$filead = $dir.$citycode.'/'.$filename2;
			$newname = dirname(__FILE__).'/'.$filead;
				

			if(file_put_contents($newname, file_get_contents($url)))
				//UPload Succesfully Completed
				echo $filead;
			else 
				// Upload Fail
				echo 0;
		} else {
			die('0');
		}
	}elseif($action == "loadFiles"){
		if($kind == 'usrAvatar'){
			$dir = 'media/avatars/';
						
			$files = glob($dir.$user."_*");
			
			// >> SORT GLOBED AVATARS
			array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);
			if ($files){
				echo json_encode($files);
			}
		}
	}elseif($action == "dFile"){
		$file = !empty( $_POST['file']) ? decryptIt($_POST['file']) : "";
		
		if (file_exists($file)) {
        		if(unlink($file))echo '1';
        	}else die('dd');
	}
}

sleep(1);
?>