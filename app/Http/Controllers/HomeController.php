<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;

class HomeController extends Controller
{    
    private $limitPerDay = 20;
    private $limitPerDayAdditional = 30;
    private $hashFirstPayMethod = 'KaTf5tZYHx4v7pgZ';
    private $hashSecondPayMethod = 'rTaasVHeteGbhwBx';

    public function processPay(Request $request) {
        
        if($request->merchant_id)
            $paymentType = 'first';
        elseif($request->Body['project'])
            $paymentType = 'second';
        else
            return json_encode(['success' => false]);

        switch ($paymentType) {
            case 'first':
                if($this->processPayment($request))
                    return json_encode(['success' => true]);  
                break;
            case 'second':
                if($this->processPaymentAdditional($request))
                    return json_encode(['success' => true]);
                break;
        }

        return json_encode(['success' => false, 'msg' => 'Wrong params']);
    }  

    private function isLimitToday() {
        $countPaysToday = Pay::whereDate('created_at', Carbon::today())->where('payment_type', 'first')->count();
        if($countPaysToday){
            if($countPaysToday >= $this->limitPerDay)
            {
                echo json_encode(['success' => false, 'msg' => 'limit exceeded today']);
                exit();              
            }
        }
    }

    private function isLimitAdditional() {
        $countPaysToday = Pay::whereDate('created_at', Carbon::today())->where('payment_type', 'second')->count();
        if($countPaysToday){
            if($countPaysToday >= $this->limitPerDayAdditional)
            {
                echo json_encode(['success' => false, 'msg' => 'limit exceeded today']);
                exit();              
            }
        }
    }

    private function processPayment(Request $request) {

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


    private function processPaymentAdditional(Request $request) {

        $dataParams = $request->Body;
        ksort($dataParams);   
        $dataStr = implode('.', $dataParams);
        $dataStr .= $this->hashSecondPayMethod; 
        $md5_hash = md5($dataStr);

        if($md5_hash !== $request->Headers['Authorization'])
            return false;

        $this->isLimitAdditional();  

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

    public function processPayRequest(Request $request) {
        // тестовый запрос 
        $client = new Client();

        $response = $client->post('http://app.loc/callback-url', [
            RequestOptions::JSON => [
              "merchant_id" => 6,
              "payment_id" => 13,
              "status" => "completed",
              "amount" => 500,
              "amount_paid" => 500,
              "timestamp" => 1654103837,
              "sign" => "f027612e0e6cb321ca161de060237eeb97e46000da39d3add08d09074f931728"
            ]
        ]);

        if ($response->getBody()) {
            echo $response->getBody();
        }
    }  
    
    public function processPayRequestAdditional(Request $request) {
        // тестовый запрос

        $response = Http::post('http://app.loc/callback-url', [
            "Headers" => [
                "Authorization" => 'd84eb9036bfc2fa7f46727f101c73c73',
            ],
            "Body" => [
                "project" => 816,
                "invoice" => 73,
                "status" => "completed",
                "amount" => 700,
                "amount_paid" => 700,
                "rand" => "SNuHufEJ",
            ]
        ]);
        
        echo $response;
        
    }  
    
}
