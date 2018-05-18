<?php

require_once(LIB_PATH . '/Nusoap/nusoap.php');

ini_set("display_errors", 1);

class MellatPaymentHelper {

    private $terminalId             = "122345";                 // Terminal ID
    private $userName               = "userName";               // Username
    private $userPassword           = "8237482349";             // Password
    private $callBackUrl    = "http://site.ir/varify.php";      // Callback URL
    private $client         = NULL ;
    private $namespace      = 'http://interfaces.core.sw.bps.com/';


    function __construct() {
        try {
            $this->client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        } catch (Exception $e) {
            echo '<h3>' . $e->getMessage() . '</h3>' . PHP_EOL;
            exit();
        }
    }


    public function doPay($amount , $uid = 12 , $payfor = 12 , $kind = 12 , $description = "test") {

        $orderId        = $this->random_string(16);     // Order ID Add to dataBase and then say orderID
        $localDate      = date('Ymd');                  // Date
        $localTime      = date('Gis');                  // Time
        $additionalData = '';
        $payerId        = 0;

        //-- Add variables to array
        $parameters = array(
            'terminalId'        => $this->terminalId,
            'userName'          => $this->userName,
            'userPassword'      => $this->userPassword,
            'orderId'           => $orderId,
            'amount'            => $amount,
            'localDate'         => $localDate,
            'localTime'         => $localTime,
            'additionalData'    => $additionalData,
            'callBackUrl'       => $this->callBackUrl,
            'payerId'           => $payerId
        );

        $result= $this->client->call('bpPayRequest', $parameters, $this->namespace);

        //-- Check Error Exist
        if (strlen($this->client->fault) > 0 )
        {
            echo "Error : There was a problem connecting to Bank";
            exit;
        }
        else
        {
            $err = $this->client->getError();
            if ($err)
            {
                echo "Error : " . $this->getError($err) ;
                exit;
            }
            else
            {
                $res        = explode (',',$result);
                $ResCode    = $res[0];
                if ($ResCode == "0")
                {
                    try {
                        $dbObj = new DataBase();
                    } catch (Exception $e) {
                        echo '<h3>' . $e->getMessage() . '</h3>' . PHP_EOL;
                        exit();
                    }

                    $now = time();

                    $values = array(
                        'amount' => $amount ,
                        'date' => $now ,
                        'user' => $uid ,
                        'payfor' => $payfor ,
                        'kind' => $kind ,
                        'description' => $description ,
                        'status' => 'send' ,
                        'primkey' => $orderId
                    );

                    $payRequest = $dbObj->create("ctg_pays" , $values );

                    // $payRequest = $conn->prepare("INSERT INTO `ctg_pays`( `amount`, `date`, `user`, `payfor`, `kind`, `description`, `status`, `trankey`, `primkey`) VALUES (:amount , :date, :uid , :payfor , :kind ,:description , 'send' , :trankey , :primkey )");
                    // $payRequest->bindValue(':amount', $price);
                    // $payRequest->bindValue(':date', $now);
                    // $payRequest->bindValue(':uid', $user->user_id);
                    // $payRequest->bindValue(':payfor', $payfor);
                    // $payRequest->bindValue(':kind', $kind);
                    // $payRequest->bindValue(':description', $Description);
                    // $payRequest->bindValue(':primkey', random_string(16));
                    // $payRequest->execute();


                    if ($payRequest) {
                        //-- Redirect to BPW shaparak
                        echo 'در حال انتقال به درگاه پرداخت ...
                            <form name="myform" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
                                <input type="hidden" id="RefId" name="RefId" value="'. $res[1] .'">
                                <input type="hidden" id="csrf" name="csrf" value="'. $this->random_string(32) .'">
                            </form>
                            <script type="text/javascript">window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>';
                        exit;
                    } else {
                        echo "Error : DB error ";
                        exit;
                    }
                }
                else
                {
                    echo "Error : ". $this->getError($result) ;
                    exit;
                }
            }
        }
    }

    public function checkPay($rescode , $orderId , $verifySaleOrderId , $verifySaleReferenceId) {
        // $orderId                = $_POST['SaleOrderId'];            // Order ID
        // $verifySaleOrderId      = $_POST['SaleOrderId'];
        // $verifySaleReferenceId  = $_POST['SaleReferenceId'];
        // rescode

        if ($rescode == '0') {
            //-- Payment Succesful
            $client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
            $namespace='http://interfaces.core.sw.bps.com/';

            $parameters = array(
                'terminalId'        => $this->terminalId,
                'userName'          => $this->userName,
                'userPassword'      => $this->userPassword,
                'orderId'           => $orderId,
                'saleOrderId'       => $verifySaleOrderId,
                'saleReferenceId'   => $verifySaleReferenceId
            );

            // Call the SOAP method
            $result = $this->client->call('bpVerifyRequest', $parameters, $namespace);
            if($result == 0) {
                //-- Verify Success , get money request
                // Call the SOAP method
                $result = $this->client->call('bpSettleRequest', $parameters, $namespace);
                if($result == 0) {
                    //-- All success.
                    return true;
                } else {
                    // error on money request , request reverese money
                    $this->client->call('bpReversalRequest', $parameters, $namespace);
                    echo 'Error : '. $result;
                }
            } else {
                // verify failure , show error and reverse money
                $this->client->call('bpReversalRequest', $parameters, $namespace);
                echo 'Error : '. $result;
            }
        } else {
            //-- Pay has error
            echo 'Error : '. $this->getError($rescode);
        }

    }


    private function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9));
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }

    private function getError($code) {
        $keys = array(
            '0' => 'با موفقیت انجام شد ',
            '11' => 'شماره کارت نامعتبر است ',
            '12' => 'موجودی کافی نیست ',
            '13' => 'با موفقیت انجام شد ',
            '14' => 'با موفقیت انجام شد ',
            '15' => 'با موفقیت انجام شد ',
            '16' => 'با موفقیت انجام شد ',
            '17' => 'با موفقیت انجام شد ',
            '18' => 'با موفقیت انجام شد ',
            '19' => 'با موفقیت انجام شد ',
            '111' => 'با موفقیت انجام شد ',
            '112' => 'با موفقیت انجام شد ',
            '113' => 'با موفقیت انجام شد ',
            '114' => 'با موفقیت انجام شد ',
            '21' => 'با موفقیت انجام شد ',
            '23' => 'با موفقیت انجام شد ',
            '24' => 'با موفقیت انجام شد ',
            '25' => 'با موفقیت انجام شد ',
            '31' => 'با موفقیت انجام شد ',
            '32' => 'با موفقیت انجام شد ',
            '33' => 'با موفقیت انجام شد ',
            '34' => 'با موفقیت انجام شد ',
            '35' => 'با موفقیت انجام شد ',
            '41' => 'با موفقیت انجام شد ',
            '42' => 'با موفقیت انجام شد ',
            '43' => 'با موفقیت انجام شد ',
            '44' => 'با موفقیت انجام شد ',
            '45' => 'با موفقیت انجام شد ',
            '46' => 'با موفقیت انجام شد ',
            '47' => 'با موفقیت انجام شد ',
            '48' => 'با موفقیت انجام شد ',
            '49' => 'يافت نشد  Refund تراكنش ',
            '412' => 'شناسه قبض نادرست است ',
            '413' => 'شناسه پرداخت نادرست است ',
            '414' => 'سازمان صادر كننده قبض نامعتبر است ',
            '415' => 'زمان جلسه كاري به پايان رسيده است ',
            '416' => 'خطا در ثبت اطلاعات ',
            '417' => 'شناسه پرداخت كننده نامعتبر است ',
            '418' => 'اشكال در تعريف اطلاعات مشتري ',
            '419' => 'تعداد دفعات ورود اطلاعات از حد مجاز گذشته است ',
            '421' => 'نامعتبر است  IP',
            '51' => 'تراكنش تكراري است ',
            '54' => 'تراكنش مرجع موجود نيست ',
            '55' => 'تراكنش نامعتبر است ',
            '61' => 'خطا در واریز ',
        );
        /*
        0 ok
        شماره كارت نامعتبر است 11
        موجودي كافي نيست 12
        رمز نادرست است 13
        تعداد دفعات وارد كردن رمز بيش از حد مجاز است 14
        كارت نامعتبر است 15
        دفعات برداشت وجه بيش از حد مجاز است 16
        كاربر از انجام تراكنش منصرف شده است 17
        تاريخ انقضاي كارت گذشته است 18
        مبلغ برداشت وجه بيش از حد مجاز است 19
        صادر كننده كارت نامعتبر است 111
        خطاي سوييچ صادر كننده كارت 112
        پاسخي از صادر كننده كارت دريافت نشد 113
        دارنده كارت مجاز به انجام اين تراكنش نيست 114
        پذيرنده نامعتبر است 21
        خطاي امنيتي رخ داده است 23
        اطلاعات كاربري پذيرنده نامعتبر است 24
        مبلغ نامعتبر است 25
        پاسخ نامعتبر است 31
        فرمت اطلاعات وارد شده صحيح نمي باشد 32لف
        حساب نامعتبر است 33
        خطاي سيستمي 34
        تاريخ نامعتبر است 35
        شماره درخواست تكراري است 41
        يافت نشد 42 Sale تراكنش
        43 Verify قبلا درخواست
        يافت نشد 44 Verfiy درخواست
        شده است 45 Settle تراكنش
        نشده است 46 Settle تراكنش
        يافت نشد 47 Settle تراكنش
        شده است 48 Reverse تراكنش
        */
        return $code;

    }


}
