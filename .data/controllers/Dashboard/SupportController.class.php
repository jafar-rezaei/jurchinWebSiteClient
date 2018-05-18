<?php

class SupportController extends Controller{

	const SupportPath = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct(){

		self::$twig = parent::callTwig();
		parent::forceLogin();
	}




	public function __call($name, $arguments)
	{
		$this->IndexAction();
	}



	public function IndexAction(){

		$uid = parent::$user->user_id;


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "delete"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;

				$SupportModel = new SupportModel(parent::$site['key']);
				$SupportModel->DeleteTicket($paramID);
			}
			else if($action == "close"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;

				$SupportModel = new SupportModel(parent::$site['key']);
				$SupportModel->closeTicket($paramID);
			}
			else if($action == "new"){
				$ticketTitle = isset($_POST['ticketTitle']) ? safe($_POST['ticketTitle']) : "" ;
				$ticketDepartment = isset($_POST['ticketDepartment']) ? safe($_POST['ticketDepartment']) : "" ;
				$ticketPrioerity = isset($_POST['ticketPrioerity']) ? safe($_POST['ticketPrioerity']) : "" ;
				$ticketContent = isset($_POST['ticketContent']) ? safe($_POST['ticketContent']) : "" ;

				$ticketAttach = "" ;


				if(isset($_FILES['ticketAttach']) !== ""){
					$imageFileType = pathinfo($_FILES['ticketAttach']['name'] ,PATHINFO_EXTENSION);
					$target_file = UPLOAD_PATH . random_string(12) .".". $imageFileType;
					$uploadOk = 1;

					// Check if image file is a actual image or fake image
					if(getimagesize($_FILES["ticketAttach"]["tmp_name"]) == false) {
						$uploadOk = 0;
					}

					// Check file size
					if ($_FILES["ticketAttach"]["size"] > 500000) {
						$uploadOk = 0;
					}
					if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
						$uploadOk = 0;
					}


					if ($uploadOk == 1) {
						if (move_uploaded_file($_FILES["ticketAttach"]["tmp_name"], $target_file)) {
							$ticketAttach = $target_file;
						}
					}
				}

				$SupportModel = new SupportModel(parent::$site['key']);
				$addResult = $SupportModel->newTicket( $uid , $ticketTitle , $ticketContent , $ticketDepartment , $ticketPrioerity , $ticketAttach);

				if($addResult){
					safe_redirect(SITEURL . 'Dashboard/Support');
				}

			}
			else{
				json_encode(array("message" => "nok"));
			}

		}else{

			$SupportModel = new SupportModel(parent::$site['key']);
			$tickets = $SupportModel->getTicketList(parent::$user->user_id );



			$twigParameters = array(
				'pageTitle' => 'لیست تیکت ها',
				'pageDes'	   => 'سوالات یا مشکلات در مورد جورچین رو با کارشناسان ما مطرح کنید' ,
				'pageKeywords'  => 'تیکت,پشتیبانی,پشتیبانی جورچین,jurchin supprot',
				'canonical'	 => '{{siteurl}}Dashboard/Support' ,
				'pageImage'	 => '' ,
				'extraJs'	   => '
	<!-- Tables functionality -->
	<script src="{{ asset(\'js/jquery.dataTables.js\') }}"></script>

	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,


				'extraCss'	  => '
	<!-- table view -->
	<link href="{{ asset(\'/css/table.view.css\') }}" rel="stylesheet">' ,

				'endDom'		=> '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initTable();
			jurchin.initSupport();
		});
	</script>' ,


				// data
				'tickets' 		=> $tickets
			);


			echo self::$twig->render(self::SupportPath . 'list.html', $twigParameters );
		}
	}



	public function NewAction(){

		$SupportModel = new SupportModel(parent::$site['key']);
		$ticketCats = $SupportModel->getTicketCats();

		$twigParameters = array(
			'pageTitle' => 'تیکت جدید',
			'pageDes'	   => 'اگر سوال یا مشکلی در رابطه با جورچین دارین همواه شما هستیم ' ,
			'pageKeywords'  => 'تیکت,تیکت جدید',
			'canonical'	 => '{{siteurl}}Dashboard/Support/new' ,
			'pageImage'	 => '' ,
			'extraJs'	   => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
			'extraCss'	  => '' ,
			'endDom'		=> '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initImageHandle();
		});
	</script>' ,

			'ticketCats' => $ticketCats

		);


		echo self::$twig->render(self::SupportPath . 'new.html', $twigParameters );

	}



	public function ShowAction(){

		$tid = parent::$parameter['id'];


		$SupportModel = new SupportModel(parent::$site['key']);


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "answer"){
				$ticketID = isset($_POST['tid']) ? safe($_POST['tid']) : "" ;
				$ticketAnswer = isset($_POST['ticketAnswer']) ? safe($_POST['ticketAnswer']) : "" ;

				$SupportModel->answerTicket($ticketID , $ticketAnswer );
			}

		}else{


			// get ticket info
			$ticket = $SupportModel->getTicketData($tid);



			if(count($ticket) == 0 ){
				safe_redirect(SITEURL . 'Dashboard/Support');
			}

			$twigParameters = array(
				'pageTitle' => 'مشاهده تیکت',
				'pageDes'	   => 'مشاهده تیتکت در جورچین' ,
				'pageKeywords'  => 'مشاده تیکت،show ticket',
				'canonical'	 => '{{siteurl}}Dashboard/Support/Show/id:'.$tid ,
				'pageImage'	 => '' ,
				'extraJs'	   => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'	  => '' ,

				'endDom'		=> '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initSupport();
		});
	</script>' ,



				// data
				'ticket' => $ticket ,
			);


			echo self::$twig->render(self::SupportPath . 'show.html', $twigParameters );
		}

	}


}
