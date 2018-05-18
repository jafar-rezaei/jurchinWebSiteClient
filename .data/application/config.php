<?php
require_once(APP_PATH . "configSite.php");

header('Content-type: text/html; charset=UTF-8');
header('X-XSS-Protection: 1; mode=block');
header('X-Powered-By:Express');
header('X-Content-Type-Options: nosniff ');


if(array_key_exists("HTTP_REFERER" , $_SERVER) && strpos(strtolower($_SERVER['HTTP_REFERER']),"www.") !== -1){
	header('X-Frame-Options: ALLOW-FROM ' . SITEURLWWW );
}else{
	header('X-Frame-Options: ALLOW-FROM ' . SITEURL );
}




if(array_key_exists("HTTP_ORIGIN" , $_SERVER) && $http_origin = $_SERVER['HTTP_ORIGIN']){
	$allowed_domains = array(
		SITEURL ,
		SITEURLWWW
	);
	if (in_array($http_origin, $allowed_domains)){
		header("Access-Control-Allow-Origin: $http_origin");
	}
}else{
	header("Access-Control-Allow-Origin: " . SITEURLWWW);
}

mb_internal_encoding('UTF-8');
date_default_timezone_set("Asia/Tehran");




class configApp {


	private static $config = array();
	private static $langsArray =  array("fa" , "en");

	public static function getConfig(){


		// main db config
		self::$config["DB_HOST"] =  DB_HOST;
		self::$config["DB_NAME"] =  DB_NAME;
		self::$config["DB_USER"] =  DB_USER;
		self::$config["DB_PASS"] =  DB_PASS;



		self::$config["SITENAME"] =  SITENAME;			// site name
		self::$config["SITEMAIL"] =  SITEMAIL;

		self::$config["BASEURL"] =  BASEURL;		// example.com
		self::$config["SITEPATH"] =  "";			// without last backSlash
		self::$config["SITEURL"] =  SITEURLWWW;
		self::$config["SITEKEY"] = SITEKEY;	//24 Char

		self::$config["lang"] =  self::getLang();


		self::$config["COOKIE_RUNTIME"] 	=  991209600;
		self::$config["COOKIE_DOMAIN"] 		=  "." . BASEURL;
		self::$config["COOKIE_SECRET_KEY"] 	=  self::$config["SITEKEY"];

		self::$config["EMAIL_USE_SMTP"] 	= true;
		self::$config["EMAIL_SMTP_HOST"] 	= "mail.jurchin.com";
		self::$config["EMAIL_SMTP_AUTH"] 	= true;
		self::$config["EMAIL_SMTP_USERNAME"] 	= "info@jurchin.com";
		self::$config["EMAIL_SMTP_PASSWORD"] 	= "0003115006";
		self::$config["EMAIL_SMTP_PORT"] 		= 25;	// ssl : 465 - regular : 25
		self::$config["EMAIL_SMTP_ENCRYPTION"] 	=  "";

		self::$config["HASH_COST_FACTOR"] =  "10";

		return self::$config;

	}

	public static function getLang(){

		$url = $_SERVER["REQUEST_URI"];
		$langPos = strrpos($url, "lang:");
		if($langPos !== -1){
			$langName = substr($url, $langPos+5 , 2);
			if(in_array( strtolower($langName) , self::$langsArray )){
				return self::setLang($langName);
			}

		}

		// setcookie('_JurchinEditorLang', 'd', 1);
		// setcookie('_JurchinLang', 'd', 1);
		if(!empty($_COOKIE["_JurchinLang"]) ){
			return $_COOKIE["_JurchinLang"];
		}else{
			return self::setLang("fa");
		}
	}

	public static function setLang($langName){
		if(in_array($langName, self::$langsArray)){
			$duration = 24*3600*365 ;		//one year
			setcookie(
				"_JurchinLang" ,
				$langName ,
				time()+$duration ,
				"/",
				BASEURL
			);
			return $langName;
		}
	}

	// route my urls
	public static function getRoute(){

		return array(
			"admin" 							=> "Dashboard/Main",
			"auth/login" 					=> "Dashboard/Auth/Login",
			"auth/register" 			=> "Dashboard/Auth/Register",
			"auth/resetPassword" 	=> "Dashboard/Auth/ResetPassword",
			"auth/forgetPass" 		=> "Dashboard/Auth/forgetPass",
			"auth/verifyPassword" => "Dashboard/Auth/VerifyPassword",


			"post/" 							=> "Home/Index/Post",
			"search/" 						=> "Home/Index/Search",
			"tag/" 								=> "Home/Index/tag",
			"category/" 					=> "Home/Index/category",


			"sitemap.xml" 				=> "Home/Sitemap/Render",
			"error" 							=> "Home/Page/error",
			"nojs" 								=> "Home/Page/nojs"

		);

	}
}



if (version_compare(PHP_VERSION, '5.3.7', '<')) {
	exit('Sorry, this script does not run on a PHP version smaller than 5.3.7 !');
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
	require_once(LIB_PATH . '/password_compatibility_library.php');
}


require_once(APP_PATH . 'functions.php');
require_once(LIB_PATH . 'PHPMailer.php');
require_once(LIB_PATH . 'jdate.php');
// require_once(CLASS_PATH . 'MiniDDosHelper.php');
