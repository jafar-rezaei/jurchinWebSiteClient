<?php

require(LIB_PATH."HostVendor/autoload.php");

class HostHelper {

	protected static $manager ;
	protected static $isCpanel = false;
	
	private $account = null;
	private $password = null;
	
	
	public function __construct($account , $password , $cpanel = false){
		
		$this->account = $account;
		$this->password = $password;
		

		if($cpanel){
			// is Cpanel
			self::$isCpanel = true;
			self::$manager = new \Gufy\CpanelPhp\Cpanel([
				'host'        =>  'http://jurchin.com:2082', // ip or domain complete with its protocol and port {2087 :whm - 2082 : cpanel}
				'username'    =>  $this->account, // username of your server, it usually root.
				'auth_type'   =>  'password', // set 'hash' or 'password'
				'password'    =>  $this->password, // long hash or your user's password
			]);
		}else{
			// is WHM
			self::$manager = new \Gufy\CpanelPhp\Cpanel([
				'host'        =>  '136.243.62.81:2087', // ip or domain complete with its protocol and port 
				'username'    =>  'ioir', // username of your server, it usually root.
				'auth_type'   =>  'password', // set 'hash' or 'password'
				'password'    =>  'Jm83Sg~%Ba^u=>HM', // long hash or your user's password
			]);
		
		}
	}
	




	public function listBackUps(){
		
		/**
		*	@param  no parameter availabe
		*
		*	@return data 	:	An array of the account's backup files.
		*/
		return self::$manager->execute_action(
			'3',
			'Backups',
			'list_backups',
			$this->account
		);
	
	}
	


	public function bandWidth(){
		
		/**
		*	@source 	https://documentation.cpanel.net/display/SDK/UAPI+Functions+-+Bandwidth%3A%3Aquery
		*	@param grouping 	:	    year ,year_month , year_month_day , year_month_day_hour , year_month_day_mour_minute			    
		*	@param timezone 	:     Asia/tehran
		*	@param interval 	:     daily , hourly ,5min
		*	@param protocols 	:     http , imap ,smtp ,pop3 , ftp
		*
		*	@return  data 		:	domains and used data A positive number in bytes.
		*/

		return self::$manager->execute_action(
			'3',
			'Bandwidth',
			'query',
			$this->account,
			array(
		        'grouping'        => 'domain|year',
		        'interval'        => 'daily',
		        'protocols'       => 'http|imap|smtp',
		        'timezone'        => 'America%2FChicago',
		    )
		);
		
	}
	




	public function addMail($user , $password , $quota = 200 , $domain = "jurchin.com" ){

		/**
		*	@param  *email 		:	The email account username or address.
		*	@param  *password 	:	The email account password.
		*	@param  quota 		:	The maximum amount of disk space that the new email account may use. 250 default
		*	@param  domain 	:	This parameter defaults to the cPanel account's main domain.
		*	@param  skip_update_db	: Whether to skip the update of the email accounts database's cache.
		*
		*	@return data 	:	The email account username, a plus character (+), and the email account domain.
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'add_pop',
			$this->account ,
   			array(
		        'email'           => $user ,
		        'password'        => $password ,
		        'quota'           => $quota ,
		        'domain'          => $domain ,
		        'skip_update_db'  => '0',
		    )
		);
	
	}

	public function deleteMailAccount($user , $domain = "jurchin.com"){
		
		/**
		*	@param *email 	:	A valid email account username. @example, user if the email address is user@site.com.
		*	@param domain 	:	The email account domain , This parameter defaults to the cPanel account's main domain.
		*
		*	@return data 	:	This function only returns metadata.
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'delete_pop',
			$this->account ,
			array(
				'email'           => $user,
	        	'domain'          => $domain,
	        )
		);
	
	}

	public function getMailList(){
	
		/**
		*	@param regex 	:	A valid PCRE. @example : user
		*
		*	@return data 	:	A hash of data for an email address on the cPanel account.
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'list_pops',
			$this->account , 
			array(
		        'regex'      => 'user',
		    )
		);
	
	}



	public function getAccountMailList(){
	
		/**
		*	@param  no parameter availabe
		*
		*	@return data 	:	An array of the account's backup files.
		*/
		return self::$manager->execute_action(
			'3',
			'Backups',
			'list_backups',
			$this->account
		);

	}
	
	public function deleteMail(){
		
		/**
		*	@param  no parameter availabe
		*
		*	@return data 	:	An array of the account's backup files.
		*/
		return self::$manager->execute_action(
			'3',
			'Backups',
			'list_backups',
			$this->account
		);
	
	}



	public function changeMailAccountPassword( $email , $password , $domain = "jurchin.com"){
		
		/**
		*	@param  *email 	 	 :	The email address user. @example : user
		*	@param 	*password	 :	The mail address password .
		*	@param  domain 		 :	The email address domain . @example : jurchin.com
		*
		*	@return data 	:	domain - email - forward 
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'passwd_pop',
			$this->account , 
			array(
		        'email'      => $email ,
        		'password'   => $password ,
		        'domain'     => $domain
        	)
		);
	
	}


	public function getEmailForwarders($domain){
		
		/**
		*	@param  *domain  :	The domain. @example : site.com
		*
		*	@return data 	:	"html_dest": "forwarded@example.com",
		*						"dest": "forwarded@example.com",
		*						"html_forward": "user@example.com",
		*						"forward": "user@example.com",
		*						"uri_forward": "user%40example.com",
		*						"uri_dest": "forwarded%40example.com"
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'list_forwarders',
			$this->account , 
			array(
		        'domain'     => $domain ,
        	)
		);
	
	}

	public function addEmailForwarder($domain , $email , $to){
		
		/**
		*	@param  *domain  :	The domain. @example : site.com
		*	@param  *email 	 :	The email address to forward. @example : user@site.com
		*	@param 	*fwdopt	 :	The method to use to handle the email address's mail.
		*	@param  fwdemail :	Destination email address . @example : dest@site.com
		*
		*	@return data 	:	domain - email - forward 
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'add_forwarder',
			$this->account , 
			array(
		        'domain'     => $domain,
		        'email'      => $email ,
		        'fwdopt'     => 'fwd',
		        'fwdemail'   => $to
        	)
		);
	
	}

	public function deleteEmailForwarder($address , $forwarder){
		
		/**
		*	@param  *address 	: 	The forwarder's email address.
		*	@param 	*forwarder 	: 	The forwarder's destination.
		*
		*	@return This function only returns metadata.
		*/
		$listBackUps = self::$manager->execute_action(
			'3',
			'Email',
			'delete_forwarder',
			$this->account ,
			array(
		        'address'        => 'user@example.com',
		        'forwarder'      => 'fwdtome@example.com',
        	)
		);
	
	}
	

	public function addAutoResponder($email , $from , $subject , $body , $domain , $is_html , $interval , $start , $end ){
		
		/**
		*	@param  *email 		:	@example : user
		*	@param  *from 	 	:	@example : User Name
		*	@param 	*subject	:	subject
		*	@param 	*body		:	body
		*	@param 	*domain		:	@example : site.com
		*	@param 	*subject	:	subject
		*	@param  *is_html	:	1 or 0
		*	@param  *interval	:	The amount of time, in hours, that the server waits between autoresponder messages to the same address.
		*	@param  *start 		:	in unixtime
		*	@param  *end		:	in unixtime
		*
		*	@return This function only returns metadata.
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'add_forwarder',
			$this->account , 
			array(
		        'email'         => $email ,
		        'from'          => $from ,
		        'subject'       => $subject ,
		        'body'          => $body ,
		        'domain'        => $domain ,
		        'is_html'       => $is_html ,
		        'interval'      => $interval ,
		        'start'         => $start ,
		        'stop'          => $end
		    )
		);
	
	}

	public function getAutoResponder($email){
		
		/**
		*	@param  *email 	 :	The email address to forward. @example : user@site.com
		*
		*	@return data 	 :	from : User Name
		*						subject	: Autoresponder Subject
		*						body : This is an autoresponder message.
		*						interval : 24
		*/
		return self::$manager->execute_action(
			'3',
			'Email',
			'get_auto_responder',
			$this->account , 
			array(
		        'email'         => $email,
        	)
		);
	
	}
	
	
	
	#	Mysql

	public function addDB($dbName , $user , $pass ){
	
		// add database
		$addDB = self::$manager->execute_action(
			'3',
			'Mysql',
			'create_database',
			$this->account ,
			array(
				'name' => $this->account.'_'.$dbName
			)
		);
		

		// add User and Privildage
		$this->addUser($dbName , $user , $pass );
	}

	
	
	public function addUser($db , $user , $pass ){
		
		$db 	= $this->account.'_'.$db;
		$user 	= substr( $this->account.'_'.$user , 0 , 16);


		// This function only returns metadata.
		$addDBUser = self::$manager->execute_action(
			'3',
			'Mysql',
			'create_user' , 
			$this->account ,
			array(
				'name' 		=> $user ,
				'password'	=> $pass
			)
		);

				
		// This function only returns metadata.
		/*
		SELECT CREATE GRANT
		INSERT ALTER LOCK TABLES
		UPDATE INDEX REFERENCES
		DELETE DROP
		CREATE TEMPORARY TABLES
		*/
		$addDBUserPrivilage = self::$manager->execute_action(
			'3',
			'Mysql',
			'set_privileges_on_database',
			$this->account ,
			array(
				'user'			=> $user,
				'database'		=> $db,
				'privileges' 	=> 'SELECT,DELETE,INSERT,UPDATE,ALTER,DROP,CREATE,CREATE TEMPORARY TABLES,INDEX',
			)
		);
			
		// json_decode($addDBUserPrivilage)['result']

	}
	
	
	public function deleteDB($dbName , $userName){
	
		// delete database
		$deleteDatabase = self::$manager->execute_action(
			'3',
			'Mysql',
			'delete_database',
			$this->account,
			array(
				'name' => $this->account.'_'.$dbName
			)
		);
		
		$this->deleteDBUser($userName);
	
	}
	
	
	public function deleteDBUser($user){
		
		$user 	= substr( $this->account.'_'.$user , 0 , 16);

		// delete database user
		$deleteDBUser = self::$manager->execute_action(
			'3',
			'Mysql',
			'delete_user',
			$this->account,
			array(
				'name' => $user
			)
		);
	
	}


	
	public function repairDB($db){
		
		return self::$manager->execute_action(
			'3',
			'Mysql',
			'repair_database',
			$this->account,
			array(
				'name' => $user
			)
		);
		//[% execute('Mysql', 'repair_database', { name => 'example_test' } ) %]
		
	}
	

	public function checkDB($db){

		return self::$manager->execute_action(
			'3',
			'Mysql',
			'check_database',
			$this->account,
			array(
				'name' => $user
			)
		);
		//[% execute('Mysql', 'check_database', { name => 'example_test' } ) %]

	}



	
	// domain ex: example.com
	public function addSubDomain($sub , $domain){
		
		// add subdomain
		$addSubDomain = self::$manager->cpanel(
			'SubDomain',
			'addsubdomain',
			$this->account,
			array(
				'domain'                => $sub,
				'rootdomain'            => $domain,
				'dir'                   => '/public_html/'.$sub,
				'disallowdot'           => '1',
			)
		);
	
	}
	

	
	
	public function listIpDeny(){
	
		// ip deny list
		$listIpDenys = self::$manager->cpanel(
			'DenyIp',
			'listdenyips',
			$this->account
		);
		
	}
	
	
	

	
	
	public function diskSpace(){
	
		// get disk size
		$diskUsage = self::$manager->cpanel(
			'DiskUsage',
			'fetchdiskusage',
			$this->account
		);
	
	}
	


	###################
	#### FTP MANAGMENT
	###################

	public function addFtp($user , $password , $quota , $dir){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'add_ftp',
			$this->account,
			array(
				'user'		=> $user,
				'pass'		=> $password,
				'quota'		=> $quota,
				'homedir'	=> $dir
			)
		);
		// This function creates an FTP account.
		// <!-- Create a new FTP account. -->
		// [% execute( 'Ftp', 'add_ftp', { 'user' => 'weeones', 'pass' => '12345luggage', 'quota' => '42' } ); %]
	}

	public function deleteFtp($user , $deleteDir = 0){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'delete_ftp',
			$this->account,
			array(
				'user' 		=> $user ,
				'destroy'	=> $deleteDir
			)
		);
		// This function deletes an FTP account.
		// <!-- Delete an FTP account. -->
		// [% execute( 'Ftp', 'delete_ftp', { 'user' => 'weeones', 'destroy' => '1' } ); %]
	}


	public function ftpList(){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'list_ftp',
			$this->account
		);
		// <!-- List FTP account information. -->
		// [% execute( 'Ftp', 'list_ftp' ); %]
	}

	public function ftpSessionList(){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'list_sessions',
			$this->account
		);
		//This function lists the FTP server's active sessions.
		// <!-- List current active FTP sessions. -->
		// [% execute( 'Ftp', 'list_sessions' ); %]

	}


	public function ftpPwdChange($ftpaccount , $pass){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'passwd',
			$this->account,
			array(
				'user' 	=> $ftpaccount,
				'pass'	=> $pass
			)
		);
		// <!-- Change the FTP account password. -->
		// [% execute( 'Ftp', 'passwd', { user => 'ftpaccount', pass => '12345luggage' } ); %]
	}

	public function ftpQuotaChange($user , $quota){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'set_quota',
			$this->account,
			array(
				'name' 	=> $user,
				'quota'	=> $quota
			)
		);
		// This function changes an FTP account's quota.
		// <!-- Set the new quota for the ftpaccount FTP user. -->
		// [% execute( 'Ftp', 'set_quota', { user => 'ftpaccount', quota => '500' } ); %]
	}

	public function ftpSetHomeDir($user , $homedir){

		return self::$manager->execute_action(
			'3',
			'Ftp',
			'set_homedir',
			$this->account,
			array(
				'user' 		=> $user,
				'homedir'	=> $homedir
			)
		);
		// This function changes the home directory for FTP accounts.
		// <!-- Set the home directory for the ftpaccount FTP user. -->
		// [% execute( 'Ftp', 'set_homedir', { user => 'example1', domain => 'example.com', homedir => 'example1/' } ); %]
	}
	
	
	
	
	############################
	#####	WHM Methods	####
	
	
	/*
	// Set these values using your reseller account credentials
	$user = 'ioir';
	$pass  = 'Jm83Sg~%Ba^u=>HM';
	
	// Set this to the domain or IP of the server you're accessing.
	$host = '136.243.62.81';
	
	// Set this to the information you want to use for the newly created account
	$user_account = 'mellatshop';
	$user_domain = 'mellatshop.com';
	$user_pass = 'Jm83Sg~%Bsd';
	$plan = "ioir_mellat-10meg";
	*/

	public function addAccount(){

	}

}