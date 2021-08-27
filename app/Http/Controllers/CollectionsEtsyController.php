<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EtsyShop;
use App\Collection;
use App\CollectionsEtsy;
use App\CollectionDesigns;
use App\Design;
use App\Mockup;
use App\CollectionMockups;

class CollectionsEtsyController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth.admin');

        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    public function etsy()
    {
    	$success = 1;
        $message = '';
        $data = [];
        $etsy_infor = [];

    	$collection_etsy = CollectionsEtsy::where('state', 0)->first(); // state = 0 is already to list

		if (!$collection_etsy) return response()->json([
			'success' => 0,
			'message' => 'not found!',
			'data' => null
		]);

    	$etsy_shop = EtsyShop::find($collection_etsy->shop_id);

        if (!$etsy_shop)
    	{
    		CollectionsEtsy::find($collection_etsy->id)->delete();
    		return response()->json([
				'success' => 0,
				'message' => 'shop not found!',
				'data' => null
			]);

    	}

    	$collection_id = $collection_etsy->collection_id;
    	$design_ids = CollectionDesigns::where('collection_id',$collection_id)->pluck('design_id')->toArray();
        $designs = Design::whereIn('id', $design_ids)->get();

        if($designs->isEmpty()) return response()->json([
        	'success' => 0,
			'message' => 'design not found!',
			'data' => null
        ]);

        $mockup_ids = CollectionMockups::where('collection_id',$collection_id)->pluck('mockup_id')->toArray();

        if(empty($mockup_ids)) return response()->json([
        	'success' => 0,
			'message' => 'mockup not found!',
			'data' => null
        ]);

        foreach ($designs as $design){
        	if($design->filename != null){

        		//$mockup = Mockup::whereIn('id',$mockup_ids)->where('is_active',true)->where('color','!=',$design->color)->inRandomOrder()->first();
                $mockup = Mockup::whereIn('id',$mockup_ids)->where('is_active',true)->where('color','!=',$design->color)->inRandomOrder()->first();

                // create 4 random mockups for a design
                // $numMockups = 4;
                // $mockups = Mockup::whereIn('id',$mockup_ids)->where('is_active',true)->where('color','!=',$design->color)->inRandomOrder()->limit($numMockups)->get();
                // $mockup_urls = array();

                // foreach ($mockups as $m) {
                //     $mockup_urls[] = $design->makeMockup($m);
                // }

        		$data[] = [
        			'title' => $design->title,
        			'tags' => $design->tags,
        			'mockup_url' => $design->makeMockup($mockup)
        			//'mockup_urls' => $mockup_urls
        		];
        	}
        }

        $etsy_infor[] = [
    		'key_string' => $etsy_shop->key_string,
    		'share_secret' => $etsy_shop->share_secret,
    		'access_token' => $etsy_shop->access_token,
    		'access_token_secret' => $etsy_shop->access_token_secret,
    		'shipping_template_id' => $etsy_shop->shipping_template_id,
    		'price' => $etsy_shop->price,
    		'quantity' => $etsy_shop->quantity,
    		'image_url_1' => $etsy_shop->image_url_1,
    		'image_url_2' => $etsy_shop->image_url_2,
    		'image_url_3' => $etsy_shop->image_url_3
    	];

    	return response()->json([
    		'success' => 1,
    		'message' => 'success',
    		'id' => $collection_etsy->id,
    		'etsy_shop' => $etsy_infor,
    		'data' =>$data
    	]);
    }

    public function update_state(Request $request){

    	if (!$request->has('id')) {
            return response()->json([
                'success' => -1, //resource not found
                'data' => 'missing id'
            ]);
        }

        // var_dump(intval($request->id));

        $collection_etsy = CollectionsEtsy::find(intval($request->id));

        if(!$collection_etsy) return response()->json([
            'success' => -1, //resource not found
            'data' => 'not found'
        ]);

        $collection_etsy-> state = 1;
        $collection_etsy->save();

         return response()->json([
            'success' => 1, //resource not found
            'data' => 'success'
        ]);
    }
}
