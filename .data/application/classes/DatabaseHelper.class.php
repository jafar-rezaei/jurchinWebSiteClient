<?php

class DatabaseHelper
{
	private $dbh = NULL;
	private $config;

	public function __construct($conName = 'conn1' )
	{
		$this->config = jamework::getConfig();
		$this->$conName();
	}


	private function conn1()
	{
		try {
			$this->dbh = new PDO('mysql:host='.$this->config['DB_HOST'].';dbname='.$this->config['DB_NAME'].';charset=utf8', $this->config['DB_USER'], $this->config['DB_PASS']);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbh->exec("SET character_set_results=utf8;");
			$this->dbh->exec("SET character_set_client=utf8;");
			$this->dbh->exec("SET character_set_connection=utf8;");
			$this->dbh->exec("SET character_set_database=utf8;");
			$this->dbh->exec("SET character_set_server=utf8;");
		}
		catch(PDOException $e){
			throw new Exception("Connection failed: " . $e->getMessage());
		}
		return $this->dbh;
	}

	private function support()
	{
		try {
			$this->dbh = new PDO('mysql:host='.$this->config['DB_HOST'].';dbname='.$this->config['DB_NAME_SUPPORT'].';charset=utf8', $this->config['DB_USER_SUPPORT'], $this->config['DB_PASS_SUPPORT']);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbh->exec("SET character_set_results=utf8;");
			$this->dbh->exec("SET character_set_client=utf8;");
			$this->dbh->exec("SET character_set_connection=utf8;");
			$this->dbh->exec("SET character_set_database=utf8;");
			$this->dbh->exec("SET character_set_server=utf8;");
		}
		catch(PDOException $e){
			throw new Exception("Connection failed: " . $e->getMessage());
		}
		return $this->dbh;
	}


	public function __call($name, $arguments){


		if($name == 'site'){
			$dbInfo = $arguments[0];

			if(empty($dbInfo['DataBaseHost'])){die("no Host");}
			if(empty($dbInfo['DataBaseName'])){die("no DB");}
			if(empty($dbInfo['DataBaseUser'])){die("no User");}else{
				$dbInfo['DataBaseUser'] = strlen($dbInfo['DataBaseUser']) <= 16 ? $dbInfo['DataBaseUser'] : substr( $dbInfo['DataBaseUser'] , 0 , 16);
			}
			if(empty($dbInfo['DataBasePass'])){die("no Pass");}

			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
			);


			try {
				$this->dbh = new PDO('mysql:host='.$dbInfo['DataBaseHost'].';dbname='.$dbInfo['DataBaseName'].';charset=utf8', $dbInfo['DataBaseUser'], $dbInfo['DataBasePass'] , $options );
				$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->dbh->exec("SET character_set_results=utf8;");
				$this->dbh->exec("SET character_set_client=utf8;");
				$this->dbh->exec("SET character_set_connection=utf8;");
				$this->dbh->exec("SET character_set_database=utf8;");
				$this->dbh->exec("SET character_set_server=utf8;");
			}
			catch(PDOException $e){
				throw new Exception("Connection failed: " . $e->getMessage());
			}
			return $this->dbh;
		}
	}



	public function read($tableName, $fields = array(), $where = array(), $operations = '=', $andOr = '', $options = '', $mode = 'm')
	{
		if ($this->dbh) {
			if (count($fields) > 0) {
				foreach ($fields as $key => $value) {
					if (strpos(strtolower($value), 'as') !== false) {
						$fieldsArray[] =  $value ;
					}else{
						$fieldsArray[] = '`' . $value . '`';
					}
				}

				$fieldsText = implode(',', $fieldsArray);
			}
			else {
				$fieldsText = '*';
			}

			$whereArrayCount = count($where);

			if ($whereArrayCount > 0) {
				$whereText = 'WHERE (';

				$andOrArray = explode('-', $andOr);
				$andOrArrayCount = count($andOrArray);



				$operationsArray = explode('-', $operations);
				$operationsArrayCount = count($operationsArray);


				if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

					$i = 0; // for operations
					$j = 0; // for AND OR



					foreach ($where as $key => $value) {
						$whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
						$i++;
						$j++;
					}


				}
				elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

					$i = 0; // for operations


					foreach ($where as $key => $value) {
						$whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . '  ';
						$i++;
					}


				}
				else {
					//die("count operations and where is not ok " .__FILE__.__LINE__);
				}

				$whereText = substr($whereText, 0, -2) . ')';

				if (strlen($options) > 0) {
					$query = "SELECT {$fieldsText} FROM `{$tableName}` {$whereText} {$options}";
				}
				elseif (strlen($options) == 0) {
					$query = "SELECT {$fieldsText} FROM `{$tableName}` {$whereText}";
				}


				$stmt = $this->dbh->prepare($query);

				// jadid

				if (in_array('IN', $operationsArray)) {
					$k = 0;

					$keysArray = array_keys($where);

					$valuesArray = array_values($where);

					for ($k = 0; $k < $operationsArrayCount - 1; $k++) {
						$stmt->bindValue(':' . $keysArray[$k], $valuesArray[$k]);
					}
				}
				else {
					foreach ($where as $key => $value) {
						$stmt->bindValue(':' . $key, $value);
					}
				}


				// end jadid
				//echo $query."<br/>";
				$stmt->execute();
			}
			else {
				if (strlen($options) > 0) {
					$query = "SELECT {$fieldsText} FROM `{$tableName}` {$options}";
				}
				elseif (strlen($options) == 0) {
					$query = "SELECT {$fieldsText} FROM `{$tableName}`";
				}

				$stmt = $this->dbh->prepare($query);
				$stmt->execute();
			}

			if ($stmt && $stmt->rowCount() > 0) {

				switch ((string)$mode) {
					case 's':
						return $stmt->fetchObject();
						break;
					case 'm':
						return $stmt->fetchAll(PDO::FETCH_ASSOC);
						break;
					case 'count':
						return $stmt->rowCount();
						break;
				}


			}
			else {
				return array();
			}
		}
	}

	public function create($tableName, $inputs = array())
	{
		if ($this->dbh && count($inputs) > 0) {
			foreach ($inputs as $key => $value) {
				$fields[] = '`' . $key . '`';
				$values[] = ':' . $key;
			}

			$fields = implode(',', $fields);
			$values = implode(',', $values);

			$stmt = $this->dbh->prepare("INSERT INTO `{$tableName}` ($fields) VALUES ($values)");

			foreach ($inputs as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}

			$stmt->execute();

			if ($stmt && $stmt->rowCount()) {
				return $this->dbh->lastInsertId();
			}
			else {
				throw new Exception("Error: Khatayi Dar Darj Rokh Dadeh Ast!");
				return false;
			}
		}
	}

	public function delete($tableName, $where = array(), $operations = '=', $andOr = '')
	{
		$whereArrayCount = count($where);

		if ($this->dbh && $whereArrayCount > 0) {
			$whereText = 'WHERE (';

			$andOrArray = explode('-', $andOr);
			$andOrArrayCount = count($andOrArray);

			$operationsArray = explode('-', $operations);
			$operationsArrayCount = count($operationsArray);

			if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

				$i = 0; // for operations
				$j = 0; // for AND OR

				foreach ($where as $key => $value) {
					$whereText .= '`' . $key . '` ' . $operationsArray[$i] . ' :' . $key . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
					$i++;
					$j++;
				}
			}
			elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

				$i = 0; // for operations

				foreach ($where as $key => $value) {
					$whereText .= '`' . $key . '` ' . $operationsArray[$i] . ' :' . $key . '  ';
					$i++;
				}
			}
			else {
				die("Counts operations and where Not ok ");
			}

			$whereText = substr($whereText, 0, -2) . ')';

			$stmt = $this->dbh->prepare("DELETE FROM `{$tableName}` {$whereText}");

			foreach ($where as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}


			if ($stmt->execute()) {
				return true;
			}
			else {
				throw new Exception("Error: Error On delete !");
			}
		}
	}

	public function update($tableName, $update = array(), $where = array(), $operations = '=', $andOr = '')
	{
		$whereArrayCount = count($where);

		if ($this->dbh && count($update) > 0 && $whereArrayCount > 0) {
			$whereText = 'WHERE (';

			$andOrArray = explode('-', $andOr);
			$andOrArrayCount = count($andOrArray);

			$operationsArray = explode('-', $operations);
			$operationsArrayCount = count($operationsArray);

			if ($andOr != '' && $andOrArrayCount == $whereArrayCount - 1 && $operationsArrayCount == $whereArrayCount) {

				$i = 0; // for operations
				$j = 0; // for AND OR



				foreach ($where as $key => $value) {
					$whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . ' ' . (isset($andOrArray[$j]) ? $andOrArray[$j] : '') . ' ';
					$i++;
					$j++;
				}

			}
			elseif ($andOr == '' && $andOrArrayCount == $whereArrayCount && $operationsArrayCount == $whereArrayCount) {

				$i = 0; // for operations

				foreach ($where as $key => $value) {
					$whereText .= '`' . $key . '` ' . ($operationsArray[$i] == 'IN' ? 'IN(' . $value . ')' : $operationsArray[$i] . ' :' . $key) . '  ';
					$i++;
				}


			}
			else {
				die();
			}

			$whereText = substr($whereText, 0, -2) . ')';

			$updateText = '';

			foreach ($update as $key => $value) {
				$updateText .= '`' . $key . '` = :' . $key . ' , ';
			}

			$updateText = substr($updateText, 0, -3);

			$stmt = $this->dbh->prepare("UPDATE `{$tableName}` SET {$updateText} {$whereText}");

			foreach ($update as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}

			foreach ($where as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}


			if ($stmt->execute()) {
				return true;
			}
			else {
				throw new Exception("Error: Error On Update !");
			}
		}
	}

	public function query($query) {
		return $this->dbh->prepare($query);
	}

	public function closeDataBase()
	{
		$this->dbh = NULL;
	}
}
