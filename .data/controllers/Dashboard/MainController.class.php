<?php

class MainController extends Controller{

	const PanelPath = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;


	public function __construct(){

		self::$twig = parent::callTwig();
		parent::forceLogin();
		
	}



	public function __call($name, $arguments){
		$this->indexAction();
	}




	public function indexAction(){

		//show states
		$dashboard = new DashboardModel();
		$data = $dashboard->getStatics();

		$twigParameters = array(
			'pageTitle' => 'پنل مدیریت',
			'pageDes'       => '' ,
			'pageKeywords'  => '',
			'canonical'     => '{{siteurl}}Dashboard/Main' ,
			'pageImage'     => '' ,
			'extraJs'       => '<script src="{{ asset(\'js/chartist.min.js\') }}"></script>' ,
			'extraCss'      => '' ,
			'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initUserMainPage();
		});
	</script>' ,

			// data
			'data' => $data ,
		);


		echo self::$twig->render(self::PanelPath . 'dashboard.html', $twigParameters );

	}


	public function ServicesAction(){


		$twigParameters = array(
			'pageTitle' => 'سرویس های جورچین',
			'pageDes'       => '' ,
			'pageKeywords'  => '',
			'canonical'     => '{{siteurl}}Dashboard/Services' ,
			'pageImage'     => '' ,
			'extraJs'       => '' ,
			'extraCss'      => '' ,
			'endDom'        => '' ,
		);


		echo self::$twig->render(self::PanelPath . 'services.html', $twigParameters );
	}


	public function logoutAction(){

		$logKey = jamework::getUrls();
		$AuthModel = new AuthModel();
		$logoutResult = $AuthModel->logout(safe($logKey[3]));

		if($logoutResult == "ok")
			safe_redirect(SITEURL . 'Auth/Login');
		else{
			echo "error : " .$logoutResult;
		}
	}

}
