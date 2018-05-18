<?php

class PagesModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}


	public function getTemplateName(){

		if(defined('Controller')){
			$templateName = Controller::$site['template'];
		}else{

			$SiteModel = new SiteModel();
			$site = $SiteModel->getSiteSetting();
			$templateName = $site['template'];

		}

		return $templateName;
	}

	public function getPagesList() {

		$SiteModel = new SiteModel();
		$site = $SiteModel->getSiteSetting();
		$templateName = $site['template'];

		// pages
		$pages = glob(VIEW_PATH . "Home" . DS . $templateName . "/page_*");

		$retArray = array();

		// >> SORT GLOBED PAGES
		array_multisort(array_map('filemtime', $pages), SORT_NUMERIC, SORT_DESC, $pages);
		if ($pages){
			foreach ($pages as $key => $page) {
				$pageName = str_replace(VIEW_PATH . "Home" . DS . $templateName . DS , "", $page);
				$pageName = str_replace("page_" , "", $pageName);
				$pageName = str_replace(".html" , "", $pageName);
				$retArray[$key]["name"] = $pageName;

				$retArray[$key]["info"] = self::$dbh->read(
					'jor_pages',
					array(),
					array(
						"address"	=> $pageName
					),
					"=",
					"",
					"",
					"s"
				);
			}



		}

		return $retArray;
	}


	public function getPageData($name) {


		$SiteModel = new SiteModel();
		$site = $SiteModel->getSiteSetting();
		$templateName = $site['template'];


		$path = VIEW_PATH . "Home" . DS . $templateName . "/page_";

		if(!file_exists($path . $name . ".html")){
				return "NoFile";
		}

		$pageContent = "<var>" . file_get_contents( $path . $name . ".html") . "</var>";


		$pattern = '/{#\s*(.*?<helper>(.*?)\<\/helper>.*?)?#}\s*\n*(.*?){#(.+?)#}/';
		$pageData = preg_replace_callback(
			$pattern,
			function ($matches) {
				$options = "";
				if(trim($matches[2]) !== ""){
					$options = 'data-options="'.$matches[2].'"';
				}
				return '<twig data-role="' . htmlentities($matches[3]) . '" ' . $options . ' >'.trim($matches[4]).'</twig>';
			},
			$pageContent
		);


		// add page to temp
		$tempContent = file_get_contents( $path . "temp.html");
		$pattern = '/{{.*pageContent.*}}/';
		$pageData = preg_replace(
			$pattern,
			$pageData,
			$tempContent
		);


		return $pageData;

	}


	public function DeletePage($paramID){

		$templateName = $this->getTemplateName();

		$pageName = "page_".$paramID.".html";

		if(unlink(VIEW_PATH . "Home" . DS . $templateName . DS . $pageName)){

			// delete from db
			$deletePage = self::$dbh->delete(
				'jor_pages',
				array(
					"address"	=> $paramID
				),
				"=",
				""
			);

			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}




	public function CreatePage($pageAddress , $pageTitle , $pageDes , $pageKeyword , $pageImage , $temp , $showMessage = true ){


		$templateName = $this->getTemplateName();

		$add = self::$dbh->create(
			'jor_pages',
			array(
				"address"			=> $pageAddress ,
				"title"				=> $pageTitle ,
				"description"	=> $pageDes ,
				"keywords"		=> $pageKeyword ,
				"image"				=> $pageImage ,
				"tempname"		=> $temp
			)
		);


		$dir = VIEW_PATH . "Home" . DS . $templateName . DS;
		$pageName = "page_".$pageAddress.".html";

		$myfile = fopen($dir.$pageName, "w") or die("error file");
		$txt = '<div class="container"><div class="row"><div class="col-md-12"><br/><h1>صفحه ی ساخته شده جدید شما</h1><br/></div></div></div>';
		if(fwrite($myfile, $txt) && $showMessage){
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}

	}


	public function EditPage($pageAddress , $pageTitle , $pageDes , $pageKeyword , $pageImage , $temp ){


		$updatePage = self::$dbh->update(
			'jor_pages',
			array(
				"address"			=> $pageAddress ,
				"title"				=> $pageTitle ,
				"description"	=> $pageDes ,
				"keywords"		=> $pageKeyword ,
				"image"				=> $pageImage ,
				"tempname"		=> $temp
			),
			array(
				"address"		=> $pageAddress
			),
			"=",
			""
		);

		if($updatePage){
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}

	}




	public function getPageInfo($pageName){
		return self::$dbh->read(
			'jor_pages',
			array(),
			array(
				"address"	=> $pageName
			),
			"=",
			"",
			"",
			"s"
		);
	}


}
?>
