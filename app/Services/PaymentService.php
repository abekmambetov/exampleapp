<?php

namespace App\Services;
use App\Models\Pay;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentService
{
    private $limitPerDay = 20;
    private $limitPerDayAdditional = 30;
    private $hashFirstPayMethod = 'KaTf5tZYHx4v7pgZ';
    private $hashSecondPayMethod = 'rTaasVHeteGbhwBx';

    public function isLimitToday() {
        $countPaysToday = Pay::whereDate('created_at', Carbon::today())->where('payment_type', 'first')->count();
        if($countPaysToday){
            if($countPaysToday >= $this->limitPerDay)
            {
                echo json_encode(['success' => false, 'msg' => 'limit exceeded today']);
                exit();              
            }
        }
    }

    public function isLimitTodayAdditional() {
        $countPaysToday = Pay::whereDate('created_at', Carbon::today())->where('payment_type', 'second')->count();
        if($countPaysToday){
            if($countPaysToday >= $this->limitPerDayAdditional)
            {
                echo json_encode(['success' => false, 'msg' => 'limit exceeded today']);
                exit();              
            }
        }
    }

    public function processPayment(Request $request) {

        $dataParams = $request->all();
        unset($dataParams['sign']);
        ksort($dataParams);   
        $dataStr = implode(':', $dataParams);
        $dataStr .= $this->hashFirstPayMethod; 
        $sha256 = hash('sha256', $dataStr);

        if($sha256 !== $request->sign)
            return false;

        $this->isLimitToday();  

        $pay = Pay::where('payment_type', 'first')->where('payment_id', $dataParams['payment_id'])->first();

        if(!$pay)
        {
          $pay = new Pay;
        }

        $pay->payment_type = 'first';
        $pay->merchant_id = $dataParams['merchant_id'];
        $pay->payment_id = $dataParams['payment_id'];
        $pay->status = $dataParams['status'];
        $pay->amount = $dataParams['amount'];
        $pay->amount_paid = $dataParams['amount_paid'];
        $pay->save();

        return true;
    }


    public function processPaymentAdditional(Request $request) {

        $dataParams = $request->Body;
        ksort($dataParams);   
        $dataStr = implode('.', $dataParams);
        $dataStr .= $this->hashSecondPayMethod; 
        $md5_hash = md5($dataStr);

        if($md5_hash !== $request->Headers['Authorization'])
            return false;

        $this->isLimitTodayAdditional();  

        $pay = Pay::where('payment_type', 'second')->where('invoice', $dataParams['invoice'])->first();

        if(!$pay)
        {
          $pay = new Pay;
        }

        $pay->payment_type = 'second';
        $pay->project = $dataParams['project'];
        $pay->invoice = $dataParams['invoice'];
        $pay->status = $dataParams['status'];
        $pay->amount = $dataParams['amount'];
        $pay->amount_paid = $dataParams['amount_paid'];
        $pay->save();

        return true;
    }
}