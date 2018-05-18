<?php

class SiteModel extends Model {

	protected static $database = NULL;




	// have config file in self hosted
	// public static function checkAddress() {
	//
	// 	$address = str_replace("Action", "", ACTION);
	// 	$dbh = parent::dataBase();
	// 	return $dbh->read(
	// 		'jor_sites',
	// 		array(),
	// 		array(
	// 			"sub" => $address
	// 		),
	// 		'=',			// operation
	// 		'',				// and or
	// 		'',				// option
	// 		's'				// mode
	// 	);
	// }


	// public function gateWayInfo($id) {
	// 	return self::$database->read(
	// 		'jor_gateways',
	// 		array(),
	// 		array(
	// 			"id"	=> $id
	// 		),
	// 		"=",
	// 		"",
	// 		"",
	// 		"s"
	// 	);
	// }
	//




	//
	// public function getSiteInfo() {
	//
	// 	if(null !== self::$database){
	// 		$result = self::$database->read(
	// 			'jor_settings',
	// 			array("parameter","value"),
	// 			array(),
	// 			'=',			// operation
	// 			'',				// and or
	// 			'',				// option
	// 			'm'				// mode
	// 		);
	// 		return $result;
	// 	}else{
	// 		return false;
	// 	}
	//
	// }
	//




	//
	// public function getPageInfo($pageName){
	// 	return self::$database->read(
	// 		'jor_pages',
	// 		array(),
	// 		array(
	// 			"address"	=> $pageName
	// 		),
	// 		"=",
	// 		"",
	// 		"",
	// 		"s"
	// 	);
	// }





}
?>
