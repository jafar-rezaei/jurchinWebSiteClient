<?php

class SiteModel extends Model {

	protected static $dbh;


	protected static $settings;


	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function getSiteSetting(){

		if(null == self::$settings){

			$settingsArray = self::$dbh->read(
				'jor_settings',
				array(),
				array(),
				'',
				'',
				'',
				'm'
			);

			self::$settings = array();
			foreach ($settingsArray as $setting) {
				self::$settings[$setting["parameter"]] = $setting["value"];
			}
			unset($settingsArray );

		}

		return self::$settings;
	}




	public function changeSetting($key , $value){


		return self::$dbh->update(
			'jor_settings',
			array(
				"value" => $value
			),
			array(
				"parameter" => $key
			),
			'=',
			''
		);

	}


	public function addGateway($name , $kind , $data ){


		$result = self::$dbh->create(
			'jor_gateways',
			array(
				"name" 	=> $name ,
				"kind"	=> $kind ,
				"data"	=> serialize($data)
			)
		);
		if($result){
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
			validateCSRFToken($_POST['CSRF_Token'] , null , 1);
		}
	}


	public function editGateway($id , $name , $kind , $data ){

		$result = self::$dbh->update(
			'jor_gateways',
			array(
				"name"	=> $name,
				"kind"	=> $kind,
				"data"	=> serialize($data)
			),
			array(
				"id" => $id
			),
			'=',
			''
		);
		if($result){
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
			validateCSRFToken($_POST['CSRF_Token'] , null , 1);
		}
	}

	public function deleteGateway($id ){

		$result = self::$dbh->delete(
			'jor_gateways',
			array(
				"id" 	=> $id
			)
		);
		if($result){
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
			validateCSRFToken($_POST['CSRF_Token'] , null , 1);
		}

	}

	public function getGateways(){

		return self::$dbh->read(
			'jor_gateways',
			array(),
			array(),
			'',
			'',
			'm'
		);

	}

	public function getGatewayInfo($gid){

		$result = self::$dbh->read(
			'jor_gateways',
			array(),
			array(
				'id'	=> $gid
			),
			'=',
			'',
			'',
			's'
		);

		$result->dataArray = unserialize($result->data);
		return $result;
	}


	public function makeVisit($visits){
		self::$dbh->update(
			'jor_settings',
			array(
				"value"	=> ($visits + 1)
			),
			array(
				"parameter"		=> 'visits'
			),
			"=",
			""
		);

		self::$dbh->create(
			'jor_visits',
			array(
				"date"	=> time()
			)
		);


	}


	public function DeleteSite($uid){



		// delete folder
		if(count($deletedSiteInfo) > 0 && strlen($deletedSiteInfo->sub) > 3){

			$this->delete_dir( MAINROOT);

			$cpAccount = 'jrchin';
			$cpanel = new HostHelper($cpAccount,'000311500666', true);
			$cpanel->deleteDB("site_" . $deletedSiteInfo->sub , $deletedSiteInfo->sub);


		}else{

			// manage dir and db delete manually
			echo json_encode(
				array(
					"message"	=> "خطا در تجزیه پایگاه داده و فایل ها ."
				)
			);
			exit();

		}


		echo json_encode(
			array(
				"message"	=> "ok"
			)
		);
		validateCSRFToken($_POST['CSRF_Token'] , null , 1);

	}



	function delete_dir($src) {

		$dir = is_dir($src) ? opendir($src) : FALSE;

		if( FALSE !== $dir){
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src . '/' . $file) ) {
						$this->delete_dir($src . '/' . $file);
					}
					else if(file_exists($src . '/' . $file)) {
						unlink($src . '/' . $file);
					}
				}
			}
			closedir($dir);
			rmdir($src);
		}
	}



}
?>
