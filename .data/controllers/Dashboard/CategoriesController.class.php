<?php

class CategoriesController extends Controller{

	const CategoriesPath = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct(){

		self::$twig = parent::callTwig();
		parent::forceLogin();
	}


	// default method calling
	public function __call($name, $arguments){
		$this->listAction();
	}



	// show list of cats
	public function listAction(){


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "delete"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;
				$deletePosts = isset($_POST['deletePosts']) ? safe($_POST['deletePosts']) : 0 ;

				if(intval($paramID) == 1){
					die("دسته عموم یمی تواند حذف شود");
				}

				$CategoriesModel = new CategoriesModel();
				$CategoriesModel->DeleteCat($paramID , $deletePosts);
			}else if($action == "edit"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;
				$catTitle = isset($_POST['catTitle']) ? safe($_POST['catTitle']) : 0 ;


				$CategoriesModel = new CategoriesModel();
				$CategoriesModel->editCat($paramID , $catTitle);
			}else if($action == "new"){
				$catName = isset($_POST['catName']) ? safe($_POST['catName']) : 0 ;
				$parentCat = isset($_POST['parentCat']) ? safe($_POST['parentCat']) : 0 ;

				$CategoriesModel = new CategoriesModel();
				$CategoriesModel->newCat($catName , $parentCat );
			}

		}else{
			$CatsModel = new CategoriesModel();
			$Cats = $CatsModel->getCatList();


			$twigParameters = array(
				'pageTitle'     => 'دسته بندی ها',
				'pageDes'       => 'لیست دسته بندی های سایت برای مدیریت پست ها' ,
				'pageKeywords'  => 'نوشته ها,نوشته های وبسایت',
				'canonical'     => '{{siteurl}}Dashboard/Categories' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Tables functionality -->
	<script src="{{ asset(\'js/jquery.dataTables.js\') }}" type="text/javascript"></script>
	<script src="{{ asset(\'js/ion.rangeSlider.min.js\') }}" type="text/javascript"></script>

	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>

	<script type="text/javascript">
		var cats = "";
	</script>
		' ,


				'extraCss'      => '
	<!-- table view -->
	<link href="{{ asset(\'/css/table.view.css\') }}" rel="stylesheet">' ,

				'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initTable();
			jurchin.initCatList();
			cats = \' { {% for c in cats %} "{{c.cid}}" : "{{c.name}}" {% if loop.index != cats|length %}, {% endif %} {% endfor %} }\';
		});
	</script>' ,


				// data
				'cats' => $Cats ,
			);


			echo self::$twig->render(self::CategoriesPath . 'list.html', $twigParameters );
		}
	}

}
