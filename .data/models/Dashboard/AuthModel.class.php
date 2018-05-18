<?php
class AuthModel extends Model {

	protected static $dbh;
	protected static $login;

	public function __construct() {

		self::$login = new LoginHelper();
		self::$dbh = parent::dataBase();

	}



	public function login($username , $password , $remember ) {

		// if logged already
		if($this->checkLogin()){
			return 'ok';
		}

		
		if (!empty(jamework::$session->get('user_id')) && (jamework::$session->get('user_logged_in') == 1)) {
			return self::$login->loginWithSessionData();
		} elseif (isset($_COOKIE['rememberme'])) { 
			// remember me information
			return self::$login->loginWithCookieData();
		} else {

			return self::$login->loginWithPostData($username, $password, $remember);

		}

	}


	public static function checkLogin(){
		
		if(self::$login->isUserLoggedIn() || jamework::$session->get('user_logged_in') == 1){
			return true;
		}else{

			if (isset($_COOKIE['rememberme'])) { 
				// remember me information
				if(self::$login->loginWithCookieData() == "ok")
					return true;
			}

			return false;
		}

	}


	public static function logout($logKey){

		return self::$login->doLogout($logKey);

	}



	public static function forgotPassword($mail){

		$forgotPassword = self::$login->setPasswordResetDatabaseTokenAndSendMail($mail);
		if(count(self::$login->errors) > 0 || $forgotPassword == false){
			return self::$login->errors;
		}else{
			return true;
		}
	}



	public static function checkEmailVerificationCode($id , $verification_code){

		return self::$login->checkIfEmailVerificationCodeIsValid($id, $verification_code);

	}


	public static function passwordReset($id , $verification_code){

		return self::$login->setPasswordResetDatabaseTokenAndSendMail($username);

	}


	/**
	* user with password reset hash
	* 
	*/
	public static function varifyPasswordReset($username , $password_reset_hash , $user_password_new , $user_password_repeat){

		return self::$login->checkIfEmailVerificationCodeIsValid($username , $password_reset_hash , $user_password_new , $user_password_repeat);

	}

}
?>