<?php

class MailHelper extends Model {

	public $mail = NULL;
	public $mailto = NULL;
	public $mailOptions = array() ; 
	
	private $config = array();
	protected static $dbh;
	
	
	public function __construct($to = 0 , array $options)
	{
		
		// LOAD SEND MAIL LIBRARY
		
		$this->mail = new PHPMailer;
		$this->mail->CharSet = "UTF-8"; 
		
		$this->config = jamework::getConfig();
		
		self::$dbh = parent::dataBase();
		
		//Create a new PHPMailer instance
		if ($this->config["EMAIL_USE_SMTP"]) {
	            // Set mailer to use SMTP
	            $this->mail->IsSMTP();
		            
		    $this->mail->SMTPDebug 	= 0;
		    $this->mail->Debugoutput 	= 'html';
	            $this->mail->SMTPAuth 	= $this->config["EMAIL_SMTP_AUTH"];
	            // Enable encryption, usually SSL/TLS
	            if (isset($this->config["EMAIL_SMTP_ENCRYPTION"])) {
	                $this->mail->SMTPSecure = $this->config["EMAIL_SMTP_ENCRYPTION"];
	            }
	            // Specify host server
	            $this->mail->Host 		= $this->config["EMAIL_SMTP_HOST"];
	            $this->mail->Username 	= $this->config["EMAIL_SMTP_USERNAME"];
	            $this->mail->Password 	= $this->config["EMAIL_SMTP_PASSWORD"];
	            $this->mail->Port 		= $this->config["EMAIL_SMTP_PORT"];
	        } else {
	            $this->mail->IsMail();
	        }
		
		if($to !== 0 ){
			$this->mailto	= $to ;

			if(count($options) > 0){
				$this->mailOptions 	= $options;
			}

		}
	}
	
	function addToDataBase( $status)
	{		
		self::$dbh->create(
			'jor_mails',
			array(
				"mailto"	=> $this->mailto,
				"date"		=> time(),
				"status"	=> $status,
				"options"	=> serialize($this->mailOptions)
			)
		);
	}
	
	function updateSendedMail ($mid)
	{
		self::$dbh->update(
			'jor_mails',
			array(
				"status"	=> 1,
				"date"		=> time()
			),
			array(
				"mid"		=> $mid
			),
			"=",
			""
		);
	}
	
	function checkMaxHourMails( )
	{
		$oneHourAgo = time() - (60 * 60) ;
		$readStatus = self::$dbh->read(
			'jor_mails',
			array(),
			array(
				"date"		=> $oneHourAgo ,
				"status"	=> 1
			),
			">-=",
			"AND",
			"",
			"m"
		);
		return count($readStatus)  < 79 ? TRUE : FALSE;
	}
	
	function sendMail()
	{
		$this->mail->setFrom('info@jurchin.com', 'جورچین');
		$this->mail->addReplyTo('no-reply@jurchin.com', 'جورچین');
		$this->mail->addAddress($this->mailto);
		$this->mail->Subject = $this->mailOptions['mailsubject'] ;
		

		//'سیستم سایت ساز جورچین میگه : طراحی سایت باید ساده ، زیبا و اصولی باشه'

		$mailTemplate = file_get_contents(VIEW_PATH.'templates/mail_contents.html');
		$this->mail->msgHTML(strtr(
			$mailTemplate ,
			array(
				'%HeadTop1%' 	=> $this->mailOptions['HeadTop1']  ,
				'%HeadBottom1%' => $this->mailOptions['HeadBottom1'],
				'%HeadBottom2%' => $this->mailOptions['HeadBottom2'],
				'%mainContent%' => $this->mailOptions['mainContent']  ,
				'%TopBtnText%'	=> $this->mailOptions['TopBtnText'] ,
				'%TopBtnLink%'	=> $this->mailOptions['TopBtnLink'] ,
			)
		));
		
		if($this->checkMaxHourMails() == true){
			if (!$this->mail->send()) {
				// Did Not sended 
				$this->addToDataBase( '0' );

			} else {
				$this->addToDataBase( '1' );
			}
		}else {
			$this->addToDataBase( '0' );
			echo '2';
		}
	}
	
	function resend()
	{
		if($this->checkMaxHourMails( ) == true){
			$mailTemplate = file_get_contents(VIEW_PATH.'templates/mail_contents.html');
			
			$didNotSended = self::$dbh->read(
				'jor_mails',
				array(),
				array(
					"status"	=> 0
				),
				"=",
				"",
				"",
				"m"
			);
			
			if(count($didNotSended) > 0){
				foreach($didNotSended  as $mts){
					$this->mail->setFrom('info@jurchin.com', 'جورچین');
					$this->mail->addReplyTo('no-reply@jurchin.com', 'جورچین');
					$this->mail->addAddress($mts['mailto']);
					$this->mail->Subject = $mts['mailsubject'];
					$mailOptions =  unserialize($mts['options']);

					$this->mail->msgHTML(
						strtr(
							$mailTemplate ,
							array(				
								'%HeadTop1%' 	=> $mailOptions['HeadTop1']  ,
								'%HeadBottom1%' => $mailOptions['HeadBottom1'],
								'%HeadBottom2%' => $mailOptions['HeadBottom2'],
								'%mainContent%' => $mailOptions['mainContent']  ,
								'%TopBtnText%'	=> $mailOptions['TopBtnText'] ,
								'%TopBtnLink%'	=> $mailOptions['TopBtnLink'] ,
							)
						)
					);
					
					if($this->checkMaxHourMails( ) == true){
						if (!$this->mail->send()) {
							// Did Not sended 
						} else {
							$this->updateSendedMail($mts['mid']);
							echo '1';
						}
					}else{
						echo 'Max mails Sended And Breaked ...';
						break;
					}
					// Clear all addresses and attachments for next loop
					$this->mail->ClearAllRecipients( );
				}
			}else {
				echo 'there is no email in queue';
			}
		}else{
			echo 'Max mails Sended';
		}
		
	}
	
}