<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\User;
use App\OrderItem;
use App\EtsyOrder;
use App\EtsyOrderItem;
use Carbon\Carbon;
use Auth;
use DB;

class DashboardController extends Controller
{
	protected $pagesize;

    public function __construct()
    {
        // $this->middleware('auth.seller');
        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    private function getAmazonStatistics(Request $request, $startDate, $endDate, $owner_id) {
        if (!(!$request->has('platform') || ($request->platform == '' || $request->platform == 'amazon'))) {
            return [];
        }

        $sql = "SELECT";
        $sql .= " DATE(FROM_UNIXTIME(a.amz_order_date)) AS date,";
        $sql .= " COUNT(1) count_order,";
        $sql .= " COUNT(case when a.status=3 then 1 ELSE NULL END) count_cancel,";
        $sql .= " COUNT(case when a.fulfillment_cost>0 then 1 ELSE NULL END) count_cost,";
        $sql .= " SUM(b.totalAmount) total_revenue,";
        $sql .= " SUM(case when a.status=3 then b.totalAmount ELSE 0 END) total_cancel,";
        $sql .= " SUM(a.fulfillment_cost) total_cost";
        $sql .= " FROM orders a, order_items b";
        $sql .= " WHERE";
        // $sql .= " a.owner_id=?";
        $sql .= " ('{$owner_id}'='' OR a.owner_id=?)";
        $sql .= " AND DATE(FROM_UNIXTIME(a.amz_order_date)) BETWEEN ? AND ?";
        $sql .= " AND a.deleted_at IS NULL";
        $sql .= " AND a.id=b.order_id";
        $sql .= " GROUP BY";
        $sql .= " DATE(FROM_UNIXTIME(a.amz_order_date))";
        $sql .= " ORDER BY date";

        $results = DB::select($sql, [$owner_id, Carbon::parse($startDate), Carbon::parse($endDate)]);

        return $results;
    }

    private function getEtsyStatistics(Request $request, $startDate, $endDate, $owner_id) {
        if (!(!$request->has('platform') || ($request->platform == '' || $request->platform == 'etsy'))) {
            return [];
        }

        $sql = "SELECT";
        $sql .= " DATE(FROM_UNIXTIME(a.creation_tsz)) AS date,";
        $sql .= " COUNT(1) count_order,";
        $sql .= " COUNT(case when a.status=3 then 1 ELSE NULL END) count_cancel,";
        $sql .= " COUNT(case when a.fulfillment_cost>0 then 1 ELSE NULL END) count_cost,";
        $sql .= " SUM(a.revenue) total_revenue,";
        $sql .= " SUM(case when a.status=3 then revenue ELSE 0 END) total_cancel,";
        $sql .= " SUM(a.fulfillment_cost) total_cost";
        $sql .= " FROM etsy_orders a";
        $sql .= " WHERE";
        // $sql .= " a.owner_id=?";
        $sql .= " ('{$owner_id}'='' OR a.owner_id=?)";
        $sql .= " AND DATE(FROM_UNIXTIME(a.creation_tsz)) BETWEEN ? AND ?";
        $sql .= " AND a.deleted_at IS NULL";
        $sql .= " GROUP BY";
        $sql .= " DATE(FROM_UNIXTIME(a.creation_tsz))";
        $sql .= " ORDER BY date";

        $results = DB::select($sql, [$owner_id, Carbon::parse($startDate), Carbon::parse($endDate)]);

        return $results;
    }

    public function getStatistics(Request $request) {
        // error_log('$request->platform=' . $request->platform);
        // error_log('$request->user_id=' . $request->user_id);
        // error_log('$request->date_from=' . $request->date_from);
        // error_log('$request->date_to=' . $request->date_to);

        if ($request->has('user_id'))
        {
          $owner_id = $request->user_id ? $request->user_id : '';
        } else {
          $owner_id = $request->user()->id;
        }

        if (!$request->has('date_from')) {
          $startDate = Carbon::now()->startOfMonth();
          $endDate = Carbon::now()->endOfMonth();
        } else {
          $startDate = Carbon::parse($request->date_from);
          $endDate = Carbon::parse($request->date_to);
        }

        $amazonStatistics = $this->getAmazonStatistics($request, $startDate, $endDate, $owner_id);
        $etsyStatistics = $this->getEtsyStatistics($request, $startDate, $endDate, $owner_id);

        $sql = "SELECT b.asin, COUNT(1) count_product";
        $sql .= " FROM orders a, order_items b";
        $sql .= " WHERE";
        $sql .= " ('{$owner_id}'='' OR a.owner_id=?)";
        $sql .= " AND DATE(FROM_UNIXTIME(a.amz_order_date)) BETWEEN ? AND ?";
        $sql .= " AND a.deleted_at IS NULL";
        $sql .= " AND a.id=b.order_id";
        $sql .= " GROUP BY asin";
        $sql .= " ORDER BY COUNT(1) DESC";
        $sql .= " LIMIT 5";

        // error_log('sql=' . $sql);

        $topProducts = DB::select($sql, [$owner_id, Carbon::parse($startDate), Carbon::parse($endDate)]);

        // error_log('topProducts=');
        // error_log(print_r($topProducts,true));

        return response()->json([
            'success' => 1,
            'message' => 'Get data success',
            'data' => [
              'amazon_statistics' => $amazonStatistics,
              'etsy_statistics' => $etsyStatistics,
              'top_products' => $topProducts,
            ]
        ]);
    }

    public function getSellerList(Request $request)
    {
      $selllers = [];

      if (!Auth::user()->isAdmin())
      {
          $selllers = [];
      }

      $selllers = DB::table('users')
          ->select('id', 'name')
          ->where('is_seller', '=', 1)
          ->orderBy('name', 'ASC')
          ->get();

      return response()->json([
          'success' => 1,
          'message' => 'Get data success',
          'data' => [
            'sellers' => $selllers
          ]
      ]);
    }

    public function statistic(Request $request) {

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
    	return view('dashboard.index');
    }
}
