<?php


class PostModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function getPostList($cat = "" , $subcat = "" , array $data = array() ) {

		// defaults
		$operation = "";
		$andOr = "";
		$whereArray = array();


		if($cat !== "") {
			$whereArray["cat"] = $cat;
			$operation = "=";
			$andOr = "";
		}

		if($subcat !== "") {
			$whereArray["subcat"] = $subcat;
			$operation .= ($cat !== "") ? "-=" : "=" ;
			$andOr .= ($cat !== "") ? "AND" : "";
		}


		$posts = self::$dbh->read(
			'jor_posts',
			$data,
			$whereArray,
			$operation,
			$andOr,
			'ORDER BY adddate DESC',
			'm'
		);

		foreach ($posts as $key => $post) {

			if($post['cat'] !== "0") {

				$CategoriesModel = new CategoriesModel();
				$cat = $CategoriesModel->getCatInfo($post['cat'] , array("name"));

				$posts[$key]['cat'] = $cat->name;
				
			}else{
				$posts[$key]['cat'] = "عمومی";
			}
			$posts[$key]['adddate'] = jdate("Y/m/d",$post['adddate']);
			$posts[$key]['adddateTime'] = jdate("Y/m/d-H:i",$post['adddate']);

		}
		return $posts;

	}


	public function getPostInfo($pid) {
		$post = self::$dbh->read(
			'jor_posts',
			array(),
			array(
				"pid" =>  $pid
			),
			'=',
			'',
			'LIMIT 1',
			's'
		);


		// get post category info
		if($post->cat !== "0"){

			$CategoriesModel = new CategoriesModel();
			$cat = $CategoriesModel->getCatInfo($post->cat , array("name"));

			$post->cat = count($cat) ? $cat->name : "unkown";
			$post->catid = $post->cat;
		}else{
			$post->cat = "عمومی";
			$post->catid = "0";
		}


		// date
		$post->adddate = jdate("d F Y",$post->adddate);
		$post->addtime = jdate("h:i",$post->adddate);
		$post->adddateTime = jdate("Y/m/d-H:i",$post->adddate);

		// author
		$UserModel = new UserModel();
		$user = $UserModel->userInfo($post->uid , array("firstname","lastname","avatar"));


		if(count($user)){
			$posts[$key]['author'] = $user->firstname." ".$user->lastname;
			$posts[$key]['avatar'] = $user->avatar;
		}else{
			$posts[$key]['author'] = "unkown";
			$posts[$key]['avatar'] = "http://www.jurchin.com/public/img/savatar.png";
		}


		return $post;

	}


	public function format_uri( $string, $separator = '-' ) {
		$accents_regex = '~&([a-zآ-ی]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
		$special_cases = array( '&' => 'and', "'" => '');
		$string = mb_strtolower( trim( $string ), 'UTF-8' );
		$string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
		$string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
		$string = preg_replace("/[^a-z0-9آ-ی]/u", "$separator", $string);
		$string = preg_replace("/[$separator]+/u", "$separator", $string);
		return $string;
	}


	public function newPost($uid , $postTitle , $postCategory , $postKind , $postContent , $postTags , $postPic , $comment_active_perm) {

    $slug = $this->format_uri($postTitle);

		$checkSlug = self::$dbh->read(
			'jor_posts',
			array("pid"),
			array(
				"slug"		=> $slug
			),
			"=",
			"",
		    ""
		);

		if(count($checkSlug) > 0 ) {
		    $slug = $slug + count($checkSlug);
		}


		$addPost = self::$dbh->create(
			'jor_posts',
			array(
				"uid"		=> $uid ,
				"title"		=> $postTitle ,
				"slug"		=> $slug ,
				"cat"		=> $postCategory ,
				"kind"		=> $postKind ,
				"content"	=> $postContent ,
				"image"		=> $postPic ,
				"adddate"	=> time() ,
				"comment_active_perm"	=> $comment_active_perm
			)
		);


		if($addPost) {

			// add tags
			if(count($postTags) > 0) {
				foreach ($postTags as $key => $tag) {
					if(strlen($tag) > 1) {
						self::$dbh->create(
							'jor_tags',
							array(
								"pid" 		=> $addPost ,
								"tag"		=> $tag
							)
						);
					}
				}
			}

			return true;
		}
	}


	public function DeletePost($pid ) {

		$delete = self::$dbh->delete(
			'jor_posts',
			array(
				"pid" 		=> $pid
			),
			"=",
			""
		);


		if($delete) {
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}


	public function DeletePostWithCat($cid) {

		$posts = $this->getPostList($cid , $cid);

		foreach ($posts as $key => $post) {

			// delete comments
			$CommentsModel = new CommentsModel();
			$CommentsModel->DeleteCommentsWithPost($pid);

			// delete tags
			self::deleteTag($pid);

		}

		// TODO : delete attachs

		return self::$dbh->delete(
			'jor_posts',
			array(
				"cat"		=> $cid ,
				"subcat"	=> $cid
			),
			"=-=",
			"OR"
		);
	}


	public function movePost($cid , $to = 0) {

		$movePost = self::$dbh->update(
			'jor_posts',
			array(
				"cat" 		=> $to ,
				"subcat"	=> $to
			),
			array(
				"cat" 		=> $cid ,
				"subcat" 	=> $cid
			),
			"=-=",
			"OR"
		);

		return $movePost;
	}


	public function getPostListMain($cat = 0 , $subcat = 0 , $sortBy = "new" , $search = "" ) {

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
			$posts_query->bindValue($i , $cat , PDO::PARAM_INT);
			$i++;
			$posts_query->bindValue($i , $cat , PDO::PARAM_INT);
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


			// $post->cat = count($cat) ? $cat->name : "unkown";
			// $post->catid = $post->cat;


			// get post category info
			if($post['cat'] !== "0"){

				$CategoriesModel = new CategoriesModel();
				$cat = $CategoriesModel->getCatInfo($post['cat'] , array("name"));

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
			$UserModel = new UserModel();
			$user = $UserModel->userInfo($post['uid'] , array("firstname","lastname","avatar"));


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

			$CategoriesModel = new CategoriesModel();
			$cat = $CategoriesModel->getCatInfo($post->cat , array("name"));

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





	########################
	#### 		 Tag Methods
	########################

	public static function deleteTag($pid) {

		return self::$dbh->delete(
			'jor_tags',
			array(
				"pid" 	=> $pid
			),
			"=",
			""
		);
	}

	public function getTagsList($pid = "" , array $data = array() ) {

		$where = array();
		$operation = "";
		if($pid !== ""){
			$where = array("pid" 	=> $pid);
			$operation = "=";
		}
		return self::$dbh->read(
			'jor_tags',
			$data,
			$where,
			$operation,
			"" ,
			"" ,
			"m"
		);
	}

	public function getTagPosts($title) {
			$tags = self::$dbh->read(
				'jor_tags',
				array(),
				array(
					"tag" 	=> urldecode($title)
				),
				"=",
				"" ,
				"" ,
				"m"
			);


			$posts = array();
			foreach ($tags as $tag) {
				$posts[] = $this->getPostInfo($tag['pid']);
			}

			return $posts;

	}



}
?>
