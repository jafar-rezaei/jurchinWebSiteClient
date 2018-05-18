<?php

class CommentsModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function getCommentsList() {

		$comments = self::$dbh->read(
			'jor_comments',
			array(),
			array(),
			'',
			'',
			'ORDER BY date DESC',
			'm'
		);

		foreach ($comments as $key => $cm) {

			$comments[$key]['date'] = jdate("Y/m/d",$cm['date']);

			// get answers
			$comments[$key]["answers"] = self::$dbh->read(
				'jor_comment_Answers',
				array(),
				array(
					"cid"	=> $cm['cid'],
				),
				'=',
				'',
				'ORDER BY date ASC',
				'm'
			);


			// get user data
			if(is_serial($cm['user'])){
				$user = unserialize($cm['user']);
				$comments[$key]['username'] = $user['name'] ;
			}else{
				$user = self::$dbh->read(
					'jor_users',
					array("firstname","lastname"),
					array(
						"user_id" =>  $cm['user']
					),
					'=',
					'',
					'',
					's'
				);
				if(count($user) == 0){
					$comments[$key]['username'] = "بدون نام" ;
				}else{
					$comments[$key]['username'] = $user->firstname." ".$user->lastname ;
				}
			}
		}

		return $comments;
	}

	public function editComment($cid , $comment ){
		$editComment = self::$dbh->update(
			'jor_comments',
			array(
				"comment" => $comment
			),
			array(
				"cid"=>$cid
			),
			'=',
			''
		);

		if($editComment){

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}

	public function answerComment($cid , $uid , $comment ){

		$answerComment = self::$dbh->create(
			'jor_comment_Answers',
			array(
				"cid"		=> $cid,
				"comment"	=> $comment ,
				"date"		=> time() ,
				"uid"		=> $uid
			)
		);

		if($answerComment){

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}


	public function DeleteComment($cid){
		$DeleteComment = self::$dbh->delete(
			'jor_comments',
			array(
				"cid"		=> $cid
			),
			"=",
			""
		);

		if($DeleteComment){

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}

	}

	public function DeleteCommentsWithPost($pid){
		return self::$dbh->delete(
			'jor_comments',
			array(
				"pid"		=> $pid
			),
			"=",
			""
		);
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
