<?php

// Base Controller

class Controller extends jamework {

	public static $site;
	protected static $parameter;
	protected static $user = array();
	protected static $sideBarTickets;
	protected static $isLogged = false;

	public static function AllwaysRun() {

		$config = jamework::getConfig();


		$auth = new AuthModel();
		self::$isLogged = $auth->checkLogin();
		self::$parameter = self::getUrlData();


		$SiteModel = new SiteModel();
		self::$site = $SiteModel->getSiteSetting();

		if(self::$isLogged == true) {


			$UserModel = new UserModel();
			self::$user = $UserModel->userInfo();



			$uid = self::$user->user_id;


			if(strtolower(PLATFORM) == "dashboard" && is_object(self::$site)) {

				$SupportModel = new SupportModel();
				//self::$sideBarTickets = $SupportModel->getTicketListSideBar($uid , self::$site->sid);
			}

		}


	}

	public static function callTwig(array $args = array('doCache' => 0 , 'debug' => 0 , 'callAllways' => 1 , 'viewsPath' => '') ) {

		require_once LIB_PATH . '/Twig/Autoloader.php';

		if(in_array(strtolower(PLATFORM) , array("dashboard" , "home" )) && $args['callAllways'] == 1) {
			self::AllwaysRun();
		}

		Twig_Autoloader::register();


		$loader = new Twig_Loader_Filesystem(
			(
				(!isset($args['viewsPath']) || $args['viewsPath'] == '')
				? VIEW_PATH
				: $args['viewsPath']
			)
		); // can add array of path




		$parameters = array();

		if(isset($args['doCache']) && $args['doCache'] == 1) {
			$parameters['cache'] = VIEW_PATH.'/cache' ;
		}
		if(isset($args['debug']) && $args['debug'] == 1) {
			$parameters['debug'] = true;
		}


		$twig = new Twig_Environment($loader, $parameters);
		$twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) {
			return sprintf(SITEURL . 'public/%s', ltrim($asset, '/'));
		}));


		$twig->addFunction(new \Twig_SimpleFunction('mimes', function ($ext) {
				$ext = explode("?ver=", $ext);
				$mimes = array(
					'jpg' 	=> 'image/jpeg',
					'jpeg' 	=> 'image/jpeg',
					'gif' 	=> 'image/gif',
					'png' 	=> 'image/png',
				);
				return $mimes[$ext[0]];
		}));

		$twig->addFunction(new \Twig_SimpleFunction('content', function ($content , $length) {
			mb_internal_encoding("UTF-8");
			return mb_substr(strip_tags($content),0,intval($length))."...";
		}));

		$twig->addFunction(new \Twig_SimpleFunction('image', function ($src , $title , $classes = "" , $id = "") {
			return '<img src="'.$src.'" title="'.$title.'" alt="'.$title.'" class="'.$classes.'" id="'.$id.'" />';
		}));


		$twig->addFunction(new \Twig_SimpleFunction('postUrl', function ($userSiteAddress , $slug ) {
			return $userSiteAddress."/Post/".$slug;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('postImage', function ($userSiteAddress , $image) {

			return strlen($image) > 0 ? $userSiteAddress."/public/images/".$image : "http://www.jurchin.com//public/template2/images/img_blank.jpg";
		}));


		$twig->addFunction(new \Twig_SimpleFunction('urltext', function ($text) {
			return str_replace(" ", "-", $text);
		}));


		$twig->addFunction(new \Twig_SimpleFunction('assets', function ($asset) {
			return sprintf(SITEURL . 'sites/%s', ltrim($asset, '/'));
		}));



		$twig->addFunction(new \Twig_SimpleFunction('editor', function ($asset) {
			return sprintf(SITEURL . 'public/%s', ltrim($asset, '/'));
		}));





		$twig->addFunction(new \Twig_SimpleFunction('jdate', function ($pattern , $unix = "") {
			if(empty($uinx) || !isset($unix)) {
				$unix = time();
			}
			return jdate($pattern , $unix);
		}));


		$twig->addGlobal('siteurl', parent::$config['SITEURL'] );
		$twig->addGlobal('sitename', parent::$config['SITENAME'] );
		$twig->addGlobal('lang', parent::$config['lang'] );
		$twig->addGlobal('siteMail', SITEMAIL );
		$twig->addGlobal('platform' , PLATFORM);
		$twig->addGlobal('controller' , CONTROLLER);
		$twig->addGlobal('action' , ACTION);
		$twig->addGlobal('parameter' , self::$parameter);
		$twig->addGlobal('user' , self::$user);

		if(is_object(self::$user) && isset(self::$user->user_logkey)) {
			$twig->addGlobal('userkey' , random_string(24));
		}
		$twig->addGlobal('sideBarTickets' , self::$sideBarTickets);
		$twig->addGlobal('csrf' , getCSRFToken(1) );




		$escaper = new Twig_Extension_Escaper('html');
		$evaluate = new Twig_Extension_Evaluate();
		$twig->addExtension($escaper);
		$twig->addExtension($evaluate);

		return $twig;
	}

	public static function isMethod($method) {
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtolower($method) == 'post') {
			if(validateCSRFToken($_POST['CSRF_Token'])) {
				return true;
			} else {
				die("Security Error" . $_POST['CSRF_Token']);
			}
		} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && strtolower($method) == 'get') {
			return true;
		}
		return false;
	}



	public static function getUrlData() {

		$retArray = array();
		$url = jamework::getUrls();

		if(isset($url[3])) {
			$lastCommands = explode("-", $url[3]);
			foreach ($lastCommands as $value) {
				$sep = explode(":", $value);
				if(isset($sep[1]))
					$retArray[$sep[0]] = $sep[1];
			}
		}

		return $retArray;
	}


	public function forceLogin() {

		if(!self::$isLogged) {
			safe_redirect(SITEURL . 'Auth/Login' , 1);
		}
	}


	public function redirect($url,$message,$wait = 0) {

		if ($wait == 0) {
			header("Location:$url");
		} else {
			include CURR_VIEW_PATH . "message.html";
		}

		exit;

	}
}
