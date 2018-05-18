<?php

class SiteController extends Controller{

	const PATH = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct(){

		header("Access-Control-Allow-Credentials:true");

		self::$twig = parent::callTwig();
		parent::forceLogin();


	}




	public function __call($name, $arguments) {
		$this->indexAction();
	}



	public function UpgradeAction(){



		// handle request
		if(parent::isMethod('POST')){

		}


		$twigParameters = array(
			'pageTitle'     => 'ارتقا سایت',
			'pageDes'       => 'ارتقا سایت به پیشرفته' ,
			'pageKeywords'  => 'ویرایش حساب,ویرایش اکانت',
			'canonical'     => '{{siteurl}}Dashboard/Site/Upgrade' ,
			'pageImage'     => '' ,
			'extraJs'       => '' ,
			'extraCss'      => '' ,

			'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initSiteUpgrade();
		});
	</script>' ,

		);


		echo self::$twig->render(self::PATH . 'upgrade.html', $twigParameters );

	}



	public function SettingAction(){

		// handle request
		if(parent::isMethod('POST')){

			$arrayOfSettings = array(
				"name" ,
				"mail" ,
				"regperm" ,
				"dateformat" ,
				"timeformat" ,
				"language" ,
				"timezone" ,
				"comments_active_perm" ,
				"comments_black_words" ,
				"comments_max_link" ,
				"sitemap_perm" ,
				"rss_perm" ,
				"rss_posts_count" ,
			);


			$SiteModel = new SiteModel();
			foreach ($_POST as $key => $value) {
				if(in_array(strtolower($key), $arrayOfSettings)){
					$SiteModel->changeSetting($key , safe($value));
				}
			}
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);

		} else {


			$SiteModel = new SiteModel();
			$settings = $SiteModel->getSiteSetting();
			$gateways = $SiteModel->getGateways();

			$twigParameters = array(
				'pageTitle'     => 'تنظیمات سایت',
				'pageDes'       => 'تنظیمات سایت خود را مدیریت کنید' ,
				'pageKeywords'  => 'ویرایش تنظیمات,ویرایش تنظیمات سایت',
				'canonical'     => '{{siteurl}}Dashboard/Site/Setting' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'      => '' ,

				'endDom'        => '
	<script type="text/javascript">
		jurchin.initSiteSetting();
	</script>' ,

				'settings'		=> $settings ,
				'gateways'		=> $gateways

		);


			echo self::$twig->render(self::PATH . 'setting.html', $twigParameters );
		}

	}


	public function GatewayAction(){

		// handle request
		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			$data = array();
			$gid = isset($_POST['gid']) ? safe($_POST['gid']) : "" ;
			$name = isset($_POST['name']) ? safe($_POST['name']) : "" ;
			$kind = isset($_POST['kind']) ? safe($_POST['kind']) : "" ;
			$data["apikey"] = isset($_POST['apikey']) ? safe($_POST['apikey']) : "" ;


			$SiteModel = new SiteModel();

			if($action == "add"){

				$SiteModel->addGateway($name , $kind , $data);

			}else if ($action == "edit" && $gid !== ""){

				$SiteModel->editGateway($gid , $name , $kind , $data);

			}else if ($action == "delete" && $gid !== ""){

				$SiteModel->deleteGateway($gid);

			}

		}else{

			$parameter = parent::$parameter;
			if($parameter["Action"] == "delete"){
				$pagetitle = "حذف درگاه پرداخت";
				$pageDes = "آیا از حذف کردن درگاه مورد نظر مطمئن هستین ؟";
			}else if($parameter["Action"] == "edit"){
				$pagetitle = "ویرایش درگاه پرداخت";
				$pageDes = "درگاه پرداخت مورد نظر خود را ویرایش کنید ";
			}else{
				$pagetitle = "افزودن درگاه پرداخت";
				$pageDes = "درگاه پرداخت مدنظر خودتون رو به سایتتون اضافه کنید .";
			}

			$twigParameters = array(
				'pageTitle'     => $pagetitle,
				'pageDes'       => $pageDes ,
				'pageKeywords'  => 'درگاه جدید,New Gateway',
				'canonical'     => '{{siteurl}}Dashboard/Site/Gateway' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'      => '' ,

				'endDom'        => '' ,

			);



			if($parameter["Action"] !== "add"){
				$SiteModel = new SiteModel();
				if(isset($parameter["gid"])){
					$twigParameters["gateway"] = $SiteModel->getGatewayInfo($parameter["gid"]);
				}
				else {
					MessageController::handle(
						array('message' => 'Could Not use find gid parameter ','code' => 10041) , 404
					);
				}

			}

			echo self::$twig->render(self::PATH . 'gateway.html', $twigParameters );
		}
	}



	public function ChangeFaviconAction(){


		if(parent::isMethod('POST')){

			$inputName = 'favicon';
			$SiteModel = new SiteModel();


			if((!empty($_FILES[$inputName])) && ($_FILES[$inputName]['error'] == 0)) {

				$sub = parent::$site->sub;
				$filename = basename($_FILES[$inputName]['name']);

				$mimes = array(
					'jpg' 	=> 'image/jpg',
					'jpeg' 	=> 'image/jpeg',
					'gif' 	=> 'image/gif',
					'png' 	=> 'image/png',
				);

				$ext = pathinfo($filename , PATHINFO_EXTENSION);


				// check extension allowed
				if(!(isset($mimes[$ext]) && in_array($_FILES[$inputName]["type"], $mimes , true) ) || $filename == ""){
					die("Bad file!");
				}

				if($_FILES[$inputName]["size"] > 450000) {
					die("File size error!");
				}


				$filename = 'favicon_'.random_string(6).'.'.$ext;
				$dir = MAINROOT . "/uploads/favicon/";


				$sizesArray = array(
					"57x57"		=> "apple-touch-icon",
					"60x60"		=> "apple-touch-icon",
					"72x72"		=> "apple-touch-icon",
					"76x76"		=> "apple-touch-icon",
					"114x114"	=> "apple-touch-icon",
					"120x120" 	=> "apple-touch-icon",
					"144x144"	=> "apple-touch-icon",
					"152x152"	=> "apple-touch-icon",
					"180x180"	=> "apple-touch-icon",
					"192x192"	=> "icon",
					"32x32"		=> "icon",
					"96x96"		=> "icon",
					"16x16"		=> "icon",
				);


				foreach ($sizesArray as $key => $value) {
					$favicon = "fav_".$value."-".$key.".".$ext;

					$size = explode("x", $key);
					$myuploaded = cropmypic($_FILES[$inputName]['tmp_name'] , $size[0] , $size[1] , $ext , $dir.$favicon);
				}



				// delete old favicons
				$exFavExt = $SiteModel->getSiteSetting()['favicon'];

				$exFavExt = explode("?ver=", $exFavExt);
				if($exFavExt[0] !== $ext){
					$oldFavs = glob($dir . "fav_*.".$exFavExt[0]);
					foreach ($oldFavs as $fav){
						if(is_file($fav) && file_exists($fav))
							unlink($fav);
					}
				}

				$SiteModel->changeSetting("favicon",$ext."?ver=".random_string(12));
				validateCSRFToken($_POST['CSRF_Token'] , null , 1);
				safe_redirect(SITEURL . 'Dashboard/Site/Setting');

			}
		}

	}



}
