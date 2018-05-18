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



	public function updatePay($sid , $tid){

		$updatePay = self::$db->update(
			'jor_pays',
			array(
				"status" => "success"
			),
			array(
				"id"        => $tid,
				"sid"       => $sid
			),
			'=-=',
			'AND'
		);


		if($updatePay){
			return true;
		}

	}


	public function addPay( $sid , $amount , $user , $userInfo ,  $payfor , $dataToUse , $trankey){

		$addNewPay = self::$db->create(
			'jor_pays',
			array(
				"sid" 		=> $sid ,
				"amount"	=> $amount ,
				"date"		=> time() ,
				"user"	    => 0 ,
				"userInfo"	=> time() ,
				"payfor"	=> $payfor ,
                "dataToUse" => $dataToUse
                "status"    => "send" ,
				"primkey"	=> random_string(32)
                "trankey"   =>
			)
		);

		return $addNewPay;
	}

}
?>
