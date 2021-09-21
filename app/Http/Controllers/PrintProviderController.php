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

        // print_r($response);

        $obj =  json_decode($response,true);

        // print_r($obj);
        // exit();
        return response()->json([
            'success' => 1,
            'message' => 'Get gearment products',
            'data' => $obj
        ]);
    }

    public function createOrder()
    {

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
        $id = $request->get('orderid');
        $order = Order::find($id);
        if(!$order) return response()->json([
            'success' => 0,
            'message' => "Order not found",
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
                'message' => "Submit Order",
                'data' => $result,
            ], 400);
        }

        // error_log(json_encode($result));

        // Save DB if success ;
        $order = Order::find($request->get('order_id'));

        $order->fulfillment_id = $result['id'];
        $order->fulfillment_by = 'printify';

        $order->save();

        return response()->json([
            'success' => 1,
            'message' => "Submit Order",
            'data' => $result,
        ]);
    }
}
