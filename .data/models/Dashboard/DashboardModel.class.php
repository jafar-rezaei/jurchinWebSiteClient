<?php

class DashboardModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function getStatics() {
		$uid = intval(jamework::$session->get('user_id'));
		$site = jamework::$session->get('site');

		$posts = self::$dbh->read(
			'jor_posts' ,
			array() ,
			array() ,
			'' ,
			'' ,
			'ORDER BY adddate DESC' ,
			'm'
		);


		$comments = self::$dbh->read(
			'jor_comments' ,
			array() ,
			array() ,
			'' ,
			'' ,
			'ORDER BY date DESC' ,
			'm'
		);


		$visits = self::$dbh->read(
			'jor_visits' ,
			array("DATE(FROM_UNIXTIME(date)) AS ForDate" , "COUNT(*) AS visits") ,
			array(
				'date' => time() - (24*60*60*7)
			) ,
			'>' ,
			'' ,			// and or
			'GROUP BY DATE(FROM_UNIXTIME(date)) ORDER BY ForDate DESC LIMIT 7' ,
			'm'
		);

		$SiteModel = new SiteModel();
		$visitsCount = $SiteModel->getSiteSetting()["visits"];


 		$visitsList = array();
 		for($i = 7; $i>0;$i--){

 			$now = time();
 			$aDay = 24*60*60;
 			$flgFound = false;

			$xDay = $now - $aDay*$i;
			$dayaName = jdate("l",$xDay);

 			foreach ($visits as $k => $visit) {
 				$sep = explode("-",$visit['ForDate']) ;
 				$unix = mktime("0" , "0" , "0" , $sep[1] , $sep[2] , $sep[0]);

	 			if ($xDay < $unix && ($now - $aDay*($i-1)) > $unix){

	 				$visitsList[$dayaName] = $visit['visits'];
	 				$flgFound=true;
	 				unset($visits[$k]);
	 				break;
	 			}
 			}

 			// if no visit
 			if(!$flgFound){
 				$visitsList[$dayaName] = 0;
 			}
 		}



		$cmCount = self::$dbh->read(
			'jor_comments' ,
			array("MONTH(FROM_UNIXTIME(date)) AS ForMonth", "YEAR(FROM_UNIXTIME(date)) AS ForYear" , "COUNT(*) AS count") ,
			array(
				'date' => time() - (24*60*60*30*7)
			) ,
			'>' ,
			'' ,			// and or
			'GROUP BY MONTH(FROM_UNIXTIME(date)), YEAR(FROM_UNIXTIME(date)) ORDER BY ForMonth DESC,ForYear DESC' ,
			'm'
		);


 		$cmList = array();
 		for($i = 7 ; $i>0;$i--){
 			$now = time();
 			$aMonth = 24*60*60*30;
 			$flgFound = false;
 			$xMonth = $now - ($aMonth*$i);
 			$MonthName = jdate("F",$xMonth);

 			foreach ($cmCount as $k => $cm) {
 				$unix = mktime("0" , "0" , "0" , $cm["ForMonth"] , "0" , $cm["ForYear"]);

	 			if ($xMonth < $unix && ($now - ($aMonth*($i-1))) > $unix){

	 				$cmList[$MonthName] = $cm['count'];
	 				$flgFound=true;
	 				unset($cmCount[$k]);
	 				break;
	 			}
 			}

 			// if no visit
 			if(!$flgFound){
 				$cmList[$MonthName] = 0.1;
 			}
 		}

 		$lastVisit = self::$dbh->read(
			'jor_visits' ,
			array("date") ,
			array() ,
			'' ,
			'' ,			// and or
			'ORDER BY date DESC LIMIT 1' ,
			's'
		);



		// pages
 		$dir = VIEW_PATH.'Home/';
		$pages = glob($dir."page_*");

		// >> SORT GLOBED PAGES
		array_multisort(array_map('filemtime', $pages), SORT_NUMERIC, SORT_DESC, $pages);
		if ($pages){
			foreach ($pages as $key => $page) {
				$pageName = str_replace($dir , "", $page);
				$pageName = str_replace("page_" , "", $pageName);
				$pageName = str_replace(".html" , "", $pageName);
				$pages[$key] = $pageName;
			}
		}


 		// free space of user
 		$usedSpace 			= foldersize(MAINROOT);
 		$siteSpaceInByte 	= 367001600 ;	// 350 MB
 		$precentage 		= round(abs((($siteSpaceInByte - $usedSpace)/ $siteSpaceInByte ) *100 - 100) , 1);

 		$data = [
 			"posts" 		=> $posts,
 			"comments" 		=> $comments,
 			"visits" 		=> $visitsList ,
			"visitsCount" 	=> $visitsCount ,
 			"cmList" 		=> $cmList ,
			"lastVisit" 	=> $lastVisit ,
 			"pages" 		=> $pages ,
			"UsedSpace" 	=> $precentage
 		];


		return $data;

	}

}
