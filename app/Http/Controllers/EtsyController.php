<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\EtsyShop;
use OAuth;
use Illuminate\Support\Facades\Redirect;
use App\Collection;
use App\CollectionDesigns;
use App\Design;
use App\CollectionMockups;
use App\Mockup;
use App\CollectionsEtsy;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\EtsyOrder;
use App\EtsyOrderItem;
use DB;

class EtsyController extends Controller
{
    
    private $consumer_key = '6shkxdetr15rsb3mhnlhp3pk';
    private $consumer_secret= '9mi6e1n2cy';

    public function __construct()
    {
        $this->middleware('auth.seller');

        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }
    
    public function createOrder($id){

        $order = EtsyOrder::find($id);
        if(!$order) return response()->json(['status' => 'order not found']);

        $orderItems = EtsyOrderItem::where('receipt_id', $order->receipt_id)->get();
        if(!$orderItems) return response()->json(['success' => 'order not found']);

        return view('etsy.printer')
            ->with('order', $order)
            ->with('orderItems', $orderItems);

    }
    
    public function findAllShopListingsActive($id){
        
        $etsy_shop = EtsyShop::find($id);
        if(!$etsy_shop) return;
        
        $access_token = "9feda0a842bad1db3f1d6e5edd6ba7";
        $access_token_secret = "b2d21e99a1";
        
        $consumer_key = "vhx07ia8ezdng3grm9owxt7i";
        $consumer_secret= "mzcihzkczt";
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {
            $shop_id = $etsy_shop->shop_id;
            
            $url = "https://openapi.etsy.com/v2/shops/".$shop_id."/listings/active";    //	/shops/:shop_id/listings/active
            
            $params = array('shop_id' => $shop_id);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            
            $jsonObject = json_decode($json, true);

            foreach($jsonObject['results'] as $obj){
                if(!CollectionsEtsy::where('listing_id',$obj["listing_id"])->exists()){
                    print_r($obj["listing_id"]."</br>");
                    CollectionsEtsy::create([
                        'shop_id'    => $id,
                        'owner_id'    => $etsy_shop->owner_id,
                        'design_id'    => 0,
                        'collection_id'    => 0,
                        'listing_id'    => $obj["listing_id"],
                    ]);
                }
                
            }
            
            // $listing_id = $obj["listing_id"];
            // $listing_id = 1057995656;
            // $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/images";
            // $params = array('listing_id' => $listing_id);
        
            // $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            // $json = $oauth->getLastResponse();
            // print_r($json);
                
            return response()->json([
                'success' => 1,
                'count' => $jsonObject['count']
            ]);
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        }
    }
    
    public function getInventory() {
        $listing_id = '921652733';
        
        $access_token = "a9deffb4bef02971b7f8a8f4bd7203";
        $access_token_secret = "4ef61524de";
        
        $consumer_key = "07aroqck70mssrsxu0ua8iyb";
        $consumer_secret= "ok6k6d43r6";
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);


        try {
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/inventory";
            $params = array('listing_id' => $listing_id);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            print_r($json);
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        }
    } 
    
    public function getListing() {
        $listing_id = '865485361';
        
        $access_token = "325e4f5559d4eaea1b95fc5a8c7cc7";
        $access_token_secret = "74164a1f4b";
        
        $consumer_key = "5du361fd1p8p5hkcbv4oy2re";
        $consumer_secret= "a3twmkwm45";
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);


        try {
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id;
            $params = array('listing_id' => $listing_id);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            print_r($json);
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        }
    }    
    
    public function findAllListingImages() {
        $listing_id = '818949030';

        // $access_token = $request->session()->get('oauth_token');
        // $access_token_secret = $request->session()->get('oauth_token_secret');
        
        $access_token = "3044fab7d793323361013aba87bc5f";
        $access_token_secret = "e75771722b";

        // $consumer_key = $this->consumer_key;
        $consumer_key = "zjiu85avpsbynfpzm7dgwh8y";
        // $consumer_secret= $this->consumer_secret;
        $consumer_secret= "xrr9s180zt";
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);


        try {
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/images";
            $params = array('listing_id' => $listing_id);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            print_r($json);
        
        } catch (OAuthException $e) {
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        }
    }   
    
    public function deleteListing(Request $request)
    {
        if (!$request->has('listing_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        $listing_id = $request->listing_id;
        if (!$listing_id) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }

        $colEtsy = CollectionsEtsy::where('listing_id', $listing_id)->first();
        if (!$colEtsy) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($colEtsy->shop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        $colEtsy->delete();
        return response()->json([
            'success' => 1,
            'data' => 'OK',
        ]); 
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);
        
        try {
            
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id;
            $params = array('listing_id' => $listing_id);        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_DELETE);
        
            $json = $oauth->getLastResponse();
            // print_r($json);
            // $colEtsy->delete();

            return response()->json([
                'success' => 1,
                'data' => $json,
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }        
    }

    public function activeListing(Request $request)
    {
        if (!$request->has('listing_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        $listing_id = $request->listing_id;
        if (!$listing_id) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }

        $colEtsy = CollectionsEtsy::where('listing_id', $listing_id)->first();
        if (!$colEtsy) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($colEtsy->shop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        // $colEtsy->delete();
        // return ;
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);
        
        try {
            
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id;
            $params = array(
                'listing_id' => $listing_id, 
                'state' => "active"  // inactive
            );        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_PUT);
        
            $json = $oauth->getLastResponse();
            // print_r($json);
            // $colEtsy->delete();
            $colEtsy->state = 1;  // active listing
            $colEtsy->save();
            return response()->json([
                'success' => 1,
                'data' => $json,
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }
    }
    
    public function inactiveListing(Request $request)
    {
        if (!$request->has('listing_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        $listing_id = $request->listing_id;
        if (!$listing_id) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }

        $colEtsy = CollectionsEtsy::where('listing_id', $listing_id)->first();
        if (!$colEtsy) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($colEtsy->shop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        // $colEtsy->delete();
        // return ;
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);
        
        try {
            
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id;
            $params = array(
                'listing_id' => $listing_id, 
                'state' => "inactive"  // inactive
            );        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_PUT);
        
            $json = $oauth->getLastResponse();
            // print_r($json);
            // $colEtsy->delete();
            $colEtsy->state = -1;  // active listing
            $colEtsy->save();
            return response()->json([
                'success' => 1,
                'data' => $json,
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }
    }
    
    public function getProducts($id){

        $colEtsy = CollectionsEtsy::where('shop_id', $id)->orderby('created_at','DESC')->paginate(40);
        $data = [
            'designs'=> array(),
        ];

        return view('etsy.products')
            ->with('colEtsy', $colEtsy)
            ->with('data', $data);
    }

    public function connect(Request $request, $etsyshop_id){
        
        // instantiate the OAuth object
        // OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET are constants holding your key and secret
        // and are always used when instantiating the OAuth object 
        
        $etsyshop = EtsyShop::find($etsyshop_id);

        if (!$etsyshop) return redirect()->route('etsy.index',['message'=>'etsy shop not found!']);

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
        
        if ($consumer_key == "" || $consumer_secret == "") return redirect()->route('etsy.edit',['id' => $etsyshop_id, 'message'=>'consumer_key or consumer_secret is null!']);

        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        // $oauth->enableDebug();

        try {
            // make an API request for your temporary credentials
            $req_token = $oauth->getRequestToken("https://openapi.etsy.com/v2/oauth/request_token?scope=email_r%20listings_r%20listings_w%20transactions_r%20transactions_w%20shops_rw%20listings_d", 
                asset('') . "etsy/connect_callback/?consumer_key=$consumer_key&consumer_secret=$consumer_secret&etsyshop_id=$etsyshop_id");

            $oauth_token_secret = $req_token['oauth_token_secret'];
            $request->session()->put('oauth_token_secret', $oauth_token_secret);

            return Redirect::away($req_token['login_url']);

        } catch (OAuthException $e) {
            // error_log($e->getMessage());
            // error_log(print_r($oauth->getLastResponse(), true));
            // error_log(print_r($oauth->getLastResponseInfo(), true));
            // exit;
            return redirect()->action('EtsyController@edit', $etsyshop_id);
        }
        return redirect()->action('EtsyController@edit', $etsyshop_id);
    } 
    
    public function connect_callback(Request $request) {
        
        $etsyshop_id = $request->etsyshop_id;
        
        $etsyshop = EtsyShop::find($etsyshop_id);
        
        if (!$etsyshop) return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);

        // $consumer_key = $etsyshop->consumer_key;
        // $consumer_secret= $etsyshop->consumer_secret;

        $request_token = $request->oauth_token;
        $verifier = $request->oauth_verifier;

        $request_token_secret = $request->session()->get('oauth_token_secret');

        $consumer_key = $request->consumer_key;
        $consumer_secret= $request->consumer_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->enableDebug();
        $oauth->disableSSLChecks();
        $oauth->setToken($request_token, $request_token_secret);

        try {
            // set the verifier and request Etsy's token credentials url
            $acc_token = $oauth->getAccessToken("https://openapi.etsy.com/v2/oauth/access_token", null, $verifier, "GET");

            $oauth_token = $acc_token['oauth_token'];
            $oauth_token_secret = $acc_token['oauth_token_secret'];

            $request->session()->put('oauth_token', $oauth_token);
            $request->session()->put('oauth_token_secret', $oauth_token_secret);    
            
            $etsyshop->access_token = $oauth_token;
            $etsyshop->access_token_secret = $oauth_token_secret;
            $etsyshop->save();
            
            return redirect()->action('EtsyController@updateShopInfo', $etsyshop_id);
            //return redirect()->action('EtsyController@upload', $etsyshop_id);            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
        }
    }
    
    public function updateShopInfo(Request $request, $id) {
        $etsyshop = EtsyShop::find($id);
        if (!$etsyshop) //return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        $success = 0;

        try {
            $data = $oauth->fetch("https://openapi.etsy.com/v2/users/__SELF__", null, OAUTH_HTTP_METHOD_GET);
            $json = $oauth->getLastResponse();
            $jsonObject = json_decode($json, true);
            $etsyshop->user_id = $jsonObject['results'][0]['user_id'];
            $etsyshop->login_email = $jsonObject['results'][0]['primary_email'];
            $etsyshop->save();

            // return response()->json([
            //     'success' => 1,
            //     'data' => $json,
            // ]);   
            
            $success = 1;

        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));

            $success = 0;
            return response()->json([
                'success' => -2, //exceptions
                'data' => 'Error: ' + $e->getMessage()
            ]);
        }

        if ($success) {

            $oauth = new OAuth($consumer_key, $consumer_secret);
            $oauth->disableSSLChecks();
            $oauth->enableDebug();
            $oauth->setToken($access_token, $access_token_secret);
            try {        
                $url = "https://openapi.etsy.com/v2/users/".$etsyshop->user_id."/shops";
                $params = array(
                            'limit' => 1,
                        );
            
                $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
            
                $json = $oauth->getLastResponse();
                $jsonObject = json_decode($json, true);
                $etsyshop->shop_id = $jsonObject['results'][0]['shop_id'];
                $etsyshop->save();

                // return redirect()->action('EtsyController@upload', $id);   
                return redirect()->action('EtsyController@edit', $id);   
                
                // return response()->json([
                //     'success' => 1,
                //     'data' => $etsyshop,
                // ]); 
            
            } catch (OAuthException $e) {
                // You may want to recover gracefully here...
                print $oauth->getLastResponse()."\n";
                print_r($oauth->debugInfo);
                die($e->getMessage());

                return response()->json([
                    'success' => -2, //exceptions
                    'data' => 'Error: ' + $e->getMessage()
                ]);
            }
        }
    }
    
    public function store(Request $request)
    {
        $id = $request->user()->id;
        $shop_url = $request->shop_url;
        if (!$shop_url || !$id) return redirect()->action('EtsyController@index', ['message'=>'shop url required']);

        $data = EtsyShop::create([
            'owner_id' => $id,
            'shop_url' => $shop_url,
            'shop_name' => basename($shop_url),
            'paypal_email' => $request->has('paypal_email') ? $request->paypal_email : null,
            'login_email' => $request->has('login_email') ? $request->login_email : null,
        ]);

        return redirect()->action('EtsyController@edit', ['id' => $data->id, 'message'=>'shop created']);

    }

    public function clone(Request $request, $id)
    {
        $data = EtsyShop::find($id);
        $message = $request->has('message') ? $request->message : '';
        $message .= ' Clone completed!';

        if (!$data) return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);

        $new_shop = $data->replicate();
        $new_shop->owner_id = $request->user()->id;
        $new_shop->save();

        $collections = Collection::where('owner_id',$request->user()->id)->get()->keyBy('id');

        return view('etsy.edit')
            ->with('data', $new_shop)
            ->with('message', $message)
            ->with('collections', $collections);
    }

    public function edit(Request $request, $id)
    {
        $data = EtsyShop::find($id);
        
        // $message = $request->has('message') ? $request->message : '';

        if (!$data) return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);

        if ($data->owner_id != $request->user()->id) return redirect()->route('etsy.index',['message'=>'Not permission']);

        $collections = Collection::where('owner_id',$request->user()->id)->get()->keyBy('id');
        $users = User::where('is_seller',1)->get();
        return view('etsy.edit')
            ->with('data', $data)
            ->with('users', $users)
            ->with('collections', $collections);
    }
    
    public function ajaxDelete(Request $request)
    {      
        $id = $request->id;

        $shop = EtsyShop::find($id);

        if(!$shop) return response()->json([
            'success' => 0,
            'message' => 'etsy shop not found',
        ]);

        if (!$shop->isOwnerOrAdmin(Auth::user())) {
            return response()->json([
                'success' => 0,
                'message' => 'not permission',
            ]);
        }

        $shop->delete();

        return response()->json([
            'success' => 1,
            'message' => 'etsy shop deleted',
        ]);
    }
    
    public function ajaxArchive(Request $request)
    {      
        $id = $request->id;

        $shop = EtsyShop::find($id);

        if(!$shop) return response()->json([
            'success' => 0,
            'message' => 'etsy shop not found',
        ]);

        if (!$shop->isOwnerOrAdmin(Auth::user())) {
            return response()->json([
                'success' => 0,
                'message' => 'not permission',
            ]);
        }
        
        $shop->archived = !$shop->archived;
        $shop->save();

        return response()->json([
            'success' => 1,
            'message' => 'OK',
        ]);
    }
    
    public function update(Request $request)
    {
        $id = $request->id;
        if (!$id) return redirect()->route('etsy.index',['message'=>'Missing shop id!']);

        $data = EtsyShop::find($id);

        if (!$data) return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);

        if ($data->owner_id != $request->user()->id) return redirect()->route('etsy.index',['message'=>'Not permission']);

        $data->shop_url = $request->shop_url;
        $data->shop_name = $request->shop_name;

        $data->login_email = $request->login_email;
        $data->paypal_email = $request->paypal_email;

        $data->key_string = $request->key_string;
        $data->share_secret = $request->share_secret;
        $data->access_token = $request->access_token;
        $data->access_token_secret = $request->access_token_secret;
        $data->shipping_template_id = $request->shipping_template_id;
        $data->collection_id = $request->collection_id;
        $data->taxonomy_id = $request->taxonomy_id;

        $data->price = $request->price;
        $data->quantity = $request->quantity;
        $data->description = $request->description;
    
        $data->image_url_1 = $request->image_url_1;
        $data->image_url_2 = $request->image_url_2;
        $data->image_url_3 = $request->image_url_3;
        
        $data->image_id_1 = $request->image_id_1;
        $data->image_id_2 = $request->image_id_2;
        $data->image_id_3 = $request->image_id_3;
        
        $data->bank_account = $request->bank_account;
        $data->note = $request->note;
        
        // $data->tags = $request->tags;
        // $data->background_color = $request->background_color;
        // $data->watermark = $request->watermark;

        // $data->materials = $request->materials;
        // $data->state  = $request->state ;
        // $data->who_made = $request->who_made;
        // $data->when_made = $request->when_made;
        // $data->is_supply = $request->is_supply;
        // $data->taxonomy_id = $request->taxonomy_id;
        // $data->shop_section_id = $request->shop_section_id;
        // $data->processing_min = $request->processing_min;
        // $data->processing_max  = $request->processing_max;
        // $data->is_customizable = $request->is_customizable;
        
        if($request->is_active == 0){
            $data->is_active = $request->is_active;
            EtsyOrder::where('seller_user_id', $data->user_id)
                ->where('status', 0)
                ->update(['status' => 3]);
        }
        
        $data->is_active = $request->is_active;
        
        if($request->has('owner_id')){
            $data->owner_id = $request->owner_id;
        }

        $data->save();

        return redirect()->route('etsy.edit',['id' => $id, 'message'=>'shop updated!']);
    }

    function index(Request $request) {                 

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
        
        if (!$request->has('keyword')) {
            $keyword = "";
        } else {
            $keyword = $request->keyword;
        }
        
        $users = User::withTrashed()->orderBy('name','ASC')->get()->keyBy('id'); 
        
        $data = EtsyShop::where('owner_id', $owner_condition, $owner_id)
                        // ->where('status', $active_condition, $status)
                        ->where(function ($query) use ($keyword) {
                            $query->where('shop_name', 'LIKE', '%'.$keyword.'%')
                                ->orWhere('login_email', 'LIKE', '%'.$keyword.'%')
                                ->orWhere('bank_account', 'LIKE', '%'.$keyword.'%')
                                ->orWhere('note', 'LIKE', '%'.$keyword.'%');
                        })
                        ->orderBy('is_active','desc')->orderBy('updated_at','desc')->paginate(40);

        $message = $request->message;

        $filters = [            
            'owner_id'=> $owner_id,
            'keyword'=> $keyword,
        ];
        
        return view('etsy.index')
            ->with('data', $data)
            ->with('message', $message)
            ->with('owner_id', $owner_id)
            ->with('filters', $filters)
            ->with('users', $users);
    }

    function feedShop(Request $request) { 
        //json structure
        $success = 1;
        $message = 'Live';
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'message' => 'Missing data!',
            'data' => $data
        ]);
        
        $etsyshop = EtsyShop::find($request->id);
        
        if (!$etsyshop) {
            $success = -1;
            $message = 'Not found!';
            $data = null;
            
            $etsyshop->is_active = false;
            $etsyshop->save();
        } else {
            //to parse a webpage
            $content = $this->get_contents($etsyshop->shop_url);
            
            if (!$content) {
                $success = -2;
                $message = 'Uh Oh!';
                $data = null;

                $etsyshop->is_active = false;
                $etsyshop->save();
            }else{
                $data = $etsyshop;
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }

    function get_contents($url) {
        try {  
            $content = @file_get_contents($url, true); //getting the file content
            if($content==false)
            {
                //throw new Exception( 'Something really gone wrong');  
                return null;
            }
            return $content;
        } catch (Exception $e) {  
            echo $e->getMessage();  
            return null;
        }
    }

   //make mockups for each design in etsy shop's collection
    public function makemockups(Request $request, $id)
    {
        $etsyshop = EtsyShop::find($id);

        if (!$etsyshop) return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);

        if ($etsyshop->owner_id != $request->user()->id) return redirect()->route('etsy.index',['message'=>'Not permission']);

        $collection_id = $etsyshop->collection_id;
        
        $design_ids = CollectionDesigns::where('collection_id',$collection_id)->pluck('design_id')->toArray();

        $designs = Design::whereIn('id', $design_ids)->get();

        $collection = Collection::find($collection_id);

        $mockup_ids = CollectionMockups::where('collection_id',$id)->pluck('mockup_id')->toArray();

        $mockups = Mockup::whereIn('id', $mockup_ids)->get();

        
        $data = [
            'collection'=>$collection,
            'designs'=>$designs,
            'mockups'=>$mockups
        ];

        return view('etsy.listing')
            ->with('etsyshop', $etsyshop)
            ->with('data', $data)
            ->with('designs', $designs);
    }
    
    public function createListing(Request $request)
    {
        if (!$request->has('etsyshop_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing etsyshop_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($request->etsyshop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
            
        if (!$request->has('design_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing design_id'
            ]);
        }
        
        $design = Design::find($request->design_id);
        if (!$design) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Design not found.'
            ]);
        
        $tags_arr = explode(',', $design->tags);
        if(count($tags_arr) > 3){
            $tags = array();
            foreach($tags_arr as $tag){
                if(strlen($tag) > 3 && strlen($tag) <= 20){
                    if(!in_array($tag, $tags)){
                        $tags[] = trim($tag);
                    }
                }
            }
            
            $title_arr = array();
            $title_arr[] = $design ->title80;
            foreach(array_rand($tags_arr,2) as $element){
                $title_arr[] = $tags_arr[$element];
            }
            $title_arr[] = $design->id;
            $title = join(", ",$title_arr);
            
            $arr = array();
            if(count($tags)>13){
                foreach(array_rand($tags,13) as $element){
                    $arr[] = $tags[$element];
                }
            }else{
                $arr = $tags;
            }
            
            $etsy_tags = join(",",$arr);
            
        }else{
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'tags is invalid ('.strlen($design->tags).")"
            ]);
            exit;  
        }
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {
            $url = "https://openapi.etsy.com/v2/listings";

            $params = array(
                // 'title' => $design->title80.' '.$design->id.' '.Str::random(10),
                'title' => Str::title(substr($title,0,139)),
                'tags' => $etsy_tags,
                'description' => $design->title80.' - Buy it now !! '.$etsyshop->description,                
                // 'materials' => $etsyshop->materials,
                'price' => $etsyshop->price,
                'quantity' => $etsyshop->quantity,
                // 'quantity' => 0,
                'shipping_template_id' => $etsyshop->shipping_template_id,                
                'is_supply' => 0,
                'who_made' => "i_did",
                'when_made' => "made_to_order",
                // 'processing_min' => $etsyshop->processing_min,
                // 'processing_max' => $etsyshop->processing_max,
                // 'taxonomy_id' => 2078,                
                'taxonomy_id' => $etsyshop->taxonomy_id,         // T-Shirt    
                // taxonomy_id: 482,
                // taxonomy_path: [
                // "Clothing",
                // "Unisex Adult Clothing",
                // "Tops & Tees",
                // "T-shirts"
                // ]
                //'is_customizable' => $etsyshop->is_customizable,
                //'shop_section_id' => $etsyshop->shop_section_id,                
                'state' => "draft",
                'is_digital' => 0,  // true 1
            );      
            // 12/07/2021: 
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $json = $oauth->getLastResponse();
            $jsonObject = json_decode($json, true);
            
            // uploadListingImage
            // if(!empty($request->thumbnail)){
            //     $filename = str_replace(['https://s3.amazonaws.com/teejolly-prod'], '', $request->thumbnail);
            // }else{
            //     $filename = 'storage/' . $design->thumbnail;
            // }
            
            // $filename = 'storage/' . $design->thumbnail;
            // $filename = '/storage/images/tmp/200630/mcZfpQ-july-4th-juneteenth-day-my-ancestors-werent-free-in-1776-1249x1488.jpg';
            // $source_file = public_path() ."$filename"; // /home/rubxfbyg/public_html/public//storage/images/tmp/210713/muGQDZ-balenciaga-mode-dark-t-shirt-711x851.jpg
            // $mimetype = mime_content_type($source_file);
            
             // uploadListingImage
            $source_file = $request->thumbnail;
            $ch = curl_init($source_file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $mimetype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            //download file
            $dir = 'images/tmp/' . Carbon::now()->format('ymd');
            Storage::makeDirectory($dir);
            $filename = 'storage/'. $dir . '/m' . Str::random(5) .'-'. Str::slug($design->title80,'-')  . ".jpg";
            Image::make($request->thumbnail)->save($filename);
            $source_file = public_path() ."/$filename";
            
            // print_r($filename);
            // print_r($source_file);
            // exit();
            
            $url = "https://openapi.etsy.com/v2/listings/".$jsonObject['results'][0]['listing_id']."/images";
            $params = array('@image' => '@'.$source_file.';type='.$mimetype);
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $jsonListingImage = $oauth->getLastResponse();
            
            $etsyshopmockup = CollectionsEtsy::updateOrCreate(
                ['shop_id' => $request->etsyshop_id, 'owner_id' => $request->user()->id, 'design_id' => $request->design_id, 'collection_id' => $etsyshop->collection_id, 'main_image_url' => $request->thumbnail],
                ['listing_id' => $jsonObject['results'][0]['listing_id']]
            );
            
            return response()->json([
                'success' => 1,
                'jsonListingImage' => $jsonListingImage,
                'data' => ['listing_id' => $jsonObject['results'][0]['listing_id'], 'design_id' => $request->design_id],
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }

    }
    
    public function createListingDigital(Request $request)
    {
        if (!$request->has('etsyshop_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing etsyshop_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($request->etsyshop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
            
        if (!$request->has('design_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing design_id'
            ]);
        }
        
        $design = Design::find($request->design_id);
        if (!$design) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Design not found.'
            ]);
        
        if (!$design->filename) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Digital files not found.'
            ]);
        
        $tags_arr = explode(',', $design->tags);
        if(count($tags_arr) > 3){
            $tags = array();
            foreach($tags_arr as $tag){
                if(strlen($tag) > 1 && strlen($tag) <= 20){
                    // $tags[] = trim($tag);
                    if(!in_array($tag, $tags)){
                        $tags[] = trim($tag);
                    }
                }
            }
            // random title
            $title = ''; 
            while(strlen($title) < 90){
                $title .= $tags[array_rand($tags)].', ';
            }
            $new_title = str_replace(['t-shirt', 'shirt', 'tee', 'shirts', 't shirt', 'shirts'], 'png', $title);
            
            // random tags
            // if(count($tags)>0) $etsy_tags = implode(",", $tags);
            $etsy_tags = '';
            if(count($tags) > 0){
                $count = 0;
                $arr = array();
                while($count < 13){
                    $temp = $tags[array_rand($tags)];
                    if(!in_array($temp, $arr)){
                        $arr[] = $temp;
                    }
                    $count++;
                }
                $etsy_tags = implode(",", $arr);
            }

        }else{
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'redbubble tags is invalid ('.strlen($design->redbubble_design_tags).")"
            ]);
            exit;  
        }
        
        // print_r(Str::title(substr(rtrim($new_title, ", "),0,100))." - INSTANT DOWNLOAD - PNG Printable");
        // exit;
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {
            $url = "https://openapi.etsy.com/v2/listings";
            $params = array(
                'title' => Str::title(substr(rtrim($new_title, ", "),0,102))." - INSTANT DOWNLOAD - PNG Printable",
                // 'tags' => $design->tags,
                'tags' => $etsy_tags,
                'description' => $etsyshop->description,                
                // 'materials' => $etsyshop->materials,
                'price' => $etsyshop->price,
                'quantity' => $etsyshop->quantity,
                'shipping_template_id' => $etsyshop->shipping_template_id,                
                'is_supply' => 0,
                'who_made' => "i_did",
                'when_made' => "made_to_order",
                // 'processing_min' => $etsyshop->processing_min,
                // 'processing_max' => $etsyshop->processing_max,
                'taxonomy_id' => 2078,                
                //'is_customizable' => $etsyshop->is_customizable,
                //'shop_section_id' => $etsyshop->shop_section_id,                
                'state' => "draft",
                'is_digital' => 1,  // true 1
            );      
            
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $json = $oauth->getLastResponse();
            $jsonObject = json_decode($json, true);
            
            // uploadListingFile
            $filename = 'storage/'.$design->filename;
            $source_file = public_path() ."/$filename";
            $mimetype = mime_content_type($source_file);
            
            $url = "https://openapi.etsy.com/v2/listings/".$jsonObject['results'][0]['listing_id']."/files";
            $params = array('@file' => '@'.$source_file.';type='.$mimetype, 'name'=>basename($filename));
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $jsonListingFile = $oauth->getLastResponse();
            
            // uploadListingImage
            if(!empty($request->thumbnail)){
                $filename = str_replace(['https://admin.danangsunbay.com/public', 'http://admin.danangsunbay.com/public'], '', $request->thumbnail);
            }else{
                $filename = 'storage/' . $design->thumbnail;
            }
            // $filename = 'storage/' . $design->thumbnail;
            // $filename = '/storage/images/tmp/200630/mcZfpQ-july-4th-juneteenth-day-my-ancestors-werent-free-in-1776-1249x1488.jpg';
            $source_file = public_path() ."/$filename";
            $mimetype = mime_content_type($source_file);

            $url = "https://openapi.etsy.com/v2/listings/".$jsonObject['results'][0]['listing_id']."/images";
            $params = array('@image' => '@'.$source_file.';type='.$mimetype);
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $jsonListingImage = $oauth->getLastResponse();
            
            $etsyshopmockup = CollectionsEtsy::updateOrCreate(
                ['shop_id' => $request->etsyshop_id, 'owner_id' => $request->user()->id, 'design_id' => $request->design_id, 'collection_id' => $etsyshop->collection_id, 'main_image_url' => $request->thumbnail, 'is_digital'=>1],
                ['listing_id' => $jsonObject['results'][0]['listing_id']]
            );
            
            return response()->json([
                'success' => 1,
                // 'jsonListingFile' => $jsonListingFile,
                // 'jsonListingFile' => $jsonListingFile,
                'jsonListingImage' => $jsonListingImage,
                'data' => ['listing_id' => $jsonObject['results'][0]['listing_id'], 'design_id' => $request->design_id],
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }
    }
    
    public function createListingForMug(Request $request)
    {
        if (!$request->has('etsyshop_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing etsyshop_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($request->etsyshop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
            
        if (!$request->has('design_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing design_id'
            ]);
        }
        
        $design = Design::find($request->design_id);
        if (!$design) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Design not found.'
            ]);
        
        $tags_arr = ["Funny Coffee Mug","Funny Mug","Funny Gift","Personalized Coffee Mug","Personalized Name Coffee Cup",
            "Gift for Friend","Birthday Gift","Create Your Own Mug","Personalised Text Mug","Funny Gifts","Custom Quote Gifts","Custom Mug","Personalised Mug"];
        
        $tags = array();
        foreach($tags_arr as $tag){
            if(strlen($tag) <= 20){
                if(!in_array($tag, $tags)){
                    $tags[] = trim($tag);
                }
            }
        }
        
        $title_arr = array();
        $title_arr[] = $design ->title." Ceramic Coffee Mug";
        foreach(array_rand($tags_arr,2) as $element){
            $title_arr[] = $tags_arr[$element];
        }
        $title_arr[] = $design->id;
        $title = join(", ",$title_arr);
            
        $arr = array();
        if(count($tags)>13){
            foreach(array_rand($tags,13) as $element){
                $arr[] = $tags[$element];
            }
        }else{
            $arr = $tags;
        }
        
        $etsy_tags = join(",",$arr);
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {
            $url = "https://openapi.etsy.com/v2/listings";

            $params = array(
                // 'title' => $design->title80.' '.$design->id.' '.Str::random(10),
                'title' => Str::title(substr($title,0,139)),
                'tags' => $etsy_tags,
                'description' => $design->title.' - Buy it now !! '.$etsyshop->description,                
                'price' => $etsyshop->price,
                'quantity' => $etsyshop->quantity,
                'shipping_template_id' => $etsyshop->shipping_template_id,                
                'is_supply' => 0,
                'who_made' => "i_did",
                'when_made' => "made_to_order",
                'taxonomy_id' => $etsyshop->taxonomy_id,         // T-Shirt    
                'state' => "draft",
                'is_digital' => 0,  // true 1
            );      
            
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $json = $oauth->getLastResponse();
            $jsonObject = json_decode($json, true);
            
            // uploadListingImage
            if(!empty($request->thumbnail)){
                $filename = str_replace(['https://admin.danangsunbay.com/public', 'http://admin.danangsunbay.com/public'], '', $request->thumbnail);
            }else{
                $filename = 'storage/' . $design->thumbnail;
            }
            $source_file = public_path() ."/$filename";
            $mimetype = mime_content_type($source_file);

            $url = "https://openapi.etsy.com/v2/listings/".$jsonObject['results'][0]['listing_id']."/images";
            $params = array('@image' => '@'.$source_file.';type='.$mimetype);
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $jsonListingImage = $oauth->getLastResponse();
            
            $etsyshopmockup = CollectionsEtsy::updateOrCreate(
                ['shop_id' => $request->etsyshop_id, 'owner_id' => $request->user()->id, 'design_id' => $request->design_id, 'collection_id' => $etsyshop->collection_id, 'main_image_url' => $request->thumbnail],
                ['listing_id' => $jsonObject['results'][0]['listing_id']]
            );
            
            // update Inventory
            $price = $etsyshop->price;   
            $quantity = $etsyshop->quantity;
            
            $liststyles = [
            	'Ceramic Mug' => 0,
            	'Two Tone Mug' => 3
            ];
            
            $listsizes = [
                '11Oz' => 0,
                '15Oz' => 2,
            ];
            
            $listcolors = [
            	'Black' => 0,
            	'White' => 0,
            	'Navy' => 0,
            	'Red' => 0,
            	'Maroon' => 0,
            	'Orange' => 0,
            	'Yellow' => 0,            
            	'Green bottle' => 0,	
            	'Pink' => 0,
            	'Sport Grey' => 0,
            ];
            
            $property_stylesize = 514;
            $property_color = 513;
            
            $prices = array();
            $stylesizes = array();      
            foreach ($liststyles as $style => $price_style) {
                foreach ($listsizes as $size => $price_size) {
                    $stylesize = $style . ' - ' . $size;
                    $prices[$stylesize] = $price_style + $price_size + $price;
    
                    $stylesizes[] = [
                        'property_id'   => $property_stylesize,
                        'property_name' => 'Styles & Capacity',
                        'values'        => [$stylesize],
                    ];
                }
            }
            
            $colors = array();
            foreach ($listcolors as $color => $price_color) {
                $colors[] = [
                    'property_id' => $property_color,
                    'property_name' => 'Colors',
                    'values'        => [$color],
                ];
            }
    
            $products = array();
            foreach ($stylesizes as $stylesize) {
                foreach ($colors as $color) {
                    $products[] = [
                        'property_values' => [$stylesize,$color],
                        'sku'             => '',
                        'offerings'       => [
                                                [
                                                'price'      => $prices[$stylesize['values'][0]],
                                                'quantity'   => $quantity,
                                                'is_enabled' => 1
                                            ]
                                        ]
                    ];
                }
            }
            
            $listing_id = $jsonObject['results'][0]['listing_id'];
            
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/inventory";
            $params = array(
                'listing_id' => $listing_id,
                'products'             => json_encode($products),
                'price_on_property'    => $property_stylesize,
                'quantity_on_property' => $property_stylesize,
                'sku_on_property'      => ''
            );
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_PUT);
        
            $json = $oauth->getLastResponse();
            
            return response()->json([
                'success' => 1,
                'jsonListingImage' => $jsonListingImage,
                'data' => ['listing_id' => $jsonObject['results'][0]['listing_id'], 'design_id' => $request->design_id],
            ]); 
            
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }

    }
    
    public function updateInventory(Request $request) {
        if (!$request->has('listing_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        $listing_id = $request->listing_id;
        if (!$listing_id) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $colEtsy = CollectionsEtsy::where('listing_id', $listing_id)->first();
        if (!$colEtsy) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        if ($colEtsy->is_digital == 1) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'This product is digital'
            ]);
        }
        
        $etsyshop = EtsyShop::find($colEtsy->shop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
            
        // input
        $price = $etsyshop->price;   
        $quantity = $etsyshop->quantity;
        
        $liststyles = [
            'Unisex T-Shirt' => 0,
        ];
        
        $listsizes = [
            'XS' => 0,
            'S' => 0,
            'M' => 2,
            'L' => 3,
            'XL' => 4,
            '2XL' => 5,
            '3XL' => 7,
            '4XL' => 9
        ];
        
        $liststyles_2 = [
            'Women T-Shirt' => 3,
            'Unisex V-Neck' => 5,
            'Unisex Long Sleeve' => 8,
            'Unisex Sweatshirt' => 16,
            'Unisex Hoodie' => 25,
        ];
        
        $listsizes_2 = [
            'S' => 1,
            'M' => 2,
            'L' => 3,
            'XL' => 4,
            '2XL' => 6,
            '3XL' => 9,
        ];
        
        $liststyles_3 = [
            'Unisex Tank Top' => 5,
            'Youth T-Shirt' => 1,
            'Youth Hoodie' => 25,
            
        ];
        
        $listsizes_3 = [
            'S' => 1,
            'M' => 2,
            'L' => 3,
            'XL' => 4
        ];
        
        $liststyles_4 = [
            'Kids Tee' => 2,
        ];
        
        $listsizes_4 = [
            '6M' => 1,
            '12M' => 2,
            '18M' => 3,
            '24M' => 4,
            '2T' => 1,
            '3T' => 2,
            '4T' => 3,
            '5-6T' => 4,
        ];
        
        $listcolors = [
            'Black' => 0,
            'Dark Heather' => 0,
            'Navy' => 0,
            'Royal Blue' => 0,
            'Red' => 0,
            'Purple' => 0,
            'Orange' => 0,
            'Yellow' => 0,            
            'Forest Green' => 0,
            'White' => 0,
            'Light Pink' => 0,
            'Light Blue' => 0,
            'Sports Grey' => 0,
        ];
        
        //api
        $property_stylesize = 514;
        $property_color = 513;
        
        $prices = array();
        $stylesizes = array();      
        foreach ($liststyles as $style => $price_style) {
            foreach ($listsizes as $size => $price_size) {
                $stylesize = $style . ' - ' . $size;
                $prices[$stylesize] = $price_style + $price_size + $price;

                $stylesizes[] = [
                    'property_id'   => $property_stylesize,
                    'property_name' => 'Styles & Sizes',
                    'values'        => [$stylesize],
                ];
            }
        }
        // add varriant long sleeve, hoodie
        foreach ($liststyles_2 as $style => $price_style) {
            foreach ($listsizes_2 as $size => $price_size) {
                $stylesize = $style . ' - ' . $size;
                $prices[$stylesize] = $price_style + $price_size + $price;

                $stylesizes[] = [
                    'property_id'   => $property_stylesize,
                    'property_name' => 'Styles & Sizes',
                    'values'        => [$stylesize],
                ];
            }
        }
        // add varriant for youth
        foreach ($liststyles_3 as $style => $price_style) {
            foreach ($listsizes_3 as $size => $price_size) {
                $stylesize = $style . ' - ' . $size;
                $prices[$stylesize] = $price_style + $price_size + $price;

                $stylesizes[] = [
                    'property_id'   => $property_stylesize,
                    'property_name' => 'Styles & Sizes',
                    'values'        => [$stylesize],
                ];
            }
        }
        
        // add varriant for youth
        foreach ($liststyles_4 as $style => $price_style) {
            foreach ($listsizes_4 as $size => $price_size) {
                $stylesize = $style . ' - ' . $size;
                $prices[$stylesize] = $price_style + $price_size + $price;

                $stylesizes[] = [
                    'property_id'   => $property_stylesize,
                    'property_name' => 'Styles & Sizes',
                    'values'        => [$stylesize],
                ];
            }
        }
        
        $colors = array();
        foreach ($listcolors as $color => $price_color) {
            $colors[] = [
                'property_id' => $property_color,
                'property_name' => 'Colors',
                'values'        => [$color],
            ];
        }

        $products = array();
        foreach ($stylesizes as $stylesize) {
            foreach ($colors as $color) {
                $products[] = [
                    'property_values' => [$stylesize,$color],
                    'sku'             => '',
                    'offerings'       => [
                                            [
                                            'price'      => $prices[$stylesize['values'][0]],
                                            'quantity'   => $quantity,
                                            'is_enabled' => 1
                                        ]
                                    ]
                ];
            }
        }
        
        for ($i = 0 ; $i < 13; $i++){
            $products[$i]['offerings'][0]['price'] -= $products[$i]['offerings'][0]['price']*0.2;
            $products[$i]['offerings'][0]['quantity'] = 0;
        }

        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);
        
        try {
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/inventory";
            $params = array(
                'listing_id' => $listing_id,
                'products'             => json_encode($products),
                'price_on_property'    => $property_stylesize,
                'quantity_on_property' => $property_stylesize,
                'sku_on_property'      => ''
            );
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_PUT);
        
            $json = $oauth->getLastResponse();
            // print_r($json);
            $colEtsy->inventory = 1;
            $colEtsy->save();
            return response()->json([
                'success' => 1,
                'data' => $json,
            ]);  
        
        } catch (OAuthException $e) {
            error_log($e->getMessage());
            error_log(print_r($oauth->getLastResponse(), true));
            error_log(print_r($oauth->getLastResponseInfo(), true));            
            return response()->json([
                'success' => -2,
                'data' => 'OAuthException: ' + $e->getMessage(),
            ]);  
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());        
            return response()->json([
                'success' => -2,
                'data' => 'Exception: ' + $e->getMessage(),
            ]);  
            exit;
        }
    }
    
    //upload image from etsy shop settings
    public function uploadListingImagesFromShop(Request $request) {
        
        // listing id
        if (!$request->has('listing_id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        $listing_id = $request->listing_id;
        if (!$listing_id) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $colEtsy = CollectionsEtsy::where('listing_id', $listing_id)->first();
        if (!$colEtsy) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Missing listing_id'
            ]);
        }
        
        $etsyshop = EtsyShop::find($colEtsy->shop_id);
        if (!$etsyshop) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
            
        $success = true;
        $rank = 2;
        
        if($colEtsy->additional_image_url){
            //download file
            $dir = 'images/tmp/' . Carbon::now()->format('ymd');
            Storage::makeDirectory($dir);
            $filename = 'storage/'. $dir . '/m' . Str::random(5) . ".jpg";
            Image::make($colEtsy->additional_image_url)->save($filename);

            //upload to etsy
            $listing_image_id = $this->uploadListingImage($etsyshop, $listing_id, $filename, $rank);
            if ($listing_image_id) {
                // $etsyshop->image_id_1 = $listing_image_id;
                // $etsyshop->save();
                $success = $success && true;
                $rank++;
            } else {
                $success = false;
            }
        }
        
        //image 1
        if ($etsyshop->image_id_1) {
            $success = $success && $this->uploadListingImageId($etsyshop, $listing_id, $etsyshop->image_id_1, $rank);
            $rank++;
        } elseif ($etsyshop->image_url_1) {
            //download file
            $dir = 'images/tmp/' . Carbon::now()->format('ymd');
            Storage::makeDirectory($dir);
            $filename = 'storage/'. $dir . '/m' . Str::random(5) . ".jpg";
            Image::make($etsyshop->image_url_1)->save($filename);

            //upload to etsy
            $listing_image_id = $this->uploadListingImage($etsyshop, $listing_id, $filename, $rank);
            if ($listing_image_id) {
                $etsyshop->image_id_1 = $listing_image_id;
                $etsyshop->save();
                $success = $success && true;
                $rank++;
            } else {
                $success = false;
            }
        } else {
            $success = $success && true;
        }

        //image 2
        if ($etsyshop->image_id_2) {
            $success = $success && $this->uploadListingImageId($etsyshop, $listing_id, $etsyshop->image_id_2, $rank);
            $rank++;
        } elseif ($etsyshop->image_url_2) {
            //download file
            $dir = 'images/tmp/' . Carbon::now()->format('ymd');
            Storage::makeDirectory($dir);
            $filename = 'storage/'. $dir . '/m' . Str::random(5) . ".jpg";
            Image::make($etsyshop->image_url_2)->save($filename);

            //upload to etsy
            $listing_image_id = $this->uploadListingImage($etsyshop, $listing_id, $filename, $rank);
            if ($listing_image_id) {
                $etsyshop->image_id_2 = $listing_image_id;
                $etsyshop->save();
                $success = $success && true;
                $rank++;
            } else {
                $success = false;
            }
        } else {
            $success = $success && true;
        }

        //image 3
        if ($etsyshop->image_id_3) {
            $success = $success && $this->uploadListingImageId($etsyshop, $listing_id, $etsyshop->image_id_3, $rank);
            $rank++;
        } elseif ($etsyshop->image_url_3) {
            //download file
            $dir = 'images/tmp/' . Carbon::now()->format('ymd');
            Storage::makeDirectory($dir);
            $filename = 'storage/'. $dir . '/m' . Str::random(5) . ".jpg";
            Image::make($etsyshop->image_url_3)->save($filename);

            //upload to etsy
            $listing_image_id = $this->uploadListingImage($etsyshop, $listing_id, $filename, $rank);
            if ($listing_image_id) {
                $etsyshop->image_id_3 = $listing_image_id;
                $etsyshop->save();
                $success = $success && true;
                $rank++;
            } else {
                $success = false;
            }
        } else {
            $success = $success && true;
        }
        
        return response()->json([
            'success' => $success,
            'data' => json_encode($etsyshop),
        ]); 
        
    }
    
    private function uploadListingImage($etsyshop, $listing_id, $filename, $rank = 1) {
        // $listing_id = '743290749';
        // $mimetype='image/png';
        // $filename = 'storage/images/tmp/191009/m0aan8-flamingo-nurse-friends-bestie-were-more-than-just-nurse-friends-like-a-really-small-gang-gifts-t-shirt-rn-registered-nurse-nursing-student-2054x2450.jpg';

        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {
            $source_file = public_path() ."/$filename";
            $mimetype = mime_content_type($source_file);
            //$source_file = dirname(realpath(asset('storage/'.$filename)));

            //return $source_file;
        
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/images";
            $params = array('@image' => '@'.$source_file.';type='.$mimetype, 'rank'=>$rank, 'overwrite'=>1);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
        
            $json = $oauth->getLastResponse();            
            $jsonObject = json_decode($json, true);
            return (string)$jsonObject['results'][0]['listing_image_id'];
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            //die($e->getMessage());
            return 0;
        } catch (Exception $e) {            
            return 0;
        }
        
        return 0;
    }
    
    private function uploadListingImageId($etsyshop, $listing_id, $listing_image_id, $rank = 1) {
        // $listing_id = '743290311';
        // $listing_image_id = '2038498234';

        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {            
            $url = "https://openapi.etsy.com/v2/listings/".$listing_id."/images";
            $params = array('listing_image_id' => $listing_image_id, 'rank'=>$rank, 'overwrite'=>1);
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
        
            $json = $oauth->getLastResponse();
            $jsonObject = json_decode($json, true);
            return $jsonObject['results'][0]['listing_image_id'];
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            //die($e->getMessage());
            return 0;
        } catch (Exception $e) {            
            return 0;
        }
        
        return 0;
    }
    
    public function getOrders($id) {

        $etsyshop = EtsyShop::find($id);
        
        if (!$etsyshop) //return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {        
            $url = "https://openapi.etsy.com/v2/shops/".$etsyshop->shop_id."/receipts";

            $params = array(
                'limit' => 50,
                'includes' => 'Transactions,Country',
                // 'includes' => 'Transactions,Listings,Buyer,Country,Coupon',
            );
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            
            // print_r($json);
            // exit;
            
            $obj = json_decode($json, true);
            $etsyshop->update(['shop_sales'=>$obj["count"]]);
            if($obj["count"] < 1) return response()->json(['success' => 1, 'data' => 'orders ('.$obj["count"].')']);
            
            $rate = array(
                'USD' => 1,
                'AUD' => 0.73,
                'GBP' => 1.3,
                'CAD' => 0.7,
                'EUR' => 1.19,
            );
            
            foreach($obj['results'] as $result){
                
                // if(!EtsyOrder::where('seller_user_id', $result['seller_user_id'])->where('receipt_id', $result['receipt_id'])->exists()){
                if(!EtsyOrder::where('seller_user_id', $result['seller_user_id'])->where('order_id', $result['order_id'])->exists()){
                    // create new order 
                    $new_order = new EtsyOrder();
                    $new_order -> receipt_id = strval($result['receipt_id']);
                    $new_order -> receipt_type = $result['receipt_type'];
                    $new_order -> order_id = strval($result['order_id']);
                    $new_order -> seller_user_id = $result['seller_user_id'];
                    $new_order -> buyer_user_id = $result['buyer_user_id'];
                    $new_order -> creation_tsz = $result['creation_tsz'];
                    $new_order -> can_refund = $result['can_refund'];
                    $new_order -> last_modified_tsz = $result['last_modified_tsz'];
                    // $new_order -> name = $result['name'];
                    $new_order -> name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $result['name']);
                    $new_order -> first_line = $result['first_line'];
                    $new_order -> second_line = $result['second_line'];
                    $new_order -> city = $result['city'];
                    $new_order -> state = $result['state'];
                    $new_order -> zip = $result['zip'];
                    // $new_order -> formatted_address = $result['formatted_address'];
                    $new_order -> formatted_address = preg_replace('/[^\p{L}\p{N}\s]/u', '', $result['formatted_address']);
                    $new_order -> payment_method = $result['payment_method'];
                    $new_order -> payment_email = $result['payment_email'];
                    $new_order -> message_from_seller = $result['message_from_seller'];
                    $new_order -> message_from_buyer = preg_replace('/[^\p{L}\p{N}\s]/u', '', $result['message_from_buyer']);
                    $new_order -> was_paid = $result['was_paid'];
                    $new_order -> total_price = $result['total_price'];
                    $new_order -> total_shipping_cost = $result['total_shipping_cost'];
                    $new_order -> currency_code = $result['currency_code'];
                    $new_order -> message_from_payment = $result['message_from_payment'];
                    if($result['was_shipped']){ // Digital Files
                        $new_order -> status = 2;
                    }
                    $new_order -> was_shipped = $result['was_shipped'];
                    $new_order -> buyer_email = $result['buyer_email'];
                    $new_order -> seller_email = $result['seller_email'];
                    $new_order -> discount_amt = $result['discount_amt'];
                    $new_order -> subtotal = $result['subtotal'];
                    $new_order -> grandtotal = $result['grandtotal'];
                    $new_order -> adjusted_grandtotal = $result['adjusted_grandtotal'];
                    $new_order -> buyer_adjusted_grandtotal = $result['buyer_adjusted_grandtotal'];
                    $new_order -> shipped_date = $result['shipped_date'];
                    $new_order -> owner_id = $etsyshop->owner_id;
                    $new_order -> country_code = $result['Country']['iso_country_code'];

                    if (array_key_exists($result['currency_code'], $rate)) {
                        $new_order->revenue = $result['grandtotal']*$rate[$result['currency_code']];
                    }
                    $new_order -> save();
                    
                    // save order items
                    foreach($result['Transactions'] as $trans){
                        $new_order_item = new EtsyOrderItem();
                        $new_order_item -> transaction_id = strval($trans['transaction_id']);
                        $new_order_item -> title = $trans['title'];
                        $new_order_item -> seller_user_id = $trans['seller_user_id'];
                        $new_order_item -> buyer_user_id = $trans['buyer_user_id'];
                        $new_order_item -> quantity = $trans['quantity'];
                        $new_order_item -> receipt_id = strval($trans['receipt_id']);
                        $new_order_item -> is_digital = $trans['is_digital'];
                        $new_order_item -> listing_id = $trans['listing_id'];
                        $new_order_item -> creation_tsz = $result['creation_tsz'];
                        
                        if(!empty($trans['variations'])){
                            $newArr = array_values($trans["variations"]);
                            $new_order_item -> variation_1 = $newArr[0]['formatted_value'];
                            $new_order_item -> variation_2 = $newArr[1]['formatted_value'];
                            if(array_key_exists(2,$newArr)) $new_order_item -> variation_3 = $newArr[2]['formatted_value'];
                        }
                        
                        $new_order_item -> save();
                    }
                }else{
                    // EtsyOrder::where('seller_user_id', $result['seller_user_id'])->where('order_id', $result['order_id'])->update(['receipt_id' => strval($result['receipt_id'])]);
                }
            }
            
            return response()->json(['success' => 1, 'data' => 'orders ('.$obj["count"].')']);
            
        
        } catch (\OAuthException $e) {
            // You may want to recover gracefully here...
            return response()->json(['success' => -2, 'data' => $e->getMessage()]);
            // return response()->json(['success' => -2, 'data' => 'OAuthException']);
        } catch (\Exception $e) {
            // die($e->getMessage());
            return response()->json(['success' => -2, 'data' => $e->getMessage()]);
            // return response()->json(['success' => -2, 'data' => 'Exception']);
        }
    }
    
    public function getSellerTaxonomy($id) {

        $etsyshop = EtsyShop::find($id);
        if (!$etsyshop) //return redirect()->route('etsy.index',['message'=>'Etsy shop not found!']);
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Etsy shop not found.'
            ]);
        
        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);

        try {        
            $url = "https://openapi.etsy.com/v2/taxonomy/seller/get";
            
            $params = array();
        
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_GET);
        
            $json = $oauth->getLastResponse();
            echo "<pre>";
            print_r($json);
            echo "</pre>";
        
        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public function orders(Request $request){
        
        if (!$request->has('keyword')) {
            $keyword = "";
        } else {
            $keyword = $request->keyword;
        }

        if(!$request->has('shop_id')){
            $shop_id = "all";
        }else{
            $shop_id = $request->shop_id;
        }

        if (!$request->has('status')) {
            $status_condition = ">=";
            $status_value = 0;
            $filter_status = 4;             // all
        } elseif ($request->status == 4) {
            $status_condition = ">=";
            $status_value = 0;
            $filter_status = 4;             // all
        } else {
            $status_condition = "=";
            $status_value = $request->status;
            $filter_status = $status_value;
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
        
        $seller_ids = EtsyOrder::groupBy('seller_user_id')->pluck('seller_user_id')->toArray();
        
        if(!$request->has('seller')){
            if(in_array(Auth::user()->id,[1])){
                $seller = 0;
                $seller_condition = "<>";
            }else{
                $seller = Auth::user()->id;
                $seller_condition = "=";
                $seller_ids = EtsyOrder::where('owner_id',Auth::user()->id)->groupBy('seller_user_id')->pluck('seller_user_id')->toArray();
            }
        }elseif($request->seller == 0){
            if(in_array(Auth::user()->id,[1])){
                $seller = 0;
                $seller_condition = "<>";
            }else{
                return response()->json(['message'=>"Access Denied "]);
            }
        }else{
            if(in_array(Auth::user()->id,[$request->seller,1])){
                $seller = $request->seller;
                $seller_condition = "=";
                $seller_ids = EtsyOrder::where('owner_id',$request->seller)->groupBy('seller_user_id')->pluck('seller_user_id')->toArray();
            }else{
                return response()->json(['message'=>"Access Denied "]);
            }
        }
        
        $filters = [
            'shop_id' => $shop_id,
            'keyword' => $keyword,
            'status' => $filter_status,
            'fulfillment' => $fulfillment,
            'seller' => $seller,
        ];

        if($shop_id != "all"){   // by brand name
            $data = EtsyOrder::where('seller_user_id', $shop_id)->where('status', $status_condition, $status_value)->where('fulfillment_by',$fcondition,$fulfillment)->where('owner_id',$seller_condition,$seller)
                    ->where(function ($query) use ($keyword) {
                        $query->where('order_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('receipt_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('fulfillment_id', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        }else{  
            $data = EtsyOrder::where('status', $status_condition, $status_value)->where('fulfillment_by',$fcondition,$fulfillment)->where('owner_id',$seller_condition,$seller)
                    ->where(function ($query) use ($keyword) {
                        $query->where('order_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('receipt_id', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('fulfillment_id', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        }
        
        return view('etsy.orders')
            ->with('data',$data)
            ->with('filters',$filters)
            ->with('seller_ids', $seller_ids);
    }
    
    
    public function showOrderModal(Request $request)
    {
        if(!$request->id) return response()->json([
            'success' => 0,
            'data' => 'miss parameter'
        ]);

        $order = EtsyOrder::find($request->id);

        if(!$order) return response()->json([
            'success' => 0,
            'data' => 'order not found'
        ]);

        $orderItem = EtsyOrderItem::where('seller_user_id', $order->seller_user_id)->where('receipt_id', $order->receipt_id)->get();
        
        if(!$orderItem) return response()->json([
            'success' => 0,
            'data' => 'order items not found'
        ]);
        
        $shop = EtsyShop::where('user_id', $order->seller_user_id)->first();
        
        $data[] = $order;
        $data[] = $orderItem;
        $data[] = $shop;
        
        return response()->json([
            'success' => 1,
            'data' => $data
        ]);
    }

    public function updateOrderModel(Request $request)
    {
        
        if(!$request->etsy_order_id) return response()->json([
            'success' => 0,
            'data' => 'miss parameter'
        ]);
        
        $order = EtsyOrder::find($request->etsy_order_id);

        if(!$order) return response()->json([
            'success' => 0,
            'data' => 'order not found'
        ]);

        $order->fulfillment_id = trim($request->fulfillment_id);
        if($request->fulfillment_id != "" && $order -> status == 0){
            $order -> status = 1;
        }else{
            $order->status = $request->order_status;
        }
        
        $order->fulfillment_by = trim($request->fulfillment_by); 
        $order->note = $request->note; 
        $order->tracking_code = trim($request->tracking_number);    
        $order->fulfillment_carrier = trim($request->fulfillment_carrier); 
        $order->save();
        
        return response()->json([
            'success' => 1,
            'data' => 'order was updated'
        ]);
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
                // "Cookie: Yacht=SessionId=DACC888D655A434486EC43907EE86BDA"
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        if($response == "ERROR:Invalid Request") exit("ERROR:Invalid Request");
        
        $usps = array();
		$usps[0] = '(94|93|92|94|95)[0-9]{20}';
		
        $ups = array();
		$ups[0] = '(82)[0-9]{16}';

		
		$order = EtsyOrder::find($id);
		preg_match_all('/<b>([0-9.]+)<\/b>/', $response, $arr);
		
		$cost = array_key_exists(0, $arr) ? strip_tags($arr[0][0]) : 0;
		
        if (preg_match('/('.$usps[0].')/', $response, $matches))
		{
		    $order->update([
                'tracking_code' => $matches[0],
                'fulfillment_carrier' => 'usps',
                'fulfillment_cost' => $cost
            ]);
		}elseif(preg_match('/('.$ups[0].')/', $response, $matches)){
		    $order->update([
                'tracking_code' => $matches[0],
                'fulfillment_carrier' => 'ups',
                'fulfillment_cost' => $cost
            ]);
		}else{
		    $order->update([
                'fulfillment_cost' => $cost
            ]);
		}
    }

    private function printify($fulfillment_id, $id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.printify.com/v1/shops/740379/orders/".$fulfillment_id.".json",
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
        
        // print_r($result);
        // exit;
        
        if (array_key_exists('error', $result)) exit("error");
        
        $fulfillment_cost = ($result['total_price'] + $result['total_shipping'] + $result['total_tax'])*0.01;

        $order = EtsyOrder::find($id);
    
        $order->update(['fulfillment_cost' => $fulfillment_cost]);
        
        print_r($result['status']);
        
        if($result['status'] == "canceled"){
            $order->update(['status' => 3]);
        }
        if (array_key_exists('shipments', $result)) {
            $order->update([
                'fulfillment_carrier' => $result['shipments'][0]["carrier"], 
                'tracking_code' => $result['shipments'][0]["number"],
            ]);
        }
    }
    
    private function teezily($fulfillment_id, $id)
    {
        // $fulfillment_id = '794';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://plus.teezily.com/api/v1/orders/".$fulfillment_id.".json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
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
        
        print_r('>> order status: '.$result['orders'][0]['state']);
        
        $order = EtsyOrder::find($id);
        $order->update(['fulfillment_cost' => $fulfillment_cost]);
        
        if(!empty($result['orders'][0]['tracking_number'])){
            
            $order->update([
                'fulfillment_carrier' => $result['orders'][0]['tracking_number'][0]["carrier"], 
                'tracking_code' => $result['orders'][0]['tracking_number'][0]["tracking_number"],
            ]);
            
        }
    }
    
    private function gearment($fulfillment_id, $id)
    {
        // $fulfillment_id = '0003100806';
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://account.gearment.com/api/v2/?act=order_info",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS =>"{\r\n\t\"api_key\":\"QBIVKPk3xTZEsYzI\",\r\n\t\"api_signature\":\"HH2pU54NpWGPvY5OOpDtpG6Z5t7unFLO\",\r\n\t\"order_id\":\"$fulfillment_id\"\r\n}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
        ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
            
        $obj =  json_decode($response,true);
        
        if($obj['status'] == 'error'){
            exit("response: ".$obj['message']." (".$obj['status'].")");
        }
        
        if($obj['status'] == 'success'){
            
            $obj['result']['ord_payment_status'] == "canceled" || ($obj['result']['ord_archive'] == "archived" && $obj['result']['ord_payment_status'] == "unpaid")?  $fulfillment_cost = 0 : $fulfillment_cost = ltrim($obj['result']['ord_total'], '$');
            print_r('>> order status: '.$obj['result']['ord_status']." - paymnet status: ".$obj['result']['ord_payment_status']);
            // exit;
            $order = EtsyOrder::find($id);
            $order->update([
                'fulfillment_carrier' => $obj['result']['trackings'][0]['tracking_company'], 
                'tracking_code' => $obj['result']['trackings'][0]['tracking_number'],
                'fulfillment_cost' => $fulfillment_cost,
            ]);
        }
    }
    
    public function getTracking(Request $request)
    {   
       
        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => 'missing parameter',
        ]);

        $order = EtsyOrder::find($request->id);
        
        if(!$order) return response()->json([
            'success' => 0,
            'data' => 'order not found'
        ]);

        if($order->fulfillment_id == null) return response()->json([
            'success' => 0,
            'data' => 'order_id not found',
        ]);            

        switch ($order->fulfillment_by) {
            case 'teescape':
                $this->teescape($order->fulfillment_id, $request->id);
                break;
            case 'gearment':
                $this->gearment($order->fulfillment_id, $request->id);
                break;
            case 'printify':
                $this->printify($order->fulfillment_id, $request->id);
                break;
            case 'teezily':
                $this->teezily($order->fulfillment_id, $request->id);
                break;
            default:
                return response()->json([
                    'success' => 0,
                    'data' => 'default',
                ]);
                break;
        }
    }
    
    public function submitTracking(Request $request)
    {
        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => 'id not found',
        ]);
        
        $order = EtsyOrder::find($request->id);
        
        if(!$order) return response()->json([
            'success' => 0,
            'data' => 'order not found'
        ]);
        
        if($order->fulfillment_id == null) return response()->json([
            'success' => 0,
            'data' => 'new order',
        ]);
        
        if($order->status > 1) return response()->json([
            'success' => 0,
            'data' => 'order was submited',
        ]);
        
        if($order->tracking_code == null) return response()->json([
            'success' => 0,
            'data' => 'order non-tracking',
        ]);
        
        $etsyshop = EtsyShop::where('user_id',$order->seller_user_id)->first();
        
        if(!$etsyshop) return response()->json([
            'success' => 0,
            'data' => 'etsy shop not found'
        ]);

        $access_token = $etsyshop->access_token;
        $access_token_secret = $etsyshop->access_token_secret;

        $consumer_key = $etsyshop->key_string;
        $consumer_secret= $etsyshop->share_secret;
         
        $oauth = new OAuth($consumer_key, $consumer_secret);
        $oauth->disableSSLChecks();
        $oauth->enableDebug();
        $oauth->setToken($access_token, $access_token_secret);
        
        $carriers = [
            'UPS' => 'ups',
            'USPS' => 'usps',
            'OSMWorldwide' => 'usps',
            'CANADA_POST' => 'canada-post',
            'STANDARD' => 'canada-post',
            'MI' => 'ups',
            'RM48' => 'royal-mail',
            'DHL SmartMail Parcel Gnd' => 'dhl-global-mail',
            'DHLGM' => 'dhl-global-mail',
        ];
        
        try {        
            $url = "https://openapi.etsy.com/v2/shops/".$etsyshop->shop_id."/receipts/".$order->receipt_id."/tracking";
            $params = array(
                'tracking_code' => $order->tracking_code,
                'carrier_name' => array_key_exists($order->fulfillment_carrier, $carriers) ? $carriers[$order->fulfillment_carrier] : $order->fulfillment_carrier,
                'send_bcc' => 0,
            );
            $oauth->fetch($url, $params, OAUTH_HTTP_METHOD_POST);
            $json = $oauth->getLastResponse();
            $obj = json_decode($json, true);
            
            if($obj["count"] < 1) return response()->json(['success' => -1, 'data' => 'Order not found']);
            
            if($obj['results'][0]['was_shipped']){
                $order->status = 2;
                $order->save();
                return response()->json([
                    'success' => 1,
                    'data' => $obj['results'][0]['shipments'][0]['tracking_code']
                ]); 
            }
            
            return response()->json([
                'success' => -1,
                'data' => $json
            ]);

        } catch (OAuthException $e) {
            // You may want to recover gracefully here...
            print $oauth->getLastResponse()."\n";
            print_r($oauth->debugInfo);
            die($e->getMessage());
        } catch (Exception $e) {
            die($e->getMessage());
        }
        
    }
    
    public function deleteOrder(Request $request)
    {
        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => 'id not found',
        ]);
        
        $order = EtsyOrder::find($request->id);
        
        if(!$order) return response()->json([
            'success' => 0,
            'data' => 'order not found'
        ]);
        
        $orderItem = EtsyOrderItem::where('receipt_id', $order->receipt_id)->get();
        
        if($orderItem){
            foreach($orderItem as $item){
                $item->delete();
            }
        }
        
        $order->delete();
        
        return response()->json([
            'success' => 0,
            'data' => 'Order was deleted'
        ]);
    }
    
    public function generate_mockup(Request $request)
    {
        $colEtsy = CollectionsEtsy::where('listing_id', $request->listing_id)->where('shop_id', $request->shop_id)->first();

        if(!$colEtsy) return response()->json([
            'success' => -1, 
            'data' => 'not found'
        ]);

        $design = Design::find($colEtsy->design_id);

        if (!$design) 
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'Design not found.'
            ]);

        if ($design->filename != null) { //make mockup for artwork

            $mockup_ids = CollectionMockups::where('collection_id',$colEtsy->collection_id)->pluck('mockup_id')->toArray();
            
            if (!$mockup_ids) return response()->json([
                'success' => -1, //resource not found
                'data' => $mockup_ids
            ]);

            $mockup = Mockup::whereIn('id',$mockup_ids)->where('color','!=',$design->color)->inRandomOrder()->first();
            $colEtsy->additional_image_url = $design->makeMockup($mockup);
            $colEtsy->save();
            
            return response()->json([
                'success' => 1,
                'data' => "OK",
            ]); 
        }

        return response()->json([
            'success' => 1,
            'data' => "-1",
        ]); 

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
        
        // get orders with shop was not calculate.
        $seller_user_ids = EtsyShop::where('owner_id',$owner_condition,$owner_id)->where('user_id','<>',0)->orderBy('bank_account','DESC')->groupBy('user_id')->pluck('user_id')->toArray(); // shop api is working
        
        $data = [];
        $totalCost = 0;
        $totalOrder = 0;
        $totalUnit = 0;
        
        foreach($seller_user_ids as $user_id){

            $order_count = EtsyOrder::where('seller_user_id',$user_id)->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])->count();
            $order_unit = EtsyOrderItem::where('seller_user_id',$user_id)->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])->count();
            $cost = EtsyOrder::where('seller_user_id',$user_id)->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])->sum('fulfillment_cost');
            
            $data[$user_id] = [
                "shop_name" => EtsyShop::where('user_id',$user_id)->value('shop_name'),
                "bank_account" => EtsyShop::where('user_id',$user_id)->value('bank_account'),
                "status" => EtsyShop::where('user_id',$user_id)->value('is_active'),
                "archived" => EtsyShop::where('user_id',$user_id)->value('archived'),
                "order_count" => $order_count,
                "order_unit" => $order_unit,
                "revenue" => EtsyOrder::where('seller_user_id',$user_id)->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])->sum('grandtotal'),
                "cost" => $cost,
                "currency_code" => EtsyOrder::where('seller_user_id',$user_id)->value('currency_code')
            ]; 
            $totalCost += $cost;
            $totalOrder += $order_count;
            $totalUnit += $order_unit;
        }
        
        // get orders all by time.
        $orders = DB::table('etsy_orders')
            ->select(DB::raw('DATE(FROM_UNIXTIME(creation_tsz)) as date'), DB::raw('count(*) as total'))
            // ->where('owner_id', Auth::user()->id)
            ->where('owner_id',$owner_condition,$owner_id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        $costs = DB::table('etsy_orders')
            ->select(DB::raw('DATE(FROM_UNIXTIME(creation_tsz)) as date'), DB::raw('sum(fulfillment_cost) as total'))
            // ->where('owner_id', Auth::user()->id)
            ->where('owner_id',$owner_condition,$owner_id)
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        $receipt_ids = EtsyOrder::where('owner_id',$owner_condition,$owner_id)->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(FROM_UNIXTIME(creation_tsz))'), [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->pluck('receipt_id')->toArray();
        
        $orders_units = EtsyOrderItem::whereNull('deleted_at')->whereIn('receipt_id',$receipt_ids)->count();
        
        $revenues = DB::table('etsy_orders')
            ->select(DB::raw('DATE(FROM_UNIXTIME(creation_tsz)) as date'), DB::raw('sum(revenue) as total'))
            ->whereNull('deleted_at')
            ->whereIn('receipt_id',$receipt_ids)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        return view('etsy.statistic')
            ->with('startDate',$startDate)
            ->with('endDate',$endDate)
            ->with('data',$data)
            ->with('totalCost',$totalCost)
            ->with('totalOrder',$totalOrder)
            ->with('totalUnit',$totalUnit)
            ->with('orders',$orders)
            ->with('costs',$costs)
            ->with('orders_units',$orders_units)
            ->with('users',$users)
            ->with('filters',$filters)
            ->with('owner_id', $owner_id)
            ->with('revenues',$revenues);
    }
}
