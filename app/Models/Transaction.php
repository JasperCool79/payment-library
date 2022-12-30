<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\CBPay;
use App\Models\MpuPay;
use App\Models\MpgsPay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function generateGuid()
    {
        do {
            $transId = time() . rand(100000, 999999);
        } while (self::where('guid', $transId)->first());

        return $transId;
    }

    public function cbPay()
    {
        return $this->hasOne(CBPay::class, 'transaction_id', 'id');
    }

    public function mpgsPay()
    {
        return $this->hasOne(MpgsPay::class, 'transaction_id', 'id');
    }

    public function mpuPay()
    {
        return $this->hasOne(MpuPay::class, 'transaction_id', 'id');
    }

    public function paymentTransaction($guid,$serviceType, $serviceResults)
    {
        $serviceType = strtoupper(strtolower($serviceType));
        $amount = 0;
        $transaction = $this->create([
            'guid' => $guid,
            'service_type' => $serviceType,
        ]);
        if ($serviceType === 'CBPAY') {
            $transaction->cbPay()->create([
                'msg' => $serviceResults['msg'],
                'transStatus' => $serviceResults['transStatus'],
                'bankTransId' => $serviceResults['bankTransId'],
                'transAmount' => $serviceResults['transAmount'],
                'transCurrency' => $serviceResults['transCurrency']
            ]);
            $amount = $serviceResults['transAmount'];
        } else if ($serviceType === 'MPU') {
            $transaction->mpuPay()->create([
                "pan" => $serviceResults['pan'],
                "amount" => $serviceResults['amount'],
                "invoiceNo" => $serviceResults['invoiceNo'],
                "tranRef" => $serviceResults['bankTransId'],
                "approvalCode" => $serviceResults['approvalCode'],
                "dateTime" => $serviceResults['dateTime'],
                "status" => $serviceResults['status'],
                "failReason" => $serviceResults['failReason'],
                "userDefined1" => $serviceResults['userDefined1'],
                "userDefined2" => $serviceResults['userDefined2'],
                "userDefined3" => $serviceResults['userDefined3'],
                "categoryCode" => $serviceResults['categoryCode'],
            ]);
            $amount = $serviceResults['amount'];
        } else {
            /*
            'MPU' => 'MPU', 'VISA' => 'VISA',
            'DISCOVER' => 'UNIONPAY SECUREPAY(DISCOVER)',
            'JCB' => 'JCB', 'MASTERCARD' => 'MASTERCARD'
            */
            $issuer = ($serviceType == "JCB")? "JCB issuer" : $serviceResults['sourceOfFunds']['provided']['card']['issuer']?? 'NA';
            $transaction->mpgsPay()->create([
                'funding_method' => $serviceResults['sourceOfFunds']['provided']['card']['fundingMethod'],
                'customer_note' => $serviceResults['customerNote'],
                'description' => $serviceResults['description'],
                'issuer' => $issuer,
                'name_on_card' => $serviceResults['customer']['firstName']?? 'No Name On Cart',
                'pan' => $serviceResults['sourceOfFunds']['provided']['card']['number'],
                'card_type' => $serviceType,
                'browser' => $serviceResults['device']['browser'],
                'ip_address' => $serviceResults['device']['ipAddress'],
                'total_amount' => $serviceResults['amount'],
                'currency' => $serviceResults['currency'],
                'status' => $serviceResults['status'],
                'creation_time' => Carbon::parse($serviceResults['creationTime'])->format('Y-m-d H:i:s')
            ]);
            $amount = $serviceResults['amount'];
        }
        $transaction->update(['amount' => $amount]);
        return $transaction->id;
    }
}