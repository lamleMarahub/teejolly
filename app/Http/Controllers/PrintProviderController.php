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
}
