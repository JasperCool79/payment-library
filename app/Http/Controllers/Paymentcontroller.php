<?php

namespace App\Http\Controllers;

use App\Helpers\Mpu;
use App\Helpers\Mpgs;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Paymentcontroller extends Controller
{
    /*** Payment Preview for purchase */
    public function paymentPreview(Request $request)
    {
        if ($request->status) {
            $request->session()->flash('error', $request->status);
        }
        return view('payment.payment-preview');
    }

    /**
     * To prcess   Payment  upon user slect Gateway(Card , Pay)
     * @author  
     * @create  
     * @param    \Illuminate\Http\PaymentMethodRequest $request
     * @return \Illuminate\Http\Response $view by route
    */
    public function choosePayment(PaymentMethodRequest $request)
    {
        $amount = 10000;
        $service = 600;
        $amount = $amount + $service;
        if (in_array($request->paymentMethod, ['visa', 'master', 'jcb', 'unionpay'])) {
            return $this->mpgs($amount);
        }
        if ('mpu' === $request->paymentMethod) {
            (new Mpu())->init(
                Transaction::generateGuid(),
                $amount,
                "Purchase Payment"
            );
        }

        if ('cbpay' === $request->paymentMethod) {
            $data = (new CBPay())->generateTransaction($amount);
            return view('cb_qr', [
                'checkTransactionUrl' => route(
                    'check-transaction-cbpay',
                    [
                        'transRef' => $data['transRef']
                    ]
                ),
                'data' => $data
            ]);
        }
        
    }



    /**
     * To process MPGS Pay Gateway
     * @author  
     * @create  
     * @param    total charge amount from user- $amount
     * @return \Illuminate\Http\Response $view by route
    */
    protected function mpgs($amount)
    {
        try{
            $orderId = Transaction::generateGuid();
            $result = (new Mpgs())->init($amount, $orderId, [
                'completeUrl' => route('mpgs-success', ['order_id' => $orderId]),
                'notifyUrl' => route('payment_preview', ['status' => 'notify']),
                'errorUrl' => route('payment_preview', ['status' => 'An Error Occur']),
                'cancelUrl' => route('payment_preview', ['status' => 'Payment has been cancelled.']),
                'timeoutUrl' => route('payment_preview', ['status' => 'Connection Timeout'])
            ]);

            if (!$result['initiable']) {
                return redirect()->back()->with('error', 'Sorry. This service is unavailable right now. Please try different service.');
            }
            return view('payment.mpgs_pay_start', [
                'customerNote' => "Purchase",
                'description' => "Purchase Produce",
                'customerName' => "Mr. John",
                'hostedcheckoutUrl' => $result['hostedcheckoutUrl'],
                'completeUrl' => $result['completeUrl'],
                'notifyUrl' => $result['notifyUrl'],
                'errorUrl' => $result['errorUrl'],
                'cancelUrl' => $result['cancelUrl'],
                'timeoutUrl' => $result['timeoutUrl'],
                'sessionId' => $result['sessionId'],
                'merchantId' => $result['merchantId']
            ]);
        }catch(\Exception $e) {
            Log::danger($e);
            abort(500);
        }
    }

    /**
     * To process after paymet success from MPGS gateway
     * @author  
     * @create  
     * @param    transactions table guid $orderId
     * @return \Illuminate\Http\Response $view by route
    */
    public function mpgsSuccess($orderId)
    {
        try{
            $mpgsInstance = new Mpgs();
            if (request('resultIndicator', null) !== $mpgsInstance->getSuccessIndicatorCode()) {
                return redirect()->route('payment_preview')->with('error', 'Invalid Response');
            }
            $orderDetail = $mpgsInstance->orderApi($orderId);
            $paymentCardType  = $orderDetail['sourceOfFunds']['provided']['card']['brand'];
            $transactionId = (new Transaction)->paymentTransaction($orderId, $paymentCardType, $orderDetail); 
            return redirect()->route('pr.index')->with('success', "Payment Success");

        }catch(\Exception $e) {
            Log::danger($e);
            abort(500);
        }
    }

    /**
     * For checking payment status (success or fail) from CB Pay GateWay /  if success , save transaction and make payment log
     * @author  
     * @create  
     * @param    \Illuminate\Http\Request $request,transaction ref number(GUID from transaction table) $transRef
     * @return json
    */
    public function checkTransactionCBPay(Request $request,$transRef)
    {
        DB::beginTransaction();
        try {
            $check = (new CBPay())->checkTransaction($request->transRef);
            if(env('REAL_PAYMENT') == false)
            {
                $check['transStatus'] = 'S';
                $check['bankTransId'] = 1;
                $check['transAmount'] = 1;
                $check['transCurrency'] = 'MMK';
            }
            if ($check['transStatus'] === 'S') { //if transaction success
                Log::channel('payments')->info(auth()->user()->id . ' (user_id) - Payment Success Data - ' . json_encode($check));
                
                $orderId = Transaction::generateGuid();
                $application_no = $pr->generateApplicationNo();
                if(!$application_no) {
                    $request->session()->flash('error', "Foor Product Registeration Application form Submittedb Fails.But Payment Process Success!");
                    return json_encode([
                        'transStatus' => 'E',
                        'redirect' => route('pr.index')
                    ]);
                }
                
                $transaction_id = (new Transaction)->paymentTransaction($orderId, 'CBPay', $check);
                food_payment_logger("reg-fee", auth()->user()->id, "food-pr-assement-fee", "New", $transaction_id, "CB Pay", $check['transAmount'], json_encode($check));

                $processAfterPayment = $this->paymentSuccess($transaction_id, $pr->id, $application_no);
                if($processAfterPayment) {
                    DB::commit();
                }else{
                    DB::rollback();
                }
                
                $request->session()->flash('success', "Product Registeration Application form Submitted.");
                return json_encode([
                    'transStatus' => 'S',
                    'redirect' => route('pr.index')
                ]);
            }
            return json_encode($check);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::debug($th);//write error log
            return json_encode([
                'transStatus' => 'E',
                'redirect' => route('pr.index')
            ]);
        }
    }
}