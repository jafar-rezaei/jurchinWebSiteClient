<?php

class PostController extends Controller{

	const PostPath = PLATFORM . DS . CONTROLLER . DS;
	private static $twig ;

	public function __construct(){

		self::$twig = parent::callTwig();
		parent::forceLogin();
	}




	public function __call($name, $arguments)
	{
		$this->indexAction();
	}



	public function indexAction(){


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "delete"){
				$paramID = isset($_POST['paramID']) ? safe($_POST['paramID']) : "" ;

				$PostModel = new PostModel();
				$PostModel->DeletePost($paramID);
			}

		}else{

			$PostsModel = new PostModel();

			if(isset(parent::$parameter["cat"])){
				$Posts = $PostsModel->getPostList(parent::$parameter["cat"]);
			}else{
				$Posts = $PostsModel->getPostList();
			}


			$twigParameters = array(
				'pageTitle'     => 'نوشته ها',
				'pageDes'       => 'لیست نوشته های وبسایت شما' ,
				'pageKeywords'  => 'نوشته ها,نوشته های وبسایت',
				'canonical'     => '{{siteurl}}Dashboard/Post' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Tables functionality -->
	<script src="{{ asset(\'js/jquery.dataTables.js\') }}" type="text/javascript"></script>

	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>


	<script src="{{ asset(\'js/ion.rangeSlider.min.js\') }}" type="text/javascript"></script>

	<script src="{{ asset(\'js/moments/moment.min.js\') }}" type="text/javascript"></script>
	<script src="{{ asset(\'js/moments/locale/fa.js\') }}" type="text/javascript"></script>
	<script src="{{ asset(\'js/moments/moment-jalali.js\') }}" type="text/javascript"></script>
		' ,


				'extraCss'      => '
	<!-- table view -->
	<link href="{{ asset(\'/css/table.view.css\') }}" rel="stylesheet">' ,

				'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initTable();
			jurchin.initPostsList();
		});
	</script>' ,

				// data
				'posts'         => $Posts
			);


			echo self::$twig->render(self::PostPath . 'list.html', $twigParameters );
		}
	}



	public function AddAction(){


		$uid = parent::$user->user_id;


		if(parent::isMethod('POST')){

			$action = isset($_POST['action']) ? safe($_POST['action']) : "" ;

			if($action == "new"){

				$postTitle = isset($_POST['postTitle']) ? safe($_POST['postTitle']) : "" ;
				$postCategory = isset($_POST['postCategory']) ? safe($_POST['postCategory']) : 0 ;
				$postKind = isset($_POST['postKind']) ? safe($_POST['postKind']) : "post" ;
				$postContent = isset($_POST['postContent']) ? safe($_POST['postContent']) : "بدون محتوا" ;
				$postTags = isset($_POST['postTags']) ? safe($_POST['postTags']) : "" ;
				$postActiveCommentsPerm = isset($_POST['postActiveCommentsPerm']) ? intval($_POST['postActiveCommentsPerm']) : 0 ;

				if($postTitle == ""){
					die("عنوان پست الزامی می باشد .");
				}

				$postTags = explode(",", $postTags);
				$postPic = "" ;


				if(isset($_FILES['postPic'])){
					$imageFileType = pathinfo($_FILES['postPic']['name'] ,PATHINFO_EXTENSION);
					$uploadDir = MAINROOT."/uploads/";
					$fileName = random_string(12) .".". $imageFileType;
					$uploadOk = 1;

					// Check if image file is a actual image or fake image
					if(file_exists($_FILES["postPic"]["tmp_name"]) && getimagesize($_FILES["postPic"]["tmp_name"]) == false) {
						$uploadOk = 0;
					}

					// Check file size
					if ($_FILES["postPic"]["size"] > 500000) {
						$uploadOk = 0;
					}

					$mimes = array(
						'jpg' 	=> 'image/jpg',
						'jpeg' 	=> 'image/jpeg',
						'gif' 	=> 'image/gif',
						'png' 	=> 'image/png',
					);


					// chcek extention allowed
					if(!(isset($mimes[$imageFileType]) && in_array($_FILES["postPic"]["type"], $mimes , true) )){
						$uploadOk = 0;
					}



					if ($uploadOk == 1) {
						if (move_uploaded_file($_FILES["postPic"]["tmp_name"], $uploadDir.$fileName)) {
							$postPic = $fileName;
						}
					}
				}

				$PostModel = new PostModel();
				$addResult = $PostModel->newPost($uid , $postTitle , $postCategory , $postKind , $postContent , $postTags , $postPic , $postActiveCommentsPerm);

				if($addResult){
					safe_redirect(SITEURL . 'Dashboard/Post');
				}

			}
		}else{

			$CatsModel = new CategoriesModel();
			$Cats = $CatsModel->getCatList();


			$twigParameters = array(
				'pageTitle'     => 'افزودن نوشته',
				'pageDes'       => 'درج نوشته برای وبسایت شما (مطلب ، پست ، محصول و ...)' ,
				'pageKeywords'  => 'درج نوشته,افزودن نوشته به وبسایت',
				'canonical'     => '{{siteurl}}Dashboard/Post/Add' ,
				'pageImage'     => '' ,
				'extraJs'       => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
				'extraCss'      => '' ,

				'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initPostHandle();
		});
	</script>' ,


				// data
				'cats' => $Cats ,
			);

			echo self::$twig->render(self::PostPath . 'add-edit.html', $twigParameters );
		}

	}




	public function EditAction(){

		// get pid
		$pid = parent::$parameter['id'];


		$PostsModel = new PostModel();
		$Post = $PostsModel->getPostInfo($pid);

		$CatsModel = new CategoriesModel();
		$Cats = $CatsModel->getCatList();


		// get tags
		$tags = $PostsModel->getTagsList($pid);

		$twigParameters = array(
			'pageTitle'     => 'ویرایش نوشته',
			'pageDes'       => 'ویرایش نوشته ثبت شده در وبسایت شما (مطلب ، پست ، محصول و ...)' ,
			'pageKeywords'  => 'ویرایش نوشته,ویرایش نوشته به وبسایت',
			'canonical'     => '{{siteurl}}Dashboard/Post/Edit/id:' . $pid ,
			'pageImage'     => '' ,
			'extraJs'       => '
	<!-- Notification functionality -->
	<script src="{{ asset(\'js/bootstrap-notify.js\') }}"></script>' ,
			'extraCss'      => '' ,

			'endDom'        => '
	<script type="text/javascript">
		$(document).ready(function(){
			jurchin.initPostHandle();
		});
	</script>' ,


			// data
			'post' => $Post ,
			'cats' => $Cats ,
			'tags' => $tags
		);


		echo self::$twig->render(self::PostPath . 'add-edit.html', $twigParameters );

	}



}
