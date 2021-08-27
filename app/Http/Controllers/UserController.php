<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Hash;
use DB;
use Carbon\Carbon;
use App\Design;
use App\EtsyShop;
use App\EtsyOrder;
use App\EtsyOrderItem;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->id != 1){
            return response()->json(['success' => 0, 'message' => 'Access Denied']);
        }
        $users = User::orderBy('is_active','DESC')->get();
        return view('user.index')
            ->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    // public function changePassword()
    // {
    //     $user = User::find(1);
    //     $user->password = Hash::make("XuanPhu1988@");
    //     $user->save();
    //     return $user;
    // }
    
    public function updateSetting(Request $request)
    {  
     
        if (!$request->cookie_ts) return response()->json([
            'success' => 0,
            'message' => 'cookie_ts null',
        ]);

        $user = Auth::user();
        $user->cookie = $request->cookie_ts;
        $user->save();
        
        return response()->json([
            'success' => 1,
            'message' => "Ok",
        ]);
    }
    
    public function profile(Request $request)
    {
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
        
        $users = User::where('is_active',1)->orderBy('created_at','ASC')->get();
        
        $filters = [            
            'owner_id'=> $owner_id
        ];
        
        $designs = DB::table('designs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('designer_id',$owner_condition, $owner_id)
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        $art_works = DB::table('designs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('designer_id',$owner_condition,$owner_id)
            ->where('is_shared', 1)
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        $credits = DB::table('designs')
            // ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(credit) as total'))
            ->where('designer_id',$owner_condition,$owner_id)
            ->where('is_shared', 1)
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
        return view('user.profile')
            ->with('startDate',$startDate)
            ->with('endDate',$endDate)
            ->with('users',$users)
            ->with('owner_id', $owner_id)
            ->with('filters',$filters)
            ->with('art_works',$art_works)
            ->with('credits',$credits)
            ->with('designs', $designs);
    }
    
    public function showUserModal(Request $request)
    {
        if(!$request->id) return response()->json([
            'success' => 0,
            'data' => 'miss parameter'
        ]);

        $user = User::find($request->id);

        if(!$user) return response()->json([
            'success' => 0,
            'data' => 'user not found'
        ]);

        return response()->json([
            'success' => 1,
            'data' => $user
        ]);
    }
    
    public function updateUserModel(Request $request)
    {
        if(!$request->user_id) return response()->json([
            'success' => 0,
            'data' => 'miss parameter'
        ]);
        
        $user = User::find($request->user_id);
        
        // return $request->user_id;
        // exit;
        
        if(!$user) return response()->json([
            'success' => 0,
            'data' => 'user not found'
        ]);

        $user->name = $request->name;
        if($request->has('password')){
            $user->password = Hash::make($request->password);
        }
        $user->is_active = $request->is_active;        
        $user->is_designer = $request->is_designer;        
        $user->is_seller = $request->is_seller;        
        $user->printify_shopid = $request->printify_shopid;        
        $user->printify_api = $request->printify_api;        
        $user->gearment_api_key = $request->gearment_api_key;        
        $user->gearment_api_signature = $request->gearment_api_signature;        
        $user->teezily_api = $request->teezily_api;        
        $user->save();
        
        return response()->json([
            'success' => 1,
            'data' => 'infor was updated'
        ]);
    }
    
}
