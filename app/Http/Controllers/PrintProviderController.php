<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\EtsyOrder;
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
        $obj =  json_decode($response,true);

        return response()->json([
            'success' => 1,
            'message' => 'Get gearment products',
            'data' => $obj
        ]);
    }

    public function createGearmentOrder(Request $request)
    {
        $result = [];

        if(!$request->has('order_type')) return response()->json([
            'success' => 0,
            'message' => 'order_type', 
            'data' => $result
        ]);

        if($request->get('order_type') == 1){
            $order = Order::find($request->get('order_id'));
        }elseif($request->get('order_type') == 2){
            $order = EtsyOrder::find($request->get('order_id'));
        }else{
            return response()->json([
                'success' => 0,
                'message' => 'order_type', 
                'data' => $result
            ]);
        }

        if(!$order || $order->status != 0) return response()->json([
            'success' => 0,
            'message' => "this order is already printed",
            'data' =>  $result,
        ]);

        if(!in_array($order->owner_id, [1,$request->user()->id])) return response()->json([
            'success' => 0,
            'message' => "owner_id",
            'data' =>  $result,
        ]);       

        $post_data = $request->get('postdata');
        // $post_data['api_key'] = Auth::user()->gearment_api_key; 
        // $post_data['api_signature'] = Auth::user()->gearment_api_signature; 

        $post_data['api_key'] = "QBIVKPk3xTZEsYzI"; 
        $post_data['api_signature'] = "HH2pU54NpWGPvY5OOpDtpG6Z5t7unFLO";
        $post_data['shipping_method'] = $post_data['shipping_method'] - 1;

        // $post_data['send_shipping_notification'] = (bool)0; // 0/1 -> PHP false/true
        // $json_post_data = json_encode($post_data, JSON_NUMERIC_CHECK);
        // $json_post_data = str_replace('[string]', '', $json_post_data);
        // error_log($json_post_data);

        // print_r($json_post_data);
        // exit();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://account.gearment.com/api/v2/?act=order_create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result =  json_decode($response,true);  

        if($result['status'] == 'error'){
            return response()->json([
                'success' => 0,
                'message' => $result['message'],
                'data' => $result
            ]);
        }

        $order->fulfillment_id = $result['result']['id'];
        $order->fulfillment_by = 'gearment';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => $result['message'],
            'data' => $result,
        ]);
    }

    // Webhook:
    public function tracking_updated(Request $request){
        return $request->all();
    }

    public function shipping_address_unverified(Request $request){
        return 1;
    }

    public function order_canceled(Request $request) {
        return 1;
    }

    public function order_completed(Request $request) {
        return 1;
    }

    public function getPrintifyPrintProviders(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/catalog/blueprints/" . $request->blueprint_id . "/print_providers.json ",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".Auth::user()->printify_api."",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result =  json_decode($response,true);

        if (array_key_exists('error', $result)) return "ERROR:Invalid Request";;

        return response()->json([
            'success' => 1,
            'message' => 'Get print providers success',
            'data' => $result
        ]);
    }

    public function getPrintifyVariants(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/catalog/blueprints/" . $request->blueprint_id . "/print_providers/" . $request->print_provider_id . "/variants.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".Auth::user()->printify_api."",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result =  json_decode($response,true);

        if (array_key_exists('error', $result)) return "ERROR:Invalid Request";;

        return response()->json([
            'success' => 1,
            'message' => 'Get variants success',
            'data' => $result
        ]);
    }

    public function createPrintifyOrder(Request $request)
    {
        $result = [];

        if(!$request->has('order_type')) return response()->json([
            'success' => 0,
            'message' => 'order_type', 
            'data' => $result
        ]);

        if($request->get('order_type') == 1){
            $order = Order::find($request->get('order_id'));
        }elseif($request->get('order_type') == 2){
            $order = EtsyOrder::find($request->get('order_id'));
        }else{
            return response()->json([
                'success' => 0,
                'message' => 'order_type', 
                'data' => $result
            ]);
        }

        if(!$order || $order->status != 0) return response()->json([
            'success' => 0,
            'message' => "this order is already printed",
            'data' =>  $result,
        ]);

        if(!in_array($order->owner_id, [1,$request->user()->id])) return response()->json([
            'success' => 0,
            'message' => "owner_id",
            'data' =>  $result,
        ]); 
            
        $curl = curl_init();

        $post_data = $request->get('postdata');
        $post_data['send_shipping_notification'] = (bool)0; // 0/1 -> PHP false/true

        $json_post_data = json_encode($post_data, JSON_NUMERIC_CHECK);
        $json_post_data = str_replace('[string]', '', $json_post_data);
        // error_log($json_post_data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/shops/3558273/orders.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $json_post_data,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".Auth::user()->printify_api."",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result =  json_decode($response,true);

        if (array_key_exists("errors", $result)) {
            return response()->json([
                'success' => 0,
                'message' => $result['errors']['reason'],
                'data' => $result,
            ], 400);
        }
        // {"status":"error","code":8150,"message":"Validation failed.","errors":{"reason":"line_items.0.print_provider_id: The line_items.0.print_provider_id must be an integer.","code":8150}}
        // error_log(json_encode($result));

        $order->fulfillment_id = $result['id'];
        $order->fulfillment_by = 'printify';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => "[API] Add order successfully!",
            'data' => $result,
        ]);
    }
}
