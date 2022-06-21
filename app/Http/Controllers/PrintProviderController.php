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
use Illuminate\Support\Facades\Cache;

class PrintProviderController extends Controller
{
    protected $pagesize;
    protected $DREAMSHIP_TOKEN = '2b712060b88de3f9faeb53968a27a40c33020cd5';
    protected $CUSTOMCAT_API_KEY = '57C11845-0A7E-FEE7-5FC8312B727456D4';
    protected $TEEZILY_API_KEY = 'Bearer 7cfa456c-445f-447f-953c-e6d9d365ec93';
    protected $CACHE_TTL_SECOND = 86400; // 1d

    public function __construct()
    {
        $this->middleware('auth.seller');
        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    // Printhigh
    public function getPrinthighCatalog()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://gateway.printhigh.com/api/public/catalog',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $obj =  json_decode($response,true);

        // print_r($obj);

        return response()->json([
            'success' => 1,
            'message' => 'Get printhigh products',
            'data' => $obj
        ]);

    }

    public function createPrintHighOrder(Request $request)
    {
        $result = [];

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $post_data = $request->get('postdata');
        // $post_data['api_key'] = Auth::user()->gearment_api_key;
        // $post_data['api_signature'] = Auth::user()->gearment_api_signature;

        // $post_data['api_key'] = "QBIVKPk3xTZEsYzI";
        // $post_data['api_signature'] = "HH2pU54NpWGPvY5OOpDtpG6Z5t7unFLO";
        // $post_data['shipping_method'] = $post_data['shipping_method'] - 1;
        // $post_data['send_shipping_notification'] = (bool)0; // 0/1 -> PHP false/true
        $json_post_data = json_encode($post_data, JSON_NUMERIC_CHECK);
        $json_post_data = str_replace('[string]', '', $json_post_data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://gateway.printhigh.com/api/orders?token=99656ddaef6eb883bca21ee09e8e27825009b7f63c39f2694a1483931bd65729',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_post_data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result =  json_decode($response,true);

        if (array_key_exists("message", $result)) {
            return response()->json([
                'success' => 0,
                'message' => $result['message'],
                'data' => $result
            ]);
        }

        $order->fulfillment_id = $result['order']['id'];
        $order->fulfillment_by = 'printhigh';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => '[API] Add order successfully!',
            'data' => $result,
        ]);
    }

    // Gearment
    public function getProductVariants()
    {
        $endpoint = 'https://account.gearment.com/api/v2/?act=products';

        if (Cache::has($endpoint)) {
            $cache_value = Cache::get($endpoint);
            return response()->json([
                'success' => 1,
                'message' => 'getProductVariants in cache',
                'data' => $cache_value
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>"{\r\n\t\"api_key\":\"K02QrEl5XZopqBr8\",\r\n\t\"api_signature\":\"oPxDQqHo5lOAskPtTA189NnSFsMTjtXC\"}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response,true);

        Cache::put($endpoint, $obj, $this->CACHE_TTL_SECOND);

        return response()->json([
            'success' => 1,
            'message' => 'Get gearment products',
            'data' => $obj
        ]);
    }

    // Dreamship
    public function getDreamshipCategories()
    {
        $endpoint = 'https://api.dreamship.com/v1/categories/';

        if (Cache::has($endpoint)) {
            $cache_value = Cache::get($endpoint);
            return response()->json([
                'success' => 1,
                'message' => 'getDreamshipCategories in cache',
                'data' => $cache_value
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.dreamship.com/v1/categories/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->DREAMSHIP_TOKEN
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response, true);

        Cache::put($endpoint, $obj, $this->CACHE_TTL_SECOND);

        return response()->json([
            'success' => 1,
            'message' => 'getDreamshipCategories',
            'data' => $obj
        ]);
    }

    public function getDreamshipItems($category_id)
    {
        $endpoint =  'https://api.dreamship.com/v1/categories/' . $category_id . '/items/';

        if (Cache::has($endpoint)) {
            $cache_value = Cache::get($endpoint);
            return response()->json([
                'success' => 1,
                'message' => 'getDreamshipItems in cache',
                'data' => $cache_value
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->DREAMSHIP_TOKEN
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response, true);

        Cache::put($endpoint, $obj, $this->CACHE_TTL_SECOND);

        return response()->json([
            'success' => 1,
            'message' => 'getDreamshipItems',
            'data' => $obj
        ]);
    }

    public function getDreamshipItemDetail($item_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.dreamship.com/v1/items/' . $item_id . '/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->DREAMSHIP_TOKEN
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response,true);

        return response()->json([
            'success' => 1,
            'message' => 'getDreamshipItemDetail',
            'data' => $obj
        ]);
    }

    public function createDreamshipOrder(Request $request)
    {
        $result = [];

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $post_data = $request->get('postdata');
        $curl = curl_init();

        $post_data['test_order'] = true;

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.dreamship.com/v1/orders/',
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
                "Authorization: Bearer " . $this->DREAMSHIP_TOKEN
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result =  json_decode($response,true);

        if($result['status'] == 'error'){
            return response()->json([
                'success' => 0,
                'message' => $result['status'],
                'data' => $result
            ]);
        }

        $order->fulfillment_id = $result['id'];
        $order->fulfillment_by = 'dreamship';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => $result['status'],
            'data' => $result,
        ]);
    }
    // END Dreamship

    // CustomCat
    public function getCustomCatCategories()
    {
        $endpoint = 'https://customcat-beta.mylocker.net/api/v1/catalog?limit=250' . '&api_key=' . $this->CUSTOMCAT_API_KEY;

        if (Cache::has($endpoint)) {
            $cache_value = Cache::get($endpoint);
            return response()->json([
                'success' => 1,
                'message' => 'getCustomCatCategories in cache',
                'data' => $cache_value
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response, true);

        Cache::put($endpoint, $obj, $this->CACHE_TTL_SECOND);

        return response()->json([
            'success' => 1,
            'message' => 'getCustomCatCategories',
            'data' => $obj
        ]);
    }

    public function createCustomCatOrder(Request $request)
    {
        $result = [];

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $post_data = $request->get('postdata');
        $curl = curl_init();
        $post_data['api_key'] = $this->CUSTOMCAT_API_KEY;

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://customcat-beta.mylocker.net/api/v1/order/' . $request->get('order_id'),
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

        if (array_key_exists('ERROR', $result)) {
            return response()->json([
                'success' => 0,
                'message' => $result['ERROR'],
                'data' => $result
            ]);
        }

        $order->fulfillment_id = $result['ORDER_ID'];
        $order->fulfillment_by = 'customCat';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => $result['MSG'],
            'data' => $result,
        ]);
    }
    // End CustomCat

    // Teezily
    public function getTeezilyCategories()
    {
        $endpoint = 'https://plus.teezily.com/api/v2/catalog/products?limit=100';

        if (Cache::has($endpoint)) {
            $cache_value = Cache::get($endpoint);
            return response()->json([
                'success' => 1,
                'message' => 'getTeezilyCategories in cache',
                'data' => $cache_value
            ]);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: " . $this->TEEZILY_API_KEY
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $obj =  json_decode($response, true);

        Cache::put($endpoint, $obj, $this->CACHE_TTL_SECOND);

        return response()->json([
            'success' => 1,
            'message' => 'getTeezilyCategories',
            'data' => $obj
        ]);
    }

    public function createTeezilyOrder(Request $request)
    {
        $result = [];

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $post_data = $request->get('postdata');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://plus.teezily.com/api/v2/orders',
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
                "Authorization: " . $this->TEEZILY_API_KEY
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result =  json_decode($response,true);

        if (array_key_exists('errors', $result)) {
            return response()->json([
                'success' => 0,
                'message' => $result['errors'],
                'data' => $result
            ]);
        }

        $order->fulfillment_id = $result['id'];
        $order->fulfillment_by = 'customCat';
        $order->status = 1;
        $order->save();

        return response()->json([
            'success' => 1,
            'message' => 'success with Teezily id=' . $result['id'],
            'data' => $result,
        ]);
    }
    // End Teezily

    public function createGearmentOrder(Request $request)
    {
        $result = [];

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $post_data = $request->get('postdata');
        // $post_data['api_key'] = Auth::user()->gearment_api_key;
        // $post_data['api_signature'] = Auth::user()->gearment_api_signature;

        $post_data['api_key'] = "K02QrEl5XZopqBr8";
        $post_data['api_signature'] = "oPxDQqHo5lOAskPtTA189NnSFsMTjtXC";
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

        if(!Auth::user()->isAdmin()) return response()->json([
            'success' => 0,
            'message' => 'not permission - contact admin',
            'data' => $result,
        ]);

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

        $curl = curl_init();

        $post_data = $request->get('postdata');
        $post_data['send_shipping_notification'] = (bool)0; // 0/1 -> PHP false/true

        $json_post_data = json_encode($post_data, JSON_NUMERIC_CHECK);
        $json_post_data = str_replace('[string]', '', $json_post_data);
        // error_log($json_post_data);

        // [{"id":740379,"title":"Favorites Season","sales_channel":"disconnected"},{"id":3558273,"title":"API","sales_channel":"custom_integration"},{"id":3587006,"title":"TeeBiz","sales_channel":"custom_integration"}]

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/shops/3587006/orders.json",
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
