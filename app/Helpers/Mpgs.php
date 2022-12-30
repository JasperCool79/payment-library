<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

class Mpgs
{
    protected $contentType;

    protected $serviceFee;
    protected $merchantId;
    protected $apiPassword;
    protected $currency;
    protected $baseUrl;
    protected $version;
    protected $sessionUrl;

    public function __construct()
    {
        if ( 'production' === env('APP_ENV') ) {
            $serviceFee  = env('MPGS_PROD_SERVICE_FEE');
            $apiPassword = env('MPGS_PROD_API_PASSWORD');
            $currency    = env('MPGS_PROD_CURRENCY');
            $baseUrl     = env('MPGS_PROD_BASE_URL');
            $version     = env('MPGS_PROD_VERSION');
            $merchantId  = env('MPGS_PROD_MERCHANT_ID');
        } else {
            $serviceFee  = env('MPGS_LOCAL_SERVICE_FEE');
            $apiPassword = env('MPGS_LOCAL_API_PASSWORD');
            $currency    = env('MPGS_LOCAL_CURRENCY');
            $baseUrl     = env('MPGS_LOCAL_BASE_URL');
            $version     = env('MPGS_LOCAL_VERSION');
            $merchantId  = env('MPGS_LOCAL_MERCHANT_ID');
        }

        $this->contentType = 'application/json';
        $this->serviceFee  = (int) $serviceFee;
        $this->merchantId  = $merchantId;
        $this->apiPassword = $apiPassword;
        $this->baseUrl     = $baseUrl;
        $this->version     = $version;
        $this->currency    = $currency;
        $this->apiUrl      = $baseUrl . "/api/rest/version/" . $version . "/merchant/" . $merchantId;
        $this->sessionUrl = $this->apiUrl . "/session";
    }

    public function init($amount, $orderId, $urls = array() )
    {
        $result = $this->createCheckoutSession($amount, $orderId, $urls['completeUrl'], $urls['notifyUrl']);
        if( isset($result['result']) && 'SUCCESS' === $result['result'] ) {

            $this->setSession('successIndicator', $result['successIndicator']);

            return array(
                'initiable' => true,
                'hostedcheckoutUrl' => $this->baseUrl . "/checkout/version/" . $this->version . "/checkout.js",
                'errorUrl' => $urls['errorUrl'],
                'cancelUrl' => $urls['cancelUrl'],
                'completeUrl' => $urls['completeUrl'],
                'timeoutUrl' => $urls['timeoutUrl'],
                'notifyUrl' => $urls['notifyUrl'],
                'sessionId' => $result['session']['id'],
                'merchantId' => $result['merchant']
            );

        }
        if ( isset($result['result']) && 'ERROR' === $result['result'] ) {
            if(env('APP_ENV') == 'local'){
                dd($result);
            }
            Log::error('mpgs error -'.json_encode($result));
            return ['initiable' => false ];
        }
    }

    public function orderApi( $orderId )
    {
        return $this->initCurl('GET', $this->apiUrl . '/order/' . $orderId);
    }

    public function getSuccessIndicatorCode()
    {
        return session('successIndicator');
    }

    public function refundPayment($orderId, $transactionId, $amount)
    {
        $apiUrl = $this->apiUrl . "/order/" . $orderId . "/transaction/" . $transactionId . "";

        return $this->initCurl('PUT', $this->apiUrl, array(
            "apiOperation" => "REFUND",
            "transaction" => array(
                "amount" => $amount,
                "currency" => $this->currency
            )
        ));
    }

    protected function setSession($key, $val)
    {
        session([$key => $val]);
    }

    protected function createCheckoutSession($amount, $orderId, $completeUrl, $notifyUrl)
    {
        return $this->initCurl('POST', $this->sessionUrl, array(
            "apiOperation" => "CREATE_CHECKOUT_SESSION",
            "interaction" => array(
                "operation" => "PURCHASE",
                "returnUrl" => $completeUrl
            ),
            "order" => array(
                "amount" => $amount,
                "currency" => $this->currency,
                "id" => $orderId,
                "notificationUrl" => $notifyUrl
            )
        ));
    }

    protected function initCurl($method, $url, $data = array())
    {
        $response = (new \GuzzleHttp\Client())->request($method, $url,
        [
            'verify' => false,
            'headers' => array(
                'Content-Type' => $this->contentType,
                'Authorization' => 'Basic ' . base64_encode("merchant." . $this->merchantId . ":" . $this->apiPassword)
            ),
            'body' => count( $data) > 0? json_encode($data):null
        ]);

        return json_decode($response->getBody(), true);

    }

}