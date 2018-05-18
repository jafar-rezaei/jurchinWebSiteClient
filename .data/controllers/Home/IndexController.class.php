<?php

class IndexController extends Controller{


	private static $twig;
	public static $site;
	public static $flagSearch;
	public static $flagTags;
	public static $flagCategories;


	public function __construct(){

		self::$twig = parent::callTwig();
		$this->runFirst();

	}



	// default method calling
	public function __call($name, $arguments) {

		$this->indexAction();

	}


	public function runFirst() {

			self::$site = parent::$site;

			// handle if any method exists
			self::handleMethod(self::$site);

			// create new visit of site
			$siteModel = new SiteModel();
			$siteModel->makeVisit(self::$site['visits']);

			define("CURR_TEMPLATE_PATH",  PLATFORM . DS . self::$site['template'] );

	}



	public function indexAction() {

		$this->renderPage( "index");

	}





	public function postAction() {

		$data = jamework::getUrls();

		$postModal = new PostModel();
		$databaseData["post"] = $postModal->showPost(safe($data[3]));

		if( isset($databaseData["post"]) && count($databaseData["post"]) == 0 ) {
				MessageController::handle(array('message' => 'پست مورد نظر وجود ندارد و ممکن است حذف شده باشد . میتوانید جستجو کنید.','code' => 198),404);
		}

		self::renderPage("post" , $databaseData);

	}


	public function searchAction() {

		self::$flagSearch = true;
		self::renderPage("search");

	}

	public function tagAction() {

		self::$flagTags = true;
		self::renderPage("tag");

	}

	public function categoryAction() {

		self::$flagCategories = true;
		self::renderPage("category");

	}




	public static function renderPage( $page , array $databaseData = array() ) {

			// do not change
			$siteAddress = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && ! in_array(strtolower($_SERVER['HTTPS']), array( 'off', 'no' ))) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

			$pageContent = self::getPageContent($page);

			$pages = new PagesModel();
			$pageInfo = $pages->getPageInfo($page);

			$urlData = jamework::getUrls();


			preg_match_all('/{#.*?<helper>(.*?)\<\/helper>.*?#}/', $pageContent, $helpers);

			foreach ($helpers[1] as $key => $help) {

				if(isset($help)) {
					$helpArray = array();
					$help = explode("-", $help);
					foreach ($help as $value) {
						$sep = explode(":", $value);
						if(isset($sep[1]))
							$helpArray[$sep[0]] = $sep[1];
					}

					// print_r($helpArray);

					if(preg_match("/posts\d*/",$helpArray["name"] , $matches)) {

						if(intval($helpArray['cat']) == 0) {
							$helpArray['cat'] = "";
						}


						// get posts info
						$postModal = new PostModel();

						if(isset($_GET["keyword"]) && isset($helpArray["doSearch"])) {

							self::$flagSearch = false;

							$cat 			= intval(input("GET" , "Category"));
							$keyword 	= input("GET" , "keyword");
							$SearchIn = input("GET" , "SearchIn");

							$search = array(
								"keyword"	=>	$keyword ,
								"in"			=>	$SearchIn ,
								"title" 	=> "نتایج جستجو برای ".$keyword ,
							 	"des" 		=> "نتایج جستجو برای ".$keyword ,
								"keywords"=> str_replace(" " , "," , $keyword ) ,
								"image"		=> ""
							);

							$databaseData[$matches[0]] = $postModal->getPostListMain(
								$cat ,
								$cat ,
								safe($helpArray['sorting']) ,
								$search
							);
							$databaseData["search"] = $search;

						}
						else if(isset($helpArray["tagsRepeat"]) && strtolower($urlData[2]) == "tag") {

							self::$flagTags = false;
							$tag = urldecode($urlData[3]);


							if(strlen($tag) > 0 ) {
								$PostsModel = new PostModel();
								$databaseData[$matches[0]] = $PostsModel->getTagPosts($tag);
							}else{
								// no tag found
								$databaseData[$matches[0]] = array();
							}

							$databaseData["tag"] = array(
								"title" 	=> "تگ های ".$tag ,
								"des" 		=> "پست های دارای تگ ".$tag ,
								"keywords"=> str_replace(" " , "," , $tag ) ,
								"image"		=> ""
							);

						}
						else if(isset($helpArray["catsRepeat"]) && strtolower($urlData[2]) == "category") {

							self::$flagCategories = false;
							$catSlug = urldecode($urlData[3]);


							if(strlen($catSlug) > 0 ) {

								// get cat id
								$CategoriesModel = new CategoriesModel();
								$cat = $CategoriesModel->getCatInfoWithSlug($catSlug , array("cid") );

								$PostsModel = new PostModel();
								$databaseData[$matches[0]] = $PostsModel->getPostListMain(
									$cat->cid ,
									$cat->cid ,
									safe($helpArray['sorting'])
								);
							}else{
								// no catSlug found
								$databaseData[$matches[0]] = array();
							}

							$databaseData["category"] = array(
								"title" 	=> "مطالب دسته ".$catSlug ,
								"des" 		=> "مطالبی که در بخش '".$catSlug."' درج کرده ایم." ,
								"keywords"=> str_replace(" " , "," , $catSlug ) ,
								"image"		=> ""
							);

						}
						else {
							$databaseData[$matches[0]] = $postModal->getPostListMain(
								$helpArray['cat'] ,
								$helpArray['cat'] ,
								safe($helpArray['sorting'])
							);
						}


					}
					else if(preg_match("/cats\d*/",$helpArray["name"] , $matches)) {

						$helpArray['catPostCount'] = $helpArray['catPostCount'] == "true";


						// get cats info
						$CategoriesModel = new CategoriesModel();

						$databaseData[$matches[0]] = $CategoriesModel->getCatsListHome(
							$site['sid'] ,
							$helpArray['catPostCount']
						);
					}
					else if(preg_match("/comments\d*/",$helpArray["name"] , $matches)) {

						//sorting:{old,new}-paging:true
						$CommentsModel = new CommentsModel();
						$databaseData[$matches[0]] = $CommentsModel->getComments();
					}

				}

			}


			$twigParameters = array(
				'pageTitle'     	=> $pageInfo->title . " - " . self::$site['name'],
				'pageDes'       	=> $pageInfo->description ,
				'pageKeyword'   	=> str_replace("-", ",", $pageInfo->keywords ) ,
				//'canonical'     	=> $siteAddress . "/" ,
				'pageImage'     	=> $pageInfo->image ,
				'title'						=> $pageInfo->title ,
				'extraJs'       	=> '' ,
				'extraCss'      	=> '' ,
				'endDom'        	=> '' ,

				'csrf'						=> getCSRFToken() ,

				// data
				'pageContent'   	=> $pageContent ,
				'page'          	=> $pageInfo ,
				'site'          	=> self::$site ,
				'usiteurl'      	=> $siteAddress ,
				'publicPath'			=> $siteAddress."/public/",
				'templateUrl'			=> $siteAddress."/public/templates" . DS . self::$site['template'] ,
			);


			$twigParameters = array_merge($databaseData , $twigParameters);


			echo self::$twig->render(  PLATFORM . DS . self::$site['template'] . '/page_temp.html', $twigParameters );
			exit();

	}


	public static function verifyAction() {

		self::payVerify();

	}





	public static function getPageContent($page){

		$fileAddress = VIEW_PATH . CURR_TEMPLATE_PATH . "/page_".$page.".html";

		// if is not page try load file
		if((!is_file($fileAddress) && !file_exists($fileAddress)) ) {


				if(in_array(strtolower($page) , array("search" , "category" , "tag")) ) {
					$fileAddress = VIEW_PATH . CURR_TEMPLATE_PATH . "/page_single.html";
					$pageContent = file_get_contents($fileAddress);
				}
				// if page empty
				if($pageContent === false) {
					MessageController::handle(array('message' => 'آدرس وارد شده وجود ندارد یا حذف شده است .','code' => 197),404);
				}

		} else {

				$pageContent = file_get_contents($fileAddress);

				// if page empty
				if($pageContent === false) {
						MessageController::handle(array('message' => 'آدرس وارد شده وجود ندارد یا حذف شده است .','code' => 198),404);
				}
		}

		// remove show example data
		$pageContent = preg_replace('/<twig.*?>.*?\<\/twig>/s', '', html_entity_decode($pageContent) );
		$pageContent = str_replace("assets/themes", "http://www.jurchin.com/editor/assets/themes", $pageContent);

		return $pageContent;

	}

	public static function handleMethod($site) {

		if(parent::isMethod('POST')) {
			$action = !empty($_POST["action"]) ? safe($_POST["action"]) : "";

			if($action == "addComment") {

				$name = !empty($_POST["name"]) ? safe($_POST["name"]) : "";
				$email = !empty($_POST["email"]) ? safe($_POST["email"]) : "";
				$comment = !empty($_POST["comment"]) ? safe($_POST["comment"]) : "";
				$pid = !empty($_POST["pid"]) ? intval($_POST["pid"]) : "";

				$CommentsModel = new CommentsModel();
				$cmResult = $CommentsModel->addComment($name , $email , $comment , $pid);

				if($cmResult) {
						echo "با موفقیت ثبت شد ...";
				}

			} else if($action == "doStartUserPay") {

				$gateWay = intval(input("POST" , "gateWay" , ""));
				$payAmount = input("POST" , "payAmount" , "");
				$dataToUse = input("POST" , "dataToUse" , "");

				$payName = input("POST" , "payName" , "");
				$payMail = input("POST" , "payMail" , "");
				$payDes = input("POST" , "payDes" , "");

				$siteModel = new SiteModel();
				$gateWayName = $siteModel->gateWayInfo($gateWay);

				$payUnique = random_string(32);
				$ReturnPath = "http://".$site['sub'].".jurchin.com/Verify/Info";


				$userInfo = array(
					"name"	=> $payName ,
					"mail"	=> $payMail ,
					"des"	=> $payDes ,
					"pay"	=> $gateWayName->kind
				);


				// used data for identify
				$uid 		= 0;
				$productId 	= 0;


				$payModel = new PayModel();
				$factorNumber = $payModel->addPay(
					$site["sid"] ,				// site id
					$payAmount ,				// mablagh
					$uid ,						// user Id
					$userInfo ,					// serialize name , des , email
					$productId ,				// product id or something like that
					$dataToUse ,				// message used after pay
					$payUnique ,				// unique pay key
					$gateWay					// used gateway id
				);

				if(intval($factorNumber) > 0) {

					$api = unserialize($gateWayName->data)["apikey"];

					if($gateWayName->kind == "arianpal") {
						$api = explode("::", $api);
						$payAmount = $payAmount/10;
					}
					require_once(PAY_PATH . $gateWayName->kind . '/send.php');

				}


			} else {

			}

		}

	}



	private static function payVerify() {

		$gateWayName = "";

		// pay.ir
		$transId = input("POST" , "transId");
		if($transId !== "") {
			$gateWayName = "pay.ir";
			$uniqePayCode = $transId;
		}


		// zarinpal
		$Authority = input("GET" , "Authority");
		if($Authority !== "") {
			$gateWayName = "zarinpal";
			$uniqePayCode = $Authority;
		}


		// arianpal
		$Refnumber = input("POST" , "refnumber");
		$Resnumber = input("POST" , "resnumber");
		if($Refnumber !== "" || $Resnumber !== "") {
			$gateWayName = "arianpal";
			$uniqePayCode = $Resnumber;
		}



		// get apikey or MerchantID of gateWay
		$payModel = new PayModel();
		$payInfo = $payModel->getPayInfoByTranKey($uniqePayCode);

		$siteModel = new SiteModel();
		$gateWayInfo = $siteModel->gateWayInfo($payInfo->gatewayID);


		$apikey = unserialize($gateWayInfo->data)["apikey"];
		$amount = $payInfo->amount;
		$payOk = false;



		########################
		##		Verify Pays
		########################

		if($gateWayName == "pay.ir") {

			$status = input("POST" , "Status");

			if($status == 1) {

				$data = array(
					'api'       => $apikey ,
					'transId'   => $transId
				);
				$result = postSend('https://pay.ir/payment/verify',$data);
				echo $result;
				if($result) {
					$payOk = true;
				}

			}else{
				$errMessage = input("POST" , "message");
			}

		} else if($gateWayName == "zarinpal") {

			$amount = $amount/10;   //Amount will be based on Toman
			$Status = input("GET" , "Status");

			if ($Status == 'OK') {

			    $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

			    $result = $client->PaymentVerification(
			        [
			            'MerchantID'    => $apikey,
			            'Authority'     => $Authority,
			            'Amount'        => $amount
			        ]
			    );

			    if ($result->Status == 100) {
			        $payOk = true;
			        $codePeygiri = $result->RefID;
			    } else {
			        $payOk = false;
					$errMessage = '';
			    }

			} else {
			    $errMessage = 'Transaction canceled by user';
			}

		} else if($gateWayName == "arianpal") {

			$paystatus = input("POST" , "status");


			$api = explode("::", $apikey);
			$payAmount = $payAmount/10; //Price By Toman


			$MerchantID = $api[0];
			$Password = $api[1];


			if(isset($paystatus) && $paystatus == 100) {

				$client = new SoapClient('http://merchant.arianpal.com/WebService.asmx?wsdl');

				$res = $client->VerifyPayment(
					array(
						"MerchantID" 	=>	$MerchantID ,
						"Password" 		=>	$Password ,
						"Price" 		=>	$payAmount,
						"RefNum" 		=>	$Refnumber
					)
				);


				$Status = $res->verifyPaymentResult->ResultStatus;
				$PayPrice = $res->verifyPaymentResult->PayementedPrice;
				if($Status == 'Success') {
					$payOk = true;
					$codePeygiri = $Refnumber;
				} else {
					$payOk = false;
					$errMessage = "";
				}

			} else {
				$errMessage = 'Transaction canceled by user';
			}

		}




		if($payOk == true) {
			// update pay status
			$payModel->updatePay($payInfo->id , $uniqePayCode);

			echo "پرداخت با موفقیت انجام شد .کد رهگیری : $codePeygiri <Br/> $payInfo->dataToUse";
		}else{
			echo $errMessage;
		}

	}


}
