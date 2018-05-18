<?php
include_once("config.php");
include_once("conn2.php");
require_once('lib/Nusoap/nusoap.php');

// MELLAT DESIGN CODES

##################################
#### CONFIG SMS PANEL IRAN.TC ####
##################################

	

class SmsHelper {

	private $panelusername = 'username';
	private $panelpassword = 'password';
	private $panelsmsnum = 'ramz';
	private $runsmspass = 'mellatsms';
	private $client ;
	private $conn;
	
	public $errors = array(
	    "1" => "شماره دریافت کننده خالی است",
	    "2" => "ارسال کننده خالی است",
	    "3" => "Not Valid encoding",
	    "4" => "Not Valid Message Class",
	    "6" => "UDH Error",
	    "13" => "پیامک خالی است",
	    "14" => "خطا در سرور",
	    "15" => "لطفا مجددا تلاش کنید",
	    "16" => "حساب غیرفعال است",
	    "17" => "حساب منقضی شده",
	    "19" => "درخواست بی اعتبار است",
	    "22" => "سرویس مقدور نیست",
	    "23" => "ترافیک سنگین ارسال پیام",
	    "25" => "نوع سرویس درخواستی نامعتبر است",
	    "27" => "تبلیغات غیر فعال شده است",
	    "106" => "آرایه دریافت کنندگان خالی است",
	    "107" => "طول آرایه بیشتر از حد مجاز است",
	    "108" => "شماره فرستنده خالی است",
	    "-1" => "نام کاربری یا رمز عبور اشتباه است",
	    "-2" => "شماره مجاز نیست یا پیامک خالی است",
	    "-3" => "عدم اعتبار",
	    "-4" => "خطا در برقراری ارتباط با وب سرویس",
	    "-5" => "خطا در تراکنش مالی",
	    "-11" => "خطا در مشخصات",
	    "-15" => "عدم دسترسی ",
	    "-100" => "امکان ارسال پیام وجود ندارد"
	);
	
	public $smstext = NULL ; 
	public $smsto = NULL;
	
	public function __construct()
	{
		// CREATE A SOAP OBJECT
		$this->client = new nusoap_client('http://ws.iran.tc/index.php?wsdl',true);
		$err = $this->client->getError();
	}
	
	public function addToQeue($key , $smspass , $to , $text ){
		$key = decryptIt($key);
		$action = substr( $key , '0' , strripos($key , "_"));
		$this->smstext = $text;
		$this->smsto = $to;
		$err == 0;
		
		// IF NO ERROR
		if($err == 0){
			//IF PASSWORD IS TRUE WITH COFNIG
			if($smspass == md5($this->runsmspass)){
				if($action == "send"){
					$this->addToDataBase(0);
				}
			}
		}
	}
	
	public function send($key , $smspass , $to , $text , $smsid = NULL){
		$key = decryptIt($key);
		$action = substr( $key , '0' , strripos($key , "_"));
		$this->smstext = $text;
		$this->smsto = $to;

		$err = 0;
		if(empty( $to)){echo 'دریافت کننده خالی است';$err ++;}
		
		// IF NO ERROR
		if(!$err){
			//IF PASSWORD IS TRUE WITH COFNIG
			if($smspass == md5($this->runsmspass)){
				if($action == "send"){
					$this->sendSimplesms($smsid);
				}else if($action == "smsstatus"){
					$this->getSmsStatus();
				}else if($action == "groupsms"){
					$this->groupSms();
				}else 
					echo 'کلید نا متعتبر'.$action ;
			}else{
				echo 'رمز نامعتبر';
			}
		}
	}
	 
		
	function Connection()
	{
		try {$this->conn = new PDO('mysql:host='. DB_HOST2 .';dbname='. DB_NAME2 . ';charset=utf8', DB_USER2, DB_PASS2);$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);}
		catch(PDOException $e){echo "Connection failed: " . $e->getMessage();}
		$this->conn->exec("SET character_set_results=utf8;");
		$this->conn->exec("SET character_set_client=utf8;");
		$this->conn->exec("SET character_set_connection=utf8;");
		$this->conn->exec("SET character_set_database=utf8;");
		$this->conn->exec("SET character_set_server=utf8;");
		return true;
	}
	
	
	function addToDataBase( $send =0  )
	{
		$now = time();
		if($this->Connection()){
			$stmt = $this->conn->prepare("INSERT INTO `smses` (smsto, senddate, smstext, status)
			VALUES (:smsto, :date, :smstext, :status)");
			$stmt->bindParam(':smsto', $this->smsto);
			$stmt->bindParam(':date', $now );
			$stmt->bindParam(':smstext', $this->smstext);
			$stmt->bindParam(':status', $send);
			$stmt->execute();
		} else{
			echo 'DataBase Error Occured !';
		}
	}
	
	public function resend()
	{
		if($this->Connection()){
			$resend = $this->conn->prepare("SELECT * FROM `smses` WHERE `status` = 0  ");
			$resend->execute();
			if($resend->rowCount() > 0){
				$SmsTOSend = $resend->fetchAll();
				foreach($SmsTOSend as $sts){
					$this->send(encryptIt('send_5522'), md5('mellatsms') , $sts['smsto'] , $sts['smstext'], $sts['smsid']);
				}
			}else {
				echo 'No SMS In Qeue';
			}
		}else{
			echo 'No Connection';
		}
	}
	
	
	
	public function UpdateSendedStatus( $send , $smsid)
	{
		if($this->Connection()){
			$resend = $this->conn->prepare("UPDATE `smses` SET status = :status WHERE `smsid` = :smsid  ");
			$resend->bindValue(':status', $send);
			$resend->bindValue(':smsid', $smsid);
			$resend->execute();
			if($resend->rowCount() > 0){
				echo 'Sended .';
			}
		}else{
			echo 'No Connection';
		}
	}
	
	function sendSimplesms($smsid= NULL)
	{
		$send = $this->client->call('SendSMS',
			array(
				'username' => $this->panelusername ,
				'password' => $this->panelpassword ,
				'reciver' =>  $this->smsto ,
				'text' => $this->smstext ,
				'sender' => $this->panelsmsnum
			)
		);
		$err = $this->client->getError();
		
		// IF ERROR EX
		if($err) 
			print_r($err); 
		else {
				// Sms is sended
				$this->UpdateSendedStatus(  $send, $smsid );
			}
	}
	
	function getSmsStatus($smscode)
	{
		$smscode = !empty( $_GET['smscode']) ? safe($_GET['smscode']) : "";
		$send = $client->call('StatusSMS',
			array(
				'username' => $this->panelusername,
				'password' => $this->panelpassword,
				'follow' => $smscode
			)
		);
		$err = $client->getError();
		if($err)
			print_r($err);
		else 
			print_r($send);
	}
	
	function groupSms()
	{
		// GROUP SMS : GET [smsgroups,smstext] -> SEPRSTE WITH ',' -> UP TO 70 num
		$send = $client->call('Send_GROUP_SMS',
			array(
				'username' => $this->panelusername,
				'password' => $this->panelpassword,
				'reciver' => $this->smsto ,
				'text' => $this->smstext ,
				'sender' => $this->anelsmsnum,
				'mes_msclass' => '1'
			)
		);
		$err = $client->getError();
		if($err) print_r($err); 
		else {
			print_r($send);
			$this->addToDataBase( $send );
		}
	}
}
