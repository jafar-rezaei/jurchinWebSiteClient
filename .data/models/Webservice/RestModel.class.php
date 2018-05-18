<?php

class RestModel extends Model {

	protected static $dbh;
	protected static $database;


	public function __construct() {

		self::$dbh = parent::dataBase();

	}

	public function checksiteKey($sitekey) {

		$sitekey = self::$dbh->read(
			'jor_settings',
			array("value"),
			array(
				"parameter"	=> "bot_key",
				"value"				=> $sitekey
			),
			'=-=',
			'AND',
			'',
			's'
		);
		if(count($sitekey) > 0){
			return "ok";
		}else{
			return "notValidKey";
		}

	}




	public function getCities($country) {
		$res = self::$dbh->read(
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
		return $res ;
	}


	public function changePayKey($pid , $trankey , $primkey) {

		$res = self::$dbh->update(
			'jor_pays',
			array(
				"trankey"	=> $trankey
			),
			array(
				"id" 		=> $pid ,
				"primkey"	=> $primkey
			),
			'=-=',					// operation
			'AND'					// and or
		);

		return $res > 0;

	}

	public function checkHasAccount($mail){

		$res = self::$dbh->read(
			'jor_users',
			array("user_id"),
			array(
				"user_email" 		=> $mail
			),
			'=',					// operation
			'',						// and or
			'',
			'm'
		);

		return count($res);

	}



	public function getCatList($cid = 0) {

		$catList = array();
		$catList = self::$dbh->read(
			'jor_cats',
			array(),
			array(
				"parent"	=>	$cid
			),
			'=',
			'',
			'',
			'm'
		);


		// get posts count
		if(count($catList) > 0 && $cid == 0){
			foreach ($catList as $key => $cat) {
				$posts = self::$dbh->read(
					'jor_posts',
					array("count(pid) as count"),
					array(
						"cat"	=>	$cat['cid']
					),
					'=',
					'',
					'',
					's'
				);
				$catList[$key]['posts'] = $posts->count;

				// if main cat
				if($cid == 0){
					$catList[$key]['subcats'] = $this->getCatList($cat['cid']);
				}
			}
		}


		return $catList;
	}

	public function getPostsList($cat = "" , $subcat = "") {

		// defaults
		$operation = "";
		$andOr = "";
		$whereArray = array();


		if($cat !== ""){
			$whereArray["cat"] = $cat;
			$operation = "=";
			$andOr = "";
		}

		if($subcat !== ""){
			$whereArray["subcat"] = $subcat;
			$operation .= ($cat !== "") ? "-=" : "=" ;
			$andOr .= ($cat !== "") ? "AND" : "";
		}


		$posts = self::$dbh->read(
			'jor_posts',
			array(),
			$whereArray,
			$operation,
			$andOr,
			'ORDER BY adddate DESC',
			'm'
		);

		foreach ($posts as $key => $post) {

			if($post['cat'] !== "0"){
				$cat = self::$dbh->read(
					'jor_cats',
					array("name"),
					array(
						"cid" =>  $post['cat'] ,
					),
					'=',
					'',
					'',
					's'
				);
				$posts[$key]['cat'] = $cat->name;
			}else{
				$posts[$key]['cat'] = "عمومی";
			}
			$posts[$key]['adddate'] = jdate("Y/m/d",$post['adddate']);
			$posts[$key]['adddateTime'] = jdate("Y/m/d-H:i",$post['adddate']);

		}
		return $posts;

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


	public function getGatewaysInfo() {
		$gateways = self::$dbh->read(
			'jor_gateways',
			array(),
			array(),
			'',
			'',
			'',
			'm'
		);
		return $gateways;

	}


	public function getPagesList() {

		$PagesModel = new PagesModel();
		return $PagesModel->getPagesList();

	}




	public function getPageData($name) {


			$PagesModel = new PagesModel();
			return $PagesModel->getPageData($name);

	}

	public function getTemplateName(){
		return self::$dbh->read(
			'jor_settings',
			array("value"),
			array("parameter" => "template"),
			'=',
			'',
			'',
			's'
		);
	}



	private static function replaceTags($content) {


			//file_put_contents(ROOT."file.txt" , $content);
			$dom = new DOMDocument();
			@$dom->loadHTML($content);
			$elements = $dom->getElementsByTagName('twig');


			for ($i = $elements->length - 1; $i >= 0; $i --) {
			    $nodePre = $elements->item($i);

					$roleText = $nodePre->getAttribute("data-role");

					if(trim($roleText) == ""){
						$nodePre->parentNode->removeChild($nodePre);
					}else{

						$options = $nodePre->getAttribute("data-options");
						$helperText = $options ? "<helper>".$nodePre->getAttribute("data-options")."</helper>" : "";

				    $nodeDiv = $dom->createTextNode('{# '.$helperText.' #}'.$roleText."{# ".strip_tags($dom->saveHTML($nodePre) , "<img><a><i>") ." #}\n");
				    $nodePre->parentNode->replaceChild($nodeDiv, $nodePre);
					}
			}

			return $dom->saveHTML();
	}

	public function setPageData($name , $content) {


		header('Cache-Control:no-cache, must-revalidate');

		if($content == "") {
			return "Not valid No content";
		} else {

			$content = self::replaceTags(html_entity_decode(safe($content))) ;

		}


		// templateName

		$SiteModel = new SiteModel();
		$site = $SiteModel->getSiteSetting();
		$templateName = $site['template'];


		$dom = new DOMDocument();
		$dom->loadHTML($content);


		$index = $dom->getElementsByTagName('var')->item(0);
		$indexValue = $dom->saveHTML($index);


		$indexValue = str_replace(array('&#39;' , '&gt;' , '&lt;' , '<var>' , '</var>' , '%7B' , '%7D' , '%20') , array("'",">","<" , "" , "" , "{" , "}" , " ") ,$indexValue);


		$saveRes = file_put_contents( VIEW_PATH . "Home" . DS . $templateName. DS . "page_" . $name . ".html" , $indexValue);



		if($saveRes !== FALSE) {
			return "ok";
		} else {
			return "Error on page save";
		}

	}

	public function saveImage($name , $b64str) {


		//Save image
		$success = file_put_contents( UPLOAD_PATH . $name, base64_decode($b64str));


    if ($success == FALSE) {
			return "Error on image save";
		} else {
			return "ok";
		}


	}



	private function makeConnectionWithSubDomain($sub , $key , $pdo = false){

		if(null !== self::$dbh){
			return self::$dbh;
		}else if($sub == ""){
			return false;
		}

		self::$dbh = new DatabaseHelper();
		$siteDBH = self::$dbh->site(
			array(
				'DataBaseHost' => '127.0.0.1' ,
				'DataBaseName' => 'jrchin_site_' . $sub,
				'DataBaseUser' => 'jrchin_' . $sub ,
				'DataBasePass' => str_rot13($key)
			)
		);

		return $pdo ? $siteDBH : self::$dbh ;

	}

}
?>
