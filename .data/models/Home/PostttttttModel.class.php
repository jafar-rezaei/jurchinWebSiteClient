<?php

class PostModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = SiteModel::SiteDB();

	}



	public function getPostList($cat = 0 , $subcat = 0 , $sortBy = "new" , $search = "" ) {

		// defaults
		$where = "";
		$i = 1;


		// show from cat
		if($cat > 0){
			$where = "WHERE (cat = ? OR subcat = ? ) ";
		}


		// is that search
		if( is_array($search) ){

			$hasAnd  = (strlen($where) > 0 ? " AND " : " WHERE ");

			if($search["in"] == 0) {		// both
				$where = $hasAnd . " ( `title` LIKE ? OR `content` LIKE ? ) ";
			}else if($search["in"] == 2) {	// just content
				$where = $hasAnd . " ( `content` LIKE ? ) ";
			}else{							// just title
				$where = $hasAnd . " ( `title` LIKE ? ) ";
			}

		}


		// sorting
		if($sortBy == "visit"){
			$sortBy = " ORDER BY visits DESC";
		}else if($sortBy == "comment"){
			$sortBy = " ORDER BY comments DESC";
		}else{
			$sortBy = " ORDER BY adddate DESC";
		}


		$posts_query = self::$dbh->query("SELECT * from jor_posts " . $where . $sortBy);


		if($cat > 0){
			$posts_query->bindValue($i , "%$cat%" , PDO::PARAM_STR);
			$i++;
			$posts_query->bindValue($i , "%$cat%" , PDO::PARAM_STR);
			$i++;
		}

		if( is_array($search) ){
			$keyword = $search['keyword'];

			$posts_query->bindValue($i , "%$keyword%" , PDO::PARAM_STR);
			$i++;

			if($search["in"] == 0) {
				$posts_query->bindValue($i , "%$keyword%" , PDO::PARAM_STR);
				$i++;
			}
		}


		$posts_query->execute();
		$posts = $posts_query->fetchAll();



		foreach ($posts as $key => $post) {

			// get post category info
			if($post['cat'] !== "0"){
				$cat = self::$dbh->read(
					'jor_cats',
					array("name"),
					array(
						"cid" =>  $post['cat']
					),
					'=',
					'',
					'',
					's'
				);

				$posts[$key]['cat'] = count($cat) ? $cat->name : "unkown";
				$posts[$key]['catid'] = $post['cat'];
			}else{
				$posts[$key]['cat'] = "عمومی";
				$posts[$key]['catid'] = "0";
			}

			// date
			$posts[$key]['adddate'] = jdate("d F Y",$post['adddate']);
			$posts[$key]['addtime'] = jdate("h:i",$post['adddate']);
			$posts[$key]['adddateTime'] = jdate("Y/m/d-H:i",$post['adddate']);

			// author
			$user = self::$dbh->read(
				'jor_users',
				array("firstname","lastname","avatar"),
				array("user_id" => $post['uid']),
				"=",
				"",
				"",
				"s"
			);

			if(count($user)){
				$posts[$key]['author'] = $user->firstname." ".$user->lastname;
				$posts[$key]['avatar'] = $user->avatar;
			}else{
				$posts[$key]['author'] = "unkown";
				$posts[$key]['avatar'] = "http://www.jurchin.com/public/img/savatar.png";
			}


		}
		return $posts;

	}



	public function showPost($slug){

		$post = self::$dbh->read(
			'jor_posts',
			array(),
			array(
				"slug" => urldecode($slug)
			),
			'LIKE',
			'',
			'LIMIT 1',
			's'
		);


		if($post->cat !== "0"){
			$cat = self::$dbh->read(
				'jor_cats',
				array("name"),
				array(
					"cid" =>  $post->cat
				),
				'=',
				'',
				'',
				's'
			);

			$post->cat = count($cat) ? $cat->name : "unkown";
			$post->catid = $post->cat;
		}else{
			$post->cat = "عمومی";
			$post->catid = "0";
		}


		// load comments
		if($post->comments !== "0"){

			$commentsModel = new CommentsModel();
			$post->comments = $commentsModel->getComments($post->pid);

		}


		// date
		$post->adddate = jdate("d F Y",$post->adddate);
		$post->addtime = jdate("h:i",$post->adddate);
		$post->adddateTime = jdate("Y/m/d-H:i",$post->adddate);

		// author
		$user = self::$dbh->read(
			'jor_users',
			array("firstname","lastname","avatar"),
			array("user_id" => $post->uid),
			"=",
			"",
			"",
			"s"
		);


		$defaultAvatar = "http://www.jurchin.com/public/img/savatar.png";
		if(count($user)){
			$post->author = $user->firstname." ".$user->lastname;
			$post->avatar = empty($post->avatar) ? $defaultAvatar : $user->avatar;
		}else{
			$post->author = "unkown";
			$post->avatar = $defaultAvatar;
		}

		$post->tags = $this->getTagsList($post->pid);

		return $post;
	}




	public function getTagsList($pid){

		return self::$dbh->read(
			'jor_tags',
			array(),
			array(
				"pid" 	=> $pid
			),
			"=",
			"" ,
			"" ,
			"m"
		);
	}


}
?>
