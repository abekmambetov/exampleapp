<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Carbon\Carbon;
use App\Services\PaymentService; 

class HomeController extends Controller
{    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processPay(Request $request) {
        
        if($request->merchant_id)
            $paymentType = 'first';
        elseif($request->Body['project'])
            $paymentType = 'second';
        else
            return json_encode(['success' => false]);

        switch ($paymentType) {
            case 'first':
                if($this->paymentService->processPayment($request))
                    return json_encode(['success' => true]);  
                break;
            case 'second':
                if($this->paymentService->processPaymentAdditional($request))
                    return json_encode(['success' => true]);
                break;
        }

        return json_encode(['success' => false, 'msg' => 'Wrong params']);
    }  

    public function processPayRequest() {
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
    
    public function processPayRequestAdditional() {
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
