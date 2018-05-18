<?php

class CommentsController extends Controller{

	const CommentsPath = PLATFORM . DS . CONTROLLER . DS;
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


	// show list of comments
	public function indexAction(){

		$uid = parent::$user->user_id;


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "delete"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;

				$CommentsModel = new CommentsModel();
				$CommentsModel->DeleteComment($paramID);
			}else if($action == "edit"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;
				$commentContent = isset($_POST['commentContent']) ? safe($_POST['commentContent']) : "" ;

				$CommentsModel = new CommentsModel();
				$CommentsModel->editComment($paramID , $commentContent);
			}else if($action == "show"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;
				$commentContent = isset($_POST['commentContent']) ? safe($_POST['commentContent']) : "" ;

				$CommentsModel = new CommentsModel();
				$CommentsModel->answerComment($paramID , $uid , $commentContent);
			}else{
				json_encode(array("message" => "nok"));
			}

		}else{

			$CommentsModel = new CommentsModel();
			$Comments = $CommentsModel->getCommentsList();


			$twigParameters = array(
				'pageTitle'     => 'نظرات',
				'pageDes'       => 'لیست نظرات ثبت شده در وبسایت شما' ,
				'pageKeywords'  => 'نوشته دیدگاه ها,نوشته های وبسایت',
				'canonical'     => '{{siteurl}}Dashboard/Comments' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Tables functionality -->
	<script src="{{ asset(\'js/jquery.dataTables.js\') }}" type="text/javascript"></script>

	<script src="{{ asset(\'js/ion.rangeSlider.min.js\') }}" type="text/javascript"></script>

	<script src="{{ asset(\'js/moments/moment.min.js\') }}" type="text/javascript"></script>
	<script src="{{ asset(\'js/moments/locale/fa.js\') }}" type="text/javascript"></script>
	<script src="{{ asset(\'js/moments/moment-jalali.js\') }}" type="text/javascript"></script>

	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>
		' ,


				'extraCss'      => '
	<!-- table view -->
	<link href="{{ asset(\'/css/table.view.css\') }}" rel="stylesheet">' ,

				'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initTable();
			jurchin.initCommentsList();
		});
	</script>' ,


				// data
				'Comments'      => $Comments ,
			);


			echo self::$twig->render(self::CommentsPath . 'list.html', $twigParameters );
		}

	}

}
