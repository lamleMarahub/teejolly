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

        $sql = "SELECT aa.*, bb.total_cost FROM ";
        $sql .= "(SELECT";
        $sql .= " DATE(FROM_UNIXTIME(a.amz_order_date)) AS date,";
        $sql .= " COUNT(DISTINCT a.id) count_order,";
        $sql .= " COUNT(1) count_order_item,";
        $sql .= " COUNT(distinct case when a.status=3 then a.id ELSE NULL END) count_cancel,";
        $sql .= " COUNT(distinct case when a.fulfillment_cost>0 then a.id ELSE NULL END) count_cost,";
        $sql .= " SUM(b.totalAmount) total_revenue,";
        $sql .= " SUM(case when a.status=3 then b.totalAmount ELSE 0 END) total_cancel";
        // $sql .= " SUM(a.fulfillment_cost) * COUNT(DISTINCT a.id) / COUNT(1) total_cost";
        $sql .= " FROM orders a, order_items b";
        $sql .= " WHERE";
        // $sql .= " a.owner_id=?";
        $sql .= " ('{$owner_id}'='' OR a.owner_id=?)";
        $sql .= " AND DATE(FROM_UNIXTIME(a.amz_order_date)) BETWEEN ? AND ?";
        $sql .= " AND a.deleted_at IS NULL";
        $sql .= " AND a.id=b.order_id";
        $sql .= " GROUP BY";
        $sql .= " DATE(FROM_UNIXTIME(a.amz_order_date))) aa,";
        // $sql .= " ORDER BY date";
        $sql .= " (SELECT";
        $sql .= "   DATE(FROM_UNIXTIME(amz_order_date)) AS date,";
        $sql .= "   SUM(fulfillment_cost) total_cost";
        $sql .= " FROM orders";
        $sql .= " WHERE ('{$owner_id}'='' OR owner_id=?)";
        $sql .= "   AND DATE(FROM_UNIXTIME(amz_order_date)) BETWEEN ? AND ?";
        $sql .= "   AND deleted_at IS NULL";
        $sql .= " GROUP BY DATE(FROM_UNIXTIME(amz_order_date))) bb";
        $sql .= " WHERE aa.date=bb.date";
        $sql .= " ORDER BY aa.date";

        // error_log($sql);

        $results = DB::select($sql, [
          $owner_id, Carbon::parse($startDate), Carbon::parse($endDate),
          $owner_id, Carbon::parse($startDate), Carbon::parse($endDate)
        ]);

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

    private function getDesignStatistics(Request $request, $startDate, $endDate, $owner_id) {
        // error_log('---------------------------getDesignStatistics');
        // error_log($startDate);
        // error_log(Carbon::parse($startDate));
        // error_log($endDate);
        // error_log(Carbon::parse($endDate)->addDay(1));

        $sql = "select ".
        "  DATE(created_at) as date, ".
        "  COUNT(1) count_design, ".
        "  SUM(credit) total_credit ".
        "from ".
        "  designs ".
        "where ".
        "  created_at >= ? ".
        "  AND created_at < ? ".
        "  AND deleted_at IS NULL ".
        "  AND ('{$owner_id}'='' OR owner_id=?) ".
        "GROUP BY   DATE(created_at) ".
        "ORDER BY   date";

        $results = DB::select($sql, [Carbon::parse($startDate), Carbon::parse($endDate)->addDay(1), $owner_id]);

        return $results;
    }

    private function getUserCreditStatistics(Request $request, $startDate, $endDate, $owner_id) {
        $sql = "SELECT ".
            "  a.id, ".
            "  a.name, ".
            "  IFNULL(b.count_design, 0) count_design, ".
            "  IFNULL(b.total_credit, 0) total_credit ".
            "from ".
            "  users a ".
            "  LEFT JOIN (".
            "    SELECT ".
            "      owner_id, ".
            "      COUNT(1) count_design, ".
            "      SUM(credit) total_credit ".
            "    FROM       designs ".
            "    WHERE       created_at >= ? ".
            "      AND created_at < ? ".
            "      AND deleted_at IS NULL ".
            "      AND ('{$owner_id}'='' OR owner_id=?) ".
            "    GROUP BY       owner_id".
            "  ) b ON a.id = b.owner_id ".
            "WHERE   a.is_active = 1 ".
            "       AND ('{$owner_id}'='' OR id=?) ".
            "ORDER BY   b.total_credit DESC";

        $results = DB::select($sql, [Carbon::parse($startDate), Carbon::parse($endDate)->addDay(1), $owner_id, $owner_id]);

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

        if (!Auth::user()->isAdmin())
        {
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
        $designStatistics = $this->getDesignStatistics($request, $startDate, $endDate, $owner_id);
        $userCreditStatistics = $this->getUserCreditStatistics($request, $startDate, $endDate, $owner_id);

        $sql = "SELECT b.asin, COUNT(1) count_product";
        $sql .= " FROM orders a, order_items b";
        $sql .= " WHERE";
        $sql .= " ('{$owner_id}'='' OR a.owner_id=?)";
        $sql .= " AND DATE(FROM_UNIXTIME(a.amz_order_date)) BETWEEN ? AND ?";
        $sql .= " AND a.deleted_at IS NULL";
        $sql .= " AND a.id=b.order_id";
        $sql .= " GROUP BY asin";
        $sql .= " ORDER BY COUNT(1) DESC";
        $sql .= " LIMIT 10";

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
              'design_statistics' => $designStatistics,
              'user_credit_statistics' => $userCreditStatistics,
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
      } else {
          $selllers = DB::table('users')
            ->select('id', 'name', 'is_designer')
            ->where('is_active', '=', 1)
            ->orderBy('is_seller', 'ASC')
            ->orderBy('is_designer', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();
      }

      return response()->json([
          'success' => 1,
          'message' => 'Get data success',
          'data' => [
            'sellers' => $selllers
          ]
      ]);
    }

    public function index(Request $request)
    {
    	return view('dashboard.index');
    }
}
