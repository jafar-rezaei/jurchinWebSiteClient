<?php

// Base Jamework Model Class

class Model{

	protected static $dbh = NULL;

	public static function dataBase($db = null){

		// if another db connect
		if($db !== null){
			return new DatabaseHelper($db);
		}

		// Connect to dataBase
		if(self::$dbh == null){
			self::$dbh = new DatabaseHelper();
		}

		return self::$dbh;
 
	}


}