<?php

class AuthController extends Controller{

	const AuthPath = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;
	private static $AuthModel ;

	public function __construct(){

		self::$twig = parent::callTwig();

		self::$AuthModel = new AuthModel();
		if(self::$AuthModel->checkLogin()){
			safe_redirect(SITEURL . 'Dashboard/Main');
		}

	}


	public function __call($name, $arguments){
		$this->loginAction();
	}



	public function loginAction(){

		if(parent::isMethod('POST')){

			$username = isset($_POST['user_name']) ? safe($_POST['user_name']) : "";
			$password = isset($_POST['user_password']) ? safe($_POST['user_password']) : "";
			$remember = isset($_POST['user_rememberme']) ? safe($_POST['user_rememberme']) : "";

			$loginResult = self::$AuthModel->login($username , $password , $remember);

			if($loginResult == "ok"){
				safe_redirect(SITEURL . 'Dashboard/Main');
			}else{

				echo self::$twig->render(self::AuthPath . 'login.html', array(
						'pageTitle'	 => 'ورود به سیستم - خطا',
						'pageKeywords'  => '',
						'pageDes'	   => 'با وارد کردن نام کاربری و رمز عبور خود به سیستم وارد شوید ' ,
						'canonical'	 => '' ,
						'pageImage'	 => '' ,
						'extraJs'	   => '' ,
						'extraCss'	  => '' ,
						'messages'	  => $loginResult
					)
				);
			}

		}elseif(parent::isMethod('GET')){


			echo self::$twig->render(self::AuthPath . 'login.html', array(
					'pageTitle' => 'ورود به سیستم',
					'pageDes' => 'با وارد کردن نام کاربری و رمز عبور خود به سیستم وارد شوید ' ,
					'pageIcon' => 'lock',
				)
			);

		}
	}

	public function registerAction(){

		echo self::$twig->render(self::AuthPath . 'register.html', array(
				'pageTitle' => 'عضویت در سیستم',
				'pageDes' => 'می توانید با پر کردن فیلد های زیر در سیستم عضو شوید ' ,
				'pageIcon' => 'user-plus',
			)
		);

	}

	public function forgetPassAction(){

		$message = '';
		if(parent::isMethod('POST')){

			$user_name = isset($_POST['user_name']) ? safe($_POST['user_name']) : "";

			$forgetPassResult = self::$AuthModel->forgotPassword($user_name);

			if($forgetPassResult == true){
				$message = "با موفقیت ارسال شد .";
			}else{
				$message = implode($forgetPassResult, "-");
			}
		}

		echo self::$twig->render(self::AuthPath . 'forgetPass.html', array(
				'pageTitle' => 'فراموشی رمز عبور',
				'pageDes'	=> 'اگر رمز عبور خود را فراموشی کرده اید میتوانید اینجا ریست کنید ' ,
				'pageIcon'	=> 'copy',
				'messages'	=> $message
			)
		);

	}


	public function activationAction(){

		echo self::$twig->render(self::AuthPath . 'activation.html', array(
				'pageTitle' => 'فعالسازی حساب کاربری',
				'pageDes'	=> 'لطفا حساب کاربری خودتان را فعال کنید  ' ,
				'pageIcon'	=> 'check',
			)
		);

	}


}
