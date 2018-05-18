<?php

class UserModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function userInfo($uid = 0 , array $info = array() ) {

		$getLogoutLink = false;
		if($uid == 0){
			$uid = intval(jamework::$session->get('user_id'));
			$getLogoutLink = true;
		}

		$user = self::$dbh->read(
			'jor_users' ,
			$info ,
			array(
				'user_id' => $uid ,
			) ,
			'=' ,
			'' ,
			'' ,
			's'
		);

		if($getLogoutLink && count($user) > 0) {
			$user->logOutLink = jamework::getConfig()["SITEURL"]. "Dashboard/Main/Logout/".$user->user_logkey;
		}

		if($user->avatar == "") {
			$user->avatar = "http://www.jurchin.com/public/img/savatar.png";
		}

		return $user;

	}

	public function changeAvatar($uid , $avatar){

		$user = self::$dbh->update(
			'jor_users' ,
			array(
				"avatar"	=> $avatar
			) ,
			array(
				'user_id'	=> intval($uid) ,
			) ,
			'=' ,
			''
		);
	}


	public function newUser($user_password , $user_email , $firstname = "نام" , $lastname = "نام خانوادگی" , $ug = 3 , $ExtraData){

		$user_password_hash = password_hash(
			$user_password,
			PASSWORD_DEFAULT,
			array('cost' => jamework::getConfig()['HASH_COST_FACTOR'])
		);

		$dateTime = date('m/d/Y h:i:s', time());

		$createdArray = array(
			"user_password_hash" 			=> $user_password_hash ,
			"user_email"					=> $user_email ,
			"user_active"					=> 1 ,
			"user_registration_datetime"	=> $dateTime ,
			"user_registration_ip"			=> getRealIp() ,
			"firstname"						=> $firstname ,
			"lastname"						=> $lastname ,
			"user_group"					=> 3		//user
		);



		if(array_key_exists("phoneNumber", $ExtraData)){
			$createdArray["user_name"] = $ExtraData["phoneNumber"];
		}

		if(array_key_exists("gender", $ExtraData)){
			$createdArray["gender"] = $ExtraData["gender"];
		}


		return self::$dbh->create(
			'jor_users',
			$createdArray
		);
	}

}
