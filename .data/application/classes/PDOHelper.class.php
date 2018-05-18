<?php

/**
*   Back Up class
*   auther : jafar Rezaei
*   2016 / 04 / 22
*/

 Class PDOHelper {

	private $tables = array();
	private $handler;
	public $error = array();
	private $final;

	private static $keywords = array(
		'ALTER', 'CREATE', 'DELETE', 'DROP', 'INSERT',
		'REPLACE', 'SELECT', 'SET', 'TRUNCATE', 'UPDATE', 'USE',
		'DELIMITER', 'END'
	);


	/**
	 *
	 * The main function
	 * @method DBBackup
	 * @uses Constructor
	 * @param Array $args{host, driver, user, password, database}
	 * @example $db = new DBBackup(array('host'=>'my_host', 'driver'=>'bd_type(mysql)', 'user'=>'db_user', 'password'=>'db_password', 'database'=>'db_name'));
	 */
	public function __construct($conn){

		$this->handler = $conn;

	}


	/**
	 *
	 * Connect to a database
	 * @uses Private use
	 */
	private function connect(){
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
		);

		try {
			$this->handler = new PDO($this->dsn, $this->user, $this->password, $options);
			$this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->handler->exec("SET character_set_results=utf8;");
			$this->handler->exec("SET character_set_client=utf8;");
			$this->handler->exec("SET character_set_connection=utf8;");
			$this->handler->exec("SET character_set_database=utf8;");
			$this->handler->exec("SET character_set_server=utf8;");
		} catch(PDOException $e) {
			$this->handler = null;
			$this->error[] = $e->getMessage();
			return false;
		}
	}



	/**
	 * Loads an SQL stream into the database one command at a time.
	 *
	 * @params $sqlfile The file containing the mysql-dump data.
	 * @params $connection Instance of a PDO Connection Object.
	 * @return boolean Returns true, if SQL was imported successfully.
	 * @throws Exception
	 */
	public function importSQL($sqlfile , array $changes = array()) {  

		# read file into array
		$file = file_get_contents($sqlfile);
		
		$file = strtr(
			$file ,
			$changes
		);

		$file = explode("\n", $file);

		if(FALSE !== $file && null !== $this->handler){
			# import file line by line
			# and filter (remove) those lines, beginning with an sql comment token
			$file = array_filter(
				$file,
				create_function(
					'$line',
					'return strpos(ltrim($line), "--") !== 0;'
				)
			);

			# and filter (remove) those lines, beginning with an sql notes token
			$file = array_filter(
				$file,
				create_function(
					'$line',
					'return strpos(ltrim($line), "/*") !== 0;'
				)
			);


			$sql = "";
			$del_num = false;
			foreach($file as $line){
				$query = trim($line);
				try {
					$delimiter = is_int(strpos($query, "DELIMITER"));
					if($delimiter || $del_num){
						if($delimiter && !$del_num ){
							$sql = "";
							$sql = $query."; ";
							// echo "OK";
							// echo "<br/>";
							// echo "---";
							// echo "<br/>";
							$del_num = true;
						}else if($delimiter && $del_num){
							$sql .= $query." ";
							$del_num = false;
							// echo $sql;
							// echo "<br/>";
							// echo "do---do";
							// echo "<br/>";
							$this->handler->exec($sql);
							$sql = "";
						}else{                            
							$sql .= $query."; ";
						}
					}else{
						$delimiter = is_int(strpos($query, ";"));
						if($delimiter){
							$this->handler->exec("$sql $query");
							// echo "$sql $query";
							// echo "<br/>";
							// echo "---";
							// echo "<br/>";
							$sql = "";
						}else{
							$sql .= " $query";
						}
					}
				}
				catch (\Exception $e) {
					echo $e->getMessage() . "<br /> <p>The sql is: $query</p>";
				}
				
			}
		}else{
			echo "db or file error";
		}
	}


	/**
	 *
	 * Call this function to get the database backup
	 * @example DBBackup::backup();
	 */
	public function backup(){

		$this->final 	= 'CREATE DATABASE ' . $this->dbName.";\n\n";
		$this->getTables();
		$this->generate();

		if(count($this->error)>0){
			return array('error'=>true, 'msg'=>$this->error);
		}

		return array('error'=>false, 'msg'=>$this->final);
	}

	/**
	 *
	 * Generate backup string
	 * @uses Private use
	 */
	private function generate(){
		foreach ($this->tables as $tbl) {
			$this->final .= '--CREATING TABLE '.$tbl['name']."\n";
			$this->final .= $tbl['create'] . ";\n\n";
			$this->final .= '--INSERTING DATA INTO '.$tbl['name']."\n";
			$this->final .= $tbl['data']."\n\n\n";
		}
		$this->final .= '-- THE END'."\n\n";
	}


	/**
	 *
	 * Get the list of tables
	 * @uses Private use
	 */
	private function getTables(){
		try {
			$stmt = $this->handler->query('SHOW TABLES');
			$tbs = $stmt->fetchAll();
			$i=0;
			foreach($tbs as $table){
				$this->tables[$i]['name'] = $table[0];
				$this->tables[$i]['create'] = $this->getColumns($table[0]);
				$this->tables[$i]['data'] = $this->getData($table[0]);
				$i++;
			}
			unset($stmt);
			unset($tbs);
			unset($i);

			return true;
		} catch (PDOException $e) {
			$this->handler = null;
			$this->error[] = $e->getMessage();
			return false;
		}
	}

	/**
	 *
	 * Get the list of Columns
	 * @uses Private use
	 */
	private function getColumns($tableName){
		try {
			$stmt = $this->handler->query('SHOW CREATE TABLE '.$tableName);
			$q = $stmt->fetchAll();
			$q[0][1] = preg_replace("/AUTO_INCREMENT=[\w]*./", '', $q[0][1]);
			return $q[0][1];
		} catch (PDOException $e){
			$this->handler = null;
			$this->error[] = $e->getMessage();
			return false;
		}
	}

	/**
	 *
	 * Get the insert data of tables
	 * @uses Private use
	 */
	private function getData($tableName){
		try {
			$stmt = $this->handler->query('SELECT * FROM '.$tableName);
			$q = $stmt->fetchAll(PDO::FETCH_NUM);
			$data = '';
			foreach ($q as $pieces){
				foreach($pieces as &$value){
					$value = htmlentities(addslashes($value));
				}
				$data .= 'INSERT INTO '. $tableName .' VALUES (\'' . implode('\',\'', $pieces) . '\');'."\n";
			}
			return $data;
		} catch (PDOException $e){
			$this->handler = null;
			$this->error[] = $e->getMessage();
			return false;
		}
	}
}
?>