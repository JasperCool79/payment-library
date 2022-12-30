<?php

namespace App\Helpers;

use Exception;

class CBPay
{
    protected $url;
    protected $authToken;
    protected $contentType;
    protected $reqId;
    protected $merId;
    protected $subMerId;
    protected $terminalId;
    protected $transAmount;
    protected $transCurrency;
    protected $serviceFee;

    protected $ref1;
    protected $ref2;
    protected $verify;
    protected $client;

    public function __construct(){

        $this->contentType = 'application/json';
        $this->authToken = env('CB_QR_AUTH_TOKEN');
        $this->reqId = env('CB_QR_REQID');
        $this->merId = env('CB_QR_MERID');
        $this->subMerId = env('CB_QR_SUBMERID');
        $this->terminalId = env('CB_QR_TERMINALID');
        $this->serviceFee = env('CB_QR_MPGS_LOCAL_SERVICE_FEE');
        $this->verify = env('APP_ENV') == 'production';
        $this->serviceUrl = env('CB_QR_SERVICE_URL');
        $this->client = new \GuzzleHttp\Client();
    }

    // public function

    public function generateTransaction($transAmount, $transCurrency = 'MMK'){

        $this->transAmount = $transAmount;// + $this->serviceFee;

        $this->transCurrency = $transCurrency;

        // $this->ref1 = "Tnhi is what we vfallwg w wrgwhw4 wtqet";

        $this->ref1 = substr(str_replace("_","",$ref1), 0, 10);

        $this->ref2 = 'cbp';
        
        $body = json_encode([
            'reqId' => $this->reqId,
            'merId' => $this->merId,
            'subMerId' => $this->subMerId,
            'terminalId' => $this->terminalId,
            'transAmount' => $this->transAmount,
            'transCurrency' => $this->transCurrency
        ]);
        $response = $this->client->request('POST', $this->serviceUrl . '/generate-transaction.service',
        [
            'verify' => $this->verify,
            'headers' => [
                'Content-Type' => $this->contentType,
                'Authen-Token' => $this->authToken
            ],
            'body' => $body
        ]);
	
        $log = $response->getBody();
        file_put_contents(storage_path('logs/payment-logs').'/global-cppay-log-'.date("d-m-Y").'.log', now() ."- $log\n", FILE_APPEND);

        $responseData = json_decode($response->getBody(), true);
        if ( isset($responseData['code']) && $responseData['code'] === '0000') {
            return $responseData;
        }
        highlight_string("Someting went wrong!\nPlease contact!");
        exit();
    }

    public function checkTransaction($transRef){

        $body = json_encode([
            'merId' => $this->merId, 'transRef' => $transRef
        ]);
        $response = $this->client->request('POST', $this->serviceUrl . '/check-transaction.service',
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => $this->contentType,
                    'Authen-Token' => $this->authToken
                ],
                'body' => $body
            ]
        );
        return json_decode($response->getBody(), true);

    }
}