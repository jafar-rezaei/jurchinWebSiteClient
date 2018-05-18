<?php

class CategoriesModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public static function getCatList($cid = 0 , $countPosts = true , array $data = array("cid","parent","name") ) {

		$catList = self::$dbh->read(
			'jor_cats',
			$data ,
			array(
				"parent"	=>	$cid
			),
			'=',
			'',
			'',
			'm'
		);


		// get posts count
		if(count($catList) > 0 && $cid == 0) {
			foreach ($catList as $key => $cat) {

				if($countPosts == true){
					$posts = self::$dbh->read(
						'jor_posts',
						array("count(pid) as count"),
						array(
							"cat"		=>	$cat['cid']
						),
						'=',
						'',
						'',
						's'
					);
					$catList[$key]['posts'] = $posts->count;
				}

				// if main cat
				if($cid == 0) {
					$catList[$key]['subcats'] = self::getCatList( $cat['cid'] , $countPosts , $data);
				}
			}
		}


		return $catList;

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

	public function newCat($catName , $parentCat ) {

		$newCat = self::$dbh->create(
			'jor_cats',
			array(
				"name"		=> $catName,
				"slug"		=> $this->format_uri($catName),
				"parent"	=> $parentCat
			)
		);

		if($newCat) {

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}


	public function editCat($cid , $name) {
		$editCat = self::$dbh->update(
			'jor_cats',
			array(
				"name" => $name
			),
			array(
				"cid"=>$cid,
			),
			'=',
			''
		);

		if($editCat) {
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}

	public function DeleteCat($cid = "" , $deletePosts = false , $respose = true) {


		// defaults
		$operation = "=";
		$andOr = "";
		$whereArray = array();


		if($cid !== "") {
			$whereArray["cid"] = $cid;
			$andOr = "";
			$operation = "=";
		}

		// delete main cat
		$delete = self::$dbh->delete(
			'jor_cats',
			$whereArray,
			$operation,
			$andOr
		);


		// defaults subcat
		$whereArray = array();

		if($cid !== "") {
			$whereArray["parent"] = $cid;
		}

		// read subcats
		$getSubcats = self::$dbh->read(
			'jor_cats',
			array('cid'),
			$whereArray,
			$operation,
			$andOr,
			"",
			"m"
		);


		$postModel = new PostModel();

		if(!$deletePosts) {
			// move posts with cat
			$postModel->movePost($cid , 0);

			// move posts with scat
			if(count($getSubcats) > 0) {
				foreach ($getSubcats as $key => $subcat) {
					$postModel->DeletePostWithCat($subcat['cid']);
				}
			}

		}else{
			// delete posts with cat
			$postModel->DeletePostWithCat($cid);

			// delete posts with subcat
			if(count($getSubcats) > 0) {
				foreach ($getSubcats as $key => $subcat) {
					$postModel->DeletePostWithCat($subcat['cid']);
				}
			}
		}


		// delete subcats
		if(count($getSubcats) > 0) {
			$deleteSubcats = self::$dbh->delete(
				'jor_cats',
				$whereArray,
				$operation,
				$andOr
			);
		}


		if($delete && $respose ) {

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}


	public function getCatInfo( $cid , array $data = array() ) {
		$catInfo = self::$dbh->read(
			'jor_cats',
			$data ,
			array(
				"cid" =>  $cid
			),
			'=',
			'',
			'LIMIT 1',
			's'
		);

		return $catInfo;
	}

	public function getCatInfoWithSlug( $slug , array $data = array() ) {
		$catInfo = self::$dbh->read(
			'jor_cats',
			$data ,
			array(
				"slug" =>  $slug
			),
			'=',
			'',
			'LIMIT 1',
			's'
		);

		return $catInfo;
	}


	public function getCatsListHome($catPostCount = false) {

		// defaults
		$operation = "";
		$andOr = "";
		$whereArray = array();


		$cats = self::$dbh->read(
			'jor_cats',
			array(),
			$whereArray,
			$operation,
			$andOr,
			"",
			'm'
		);

		foreach ($cats as $key => $cat) {
			if($catPostCount){
				$cats[$key]["posts"] = $this->getCatpostsHome($sid , $cat["cid"]);
			}

			$cats[$key]["subcats"] = self::$dbh->read(
				'jor_cats',
				array(),
				array(
					"parent"	=> $cat["cid"]
				),
				"=",
				"",
				"",
				'm'
			);

		}

		return $cats;

	}

	public function getCatpostsHome($cid ){

		$operation = "=";
		$andOr = "";
		$whereArray = array(
			"cat"	=> $cid
		);


		$posts = self::$dbh->read(
			'jor_posts',
			array(),
			$whereArray,
			$operation,
			$andOr,
			"",
			'm'
		);

		return count($posts);


	}



	public function getCatInfoHome($sid , $pid){
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

		return $post;
	}




}
?>
