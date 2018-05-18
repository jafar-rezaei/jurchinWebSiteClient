<?php

error_reporting(E_ALL);
ini_set("display_errors" , 0);
//error_reporting(E_ALL && ~E_NOTICE);


class jamework {

	protected static $config;
	public static $lang;
	public static $session;

	public static function run() {

		self::autoload();
		self::init();
		self::dispatch();

	}



	private static function init() {

		// Define path constants

		define("DS",  DIRECTORY_SEPARATOR);
		define("ROOT", getcwd() . DS);
		define("MAINROOT", dirname(ROOT) . DS);

		define("APP_PATH", ROOT . 'application' . DS);
		define("CONTROLLER_PATH", ROOT . "controllers" . DS);
		define("PUBLIC_PATH", MAINROOT. "public" . DS);
		define("MODEL_PATH", ROOT . "models" . DS);
		define("VIEW_PATH", ROOT . "views" . DS);

		define("CLASS_PATH", APP_PATH . "classes" . DS);
		define("LIB_PATH", APP_PATH . "libraries" . DS);
		define("LANG_PATH", APP_PATH . "languages" . DS);
		define("CORE_PATH", APP_PATH . "core" . DS);


		// Load core classes
		require(CORE_PATH . "controller.class.php");
		require(CORE_PATH . "model.class.php");
		require(CONTROLLER_PATH . "messagecontroller.class.php");


		// Load configuration file
		require_once(APP_PATH . "config.php");
		self::$config = configApp::getConfig();

		// Define platform, controller, action, for example:
		// site.ir/admin/Goods/add
		$urlParts = self::getUrls();


		define("PLATFORM", !empty($urlParts[0]) ? safe($urlParts[0]) : 'Home' , true);
		define("CONTROLLER", isset($urlParts[1]) ? safe($urlParts[1]) : 'Index' , true);
		define("ACTION", isset($urlParts[2]) ? safe($urlParts[2]) : 'index');

		define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . PLATFORM . DS);
		define("CURR_VIEW_PATH", VIEW_PATH . PLATFORM . DS);
		define("CURR_MODEL_PATH", MODEL_PATH . PLATFORM . DS);
		define("UPLOAD_PATH", PUBLIC_PATH . "uploads" . DS);


		// Load Controller Lang
		$langPath = LANG_PATH . self::$config['lang'] . DS . CONTROLLER . "_lang.php" ;
		if(is_file($langPath) && file_exists($langPath)) {
			require_once($langPath);
			self::$lang = getLang();
		}


		// Secure Start session
		self::secure_session_start();

	}


	private static function secure_session_start() {
		self::$session = new SecureSessionHelper(SITEKEY, "JurchinOwnSite");


		$path = "../../jurchin/sessions";

		if(!file_exists($path)) {
			mkdir($path , 0777, true);
		}

		ini_set('session.save_handler', 'files');
		session_set_save_handler(self::$session, true);
		ini_set('session.save_path',$path);
		session_save_path($path);
		// ini_set('session.gc_probability', 1);

		self::$session->start(true);
		if ( ! self::$session->isValid()) {
			self::$session->destroy(session_id());
		}

	}


	private static function autoload() {
		spl_autoload_register(array(__CLASS__,'load'));
	}


	public static function getConfig() {
		return self::$config;
	}





	private static function load($classname){

		$flagRead = false;
		$globalSearch = false;

		if (substr($classname, -10) == "Controller"){

			$path = CURR_CONTROLLER_PATH ;
			$flagRead = true;

		} elseif (substr($classname, -5) == "Model"){

			$path 			= CURR_MODEL_PATH ;
			$flagRead 		= true;
			$globalSearch 	= true;

		} elseif (substr($classname, -6) == "Helper"){

			$path 			= CLASS_PATH ;
			$flagRead 		= true;

		}

		if($flagRead){
			$classFile = $path . "$classname.class.php";

			if(is_file($classFile) && file_exists($classFile))

				require_once($classFile);

			else{

				if($globalSearch){

					$classFile = MODEL_PATH . "Dashboard" . DS .$classname.".class.php";



					if(is_file($classFile) && file_exists($classFile)) {
						require_once($classFile);
					} else {
						echo "Model : ". $classFile;
						MessageController::handle(
							array('message' => 'Could Not use url code','code' => 101) , 404
						);
					}

				}else{

					//echo $classFile;
					$PagesModel = new PagesModel();
					$pages = $PagesModel->getPagesList();

					foreach ($pages as $page) {
						if($page['info']){
							$address = $page['info']->address;
							if($address == PLATFORM){

								require_once(CONTROLLER_PATH . "Home". DS ."PageController.class.php");
								define("PLATFORM", "Home");

								$PageController = new PageController();
								$PageController->$address();

							}
						}
					}

					MessageController::handle(
						array('message' => 'Could Not use url code','code' => 101) , 404
					);
				}


			}
		}

	}




	protected static function getUrls(){

		$urlString = preg_replace("/(\/)\\1+/", "$1", safe($_SERVER["REQUEST_URI"]) );
		$urlString = str_replace(self::$config['SITEPATH'], "", $urlString );
		$urlString = trim($urlString , DS);

		// if routes happened
		foreach (configApp::getRoute() as $path => $value) {
			if(strtolower(substr($urlString , 0 , strlen($path))) == strtolower($path)){
				return explode(DS, $value. DS .substr($urlString , strlen($path) , strlen($urlString)) );
			}
		}


		if(strlen($urlString) > 0 && strpos($urlString , DS) !== FALSE) {

			if(strpos($urlString, "?") !== false){
				$urlString = substr($urlString, 0, strrpos($urlString, "?"));
			}

			$urlParts = explode(DS, $urlString);
			return $urlParts;
		}else{
			return array($urlString);
		}

	}





	// Routing and dispatching

	private static function dispatch() {

		$controller_name = CONTROLLER . "Controller";
		$action_name = ACTION . "Action";

		if (class_exists($controller_name , true)) {
			$controller = new $controller_name;
			$controller->$action_name();
		} else {
			// echo $controller_name;
			MessageController::handle(
				array('message' => 'Could Not use url request','code' => 100) , 404
			);
		}

	}

}
