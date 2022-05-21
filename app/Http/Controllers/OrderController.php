<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\User;
use App\OrderItem;
use Carbon\Carbon;
use Auth;
use DB;
use App\AmazonShop;

class OrderController extends Controller
{
	protected $pagesize;

    public function __construct()
    {
        $this->middleware('auth.seller');
        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }
    
    public function createOrder($id){
        
        $order = Order::find($id);
        if(!$order) return response()->json(['status' => 'order not found']);
        
        $orderItems = OrderItem::where('order_id', $id)->get();
        if(!$orderItems) return response()->json(['success' => 'order not found']);
        
        return view('order.printer')
            ->with('order', $order)
            ->with('orderItems', $orderItems);
        
    }
    public function statistic(Request $request){
        
        if (!$request->has('reportrange')) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }else{
            $date = explode(" - ", $request->reportrange);
            $startDate = Carbon::parse($date[0]);
            $endDate = Carbon::parse($date[1]); 
        }
        
        if (!$request->has('owner_id')) {            
            $owner_condition = "=";
            $owner_id = $request->user()->id;
        } elseif ($request->owner_id == 0) {
            $owner_condition = ">";
            $owner_id = 0;
        } else {
            $owner_condition = "=";
            $owner_id = $request->owner_id;
        }
        
        $users = User::withTrashed()->orderBy('name','ASC')->get()->keyBy('id'); 
        
        $filters = [            
            'owner_id'=> $owner_id
        ];
        
        $orders = DB::table('orders')
            ->select(DB::raw('DATE(FROM_UNIXTIME(amz_order_date)) as date'), DB::raw('count(*) as total'))
            ->where('owner_id',$owner_condition,$owner_id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(amz_order_date))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        $costs = DB::table('orders')
            ->select(DB::raw('DATE(FROM_UNIXTIME(amz_order_date)) as date'), DB::raw('sum(fulfillment_cost) as total'))
            ->where('owner_id',$owner_condition,$owner_id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(amz_order_date))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        $orders_ids = Order::where('owner_id',$owner_condition,$owner_id)->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(amz_order_date))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->pluck('id')->toArray();
            
        
        $orders_units = OrderItem::whereNull('deleted_at')->whereIn('order_id',$orders_ids)->count();
        
            
        $revenues = DB::table('order_items')
            ->select(DB::raw('DATE(FROM_UNIXTIME(amz_order_date)) as date'), DB::raw('sum(totalAmount) as total'))
            ->whereNull('deleted_at')
            ->whereIn('order_id',$orders_ids)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        return view('order.statistic')
            ->with('startDate',$startDate)
            ->with('endDate',$endDate)
            ->with('orders',$orders)
            ->with('costs',$costs)
            ->with('orders_units',$orders_units)
            ->with('users',$users)
            ->with('owner_id', $owner_id)
            ->with('revenues',$revenues);
    }
    
    public function index(Request $request)
    {
    	
    	if (!$request->has('keyword')) {
            $keyword = "";
        } else {
            $keyword = $request->keyword;
        }

        if(!$request->has('brand_name')){
        	$brand_name = "all";
        }else{
            $brand_name = $request->brand_name;
        }

        if (!$request->has('status')) {
            $status_condition = ">=";
            $status = 0;
            $filter_status = 4;
        } elseif ($request->status == 4) {
            $status_condition = ">=";
            $status = 0;
            $filter_status = 4;
        } else {
            $status_condition = "=";
            $status = $request->status;
            $filter_status = $status;
        }
        
        if(!$request->has('fulfillment')){
            $fulfillment = "all";
            $fcondition = "<>";
        }elseif($request->fulfillment == "all"){
            $fulfillment = "all";
            $fcondition = "<>";
        }else{
            $fulfillment = $request->fulfillment;
            $fcondition = "=";
        }
        
        $brand = Order::groupBy('brand')->pluck('brand')->toArray();
        
        if(!$request->has('seller')){
            if(in_array(Auth::user()->id,[1])){
                $seller = 0;
                $seller_condition = "<>";
            }else{
                $seller = Auth::user()->id;
                $seller_condition = "=";
                $brand = Order::where('owner_id',Auth::user()->id)->groupBy('brand')->pluck('brand')->toArray();
            }
        }elseif($request->seller == 0){
            if(in_array(Auth::user()->id,[1])){
                $seller = 0;
                $seller_condition = "<>";
            }else{
                return response()->json(['message'=>"Access Denied"]);
            }
        }else{
            if(in_array(Auth::user()->id,[$request->seller,1])){
                $seller = $request->seller;
                $seller_condition = "=";
                $brand = Order::where('owner_id',$request->seller)->groupBy('brand')->pluck('brand')->toArray();
            }else{
                return response()->json(['message'=>"Access Denied"]);
            }
        }
        
        if($brand_name != "all"){   // by brand name
            $data = Order::where('brand', $brand_name)->where('status', $status_condition, $status)->where('fulfillment_by',$fcondition,$fulfillment)->where('owner_id',$seller_condition,$seller)
                    ->where(function ($query) use ($keyword) {
                        $query->where('amz_order_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('full_name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('fulfillment_id', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        }else{  // all brand name
            $data = Order::where('status', $status_condition, $status)->where('fulfillment_by',$fcondition,$fulfillment)->where('owner_id',$seller_condition,$seller)
                    ->where(function ($query) use ($keyword) {
                        $query->where('amz_order_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('full_name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('fulfillment_id', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        }
        
        $filters = [
            'brand_name' => $brand_name,
            'keyword' => $keyword,
            'status' => $filter_status,
            'fulfillment' => $fulfillment,
            'seller' => $seller,
        ];

        $owner_ids = Order::groupBy('owner_id')->pluck('owner_id')->toArray();
        $sellers = User::whereIn('id', $owner_ids)->get();
        
    	return view('order.index')
    		->with('data', $data)
    		->with('filters', $filters)
    		->with('sellers', $sellers)
    		->with('brand', $brand);
    }
    
    public function ajaxDelete(Request $request)
    {

        $order = Order::find($request->id);

        if(!$order) return response()->json([
            'success' => 0,
            'message' => 'not found order!',
        ]);
        if(in_array(Auth::user()->id,[1,13])){
            $order -> delete();
            return response()->json([
                'success' => 1,
                'message' => 'orders was deleted',
            ]);
        }else{
            return response()->json([
                'success' => -1,
                'message' => 'access denied',
            ]);
        }
        
    }
    
    private function teescape($fulfillment_id, $id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://teescape.com/active/shopify/OrderDetailRow.asp?o=".$fulfillment_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,    // fix 500
            CURLOPT_SSL_VERIFYPEER => false,    // fix 500
            CURLOPT_PROXY => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cookie: ".Auth::user()->cookie.""
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        if($response == "ERROR:Invalid Request") return [
            'success' => -1,
            'data' => 'ERROR:Invalid Request',
        ];
        
        $usps = array();
		$usps[0] = '(94|93|92|94|95)[0-9]{20}';
		
        $ups = array();
		$ups[0] = '(82)[0-9]{16}';

		
		$order = Order::find($id);
		
		preg_match_all('/<b>([0-9.]+)<\/b>/', $response, $arr);
		$cost = array_key_exists(0, $arr) ? strip_tags($arr[0][0]) : 0;
		
        if (preg_match('/('.$usps[0].')/', $response, $matches))
		{
		    $carrier = "USPS";
		    $order->update([
                'tracking_number' => $matches[0],
                'carrier' => 'usps',
                'fulfillment_cost' => $cost
            ]);
            // print_r('USPS: '.$matches[0]);
		}elseif(preg_match('/('.$ups[0].')/', $response, $matches)){
		    $order->update([
                'tracking_number' => $matches[0],
                'carrier' => 'ups',
                'fulfillment_cost' => $cost
            ]);
            // print_r('UPS: '.$matches[0]);
		}else{
		    $order->update([
                'fulfillment_cost' => $cost
            ]);
            // print_r('On Order');
		}
    }
   

    private function printify($fulfillment_id, $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/shops/".Auth::user()->printify_shopid."/orders/".$fulfillment_id.".json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRhNGZkZTcyMGJhNDhiZTY2ODFkYjE1YTU2NDgyNGYyYzk4MDczMTEwODQzZDZlOWFlMzY2Yzk5MWI4MWRlMTZkNzBiZjY0NWE2MzFmNTkyIn0.eyJhdWQiOiIzN2Q0YmQzMDM1ZmUxMWU5YTgwM2FiN2VlYjNjY2M5NyIsImp0aSI6IjRhNGZkZTcyMGJhNDhiZTY2ODFkYjE1YTU2NDgyNGYyYzk4MDczMTEwODQzZDZlOWFlMzY2Yzk5MWI4MWRlMTZkNzBiZjY0NWE2MzFmNTkyIiwiaWF0IjoxNjIzNzIxMjU3LCJuYmYiOjE2MjM3MjEyNTcsImV4cCI6MTY1NTI1NzI1Nywic3ViIjoiNjMwNDEyNSIsInNjb3BlcyI6WyJzaG9wcy5tYW5hZ2UiLCJzaG9wcy5yZWFkIiwiY2F0YWxvZy5yZWFkIiwib3JkZXJzLnJlYWQiLCJvcmRlcnMud3JpdGUiLCJwcm9kdWN0cy5yZWFkIiwicHJvZHVjdHMud3JpdGUiLCJ3ZWJob29rcy5yZWFkIiwid2ViaG9va3Mud3JpdGUiLCJ1cGxvYWRzLnJlYWQiLCJ1cGxvYWRzLndyaXRlIiwicHJpbnRfcHJvdmlkZXJzLnJlYWQiXX0.AN34EkTl4Glivn65TqZu1QOQzHp3pHahIEEo5QIeTdu1OOEFJu7O3DMs1NWqxN9OtH5s6lD2_Oazrx8kbPs",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $result =  json_decode($response,true);
        
        if (array_key_exists('error', $result)) return "ERROR:Invalid Request";
        
        $fulfillment_cost = ($result['total_price'] + $result['total_shipping'] + $result['total_tax'])*0.01;

        $order = Order::find($id);
        
        $order->update(['fulfillment_cost' => $fulfillment_cost]);
        
        if (array_key_exists('shipments', $result)) {
            
            $order->update([
                'carrier' => $result['shipments'][0]["carrier"], 
                'tracking_number' => $result['shipments'][0]["number"],
                'tracking_url' => $result['shipments'][0]["url"],
            ]);
        }
    }
    
    private function teezily($fulfillment_id, $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://plus.teezily.com/api/v1/orders/".$fulfillment_id.".json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_SSL_VERIFYHOST => false,    // fix 500
            // CURLOPT_SSL_VERIFYPEER => false,    // fix 500
            // CURLOPT_PROXY => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Token token=7cfa456c-445f-447f-953c-e6d9d365ec93",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result =  json_decode($response,true);
        
        if (array_key_exists('status', $result)) exit("response: ".$result['title']." (".$result['status'].")");
        
        $result['orders'][0]['state'] == "Cancel" ?  $fulfillment_cost = 0 : $fulfillment_cost = $result['orders'][0]['total_amount'];
        
        $order = Order::find($id);
        $order->update(['fulfillment_cost' => $fulfillment_cost]);
        
        if(!empty($result['orders'][0]['tracking_number'])){
            $order->update([
                'carrier' => $result['orders'][0]['tracking_number'][0]["carrier"], 
                'tracking_number' => $result['orders'][0]['tracking_number'][0]["tracking_number"],
            ]);
            
        }
    }
    
    private function gearment($fulfillment_id, $id)
    {
        $curl = curl_init();
        
        $gearment_api_key = Auth::user()->gearment_api_key;
        $gearment_api_signature = Auth::user()->gearment_api_signature;
        
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://account.gearment.com/api/v2/?act=order_info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>"{\r\n\t\"api_key\":\"$gearment_api_key\",\r\n\t\"api_signature\":\"$gearment_api_signature\",\r\n\t\"order_id\":\"$fulfillment_id\"\r\n}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);

        $obj =  json_decode($response,true);
        
        if($obj['status'] == 'error'){
            print_r($response);
            exit;
        }
        
        if($obj['status'] == 'success'){
            $obj['result']['ord_payment_status'] == "canceled" ?  $fulfillment_cost = 0 : $fulfillment_cost = ltrim($obj['result']['ord_total'], '$');
            
            $order = Order::find($id);
            $order->update([
                'carrier' => $obj['result']['trackings'][0]['tracking_company'], 
                'tracking_number' => $obj['result']['trackings'][0]['tracking_number'],
                'tracking_url' => $obj['result']['trackings'][0]['link_tracking'],
                'fulfillment_cost' => $fulfillment_cost,
            ]);
        }
    }

    private function printhigh($fulfillment_id, $id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://gateway.printhigh.com/api/orders/'.$fulfillment_id.'?token=99656ddaef6eb883bca21ee09e8e27825009b7f63c39f2694a1483931bd65729',
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

        if (array_key_exists("message", $obj)) {
            print_r($obj);
            exit;
        } 

        $order = Order::find($id);
            
        $order->update([
            'carrier' => $obj['orderPackages'][0]['serviceCode'], 
            'tracking_number' => $obj['orderPackages'][0]['trackingNumber'],
            'tracking_url' => $this->getTrackingLink($obj['orderPackages'][0]['carrierCode'], $obj['orderPackages'][0]['trackingNumber']),
            'fulfillment_cost' => $obj['order']['totalCost'],
        ]);

    }

    public function ajaxTeescape(Request $request)
    {
        
        $success = 1;
        $message = 'get order detail #'.$request->id;

        if (!$request->id) return response()->json([
            'success' => 0,
            'message' => 'order id is null',
        ]);

        $order = Order::find($request->id);
        
        if(!$order){
            $success = -1;
            $message = 'order not found';
        }else{
                        
            if($order->fulfillment_id == null) return response()->json([
                'success' => 0,
                'message' => 'new order',
            ]);
            
            switch ($order->fulfillment_by) {
                case 'printify':
                    $this->printify($order->fulfillment_id, $request->id);
                    break;
                case 'teezily':
                    $this->teezily($order->fulfillment_id, $request->id);
                    break;
                case 'gearment':
                    $this->gearment($order->fulfillment_id, $request->id);
                    break;
                case 'teescape':
                    $this->teescape($order->fulfillment_id, $request->id);
                    break;
                case 'printhigh':
                    $this->printhigh($order->fulfillment_id, $request->id);
                    break;
                default:
                    $message = 'default';
                    break;
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
    
    public function editShipment(Request $request)
    {
        $success = 1;
        $data = [];

        if(!$request->has('brand')) return response()->json([
            'success' => 0,
            'data' => $data,
            'message' => 'miss brand name',
        ]);
        
        $brand = $request -> brand;
        $orders = Order::where('carrier','usps')->where('status',1)->whereNotNull('tracking_number')->where('brand', $brand)->inRandomOrder()->first();

        if(!$orders)
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => 'not exists (not found)',
            ]);

        $data = $orders;

        return response()->json([
            'success' => 1,
            'data' => $data,
            'message' => 'OK',
        ]);

    }

    public function orderStatus(Request $request)
    {
    	if(!$request->has('order_id')) return response()->json([
            'success' => 0,
            'message' => 'miss order_id!',
        ]);

        Order::where('amz_order_id', $request->order_id)->update(['status' => 2]);
        
        return response()->json([
            'success' => 1,
            'message' => 'OK',
        ]);
    }
    
    public function ajaxGetOrder(Request $request){        
        //json structure
        $success = 1;
        $data = [];
        $item = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);        
                
        $id = $request->id;        

        $order = Order::find($id);
        if (!$order) return response()->json([
            'success' => 0,
            'data' => $data
        ]); 

        $data = $order;
        $item = OrderItem::where('order_id',$id)->get();

        return response()->json([
            'success' => $success,
            'data' => $data,
            'item' => $item
        ]);
    }

    public function ajaxUpdateOrder(Request $request){      
          
        //json structure
        $success = 1;
        $data = [];
        if (!$request->design_id) 
        return response()->json([
            'success' => 0,
            'data' => $data,
        ]);   

        $id = $request->design_id;
        $order = Order::find($id);

        if (!$order) 
            return response()->json([
                'success' => 0,
                'data' => $data,
            ]);

        $order->fulfillment_id = trim($request->fulfillment_id);   
        if($request->fulfillment_id != "" && $order -> status == 0){
            $order -> status = 1;
        }else{
            $order->status = $request->order_status;
        }
        $order->fulfillment_by = trim($request->fulfillment_by);        
        $order->carrier = trim($request->carrier);        
        $order->tracking_number = trim($request->tracking_number);      
        $order->note = $request->note;
        
        $order->save();
        
        return response()->json([
            'success' => $success,
            'data' => $order
        ]);
    }
    
    public function getAmzOrder(Request $request)
    {		
		if (!$request->order_id || $request->order_id == "") return response()->json([
            'success' => 0,
            'message' => 'not found order_id or order_id is null',
        ]);  
        
        if (Order::where('amz_order_id', '=', $request->order_id)->exists()) {
			// user found
			return response()->json([
	            'success' => 0,
	            'message' => 'duplicate',
	        ]);
		}else{

			$order =  new Order();
			$order -> amz_order_id = $request->order_id;
			$order -> amz_order_date = $request->order_date;
			$order -> brand = trim($request->brand);
			$amz_shop = AmazonShop::where('shop_name', trim($request->brand))->first();
            if($amz_shop){
                $order->owner_id = $amz_shop->owner_id;
            }
			$order -> full_name = $request->full_name;
			$order -> address_1 = $request->address_1;
			$order -> address_2 = $request->address_2;
			$order -> city = $request->city;
			$order -> state = $request->state;
			$order -> zip_code = $request->zip_code;
			$order -> save();
            
            if(!$request->items) return response()->json([
                'success' => 0,
                'message' => 'requeest: items is null',
            ]);  

			foreach ($request->items as $item) {
				$orderItem = new OrderItem();
				$orderItem -> order_id = $order -> id;
				$orderItem -> asin = $item["asin"];
				$orderItem -> product_name = $item["productName"];
				$orderItem -> sku = $item["sku"];
				$orderItem -> thumbnail = $item["avatar"];
				$orderItem -> quantity = $item["quantity"];
				$orderItem -> price = $item["unitPrice"];
				$orderItem -> totalAmount = $item["totalAmount"];
				$orderItem -> shippingAmount = $item["shippingAmount"];
                $orderItem -> amz_order_date = $request->order_date;
				if(!empty($item['customizationGroups'])){
				    $newArr = array_values($item["customizationGroups"][0]);
                    $orderItem -> style = $newArr[0];
                    if(array_key_exists(1, $newArr)){
                        $orderItem -> size = $newArr[1];
                    }else{
                        $orderItem -> size = "";
                    }
                    //$orderItem -> size = $newArr[1];
                    if(array_key_exists(2, $newArr)){
                        $orderItem -> color = $newArr[2];
                    }
                    if(array_key_exists(3, $newArr)){
                        $orderItem -> customization = $newArr[3]."; ";
                    }
                    if(array_key_exists(4, $newArr)){
                        $orderItem -> customization .= $newArr[4]."; ";
                    }
                    if(array_key_exists(5, $newArr)){
                        $orderItem -> customization .= $newArr[5]."; ";
                    }
                    if(array_key_exists(6, $newArr)){
                        $orderItem -> customization .= $newArr[6]."; ";
                    }
                    if(array_key_exists(7, $newArr)){
                        $orderItem -> customization .= $newArr[7]."; ";
                    }
                }
                if($item["quantity"] > 0){
				$orderItem -> save();
                }
			}

			return response()->json([
	            'success' => 1,
	            'message' => 'OK',
	        ]);
		}
    }
    
    public function getTrackingLink($carrier, $trackingNumber) {
        if ($carrier == "USPS" || $carrier == 'stamps_com' || $carrier == 'OSMWorldwide' || $carrier == 'usps_first_class_mail') {
            return 'https://tools.usps.com/go/TrackConfirmAction_input?strOrigTrackNum='.$trackingNumber;
        }
        if ($carrier == "UPS") {
            return 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='.$trackingNumber;
        }
        if ($carrier == "FedEx") {
            return 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber='.$trackingNumber;
        }
        if ($carrier == "DHLGlobalmailInternational" || $carrier == "DHLGlobalMail") {
            return 'https://webtrack.dhlglobalmail.com/?trackingnumber='.$trackingNumber;
        }
            return '#';
    }   
   
}
