<?php

class PayModel extends Model {

	protected static $db;
	public function __construct() {

		self::$db = parent::dataBase();

	}

	public function getPaysList($uid , $sid) {

		$tickets = self::$db->read(
			'jor_pays',
			array('title','id'),
			array(
				"uid"=>$uid,
				"sid" => $sid,
				"status" => "Answerd",
				"readperm" => 0
			),
			'=-=-=-=',
			'AND-AND-AND',
			'ORDER BY date DESC',
			'm'
		);

		return $tickets;
	}



	public function updatePay($pid , $uniqePayCode){

		$updatePay = self::$db->update(
			'jor_pays',
			array(
				"status" => "success"
			),
			array(
				"id"        => $pid ,
				"trankey"	=> $uniqePayCode
			),
			'=-=',
			'AND'
		);
		if($updatePay){
			return true;
		}

	}


	public function addPay( $sid , $amount , $user , $userInfo ,  $payfor , $dataToUse , $payUnique , $gatewayID ){

        $now = time();
        $email = $userInfo["mail"];

		$addNewPay = self::$db->create(
			'jor_pays',
			array(
				"sid" 		=> $sid ,
				"amount"	=> $amount ,
				"date"		=> $now ,
				"user"	    => $user ,
				"userInfo"	=> serialize($userInfo) ,
				"payfor"	=> $payfor ,
                "dataToUse" => $dataToUse ,
                "status"    => "send" ,
				"primkey"	=> $payUnique ,
				"gatewayID"	=> $gatewayID
			)
		);


        if(validate_email($email)){
    		$mailer = new MailHelper(
    			$email ,
    			array(
    				"mailsubject"	=> "شروع پرداخت با کد : " . $payUnique ,
    				"mainContent" 	=> "پرداخت شما با شناسه : " . $payUnique . "<br/> در تاریخ : " . jdate("Y/m/d , H:i" , $now) . "<br/>مبلغ : " . number_format($amount)
					 . " ریال" ,
    				"HeadTop1"      => "پرداخت شما شروع شد ...",
    				"HeadBottom1"	=> "اطلاعات پرداخت در متن ایمیل موجود است .",
    				"HeadBottom2"	=> "",
    				"TopBtnText"	=> "برگشت به سایت",
    				"TopBtnLink"	=> "http://www.jurchin.com/",
    			)
    		);
			$mailer->sendmail();
        }

		return $addNewPay;
	}

	public function getPayInfoByTranKey($payUnique){

		$payInfo = self::$db->read(
			'jor_pays',
			array(),
			array(
				"status"		=> "send",
				"trankey"		=> $payUnique
			),
			'=-=',
			'AND',
			'',
			's'
		);

		return $payInfo;

	}

}
