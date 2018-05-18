<?php

class SupportModel extends Model {


	protected static $client;
	protected static $sitekey;
	protected static $accesskey;
  	private static $siteAddress = "http://www.jurchin.com";

	public function __construct($key) {

		self::$sitekey 	= $key;
	    require_once(LIB_PATH . "Guzzle" . DS . "autoload.php");
	    self::$client = new GuzzleHttp\Client();

	}

	// get accesskey for every requset
	public function getAccessKey() {

      if( self::$accesskey == null ) {
    		$res = self::$client->post(  self::$siteAddress . "/Webservice/Rest/GetAccessKey" , [
    		  'form_params' => [
    				'param' => BASEURL . 'Client_34521345'
    			]
    		]);
  		    $headerCode = $res->getStatusCode();
  	    if($headerCode == 200) {
  			$data = json_decode($res->getBody());
            if($data !== NULL && $data->result == 1){
                return $data->data;
            } else {
                die("Response error AccessKey");
            }
		} else {
			echo $res->getHeader('content-type');
			die("header : " . $headerCode );
		}
      } else {
          return self::$accesskey;
      }

	}




	public function getTicketList($uid) {

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/getTicketList" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey ,
			  'uid'				=> $uid
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
				if($data !== NULL && $data->result == 1){
					return $data->data;
				} else {
					die("Response error get ticket");
				}
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}
	}

	public function getTicketCats(){

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/getTicketCats" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
				if($data !== NULL && $data->result == 1){
					return $data->data;
				} else {
					die("Response error get ticket cats");
				}
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}

	}

	public function getTicketListSideBar($uid) {

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/getTicketsSideBar" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey ,
			  'uid'				=> $uid
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
				if($data !== NULL && $data->result == 1){
					return $data->data;
				} else {
					die("Response error get ticket side bar");
				}
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}


	}


	public function getTicketData($tid) {

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/showTicket" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey ,
			  'tid'				=> $tid
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
				if($data !== NULL && $data->result == 1){
					return $data->data;
				} else {
					die("Response error get ticket data");
				}
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}


	}


	// make fn soft delete
	public function DeleteTicket($tid){

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/DeleteTicket" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey ,
			  'tid'				=> $tid
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
				if($data !== NULL && $data->result == 1){
					echo json_encode(
						array(
							"message"	=> "ok"
						)
					);
				} else {
					die("Response error delete ticket");
				}
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}


	}

	public function closeTicket($tid){

		 // get accessKey to request usable in 1 min
	   	 $accessKey = $this->getAccessKey();

	   	 $res = self::$client->post( self::$siteAddress . "/Webservice/Rest/closeTicket" , [
	   		 'form_params' => [
	   		   'accesskey'     	=> $accessKey ,
	   		   'sitekey'       	=> self::$sitekey ,
	   		   'tid'				=> $tid
	   		 ]
	   	 ]);
	   	 $headerCode = $res->getStatusCode();
	   	 if($headerCode == 200) {
	   	   $data = json_decode($res->getBody());
	   			 if($data->result == 1){
					echo json_encode(
		 				array(
		 					"message"	=> "ok"
		 				)
		 			);
	   			 } else {
	   				 die("Response error close ticket ". $data->message);
	   			 }
	   	 } else {
	   		   echo $res->getHeader('content-type');
	   		   die("header : " . $headerCode );
	   	 }


	}

	public function newTicket( $uid , $ticketTitle , $ticketContent , $ticketDepartment , $ticketPrioerity , $ticketAttach = ""){

		 // get accessKey to request usable in 1 min
		 $accessKey = $this->getAccessKey();

		 $res = self::$client->post( self::$siteAddress . "/Webservice/Rest/addTicket" , [
			 'form_params' => [
			   'accesskey'     	=> $accessKey ,
			   'sitekey'       	=> self::$sitekey ,
			   'uid'				=> $uid ,
			   'ticketTitle'		=> $ticketTitle ,
			   'ticketContent'		=> $ticketContent ,
			   'ticketDepartment'	=> $ticketDepartment ,
			   'ticketPrioerity'	=> $ticketPrioerity ,
			   'ticketAttach'		=> $ticketAttach
			 ]
		 ]);
		 $headerCode = $res->getStatusCode();
		 if($headerCode == 200) {
		   $data = json_decode($res->getBody());
		   if($data->result == 1){
			   return true;
		   } else {
			   die("Response error close ticket ". $data->message);
		   }
		 } else {
			   echo $res->getHeader('content-type');
			   die("header : " . $headerCode );
		 }

	}


	public function answerTicket($tid , $content  ){

		// get accessKey to request usable in 1 min
		$accessKey = $this->getAccessKey();

		$res = self::$client->post( self::$siteAddress . "/Webservice/Rest/answerTicket" , [
			'form_params' => [
			  'accesskey'     	=> $accessKey ,
			  'sitekey'       	=> self::$sitekey ,
			  'tid'				=> $tid ,
			  'content'			=> $content
			]
		]);
		$headerCode = $res->getStatusCode();
		if($headerCode == 200) {
		  $data = json_decode($res->getBody());
		  if($data->result == 1){
			  echo json_encode(
  				array(
  					"message"	=> "ok"
  				)
  			);
		  } else {
			  die("Response error close ticket ". $data->message);
		  }
		} else {
			  echo $res->getHeader('content-type');
			  die("header : " . $headerCode );
		}

	}

}
