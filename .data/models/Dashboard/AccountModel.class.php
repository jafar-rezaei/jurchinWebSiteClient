<?php

class AccountModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}


	/**
	 * Search into database for the user data
	 * @param user_if which is user_id OR user_email
	 * @return user data as an object if existing user
	 */
	public static function getUserData($user_id) {
		$result_row = parent::$dbh->read(
			'jor_users' ,
			array(
				'user_id',
				'firstname' ,
				'lastname',
				'user_email' ,
				'user_actiovation_email_sendagain' ,
				'user_logkey' ,
				'user_password_hash'
			),
			array(
				'user_id' 		=> $user_id
			) ,
			'=' ,
			'' ,
			'' ,
			's'
		);
		return $result_row ;
	}


	public function checkHasAccount($mail){

		$res = self::$dbh->read(
			'jor_users',
			array("user_id" , "user_password_hash"),
			array(
				"user_email" 		=> $mail
			),
			'=',					// operation
			'',						// and or
			'',
			's'
		);

		return $res;

	}

	public function changePass( $oldPass , $newPass , $newPassRepeat) {

		if (empty($oldPass) || empty($newPass) || empty($newPassRepeat)) {
			return jamework::$lang['MESSAGE_PASSWORD_EMPTY'];
		// is the repeat password identical to password
		} elseif ($newPass !== $newPassRepeat) {
			return jamework::$lang['MESSAGE_PASSWORD_BAD_CONFIRM'];
		// password need to have a minimum length of 6 characters
		} elseif (strlen($newPass) < 6) {
			return jamework::$lang['MESSAGE_PASSWORD_TOO_SHORT'];
		} else {

			// database query, getting hash of currently logged in user (to check with just provided password)
			$uid = jamework::$session->get('user_id');
			$result_row = self::getUserData($uid);

			// if this user exists
			if (isset($result_row->user_password_hash)) {

				// using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
				if (password_verify($oldPass, $result_row->user_password_hash)) {

					$config = configApp::getConfig();
					$hash_cost_factor = (isset($config['HASH_COST_FACTOR']) ? $config['HASH_COST_FACTOR'] : 8);
					$user_password_hash = password_hash($newPass, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

					$result_row = parent::$dbh->update(
						'jor_users' ,
						array(
							'user_password_hash' => $user_password_hash
						) ,
						array(
							'user_id' => intval($uid)
						),
						'=' ,
						''
					);


					// check if exactly one row was successfully changed:
					if (count($result_row)) {
						return TRUE;
						//$this->messages[] = jamework::$lang['MESSAGE_PASSWORD_CHANGED_SUCCESSFULLY'];
					} else {
						return jamework::$lang['MESSAGE_PASSWORD_CHANGE_FAILED'];
					}
				} else {
					return jamework::$lang['MESSAGE_OLD_PASSWORD_WRONG'];
				}
			} else {
				return jamework::$lang['MESSAGE_USER_DOES_NOT_EXIST'];
			}
		}

	}


	public function editUserInfo($uid , $user) {
		return self::$dbh->update(
			'jor_users',
			array(
				"firstname" => $user["first_name"] ,
				"lastname"	=> $user["last_name"] ,
				"ucountry"	=> $user["country"] ,
				"ucity"		=> $user["city"] ,
				"address"	=> $user["address"] ,
				"phone"		=> $user["phoneNumber"] ,
				"user_name"	=> $user["mobileNumber"] ,		// mobile
				"aboutMe"	=> $user["aboutMe"]
				//"gender"	=> $user["gender"]
			),
			array(
				"user_id" 		=> $uid
			),
			'=',					// operation
			''						// and or
		);
		
	}


	public function getCountries() {
		return self::$dbh->read(
			'jor_location',
			array("local_name" , "id"),
			array(
				"in_location" 		=> NULL
			),
			'IS',					// operation
			'',						// and or
			'',
			'm'
		);

	}

	public function getCities($country) {
		return self::$dbh->read(
			'jor_location',
			array("local_name" , "id"),
			array(
				"in_location" 		=> $country
			),
			'=',					// operation
			'',						// and or
			'',
			'm'
		);

	}

}
?>
