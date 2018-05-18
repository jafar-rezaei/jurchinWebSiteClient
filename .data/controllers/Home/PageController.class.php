<?php

class PageController extends Controller{


	public function __construct(){

		require_once(CONTROLLER_PATH . "Home". DS ."IndexController.class.php");

	}


	public function __call($name , $arguments ){

		$IndexController = new IndexController();
		IndexController::renderPage($name);

	}


}
