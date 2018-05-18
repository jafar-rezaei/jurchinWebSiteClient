<?php

class LoginHelper extends AuthModel {

	private $db_connection = null;

	private $user_id = null;
	private $user_name = "";
	private $user_email = "";
	private $user_is_logged_in = false;

	public $user_gravatar_image_url = "";
	public $user_gravatar_image_tag = "";

	private $password_reset_link_is_valid  = false;
	private $password_reset_was_successful = false;


	public $errors = array();
	public $messages = array();

	private $config;


	public function __construct()
	{
	   $this->config = jamework::getConfig();
	}

	/**
	 * Search into database for the user data 
	 * @param user_if which is user_id OR user_email
	 * @return user data as an object if existing user
	 */
	protected function getUserData($user_inf)
	{
		$result_row = parent::$dbh->read(
			'jor_users' ,
			array('user_id','firstname' , 'lastname', 'user_email' , 'user_actiovation_email_sendagain' , 'user_logkey') ,
			array(
				'user_id' => $user_inf ,
				'user_email' => $user_inf
			) ,
			'=-=' ,
			'OR' ,
			'' ,
			's'
		);
		return $result_row ;
	}

	/**
	 * Logs in with S_SESSION data.
	 * Technically we are already logged in at that point of time.
	 */
	protected function loginWithSessionData()
	{
		$this->user_name = jamework::$session->get('user_name');
		$this->user_email = jamework::$session->get('user_email');
		$this->user_is_logged_in = true;
		return 'ok';
	}

	/**
	*   If login is okay this will be fire
	*   @param object of user info
	*/
	protected function loginOkay($result_row) {

		// write user data into PHP SESSION [a file on your server]
		jamework::$session->put('user_id'           , $result_row->user_id );
		jamework::$session->put('user_email'        , $result_row->user_email);
		jamework::$session->put('user_logged_in'    , 1);


		// declare user id, set the login status to true
		$this->user_id              = $result_row->user_id;
		$this->user_email           = $result_row->user_email;
		$this->user_is_logged_in    = true;

	}

	
	/**
	 * Logs in via the Cookie
	 * @return bool success state of cookie login
	 */
	protected function loginWithCookieData()
	{
		if (isset($_COOKIE['rememberme'])) {
			// extract data from the cookie
			list ($user_id, $token, $hash) = explode(':', $_COOKIE['rememberme']);
			// check cookie hash validity
			if ($hash == hash('sha256', $user_id . ':' . $token . $this->config["COOKIE_SECRET_KEY"]) && !empty($token)) {


				// get real token from database (and all other data)

				$result_row = parent::$dbh->read(
					'jor_users' ,
					array('user_id','firstname' , 'lastname', 'user_email') ,
					array(
						'user_id' => intval($user_id) ,
						'user_rememberme_token' => $token
					),
					'=-=' ,
					'AND' ,
					'' ,
					's'
				);

				if (isset($result_row->user_id)) {

					$this->loginOkay($result_row);
					// Cookie token usable only once
					$this->newRememberMeCookie();
					return 'ok';
				}
				
			}
			// A cookie has been used but is not valid... we delete it
			$this->deleteRememberMeCookie();
			$this->errors[] = jamework::$lang['MESSAGE_COOKIE_INVALID'];
		}
		return 'nok';
	}

	/**
	 * Logs in with the data provided in $_POST, coming from the login form
	 * @param $user_name
	 * @param $user_password
	 * @param $user_rememberme
	 */

	protected function loginWithPostData($user_name, $user_password, $user_rememberme) {

		if (empty($user_name)) {
			return jamework::$lang['MESSAGE_USERNAME_EMPTY'];

		} else if (empty($user_password)) {
			return jamework::$lang['MESSAGE_PASSWORD_EMPTY'];
			
		// if POST data (from login form) contains non-empty user_name and non-empty user_password
		} else {

			if (!filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
				$result_row = $this->getUserData(trim($user_name));
			} else {

				$result_row = parent::$dbh->read(
					'jor_users' ,
					array() ,
					array(
						'user_email' => trim($user_name)
					) ,
					'=' ,
					'' ,
					'' ,
					's'
				);
				
			}


			// if this user not exists
			if (! isset($result_row->user_id)) {

				return jamework::$lang['MESSAGE_LOGIN_FAILED'];

			} else if (($result_row->user_failed_logins >= 3) && ($result_row->user_last_failed_login > (time() - 30))) {

				return jamework::$lang['MESSAGE_PASSWORD_WRONG_3_TIMES'];

			} else if (! password_verify($user_password, $result_row->user_password_hash)) {

				// increment the failed login counter for that user

				$updateUser = parent::$dbh->update(
					'jor_users' , 
					array(
						'user_failed_logins' => ($result_row->user_failed_logins + 1) ,
						'user_last_failed_login' => time() 
					),
					array(
						'user_id' => intval($result_row->user_id)
					),
					'=' ,
					''
				);

				return jamework::$lang['MESSAGE_PASSWORD_WRONG'];

			}  else {


				$this->loginOkay($result_row);
				$loginKey = hash('sha256', mt_rand());


				$updateUser = parent::$dbh->update(
					'jor_users' , 
					array(
						'user_failed_logins'        => 0 ,
						'user_last_failed_login'    => NULL ,
						'user_logkey'               => $loginKey
					),
					array(
						'user_id' => intval($result_row->user_id)
					),
					'=' ,
					''
				);


				// if user has check the "remember me" checkbox, then generate token and write cookie
				if (isset($user_rememberme)) {
					$this->newRememberMeCookie();
				} else {
					// Reset remember-me token
					$this->deleteRememberMeCookie();
				}
				return 'ok';
			}
		}
	}    

	/**
	 * Create all data needed for remember me cookie connection on client and server side
	 */

	private function newRememberMeCookie()
	{
		
		// generate 64 char random string and store it in current user data
		$random_token_string = hash('sha256', mt_rand());

		$updateUser = parent::$dbh->update(
			'jor_users' , 
			array(
				'user_rememberme_token' => $random_token_string 
			),
			array(
				'user_id' => intval(jamework::$session->get('user_id'))
			),
			'=' , ''
		);
	
		// generate cookie string that consists of userid, randomstring and combined hash of both
		$cookie_string_first_part = jamework::$session->get('user_id') . ':' . $random_token_string;
		$cookie_string_hash = hash('sha256', $cookie_string_first_part . $this->config["COOKIE_SECRET_KEY"]);
		$cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;
	
	
		// set cookie
		setcookie(
			'rememberme',
			$cookie_string,
			time() + $this->config["COOKIE_RUNTIME"],
			"/",
			$this->config["COOKIE_DOMAIN"]
		);
		
	}

	/**
	 * Delete all data needed for remember me cookie connection on client and server side
	 */
	private function deleteRememberMeCookie()
	{
		
		// Reset rememberme token

		$updateUser = parent::$dbh->update('jor_users' , 
			array(
				'user_rememberme_token' => NULL 
			),
			array(
				'user_id' => intval(jamework::$session->get('user_id'))
			),
			'=' , ''
		);

		// set 10 years ago
		setcookie('rememberme', false, time() - (3600 * 3650), '/', $this->config["COOKIE_DOMAIN"]);
	}

	/**
	 * Perform the logout, resetting the session
	 */

	public function doLogout($logKey) {
		
		$user = $this->getUserData(trim(jamework::$session->get('user_id')));
		if($user->user_logkey == $logKey){
		
			// Reset rememberme token
			$updateUserLogout = parent::$dbh->update('jor_users' , 
				array(
					'user_logkey' => NULL 
				),
				array(
					'user_id' => intval(jamework::$session->get('user_id'))
				),
				'=' , ''
			);

			if(count($updateUserLogout)){

				$this->user_is_logged_in = false;
				$this->deleteRememberMeCookie();
				
				jamework::$session->forget();				
				return "ok";

			}else{
				return "nok db error";
			}

		}else{
			return "nok not good key";
		}

		
	}

	/**
	 * Simply return the current state of the user's login
	 * @return bool user's login status
	 */
	public function isUserLoggedIn()
	{
		return $this->user_is_logged_in;
	}


	/**
	 * Edit the user's password, provided in the editing form
	 */
	public function editUserPassword($user_password_old, $user_password_new, $user_password_repeat)
	{
		if (empty($user_password_new) || empty($user_password_repeat) || empty($user_password_old)) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_EMPTY'];
		// is the repeat password identical to password
		} elseif ($user_password_new !== $user_password_repeat) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_BAD_CONFIRM'];
		// password need to have a minimum length of 6 characters
		} elseif (strlen($user_password_new) < 6) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_TOO_SHORT'];

		// all the above tests are ok
		} else {
			// database query, getting hash of currently logged in user (to check with just provided password)
			$result_row = $this->getUserData(jamework::$session->get('user_id'));

			// if this user exists
			if (isset($result_row->user_password_hash)) {

				// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
				if (password_verify($user_password_old, $result_row->user_password_hash)) {

					$hash_cost_factor = (isset($this->config['HASH_COST_FACTOR']) ? $this->config['HASH_COST_FACTOR'] : 8);

					$user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

					$user_id = jamework::$session->get('user_id');

					$result_row = parent::$dbh->update(
						'jor_users' ,
						array(
							'user_password_hash' => $user_password_hash
						) ,
						array(
							'user_id' => intval($user_id) 
						),
						'=' ,
						'' 
					);


					// check if exactly one row was successfully changed:
					if ($query_update->rowCount()) {
						$this->messages[] = jamework::$lang['MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY'];
					} else {
						$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_CHANGE_FAILED'];
					}
				} else {
					$this->errors[] = jamework::$lang['MESSAGE_OLD_PASSWORD_WRONG'];
				}
			} else {
				$this->errors[] = jamework::$lang['MESSAGE_USER_DOES_NOT_EXIST'];
			}
		}
	}

	/**
	 * Sets a random token into the database (that will verify the user when he/she comes back via the link
	 * in the email) and sends the according email.
	 */
	public function setPasswordResetDatabaseTokenAndSendMail($user_name)
	{
		$user_name = trim($user_name);

		if (empty($user_name)) {
			$this->errors[] = jamework::$lang['MESSAGE_USERNAME_EMPTY'];
		} else {
			$temporary_timestamp = time();
			$user_password_reset_hash = sha1(uniqid(mt_rand(), true));

			$result_row = $this->getUserData($user_name);

			
			if (isset($result_row->user_id)) {


				if(null !== $result_row->user_actiovation_email_sendagain){
					$user_sended_emails = explode('_', $result_row->user_actiovation_email_sendagain);
				
					if ($user_sended_emails[0] > 5 && $user_sended_emails[1] > ( time()- 86400) ){
						$this->errors[] = 'تعداد ایمیل های ارسالی در 24 ساعت حداکثر 5 عدد است ، در صورت تلاش بیشتر بلاک خواهید شد .';
					}

					if ($user_sended_emails[0] > 5 ) {
						$smdss = 1 ;
					}else {
						$smdss = $user_sended_emails[0]+1 ;
					}
				}


				$now = time();
				$sended_mails = $smdss.'_'.$now;


				$updatePasswordSend = parent::$dbh->update(
					'jor_users' ,
					array(
						'user_password_reset_hash' => $user_password_reset_hash ,
						'user_password_reset_timestamp' => $now ,
						'user_actiovation_email_sendagain' => $sended_mails
					) ,
					array(
						'user_email' => $result_row->user_email 
					),
					'=' ,
					'' 
				);


				
				// check if exactly one row was successfully changed:
				if (count($updatePasswordSend)) {
					// send a mail to the user, containing a link with that token hash string
					$this->sendPasswordResetMail($result_row->firstname ." ".$result_row->lastname, $result_row->user_email, $user_password_reset_hash);
					return true;
				} else {
					$this->errors[] = jamework::$lang['MESSAGE_DATABASE_ERROR'];
				}

			} else {
				$this->errors[] = jamework::$lang['MESSAGE_USER_DOES_NOT_EXIST'];
			}
		}

		// return false (this method only returns true when the database entry has been set successfully)
		return false;
	}

	/**
	 * Sends the password-reset-email.
	 */
	public function sendPasswordResetMail($user_name, $user_email, $user_password_reset_hash)
	{
		$mail = $this->startPhpMailer();

		$mail->From = $this->config["EMAIL_PASSWORDRESET_FROM"];
		$mail->FromName = $this->config["EMAIL_PASSWORDRESET_FROM_NAME"];
		$mail->AddAddress($user_email);
		$mail->Subject = $this->config["EMAIL_PASSWORDRESET_SUBJECT"];

		$link = $this->config["EMAIL_PASSWORDRESET_URL"].''.urlencode($user_name).'-'.urlencode($user_password_reset_hash);
		$mail->Body = '<style>*{text-align:right;direction:rtl;font-family:BYekan,\'BYekan\',B Yekan,tahoma,Arial;font-weight:normal;}</style><span>'.$this->config["EMAIL_PASSWORDRESET_CONTENT"].'</span><br/><a href="'.$link.'" title="لینک">'.$link.'</a>';


		if(!$mail->Send()) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_RESET_MAIL_FAILED'] . $mail->ErrorInfo;
			return false;
		} else {
			$this->messages[] = jamework::$lang['MESSAGE_PASSWORD_RESET_MAIL_SUCCESSFULLY_SENT'];
			return true;
		}
	}

	/**
	 * Checks if the verification string in the account verification mail is valid and matches to the user.
	 */
	public function checkIfEmailVerificationCodeIsValid($user_name, $verification_code)
	{
		$user_name = trim($user_name);

		if (empty($user_name) || empty($verification_code)) {
			$this->errors[] = jamework::$lang['MESSAGE_LINK_PARAMETER_EMPTY'];
		} else {
			// database query, getting all the info of the selected user
			$result_row = $this->getUserData($user_name);

			// if this user exists and have the same hash in database
			if (isset($result_row->user_id) && $result_row->user_password_reset_hash == $verification_code) {
				$timestamp_one_hour_ago = time() - 3600; // 3600 seconds are 1 hour

				if ($result_row->user_password_reset_timestamp > $timestamp_one_hour_ago) {
					// set the marker to true, making it possible to show the password reset edit form view
					$this->password_reset_link_is_valid = true;
				} else {
					$this->errors[] = jamework::$lang['MESSAGE_RESET_LINK_HAS_EXPIRED'];
				}
			} else {
				$this->errors[] = jamework::$lang['MESSAGE_USER_DOES_NOT_EXIST'];
			}
		}
	}

	/**
	 * Checks and writes the new password.
	 */
	public function editNewPassword($user_name, $user_password_reset_hash, $user_password_new, $user_password_repeat)
	{
		// TODO: timestamp!
		$user_name = trim($user_name);
		if (empty($user_name) || empty($user_password_reset_hash) || empty($user_password_new) || empty($user_password_repeat)) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_EMPTY'];
		// is the repeat password identical to password

		} else if ($user_password_new !== $user_password_repeat) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_BAD_CONFIRM'];

		// password need to have a minimum length of 6 characters
		} else if (strlen($user_password_new) < 6) {
			$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_TOO_SHORT'];

		} else {

			$hash_cost_factor = (isset($this->config["HASH_COST_FACTOR"]) ?: null);
			$user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));



			$updateUser = parent::$dbh->update(
				'jor_users' , 
				array(
					'user_password_hash'            => $user_password_hash,   
					'user_password_reset_hash'      => NULL,
					'user_password_reset_timestamp' => NULL
				),
				array(
					'user_name' => $user_name ,
					'user_password_reset_hash' => $user_password_reset_hash
				),
				'=-=' ,
				'AND'
			);


			// check if exactly one row was successfully changed:
			if (count($updateUser) == 1) {
				$this->password_reset_was_successful = true;
				$this->messages[] = jamework::$lang['MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY'];
			} else {
				$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_CHANGE_FAILED'];
			}
		}
	}

	/**
	 * Gets the success state of the password-reset-link-validation.
	 * TODO: should be more like getPasswordResetLinkValidationStatus
	 * @return boolean
	 */
	public function passwordResetLinkIsValid()
	{
		return $this->password_reset_link_is_valid;
	}

	/**
	 * Gets the success state of the password-reset action.
	 * TODO: should be more like getPasswordResetSuccessStatus
	 * @return boolean
	 */
	public function passwordResetWasSuccessful()
	{
		return $this->password_reset_was_successful;
	}

	
	public function sendagainVerificationEmail($user_name){
		$user_name = trim($user_name);
		if (empty($user_name)) {
			$this->errors[] = '';
		}else {
						
			$result_row = $this->getUserData(trim($user_name));
			if (! isset($result_row->user_id)) {
				$this->errors[] = 'ارسال ایمیل فعالسازی انجام نشد ! با مدیریت تماس بگیرید';
			} else if (($result_row->user_failed_logins >= 3) && ($result_row->user_last_failed_login > (time() - 60))) {
				$this->errors[] = jamework::$lang['MESSAGE_PASSWORD_WRONG_3_TIMES'];
			} else if ($result_row->user_active == 1) {
				$this->errors[] = 'حساب کاربری شما فعال است و نیازی به فعالسازی دوباره ندارد !';
			}
			if($this->errors == NULL){


				$updateUser = parent::$dbh->read(
					'jor_users' , 
					array('user_id', 'user_email' , 'user_actiovation_email_sendagain'),
					array(
						'user_name' => $user_name
					),
					'=' ,
					'' ,
					'' ,
					'm'
				);


				$touser = $result['user_id'];
				$user_email = $result['user_email'];
				if(!empty($result['user_actiovation_email_sendagain'])){
					$user_sended_emails = explode('_', $result['user_actiovation_email_sendagain']);
				}
				$user_activation_hash = sha1(uniqid(mt_rand(), true));
				if ($user_sended_emails[0] > 2 && $user_sended_emails[1] > ( time()- 86400) ){
					$this->errors[] = 'تعداد ایمیل های ارسالی در 24 ساعت حداکثر 3 عدد است ، در صورت تلاش بیشتر بلاک خواهید شد .';
				} else {
					// UPDATE ACTIVATION CODE
					if ($user_sended_emails[0] > 2 ) {
						$smdss = 1 ;
					}else {
						$smdss = $user_sended_emails[0]+1 ;
					}
					$now = time();
					$sended_mails = $smdss.'_'.$now;
					$this->messages[] = jamework::$lang['MESSAGE_VERIFICATION_AGAIN_MAIL_SENT'];


					$updateUser = parent::$dbh->update(
						'jor_users' , 
						array(
							'user_activation_hash' => $user_activation_hash ,
							'user_actiovation_email_sendagain' => $sended_mails 
						),
						array(
							'user_id' => intval(trim($touser))
						),
						'=' , ''
					);


					if ($query_update_user_activation->execute()){
						$this->sendVerificationEmail($touser, $user_email, $user_activation_hash);
						$this->messages[] = jamework::$lang['MESSAGE_VERIFICATION_AGAIN_MAIL_SENT'];
					}
				}
			}
			
		}
	}
	
	/*
	 * sends an email to the provided email address
	 * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
	 */
	public function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
	{
		$mail = $this->startPhpMailer();
		$mail->From         = $this->config["EMAIL_VERIFICATION_FROM"];
		$mail->FromName     = $this->config["EMAIL_VERIFICATION_FROM_NAME"];
		$mail->AddAddress($user_email);
		$mail->Subject      = $this->config["EMAIL_VERIFICATION_SUBJECT"];

		$link               = $this->config["EMAIL_VERIFICATION_URL"].''.urlencode($user_id).'-'.urlencode($user_activation_hash);

		// the link to your register.php, please set this value in config/email_verification.php
		$mail->Body = '<style>*{text-align:right;direction:rtl;font-family:BYekan,\'BYekan\',B Yekan,tahoma,Arial;font-weight:normal;}</style><span>'.$this->config["EMAIL_VERIFICATION_CONTENT"].'</span><a href="'.$link.'" title="لینک">'.$link.'</a>';


		if(!$mail->Send()) {
			$this->errors[] = jamework::$lang['MESSAGE_VERIFICATION_MAIL_NOT_SENT'] . $mail->ErrorInfo;
			return false;
		} else {
			return true;
		}
	}


	private function startPhpMailer(){
		$mail = new PHPMailer;

		if ($this->config["EMAIL_USE_SMTP"]) {
			// Set mailer to use SMTP
			$mail->IsSMTP();

			$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = $this->config["EMAIL_SMTP_AUTH"];
			// Enable encryption, usually SSL/TLS
			if (isset($this->config["EMAIL_SMTP_ENCRYPTION"])) {
				$mail->SMTPSecure = $this->config["EMAIL_SMTP_ENCRYPTION"];
			}
			// Specify host server
			$mail->Host 	= $this->config["EMAIL_SMTP_HOST"];
			$mail->Username = $this->config["EMAIL_SMTP_USERNAME"];
			$mail->Password = $this->config["EMAIL_SMTP_PASSWORD"];
			$mail->Port 	= $this->config["EMAIL_SMTP_PORT"];
		} else {
			$mail->IsMail();
		}
		return $mail;
	}

}
