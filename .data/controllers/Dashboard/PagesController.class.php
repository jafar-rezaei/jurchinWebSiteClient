<?php

class PagesController extends Controller{

	const PATH = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct(){

		self::$twig = parent::callTwig();
		parent::forceLogin();
	}




	public function __call($name, $arguments)
	{
		$this->indexAction();
	}



	public function indexAction(){

		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "delete"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;

				$PagesModel = new PagesModel();
				$PagesModel->DeletePage($paramID);

			}if($action == "new" || $action == "edit" ){
				$pageAddress = isset($_POST['pageAddress']) ? safe($_POST['pageAddress']) : "" ;

				$pageTitle = isset($_POST['pageTitle']) ? safe($_POST['pageTitle']) : "" ;
				$pageDes = isset($_POST['pageDes']) ? safe($_POST['pageDes']) : "" ;
				$pageKeyword = isset($_POST['pageKeyword']) ? safe($_POST['pageKeyword']) : "" ;
				$pageImage = isset($_POST['pageImage']) ? safe($_POST['pageImage']) : "" ;
				$temp = isset($_POST['temp']) ? safe($_POST['temp']) : "" ;

				$PagesModel = new PagesModel();
				if($action == "edit"){
					$PagesModel->EditPage($pageAddress , $pageTitle , $pageDes , $pageKeyword , $pageImage , $temp);
				}else{
					$PagesModel->CreatePage($pageAddress , $pageTitle , $pageDes , $pageKeyword , $pageImage , $temp);
				}
			}else{
				json_encode(array("message" => "nok"));
			}

		}else{

			$PagesModel = new PagesModel();
			$pages = $PagesModel->getPagesList();

			$akey = random_string(20);
			$salt = random_string(12);


			$security = new AesHelper();
			$accessKey = $security->encrypt(parent::$site['key'] , $akey.$salt , $salt );
			$accessKey = rawurlencode($accessKey);




			$twigParameters = array(
				'pageTitle' 	=> 'صفحات سایت',
				'pageDes'	   	=> 'لیستی از صفحات موجود روی وبسایت شما' ,
				'pageKeywords'  => 'صفحات سایت در جورچین,لیست صفحات,pages list',
				'canonical'	 	=> '{{siteurl}}Dashboard/Pages' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Tables functionality -->
	<script src="{{ asset(\'js/jquery.dataTables.js\') }}"></script>

	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,


				'extraCss'      => '
	<!-- table view -->
	<link href="{{ asset(\'/css/table.view.css\') }}" rel="stylesheet">' ,

				'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initTable();
			jurchin.initPages();
		});
	</script>' ,


				// data
				'pages' 			=> $pages ,
				'remoteData'	=> "selfSite:blog-sid:106-akey:" . $akey . "-encode:".$salt."-hash:" . str_replace("-" , "(}!*)" ,$accessKey)
			);


			echo self::$twig->render(self::PATH . 'list.html', $twigParameters );
		}

	}

}
