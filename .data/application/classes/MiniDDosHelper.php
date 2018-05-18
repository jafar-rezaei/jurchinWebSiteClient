<?php

########################
### PROTECT DDOS ATTACK ####
#######################

function ip2long_v6($ip) {
    $ip_n = inet_pton($ip);
    $bin = '';
    for ($bit = strlen($ip_n) - 1; $bit >= 0; $bit--) {
        $bin = sprintf('%08b', ord($ip_n[$bit])) . $bin;
    }

    if (function_exists('gmp_init')) {
        return gmp_strval(gmp_init($bin, 2), 10);
    } elseif (function_exists('bcadd')) {
        $dec = '0';
        for ($i = 0; $i < strlen($bin); $i++) {
            $dec = bcmul($dec, '2', 0);
            $dec = bcadd($dec, $bin[$i], 0);
        }
        return $dec;
    } else {
        trigger_error('GMP or BCMATH extension not installed!', E_USER_ERROR);
    }
}

function long2ip_v6($dec) {
    if (function_exists('gmp_init')) {
        $bin = gmp_strval(gmp_init($dec, 10), 2);
    } elseif (function_exists('bcadd')) {
        $bin = '';
        do {
            $bin = bcmod($dec, '2') . $bin;
            $dec = bcdiv($dec, '2', 0);
        } while (bccomp($dec, '0'));
    } else {
        trigger_error('GMP or BCMATH extension not installed!', E_USER_ERROR);
    }

    $bin = str_pad($bin, 128, '0', STR_PAD_LEFT);
    $ip = array();
    for ($bit = 0; $bit <= 7; $bit++) {
        $bin_part = substr($bin, $bit * 16, 16);
        $ip[] = dechex(bindec($bin_part));
    }
    $ip = implode(':', $ip);
    return inet_ntop(inet_pton($ip));
}

$i = getRealIp();
# get the filename and location
$f = APP_PATH . '/logs/' . ip2long_v6($i).'.dat';
# check if the file exists and we can write
if ( is_file($f) ) {
    # get the last filetime
    $a = filemtime($f);
    # get the file content
    $b = file_get_contents($f);
    # create array from hits & seconds
    $d = explode(':',$b);
    # calculate the new result
    $h = (int)$d[0] + 1;
    $s = (int)$d[1] + (time()-$a);  
    # add the new data tot text file
    file_put_contents($f,"$h:$s",LOCK_EX);
    unset($d);
}else{
    # create the file if it doesn't exist hits:seconds
    file_put_contents($f,"1:1",LOCK_EX); #size: 3kb
    # to make sure we can write
    # chmod($f,0755); 
    # set the hits to zero
    $h = 0;
}
# create a result var
$r = $h > 10 ? (float)$s/$h : (float)1;
# calculate the diff after 10 hits, and ban when the avg is smaller than 0.20 seconds (5 hits per second)
if( $r < 0.20 ) {
    # check if we can open htaccess
    $fp = @fopen(ROOT . '/.htaccess','a'); 
    if($fp){
        # add the ip to htaccess
        @fwrite($fp,"\r\ndeny from $i"); 
        # close
        @fclose($fp);
        # mail the admin
        @mail("info@". BASEURL ,"IP Banned","Ip: $i with $r sbh (Seconds Between Hits)");
    }
    # let the user know why we deny him or her access
    die('To many requests.');
    unlink($f);
}
# if the user leaves, reset
if( $r > 30 ) {
    unlink($f);
}