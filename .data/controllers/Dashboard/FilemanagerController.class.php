<?php

class FilemanagerController extends Controller{

	private static $twig ;

	public function __construct(){

		self::$twig = parent::callTwig();

		// Authorize to access panel
		parent::forceLogin();

	}

    // not found method
    public function __call($name, $arguments){
        $this->indexAction();
    }


	public function indexAction(){

        $parameter = parent::$parameter;
        $csrf = getCSRFToken();

		if(validateCSRFToken($csrf  , Jamework::$session , false)){

			require_once(APP_PATH . "classes/AesHelper.class.php");


			$logkey = self::$user->user_logkey;

			$akey = md5($logkey . "}:@". RFP_CIPHER );

			$encodeKey = md5($akey);

			$security = new AesHelper();
			$accessKey = $security->encrypt($logkey , $encodeKey , $encodeKey );
			$accessKey = rawurlencode($accessKey);

            safe_redirect(SITEURLWWW . ".data/application/libraries/rfp_project/filemanager/dialog.php?key=".$accessKey."&type=0&akey=".$akey."}-()".random_string(12)."&".$_SERVER['QUERY_STRING']);

		}else{
			echo "Not Valid Request ! Reopen File Manager Or refresh page ...";
		}
	}

}
