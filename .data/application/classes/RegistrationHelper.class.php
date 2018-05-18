<?php
class RegistrationHelper {
    
    private $db_connection            = null;
    public  $registration_successful  = false;
    public  $verification_successful  = false;
    public  $errors                   = array();
    public  $messages                 = array();

    public function __construct() {
	if(isset($_GET['register-verification_code']) && isset($_GET['id'])){
		$this->verifyNewUser($_GET['id'], $_GET['register-verification_code']);
        }else if (isset($_POST["register"])) {
        	$smsPerm = !empty($_POST['smsperm']) ? $_POST['smsperm'] : 0 ;
        	
		if(!empty($_POST['user_mobile'] ) && !empty($_POST['user_password_new'] ) && !empty($_POST['user_password_new'] ) && !empty($_POST['g-recaptcha-response'] )  && !empty($_POST['user_realname'] ))
			$this->registerNewUser($_POST['user_mobile'], $_POST['user_email'], $_POST['user_password_new'], $_POST['user_password_repeat'] , $smsPerm , $_POST["g-recaptcha-response"] , $_POST['user_realname']);
		else 
			$this->errors[] = "فیلدهای لازم را تکمیل کنید .";
        }
    }

    /**
     * Checks if database connection is opened and open it if not
     */
    private function databaseConnection()
    {
        // connection already opened
        if ($this->db_connection != null) {
            return true;
        } else {
            // create a database connection, using the constants from config/config.php
            try {
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                $this->db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return true;
            // If an error is catched, database connection failed
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR;
                return false;
            }
        }
    }

    /**
     * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
     * everything is fine
     */
    private function registerNewUser($user_mobile, $user_email = NULL , $user_password, $user_password_repeat, $smsperm = 0, $recaptcharesponse , $user_realname)
    {
        // we just remove extra space on username and email
        $user_name  = trim($user_mobile);
        $user_email = trim($user_email);
	$success = false;
	
        $recaptchaurl = 'https://www.google.com/recaptcha/api/siteverify';
	$recaptchadata = array('secret' => urlencode('6LeBLBsTAAAAANZvI4mbN5RFS_ceTqNbZiTMYUvB') , 'response' => urlencode($recaptcharesponse));
	$fields_string = "";
	foreach($recaptchadata as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string,'&');
	
	//open connection
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$recaptchaurl );
	curl_setopt($ch,CURLOPT_POST,count($recaptchadata ));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	
	//execute post
	$result = curl_exec($ch);
	$result2 = json_decode($result);
	
	if(empty($recaptcharesponse)){
        	$this->errors[] = "کد امنیتی نامعتبر است ";
                return false;
        }elseif ($result2->success == false ) {
            $this->errors[] = MESSAGE_CAPTCHA_WRONG;
        } elseif (empty($user_name) || empty($user_realname)) {
            $this->errors[] = MESSAGE_USERNAME_EMPTY;
        } elseif (empty($user_password) || empty($user_password_repeat)) {
            $this->errors[] = MESSAGE_PASSWORD_EMPTY;
        } elseif ($user_password !== $user_password_repeat) {
            $this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
        } elseif (strlen($user_password) < 6) {
            $this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;
        } elseif (strlen($user_name) > 64 || strlen($user_name) < 2) {
            $this->errors[] = MESSAGE_USERNAME_BAD_LENGTH;
        } elseif (strlen($user_email) > 64) {
            $this->errors[] = MESSAGE_EMAIL_TOO_LONG;
        } elseif (!empty($user_email) && !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = MESSAGE_EMAIL_INVALID;

        // finally if all the above checks are ok
        } else if ($this->databaseConnection()) {
            // check if username or email already exists
            $query_check_user_name = $this->db_connection->prepare('SELECT user_name, user_email FROM ctg_users WHERE user_name=:user_name OR user_email=:user_email');
            $query_check_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            $query_check_user_name->bindValue(':user_email', $user_email, PDO::PARAM_STR);
            $query_check_user_name->execute();
            $result = $query_check_user_name->fetchAll();

            if (count($result) > 0) {
                for ($i = 0; $i < count($result); $i++) {
                    $this->errors[] = ($result[$i]['user_name'] == $user_name) ? MESSAGE_USERNAME_EXISTS : MESSAGE_EMAIL_ALREADY_EXISTS;
                }
            } else {
                $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
                // generate random hash for email verification (40 char string)
                $user_activation_hash = sha1(uniqid(mt_rand(), true));
				$sms_activation_hash = mt_rand(100000, 999999);
				$uip = $this->getRealIp();

                // write new users data into database
                $query_new_user_insert = $this->db_connection->prepare('INSERT INTO ctg_users (user_name, user_password_hash , user_email, user_activation_hash , sms_activation_hash, user_registration_ip, smsperm , user_registration_datetime , realname ) VALUES ( :user_name , :user_password_hash, :user_email, :user_activation_hash, :sms_activation_hash ,:user_registration_ip, :smsperm , now(), :realname ) ');
                $query_new_user_insert->bindValue(':user_name', $user_name );
                $query_new_user_insert->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':user_email', $user_email, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':sms_activation_hash', $sms_activation_hash, PDO::PARAM_STR );
                $query_new_user_insert->bindValue(':user_registration_ip', $uip , PDO::PARAM_STR);
                $query_new_user_insert->bindValue(':smsperm', $smsperm);
                $query_new_user_insert->bindValue(':realname', $user_realname , PDO::PARAM_STR);
                $query_new_user_insert->execute();
				

                // id of new user
                $user_id = $this->db_connection->lastInsertId();
				
				
				
				

                if ($query_new_user_insert) {
	                // SEND TWO SMS TO BIZ OWNER 
			require("./model/inc/sms.php");
			$to      = 		$user_name;
			$message =  	'باسلام،
کد فعالسازی شما : '.$sms_activation_hash.'
باتشکر
سامانه اطلاعات کشوری سیتی گرام';
			$key	 = 		encryptIt('send_'.mt_rand(100000, 999999));
			$sms = new sms_sender();
			$sms->addToQeue($key , md5('mellatsms') , $to , $message);
				
                   	 // send a verification email
                    	$this->sendVerificationEmail($user_id, $user_email, $user_activation_hash);
                        $this->messages[] = " <br/><h2>تبریک می گوییم !</h2>حساب کاربری شما ایجاد شد ، ایمیل فعالسازی برای شما ارسال شد .لطفا روی لینک فعالسازی موجود در ایمیل کلیک کنید یا کد فعالسازی ارسال شده به گوشی خود را در باکس زیر وارد کنید : <br/><form action='/' method='get'><input type='hidden' name='id' value='$user_id' /><input style='padding:5px' type='text' name='register-verification_code' placeholder='کد فعالسازی ' /><input class='inline btn btn-primary' type='submit' value='ثبت' /></form>";
                        $this->registration_successful = true;
                    }else {
	                    $this->errors[] = MESSAGE_REGISTRATION_FAILED;
	                }
            }
        }
    }
	public function getRealIp() {
		$ipaddress = '';
	        if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
	            $ipaddress =  $_SERVER['HTTP_CF_CONNECTING_IP'];
	        } else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
	            $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
	        }
	        else if (isset($_SERVER['HTTP_CLIENT_IP']))
	            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	        else if(isset($_SERVER['HTTP_X_FORWARDED']))
	            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	        else if(isset($_SERVER['HTTP_FORWARDED']))
	            $ipaddress = $_SERVER['HTTP_FORWARDED'];
	        else if(isset($_SERVER['REMOTE_ADDR']))
	            $ipaddress = $_SERVER['REMOTE_ADDR'];
	        else
	            $ipaddress = 'UNKNOWN';
	
	        return $ipaddress;
	}

    /*
     * sends an email to the provided email address
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
    {
    	require './model/inc/mail.php';
        $link = EMAIL_VERIFICATION_URL.''.urlencode($user_id).'-'.urlencode($user_activation_hash);
        $emailmessage  = EMAIL_VERIFICATION_CONTENT.' <a href="'.$link.'" target="_blank" >'.$link.'</a>';
        $mail = new mail_sender(encryptIt('send_'.random_string(2)) , $emailmessage ,$user_email);
    }
    
    
    public function redirect($url, $statusCode = 303){
	header('Location: ' . $url, true, $statusCode);
	die();
    }
	/**
     * Search into database for the user data of user_name specified as parameter
     * @return user data as an object if existing user
     * @return false if user_name is not found in the database
     * TODO: @devplanete This returns two different types. Maybe this is valid, but it feels bad. We should rework this.
     * TODO: @devplanete After some resarch I'm VERY sure that this is not good coding style! Please fix this.
     */
    private function getUserData($user_name)
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // database query, getting all the info of the selected user
            $query_user = $this->db_connection->prepare('SELECT * FROM ctg_users WHERE user_id = :user_name ');
            $query_user->bindValue(':user_name', $user_name, PDO::PARAM_STR);
            $query_user->execute();
            // get result row (as an object)
			if($query_user->rowCount() > 0){
				return $query_user->fetchObject();
			}else{
				return false;
			}
        } else {
            return false;
        }
    }
    /**
     * checks the id/verification code combination and set the user's activation status to true (=1) in the database
     */
    public function verifyNewUser($user_id, $user_activation_hash)
    {
        // if database connection opened
        if ($this->databaseConnection()) {
            // try to update user with specified information
            $query_update_user = $this->db_connection->prepare('UPDATE ctg_users SET user_active = 1, user_activation_hash = NULL, sms_activation_hash = NULL WHERE user_id = :user_id AND (user_activation_hash = :user_activation_hash OR sms_activation_hash = :sms_activation_hash)');
            $query_update_user->bindValue(':user_id', intval(trim($user_id)), PDO::PARAM_INT);
            $query_update_user->bindValue(':user_activation_hash', $user_activation_hash, PDO::PARAM_STR);
            $query_update_user->bindValue(':sms_activation_hash', $user_activation_hash, PDO::PARAM_STR);
            $query_update_user->execute();

            if ($query_update_user->rowCount() > 0) {
                $this->verification_successful = true;
                
                $result_row = $this->getUserData($user_id);
				
                // LOGIN USER
                $_SESSION['sadlsakdas_sdser_id'] = $result_row->user_id;
                $_SESSION['sadlsakdas_sdser_name'] = $result_row->user_name;
                $_SESSION['sadlsakdas_sdser_email'] = $result_row->user_email;
                $_SESSION['sadlsakdas_sdser_logged_in'] = 1;
                $this->messages[] = MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL . '<script>setTimeout(function() { window.location.href = "http://citygram.ir"} , 4000 );</script>';
            } else {
                $this->errors[] = MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL . "=" .$user_activation_hash . "= ". $user_id;
            }
        }
    }
}