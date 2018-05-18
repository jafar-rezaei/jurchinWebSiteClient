<?php

class MessageController extends Controller{

	public static function handle(array $args , $header = 200 ){

		http_response_code($header);

		$twig = parent::callTwig(array("callAllways" => 0,'doCache' => 0 , 'debug' => 0 ));

		echo $twig->render( 'messages/message.html', array(
				'pageTitle' => 'خطای '.$args['code'],
				'pageDes' 	=> 'خیلی متاسفیم , ولی یک خطا رخ داده است ،',
				'canonical'	=> '{{siteurl}}error' ,
				'message' 	=> array(
					'message'	=> $args['message'] ,
					'code'		=> $args['code'] ,
					'icon'		=> 'unhappy'
				)
			)
		);
		exit();

	}

}
