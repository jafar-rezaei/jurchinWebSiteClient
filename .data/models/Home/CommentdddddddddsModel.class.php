<?php

class CommentsModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = SiteModel::SiteDB();

	}



	public function getComments($pid = "" , $paging = "") {

		$whereArray = array();
		$operation = "";

		if($pid !== ""){
			$whereArray = array("pid" => $pid);
			$operation = "=";
		}


		$comments = self::$dbh->read(
			'jor_comments',
			array(),
			$whereArray,
			$operation,
			"",
			"",
			'm'
		);

		foreach ($comments as $key => $comment) {

			$comments[$key]["answers"] = self::$dbh->read(
				'jor_comment_Answers',
				array(),
				array(
					"cid"	=> $comment["cid"]
				),
				"=",
				"",
				"",
				'm'
			);



			$user = array();
			if(is_serial($comment["user"])){

				$userArr = unserialize($comment["user"]);
				$user["name"] 	= $userArr["name"];
				$user["email"] 	= $userArr["email"];
				$user["avatar"]	= $userArr["avatar"];

			} else {
				$userObj = self::$dbh->read(
					'jor_users',
					array("firstname" , "lastname" , "avatar" , "user_email" ),
					array(
						"user_id"	=> $comment["user"]
					),
					"=",
					"",
					"",
					's'
				);

				if(count($userObj) > 0){

					$user = (array) $userObj;
					$user["name"] = $user["firstname"]." ".$user["lastname"];
				}else{
					$user["name"] = "deleted";


				}
			}

			$comments[$key]["user"] = $user;


			// date
			$comments[$key]["adddate"] = jdate("d F Y",$comment["date"]);
			$comments[$key]["addtime"] = jdate("h:i",$comment["date"]);
			$comments[$key]["adddateTime"] = jdate("Y/m/d-H:i",$comment["date"]);

		}


		return $comments;


	}


	public function addComment($name , $email , $comment , $pid , $user = null  ){

		if($user == null){
			$user = serialize(
				array(
					"name"	=> $name ,
					"email"	=> $email
				)
			);
		}

		return self::$dbh->create(
			"jor_comments",
			array(
				'pid'		=> $pid ,
				'date'		=> time() ,
				'comment'	=> $comment ,
				'user'		=> $user
			)
		);

	}

}
?>
