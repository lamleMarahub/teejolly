<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\User;
use App\OrderItem;
use Carbon\Carbon;
use Auth;
use DB;

class PrintProviderController extends Controller
{
	protected $pagesize;

    public function __construct()
    {
        $this->middleware('auth.seller');
        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    // Webhook: 
    // tracking_updated
    // shipping_address_unverified
    // order_canceled
    // order_completed

    public function tracking_updated(Request $request){
        return $request->all();
    }

    public function shipping_address_unverified(Request $request){
        return 1;
    }

    public function order_canceled(Request $request){
        return 1;
    }

    public function order_completed(Request $request){
        return 1;
    }

    // Gearment
    public function getProductVariants()
    {
    	
    	$curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://account.gearment.com/api/v2/?act=products",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>"{\r\n\t\"api_key\":\"QBIVKPk3xTZEsYzI\",\r\n\t\"api_signature\":\"HH2pU54NpWGPvY5OOpDtpG6Z5t7unFLO\"}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        print_r($response);

        $obj =  json_decode($response,true);
        
        // print_r($obj);
        // exit();
    }

    public function createOrder()
    {
        
    }
}
