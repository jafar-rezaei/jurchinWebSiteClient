<?php

function getCSRFToken($count = 1 , $jamework = null) {

	if(null == $jamework){
		$session = jamework::$session;
	}else{
		$session = $jamework;
	}

	if (empty($session->get('csrf_UserDetection_tokens'))) {
		$session->put('csrf_UserDetection_tokens' , array() );
	}



	if ($count == 1) {
		$nonce = random_string(64);
		$session->put("csrf_UserDetection_tokens.".$nonce , true );

		if(isset($session->get('csrf_UserDetection_tokens')[$nonce])){
			return $nonce;
		}
	}else{
		$ret = array();
		for ($i=0; $i < $count; $i++) {

			$nonce = random_string(64);
			$session->put("csrf_UserDetection_tokens.".$nonce , true );

			if (isset($session->get('csrf_UserDetection_tokens')[$nonce])) {
				$ret[] = $nonce;
			}
		}

		return $ret;
	}

}

function input($kind , $name , $default = ""){
	if(strtolower($kind) == "post"){
		return (isset($_POST[$name]) ? safe($_POST[$name]) : $default);
	}else{
		return isset($_GET[$name]) ? safe($_GET[$name]) : $default;
	}
}

function validateCSRFToken($token , $jamework = null , $dontUse = true) {

	if(null == $jamework){
		$session = jamework::$session;
	}else{
		$session = $jamework;
	}


	if (isset($session->get('csrf_UserDetection_tokens')[$token])) {
		if(!$dontUse){
			unset($_SESSION['csrf_UserDetection_tokens'][$token]);
		}
		return true;
	}

	return false;
}


function postSend($url , $data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

function safe_redirect($url, $exit=true) {
	if (!headers_sent()){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $url);
		header("Connection: close");
	}
	print '<html>';
	print '<head><title>Redirecting you...</title>';
	print '<meta http-equiv="Refresh" content="0;url='.$url.'" />';
	print '</head>';
	print '<body onload="location.replace(\''.$url.'\')"><a href="'.$url.'">کلیک کنید</a>';
	print '<script>window.location.replace("'.$url.'");';
	print 'window.location.href = "'.$url.'";</script>';
	print '</body>';
	print '</html>';
	if ($exit) exit;
}

function ReplaceBadWords($comment){
	$badword = array();
	$replacementword = array();
	$wordlist = array(
		'shit',
		'fuck',
		'fucker',
		'fucking',
		'fucks',
		'fuckers',
		'nigger',
		'niggers',
		'motherfucker',
		'asshole',
		'Assface',
		'asswipe',
		'assholes',
		'pussy',
		'faggot',
		'faggots',
		'fags',
		'fag',
		'fuckin',
		'nigga',
		'cockhead',
		'cock-head',
		'CockSucker',
		'cock-sucker',
		'cunt',
		'cunts',
		'cock',
		'cocks',
		'shitty',
		'shittiest',
		'shits',

		'ass',
		'bitch',
		"bitch's",
		'bitches',
		'bitchs',
		'cock gobbler',
		'lesbionic',
		'dickhead',
		'dick head',
		'dickheads',
		'dick heads',
		'dickhole',
		'dick hole',

		'gurgle monster',
		'cum dumpster',
		'Carpet Muncher',
		'fatass',
		'fat-ass',

		'slut',
		'Blow Job',
		'Clit',
		'dildo',
		'jackoff',
		'jerk-off',
		'blow jobs',

		'anus',
		'bastard',
		'bastards',
		'butthole',
		'buttwipe',
		'crap',
		'God Damn',
		'God Damned',
		'God Damnit',
		'slut',
		'sluts',
		'Slutty',
		'jizz',
		'testicle',
		'butt-pirate',
		'nutsack',
		'nuttsack',

		'ahole',
		'ash0le',
		'ash0les',
		'asholes',
		'assh0le',
		'assh0lez',
		'assholz',
		'azzhole',
		'bassterds',
		'bastardz',
		'basterds',
		'basterdz',
		'Biatch',
		'c0ck',
		'c0cks',
		'c0k',
		'cawk',
		'cawks',
		'cuntz',
		'dild0',
		'dild0s',
		'dildos',
		'dilld0',
		'dilld0s',
		'f u c k',
		'f u c k e r',
		'f u c k i n g',
		'fag1t',
		'faget',
		'fagg1t',
		'faggit',
		'fagit',
		'fagz',
		'faig',
		'faigs',
		'Fudge Packer',
		'fuk',
		'Fukah',
		'Fuken',
		'fuker',
		'Fukin',
		'Fukk',
		'Fukker',
		'Fukkin',
		'jizm',
		'slutz',
		'assopedia',
		// presian
		' کس ',
		'کون',
		'جنده',
		'لاشی',
		'سکس',
		'پارتی',
		'ممه',
		'چوچول',
		'کیر',
		'حشر',
		'اسکل',
		'کص مغز',
		'شیطان پرست',
		'احمق',
	);
	foreach ($words as $key => $word) {
		$badword[$key] = $word;
		$replacementword[$key] = addStars($word);
		$badword[$key] = "/\b{$badword[$key]}\b/i";
	}
	$comment = preg_replace($badword, $replacementword, $comment);
	return $comment;
}

function addStars($word) {
	$length = strlen($word);
	return substr($word, 0, 1) . str_repeat("*", $length - 2) . substr($word, $length - 1, 1);
}

function userdata($uid , $select = NULL) {
	if($select !== NULL){
		$selected = $select ;
	}else{
		$selected = "*";
	}
	$query_user = $GLOBALS['conn']->prepare("SELECT $selected FROM table WHERE user_id = :uid ");
	$query_user->bindValue(':uid',$uid);
	$query_user->execute();
	if($query_user->rowCount() == 1){
		return $query_user->fetchObject();
	}else {
		return false;
	}
}

 function fa2enString($srting)
{
	$search = array("۰","۱","۲","۳","۴","۵","۶","۷","۸","۹","ض","ص","ث","ق","ف","غ","ع","ه","خ","ح","ج","چ","ش","س","ی","ب","ل","ا","ت","ن","م","ک","گ","پ","ظ","ط","ز","ر","ذ","د","ئ","و");
	$replace = array("0","1","2","3","4","5","6","7","8","9","q","w","e","r","t","y","u","i","o","p","[","]","a","s","d","f","g","h","j","k","l",";","'","\/","x","c","v","b","n","m",",");
	$int = str_replace($search, $replace, $srting);
	return $int;
}


function sec_session_start() {
	// This stops JavaScript being able to access the session id.
	$httponly = true;
	session_start();            // Start the PHP session
	session_regenerate_id(true);    // regenerated the session, delete the old one.
}

function mycutter ($maintext , $numberofch){
	$minified = substr($maintext, 0, $numberofch);
	$cutted = substr($minified , strripos($minified," "));
	return $cutted;
}
function random_string($length) {
	$key = '';
	$keys = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
	for ($i = 0; $i < $length; $i++) {
		$key .= $keys[array_rand($keys)];
	}
	return $key;
}


function getTimeZoneFromIpAddress(){
    $clientsIpAddress = getRealIp();

    $clientInformation = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$clientsIpAddress));

    $clientsLatitude = $clientInformation['geoplugin_latitude'];
    $clientsLongitude = $clientInformation['geoplugin_longitude'];
    $clientsCountryCode = $clientInformation['geoplugin_countryCode'];

    $timeZone = get_nearest_timezone($clientsLatitude, $clientsLongitude, $clientsCountryCode) ;

    return $timeZone;

}


function get_nearest_timezone($cur_lat, $cur_long, $country_code = '') {
    $timezone_ids = ($country_code) ? DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code)
        : DateTimeZone::listIdentifiers();

    if($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {

        $time_zone = '';
        $tz_distance = 0;

        //only one identifier?
        if (count($timezone_ids) == 1) {
            $time_zone = $timezone_ids[0];
        } else {

            foreach($timezone_ids as $timezone_id) {
                $timezone = new DateTimeZone($timezone_id);
                $location = $timezone->getLocation();
                $tz_lat   = $location['latitude'];
                $tz_long  = $location['longitude'];

                $theta    = $cur_long - $tz_long;
                $distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat)))
                    + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
                $distance = acos($distance);
                $distance = abs(rad2deg($distance));
                // echo '<br />'.$timezone_id.' '.$distance;

                if (!$time_zone || $tz_distance > $distance) {
                    $time_zone   = $timezone_id;
                    $tz_distance = $distance;
                }

            }
        }
        return  $time_zone;
    }
    return 'Asia/Tehran';
}


function getRealIp() {
	$ipaddress = '';
		if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
			$ipaddress =  $_SERVER['HTTP_CF_CONNECTING_IP'];
		} else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
			$ipaddress = $_SERVER['HTTP_X_REAL_IP'];
		}
		else if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR'])){
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}
		else
			$ipaddress = 'UNKNOWN';

		return $ipaddress;
}
function getBrowser() {
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}
	elseif(preg_match('/Firefox/i',$u_agent))
	{
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Chrome/i',$u_agent))
	{
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}
	elseif(preg_match('/Safari/i',$u_agent))
	{
		$bname = 'Apple Safari';
		$ub = "Safari";
	}
	elseif(preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Opera';
		$ub = "Opera";
	}
	elseif(preg_match('/Netscape/i',$u_agent))
	{
		$bname = 'Netscape';
		$ub = "Netscape";
	}

	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .
	')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version= $matches['version'][1];
		}
	}
	else {
		$version= $matches['version'][0];
	}

	// check if we have a number
	if ($version==null || $version=="") {$version="?";}

	return array(
		'userAgent' => $u_agent,
		'name'	  => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'	=> $pattern
	);
}

/**
 * Check if a string is serialized
 * @param string $string
 */
function is_serial($string) {
	return (@unserialize($string) !== false || $string == 'b:0;');
}


function validate_email($e){
	if(empty($e))return false;
	return (bool)preg_match("`^[a-z0-9!#$%&'*+\/=?^_\`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_\`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$`i", trim($e));
}
function check_email_address($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
				return false;
			}
		}
		if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
					return false;
				}
			}
		}

		return true;
	}
function tz_list() {
	$zones_array = array();
	$timestamp = time();
	foreach(timezone_identifiers_list() as $key => $zone) {
		date_default_timezone_set($zone);
		$zones_array[$key]['zone'] = $zone;
		$zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
	}
	return $zones_array;
}

function addnav($flink,$ftitle , $slink = 0 ,$stitle = 0 , $tlink = 0 ,$ttitle = 0 , $zlink = 0 ,$ztitle = 0  ) {
	$seprator = ' <i class="icon-angle-left mr5 ml5"></i> ';
	$home = '<a href="'.$GLOBALS['adminurl'].'" title="خانه" class="byekan"><i class="icon-home"></i></a> ';
	$thisnavigation = $home . $seprator . '<a href="'.$GLOBALS['adminurl'].'/'.$flink.'" title="'.$ftitle.'" class="byekan">'.$ftitle.'</a>';
	if (!empty ($stitle) ){
		$thisnavigation .= $seprator.'<a href="'.$GLOBALS['adminurl'].'/'.$flink.'/'.$slink.'" title="'.$stitle.'" class="byekan">'.$stitle.'</a>' ;
		if (!empty ($ttitle) ){
			$thisnavigation .= $seprator.'<a href="'.$GLOBALS['adminurl'].'/'.$flink.'/'.$slink.'/'.$tlink.'" title="'.$ttitle.'" class="byekan">'.$ttitle.'</a> ';
			if (!empty ($ztitle) ){
				$thisnavigation .= $seprator.'<a href="'.$GLOBALS['adminurl'].'/'.$flink.'/'.$slink.'/'.$tlink.'/'.$zlink.'" title="'.$ttitle.'" class="byekan">'.$ttitle.'</a> ';
			}
		}
	}
	return '<div class="navlinks2 byekan align-right">'.$thisnavigation.'</div>';
}


function how_datas_dif( $data , $type=0 ) {
	$nowunix = time();
	$ch = date('Y-m-d h:i:s',$data);
	$dh = date('Y-m-d h:i:s',$nowunix);
	$start_date2 = new DateTime($ch);
	$since_start = $start_date2->diff(new DateTime($dh));

	$ljustnow = "لحظاتی ";
	$lyear = "سال";
	$lmonth = "ماه";
	$lday = "روز";
	$lhour = "ساعت";
	$lminute = "دقیقه";
	$lsecound = "ثانیه";
	$lago = "پیش";
	$lafter = "بعد";
	$land = "و";

	if($since_start->y == 0){
		if($since_start->m == 0){
			if($since_start->d == 0){
				if($since_start->h == 0){
					if($since_start->i == 0){
						if($since_start->s < 15){
							$adddata = "$ljustnow";
						}else {
							$adddata = "$since_start->s $lsecound ";
						}
					} else {
						$adddata = "$since_start->i $lminute ";
						if($since_start->s !== 0 && $type !== 1){
							$adddata .= "$land $since_start->s $lsecound ";
						}
					}
				} else {
					$adddata = "$since_start->h $lhour ";
					if($since_start->i !== 0 && $type !== 1){
						$adddata .= "$land $since_start->i $lminute ";
					}
				}
			} else {
				$adddata = "$since_start->d $lday ";
				if($since_start->h !== 0 && $type !== 1){
					$adddata .= "$land $since_start->h $lhour ";
				}
			}
		} else {
			$adddata = "$since_start->m $lmonth ";
			if($since_start->d !== 0 && $type !== 1){
				$adddata .= "$land $since_start->d $lday ";
			}
		}
	} else {
		$adddata = "$since_start->y $lyear ";
		if($since_start->m !== 0 && $type !== 1){
			$adddata .= "$land $since_start->m $lmonth ";
		}
	}
	$adddata .= ($data < $nowunix ) ? $lago : $lafter ;
	return $adddata;
}


function fa2en ($srting){
	$search= array("۰","۱","۲","۳","۴","۵","۶","۷","۸","۹","ي");
	$replace= array("0","1","2","3","4","5","6","7","8","9","ی");
	$int = str_replace($search,$replace,$srting);
	return $int;
}

function safe( $value ){
	$value 	= fa2en(trim($value));
	$value	= htmlspecialchars($value);
	$value  = stripslashes($value);
	$value	= strip_tags($value);
	$value 	= str_replace(array("<",">","'","&#1740;","&amp;","&#1756;"),array("&lt;","&gt;","&#39;","&#1610;","&","&#1610;"),$value);
	return $value;
}

function _minify_html($input) {
	return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
		if(isset($m[2])) {

			return '<' . $m[1] . preg_replace(
				array(
					// From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
					'#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
					// Remove extra white-space(s) between HTML attribute(s) [^2]
					'#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
					// From `<img />` to `<img/>` [^3]
					'#\s+\/$#'
				),
				array(
					// [^1]
					' $1',
					// [^2]
					' $1$2',
					// [^3]
					'/'
				),
			str_replace("\n", ' ', $m[2])) . '>';
		}
		return '<' . $m[1] . '>';
	}, $input);
}


function minifyHtml($buffer) {
	$buffer = _minify_html($buffer);
	return
		// remove ws outside of all elements
		preg_replace( '/>(?:\s\s*)?([^<]+)(?:\s\s*)?</s', '>$1<',
			// remove ws around all elems excepting script|style|pre|textarea elems
			preg_replace(
			'/\s+(<\\/?(?!script|style|pre|textarea)\b[^>]*>)/i', '$1',
				// trim line start
				preg_replace( '/^\s\s*/m', '',
					// trim line end
					preg_replace( '/\s\s*$/m', '',
						// remove HTML comments (not containing IE conditional comments)
						preg_replace_callback(
							'/<!--[^ShowThitComment]([\s\S]*?)-->/',
							function( $m ) {
								return ( 0 === strpos($m[1], '[' ) || false !== strpos( $m[1], '<![' ) ) ? $m[0] : '';
							},
							// start point
							$buffer
						)
					)
				)
			)
		)
	;
}

/* List of File Types */
function fileTypes($extension)
{
	$fileTypes['swf']  = 'application/x-shockwave-flash';
	$fileTypes['pdf']  = 'application/pdf';
	$fileTypes['exe']  = 'application/octet-stream';
	$fileTypes['zip']  = 'application/zip';
	$fileTypes['doc']  = 'application/msword';
	$fileTypes['xls']  = 'application/vnd.ms-excel';
	$fileTypes['ppt']  = 'application/vnd.ms-powerpoint';
	$fileTypes['gif']  = 'image/gif';
	$fileTypes['png']  = 'image/png';
	$fileTypes['jpeg'] = 'image/jpg';
	$fileTypes['jpg']  = 'image/jpg';
	$fileTypes['rar']  = 'application/rar';

	$fileTypes['ra']  = 'audio/x-pn-realaudio';
	$fileTypes['ram'] = 'audio/x-pn-realaudio';
	$fileTypes['ogg'] = 'audio/x-pn-realaudio';

	$fileTypes['wav']  = 'video/x-msvideo';
	$fileTypes['wmv']  = 'video/x-msvideo';
	$fileTypes['avi']  = 'video/x-msvideo';
	$fileTypes['asf']  = 'video/x-msvideo';
	$fileTypes['divx'] = 'video/x-msvideo';

	$fileTypes['mp3']  = 'audio/mpeg';
	$fileTypes['mp4']  = 'audio/mpeg';
	$fileTypes['mpeg'] = 'video/mpeg';
	$fileTypes['mpg']  = 'video/mpeg';
	$fileTypes['mpe']  = 'video/mpeg';
	$fileTypes['mov']  = 'video/quicktime';
	$fileTypes['swf']  = 'video/quicktime';
	$fileTypes['3gp']  = 'video/quicktime';
	$fileTypes['m4a']  = 'video/quicktime';
	$fileTypes['aac']  = 'video/quicktime';
	$fileTypes['m3u']  = 'video/quicktime';
	return $fileTypes[$extension];
}
;

/*
Parameters: downloadFile(File Location, File Name,
max speed, is streaming
If streaming - videos will show as videos, images as images
instead of download prompt
*/

//downloadFile('as.zip', 'as.zip', 100, false);

function downloadFile($fileLocation, $fileName, $maxSpeed = 100, $doStream = false)
{
	if (connection_status() != 0)
		return (false);
	//    in some old versions this can be pereferable to get extention
	//    $extension = strtolower(end(explode('.', $fileName)));
	$extension = pathinfo($fileName, PATHINFO_EXTENSION);

	$contentType = fileTypes($extension);
	header("Cache-Control: public");
	header("Content-Transfer-Encoding: binary\n");
	header('Content-Type: $contentType');

	$contentDisposition = 'attachment';

	if ($doStream == true) {
		/* extensions to stream */
		$array_listen = array(
			'mp3',
			'm3u',
			'm4a',
			'mid',
			'ogg',
			'ra',
			'ram',
			'wm',
			'wav',
			'wma',
			'aac',
			'3gp',
			'avi',
			'mov',
			'mp4',
			'mpeg',
			'mpg',
			'swf',
			'wmv',
			'divx',
			'asf'
		);
		if (in_array($extension, $array_listen)) {
			$contentDisposition = 'inline';
		}
	}

	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		$fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
		header("Content-Disposition: $contentDisposition;
					filename=\"$fileName\"");
	} else {
		header("Content-Disposition: $contentDisposition; filename=\"$fileName\"");
	}

	header("Accept-Ranges: bytes");
	$range = 0;
	$size  = filesize($fileLocation);

	if (isset($_SERVER['HTTP_RANGE'])) {
		list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
		str_replace($range, "-", $range);
		$size2      = $size - 1;
		$new_length = $size - $range;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range$size2/$size");
	} else {
		$size2 = $size - 1;
		header("Content-Range: bytes 0-$size2/$size");
		header("Content-Length: " . $size);
	}

	if ($size == 0) {
		die('Zero byte file! Aborting download');
	}

	$fp = fopen("$fileLocation", "rb");

	fseek($fp, $range);

	while (!feof($fp) and (connection_status() == 0)) {
		set_time_limit(0);
		print(fread($fp, 1024 * $maxSpeed));
		flush();
		ob_flush();
		sleep(1);
	}
	fclose($fp);

	return ((connection_status() == 0) and !connection_aborted());
}


function foldersize($path) {
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/'). '/';

	foreach($files as $t) {
		if ($t<>"." && $t<>"..") {
			$currentFile = $cleanPath . $t;
			if (is_dir($currentFile)) {
				$size = foldersize($currentFile);
				$total_size += $size;
			}
			else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}

	return $total_size;
}

function isInteger($input){
    return(ctype_digit(strval($input)));
}

function format_size($size) {
	$units = explode(' ', 'B KB MB GB TB PB');

	$mod = 1024;

	for ($i = 0; $size > $mod; $i++) {
		$size /= $mod;
	}

	$endIndex = strpos($size, ".")+3;

	return substr( $size, 0, $endIndex).' '.$units[$i];
}






// Image cropping functions
function imagecreatefromfile( $filename , $ext) {
    if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ( $ext ) {
        case 'jpeg':
        case 'jpg':
            return imagecreatefromjpeg($filename);
        break;

        case 'png':
            return imagecreatefrompng($filename);
        break;

        case 'gif':
            return imagecreatefromgif($filename);
        break;

        default:
            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        break;
    }
}

function convertImage($originalImage, $outputImage, $quality){
    // jpg, png, gif or bmp?
    $exploded = explode('.',$originalImage);
    $ext = $exploded[count($exploded) - 1];

    if (preg_match('/jpg|jpeg/i',$ext))
        $imageTmp=imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i',$ext))
        $imageTmp=imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i',$ext))
        $imageTmp=imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i',$ext))
        $imageTmp=imagecreatefrombmp($originalImage);
    else
        return 0;

    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);

    return 1;
}


function cropmypic($userimage , $thumb_width , $thumb_height , $ext = "jpg" , $croped = "") {

	$image = imagecreatefromfile($userimage , $ext);
	$croped = $croped == "" ? $userimage : $croped;

	$width = imagesx($image);
	$height = imagesy($image);
	$original_aspect = $width / $height;
	$thumb_aspect = $thumb_width / $thumb_height;

	if ( $original_aspect >= $thumb_aspect ){
		// If image is wider than thumbnail (in aspect ratio sense)
		$new_height = $thumb_height;
		$new_width = $width / ($height / $thumb_height);
	}else{
		// If the thumbnail is wider than the image
		$new_width = $thumb_width;
		$new_height = $height / ($width / $thumb_width);
	}
	$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

	imagealphablending( $thumb, false );
	imagesavealpha( $thumb, true );

	// Resize and crop
	imagecopyresampled($thumb,
		$image,
		0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
		0 - ($new_height - $thumb_height) / 2, // Center the image vertically
		0, 0,
		$new_width, $new_height,
		$width, $height);

	switch ( $ext ) {
		case 'png':
			imagepng($thumb, $croped, 2);
		break;

		default:
			imagejpeg($thumb, $croped, 80);
		break;
	}
	return $croped;
}
