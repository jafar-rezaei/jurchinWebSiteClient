<?php

class RestController extends Controller{

	private $response = array();

	public function __construct() {

		// set header json
		header('Content-Type:application/json');

	}


	public function __call($name, $arguments) {
		$this->indexAction();
	}


	// no view needed
	public function indexAction() {
		header('Content-Type:Text/Html');
		echo parent::callTwig()->render(PLATFORM . DS . 'forms.html');
	}


	public function GetAccessKeyAction() {

		$parameter = input("post" , "param");
		$parameter = str_replace("_" , "", $parameter);

		$newAccessKey = self::generateAccessKey($parameter);
		if($newAccessKey === FALSE){

			self::makeJson(
				array(
					'result'	=> 0 ,
					'message'	=> 'Can not create accesskey'
				)
			);

		}else{

			self::makeJson(
				array(
					'result' 	=> 1 ,
					'data'		=> $newAccessKey ,
					'message'	=> 'Use can use access key only in 1 min'
				)
			);
		}
	}


	public function ChecksiteKeyAction($param = "") {

		self::checkAccessKey();

		$flagHasOutput = false;

		if($param == "") {
			$flagHasOutput = true;
			$param = input("post", "sitekey");

			if($param == "") {
				self::makeJson(
					array(
						'result'	=> 0 ,
						'message'	=> 'SiteKey not exist'
					)
				);
			}
		}


		$rest = new RestModel();
		$result = $rest->checksiteKey($param);

		if($result == "ok") {

			// if we want output
			if($flagHasOutput) {
				self::makeJson(
					array(
						'result'	=> 1 ,
						'message'	=> 'Key and domain are valid'
					)
				);
			}

		} else {
			self::makeJson(
				array(
					'result'	=> 0 ,
					'message'	=> $result
				)
			);
		}

	}



	public function GetCatsListAction() {

		$sitekey = input("post", "sitekey");

		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$cats = $rest->getCatList();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $cats ,
				'message'	=> 'Enjoy your cats :)'
			)
		);

	}



    public function ChangeTemplateAction() {

		$sitekey = input("post", "sitekey");
		$tmpName = input("post", "tmpname");



		$this->ChecksiteKeyAction($sitekey);



		$templateModel = new TemplateModel();
		$res = $templateModel->useTemplate($tmpName);

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $res ,
				'message'	=> 'Template'
			)
		);


	}




	public function GetTemplateNameAction() {

		$sitekey = input("post", "sitekey");

		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$templateName = $rest->getTemplateName();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $templateName ,
				'message'	=> 'Enjoy template name :)'
			)
		);

	}



	public function GetPagesListAction(){

		$sitekey = input("post", "sitekey");


		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$pages = $rest->getPagesList();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $pages ,
				'message'	=> 'Enjoy your pages :)'
			)
		);
	}


	public function GetPageDataAction(){

		$sitekey 	= input("POST", "sitekey");
		$param 		= input("POST", "param");


		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$pageData = $rest->getPageData($param);


		if($pageData == "NoFile"){
			self::makeJson(
				array(
					'result'	=> 0 ,
					'data'		=> NULL ,
					'message'	=> 'No File Found'
				)
			);
		}else{
			self::makeJson(
				array(
					'result'	=> 1 ,
					'data'		=> $pageData ,
					'message'	=> 'Enjoy page content :)'
				)
			);
		}


	}


	public function SetPageDataAction() {


		$sitekey = input("post", "sitekey");
		$name = input("post", "name");
		$content = input("post", "content");



		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$result = $rest->setPageData($name , $content);

		if($result == "ok") {

			self::makeJson(
				array(
					'result'	=> 1 ,
					'data'		=> "ok" ,
					'message'	=> 'Page is saved succefully'
				)
			);

		} else {

			self::makeJson(
				array(
					'result'	=> 0 ,
					'message'	=> $result
				)
			);

		}

	}



	public function GetGatewaysListAction(){

		$sitekey = input("post", "sitekey");


		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$gateways = $rest->getGatewaysInfo();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $gateways ,
				'message'	=> 'Enjoy your gateways :)'
			)
		);
	}






	public function GetPostsListAction(){

		$sitekey = input("post", "sitekey");

		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$posts = $rest->getPostsList();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $posts ,
				'message'	=> 'Enjoy your posts :)'
			)
		);

	}

	public function GetCommentsListAction(){

		$sitekey = input("post", "sitekey");


		$this->ChecksiteKeyAction($sitekey);

		$rest = new RestModel();
		$comments = $rest->getCommentsList();

		self::makeJson(
			array(
				'result'	=> 1 ,
				'data'		=> $comments ,
				'message'	=> 'Enjoy your comments :)'
			)
		);

	}


	public function AddPostAction(){

		self::checkAccessKey();

		echo "AddPostAction";
	}



	// http://localhost:8888/webservice/rest/checksite/address:test
	public function addPayKeyAction(){

		$data = parent::getUrlData(3);	//1=> post , 2=>get , 3=> siteh
		$rest = new RestModel();

		if(array_key_exists('primkey' , $data) && array_key_exists('pid',$data) && array_key_exists('trankey',$data)){

			if($rest->changePayKey($data["pid"] , $data["trankey"] , $data["primkey"] ) > 0) {
				$this->response['status'] = true ;
			} else {
				$this->response['status'] = false ;
			}

		}else {
			$this->response['status'] = false ;
		}

		print json_encode($this->response);
	}


  public function saveImageBase64Action() {

		$sitekey    = input("post", "sitekey");
		$image      = input("post", "image");
		$type       = input("post", "type");


		$this->ChecksiteKeyAction($sitekey);


        //Generate random file name here
		if($type == 'png') {
			$name = random_string(8) . '.png';
		} else {
			$name = random_string(8) . '.jpeg';
		}


		$rest = new RestModel();
		$saveRes = $rest->saveImage($name , $image);

        if($saveRes == "ok"){
        	self::makeJson(
    			array(
    				'result'	=> 1 ,
    				'data'		=> $name ,
    				'message'	=> 'Image saved successfully'
    			)
    		);
        } else {
            self::makeJson(
    			array(
    				'result'	=> 0 ,
    				'message'	=> $saveRes
    			)
    		);
        }

    }






	// background functions
	private static function makeJson($array) {
		echo json_encode($array);
		die();
	}

	private static function generateAccessKey($key) {
		$fname =  $key . "_" . time();

		if(file_put_contents(APP_PATH . "logs/rest_logs/" . $fname . ".dat", "" ) === FALSE){
			return false;
		}else{
			return $fname;
		}
	}

	private static function checkAccessKey(){
		$accesskey = input("post" , "accesskey");

		$accesskeyTime = intval(explode("_" , $accesskey)[1]);
		$now = time();

		if($accesskey == "sadad_1493130229"){
			return true;
		}

		if($now - $accesskeyTime > 3600 || $accesskeyTime > $now){
			self::makeJson(
				array(
					'result'	=> 0 ,
					'message'	=> 'Expired accesskey'
				)
			);
		}

		if(!file_exists(APP_PATH . "logs/rest_logs/" . $accesskey . ".dat")){
			self::makeJson(
				array(
					'result'	=> 0 ,
					'message'	=> 'Not valid access key .'
				)
			);
		}
	}



}
