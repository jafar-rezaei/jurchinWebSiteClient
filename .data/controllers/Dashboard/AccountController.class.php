<?php

class AccountController extends Controller{

	const PATH = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct() {

		self::$twig = parent::callTwig();
		parent::forceLogin();

	}



	public function __call($name, $arguments) {
		$this->editAction();
	}




	public function editAction() {


		$uid = parent::$user->user_id;
		$AccountModel = new AccountModel();

		if(parent::isMethod('POST')) {

			$user = array();
			$user["first_name"] 	= input("POST" , "first_name");
			$user["last_name"] 		= input("POST" , "last_name");
			$user["country"] 		= input("POST" , "country");
			$user["city"] 			= input("POST" , "city");
			$user["mobileNumber"] 	= input("POST" , "mobileNumber");
			$user["phoneNumber"] 	= input("POST" , "phoneNumber");
			$user["address"] 		= input("POST" , "address");
			$user["aboutMe"]		= input("POST" , "aboutMe");

			if($AccountModel->editUserInfo($uid , $user)) {
				echo json_encode(
					array(
						"message"	=> "ok"
					)
				);
			} else {

				echo json_encode(
					array(
						"message"	=> "خطایی در هنگام بروزرسانی اطلاعات حساب رخ داده است ."
					)
				);

			}

		} else {

			$countrirs = $AccountModel->getCountries();
			if(parent::$user->ucountry !== 0) {
				$cities = $AccountModel->getCities(parent::$user->ucountry);
			} else {
				$cities =  array(
					array('id' => 0 , 'local_name' => 'عمومی' )
				);
			}



			$twigParameters = array(
				'pageTitle'     => 'حساب کاربری',
				'pageDes'       => 'ویرایش حساب کاربری شما در جورچین ' ,
				'pageKeywords'  => 'ویرایش حساب,ویرایش اکانت',
				'canonical'     => '{{siteurl}}Dashboard/Account/Edit' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'      => '' ,

				'endDom'        => '
	<script type="text/javascript">
		jurchin.initAccountEdit();
		jurchin.initImageHandle();
	</script>' ,


				//data
				'countrirs'		=> $countrirs ,
				'cities'		=> $cities


			);


			echo self::$twig->render(self::PATH . 'edit.html', $twigParameters );
		}
	}


	public function ChangeAvatarAction() {

		parent::forceLogin();

		if(parent::isMethod('POST')) {

			$inputName = 'avatar';
			$uid = parent::$user->user_id;
			$UserModel = new UserModel();


			if((!empty($_FILES[$inputName])) && ($_FILES[$inputName]['error'] == 0)) {

				$filename = basename($_FILES[$inputName]['name']);

				$mimes = array(
					'jpg' 	=> 'image/jpg',
					'jpeg' 	=> 'image/jpeg',
					'gif' 	=> 'image/gif',
					'png' 	=> 'image/png',
				);

				$ext = pathinfo($filename , PATHINFO_EXTENSION);


				// check extension allowed
				if(!(isset($mimes[$ext]) && in_array($_FILES[$inputName]["type"], $mimes , true) ) || $filename == "") {
					die("Bad file!");
				}

				if($_FILES[$inputName]["size"] > 450000) {
					die("File size error!");
				}


				$dir = UPLOAD_PATH . "avatars/";


				$sizesArray = array(
					"60x60"	,
					"120x120" ,
					"180x180"
				);



				// delete old avatar
				$oldAvatars = glob($dir . "avatar".$uid."_*");
				foreach ($oldAvatars as $fav) {
					if(is_file($fav) && file_exists($fav))
						unlink($fav);
				}

				$rndName = random_string(6);
				foreach ($sizesArray as $size) {
					$filename = "avatar".$uid."_".$rndName."_".$size.".".$ext;

					$size = explode("x", $size);
					$myuploaded = cropmypic($_FILES[$inputName]['tmp_name'] , $size[0] , $size[1] , $ext , $dir.$filename);
				}


				$UserModel->changeAvatar($uid , "avatar".$uid."_".$rndName."_180x180.".$ext );

				validateCSRFToken($_POST['CSRF_Token'] , null , 1);
				safe_redirect(SITEURL . 'Dashboard/Account/Edit');

			}
		}

	}



	public function ChangePasswordAction() {

		// handle request
		if(parent::isMethod('POST')) {

			$oldPass = input("POST" , "oldPass");
			$newPass = input("POST" , "newPass");
			$newPassRepeat = input("POST" , "newPassRepeat");


			$accountModel = new AccountModel();
			$changePass = $accountModel->changePass($oldPass , $newPass , $newPassRepeat );

			if($changePass === TRUE) {
				echo json_encode(
					array(
						"message"	=> "ok"
					)
				);
			} else {
				echo json_encode(
					array(
						"message"	=> $changePass
					)
				);
			}
		} else {

			$twigParameters = array(
				'pageTitle'     => 'تغییر رمز',
				'pageDes'       => 'رمز عبور اکانت خود را تغییر دهید ' ,
				'pageKeywords'  => 'ویرایش حساب,ویرایش اکانت',
				'canonical'     => '{{siteurl}}Dashboard/Account/ChangePassword' ,
				'pageImage'     => '' ,
				'extraJs'       => '
		<!-- Notification functionality -->
		<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'      => '' ,
				'endDom'        => '' ,

			);

			echo self::$twig->render(self::PATH . 'changePass.html', $twigParameters );
		}


	}



}
