<?php
namespace App\Helpers;

use Exception;
use DB;
use Log;

class Mpu
{
    protected $merchantId;
    protected $secretKey;
    protected $pgwUrl;
    protected $serviceFee;
    protected $currencyCode;

    public function __construct()
    {
        if ( 'production' === env('APP_ENV') ) {
            $this->serviceFee = env('MPU_PROD_SERVICE_FEE');
            $this->merchantId = env('MPU_PROD_MERCHANT_ID');
            $this->secretKey  = env('MPU_PROD_SECRET_KEY');
            $this->pgwUrl     = env('MPU_PROD_PGW_URL');
            $this->currencyCode = env('MPU_PROD_CURRENCY_CODE');
        } else {
            $this->serviceFee = env('MPU_LOCAL_SERVICE_FEE');
            $this->merchantId = env('MPU_LOCAL_MERCHANT_ID');
            $this->secretKey  = env('MPU_LOCAL_SECRET_KEY');
            $this->pgwUrl     = env('MPU_LOCAL_PGW_URL');
            $this->currencyCode = env('MPU_LOCAL_CURRENCY_CODE');
        }

    }

    public function init($invoiceNo, $amount, $productDesc)
    {
        $amount = str_pad(($amount . "00"), 12, '0', STR_PAD_LEFT);

        $value = array();
        array_push($value, $this->merchantId, $invoiceNo, $productDesc, $amount, $this->currencyCode);

        $log = json_encode($value);
        file_put_contents(storage_path('logs/payment-logs').'/global-mpu-log-'.date("d-m-Y").'.log', now() ."- $log\n", FILE_APPEND);

        $hashValue = $this->generateHashValue($value);

        /*-------------------------------------------------------
                               lOG TO TRACK
        --------------------------------------------------------*/
        echo <<< PAYMENT
        <form id="fda_mpu_hidden_form" name="hidden_form" method="post"  action="{$this->pgwUrl}">
          <input type="hidden" id="merchantID" name="merchantID" value="{$this->merchantId}"> <br>
          <input type="hidden" id="invoiceNo" name="invoiceNo" value="{$invoiceNo}"> <br>
          <input type="hidden" id="productDesc" name="productDesc" value="{$productDesc}"> <br>
          <input type="hidden" id="amount" name="amount" value="{$amount}"> <br>
          <input type="hidden" id="currencyCode" name="currencyCode" value="{$this->currencyCode}"> <br>
          <input type="hidden" id="hashValue" name="hashValue" value="{$hashValue}"> <br>
          <input type="submit" id="btnSubmit" name="btnSubmit" value="Submit" style="display: none;">
        </form>
        <script language="JavaScript">
        document.forms["fda_mpu_hidden_form"].submit();
        </script>
        PAYMENT;

    }

    public function frontendRedirect()
    {        
        $value = [];
        foreach (request()->all() as $key => $val) {
          if ($key != 'hashValue') array_push($value, $val);
        }

        $log = json_encode(request()->all());
        file_put_contents(storage_path('logs/payment-logs').'/global-mpu-log-'.date("d-m-Y").'.log', now() ."Frontend - $log\n", FILE_APPEND);

        $hashValue = $this->generateHashValue($value);

        if( count(request()->all()) < 1 ) {
            return redirect()->route('index')->with('error', 'Cancelled Payment.');
        }

        if ($hashValue == request('hashValue') && request('respCode') == '00' && request('invoiceNo')) {

            return redirect()->route('index')->with('success', 'Successfully Paid.');
        }
        
        return redirect()->route('index')->with('error', 'Error Paid.');

    }

    public function backendRedirect()
    {
            $value = [];
            foreach (request()->all() as $key => $val) {
              if ($key != 'hashValue') array_push($value, $val);
            }

            $log = json_encode(request()->all());
            file_put_contents(storage_path('logs/payment-logs').'/global-mpu-log-'.date("d-m-Y").'.log', now() ."Backend - $log\n", FILE_APPEND);
 
            $hashValue = $this->generateHashValue($value); 

            if($hashValue == request('hashValue') && request('respCode') == '00' && request('invoiceNo')){
                Log::info( date('Y-m-d H:i:s') . 'Backend Payment Transaction OK[status: success]');

            } else {
                // Log For Error
                Log::info('-------------------------------------------------------------------');
                Log::info( date('Y-m-d H:i:s') . 'Backend Payment Transaction OK[status: fail]');
                Log::info( request()->all() );
                Log::info('-------------------------------------------------------------------');
            }

    }

    // private function generateHashValue($invoiceNo, $productDesc, $amount, $userDefined1, $userDefined2, $userDefined3)
    private function generateHashValue($values = array())
    {   
        sort($values, SORT_STRING);
        return strtoupper(hash_hmac('sha1', implode("", $values), $this->secretKey, false));
    }

}