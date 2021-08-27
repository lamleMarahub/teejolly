<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Design;
use App\Mockup;
use App\Collection as Collection;
use App\CollectionDesigns;
use App\CollectionExport;
use App\CollectionMockups;
use Carbon\Carbon;
use App\EtsyShop;

class CollectionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.seller');

        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->has('keyword')) {
            $keyword = "";
        } else {
            $keyword = $request->keyword;
        }

        $user_id = $request->user()->id;
        // $data = Collection::where('owner_id', $user_id)->orderBy('created_at','desc')->paginate($this->pagesize);

        $data = Collection::where('owner_id', $user_id)
                        // ->where('status', $active_condition, $status)
                        ->where(function ($query) use ($keyword) {
                            $query->where('title', 'LIKE', '%'.$keyword.'%');
                                // ->orWhere('brand_name', 'LIKE', '%'.$keyword.'%');
                                // ->orWhere('paypal_email', 'LIKE', '%'.$keyword.'%')
                                // ->orWhere('note', 'LIKE', '%'.$keyword.'%');
                        })
                        ->orderBy('created_at','desc')->paginate(20);

        $message = $request->message;

        $filters = [
            'keyword'=> $keyword
        ];

        return view('collection.index')
            ->with('data', $data)
            ->with('filters', $filters)
            ->with('message', $message);
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
        $request->validate([
            'title' => 'required',
        ]);

        $collection = Collection::create([
            'owner_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description, //htmlentities($request->description),
            'tags' => $request->tags,
            'image_url_1' => $request->image_url_1,
            'image_url_2' => $request->image_url_2,
            'image_url_3' => $request->image_url_3,
            'uid' => $request->uid,
            'brand_name' => $request->brand_name,
        ]);

        return redirect()->route('collection.edit', ['id'=>$collection->id]);
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
        if (!$id) return redirect('collection')->with('message', 'Missing id collection.');

        $collection = Collection::where('id',$id)->first();
        if (!$collection) {
            return redirect('collection')->with('message', 'Collection {{$id}} not found.');
        }

        // if (!$collection->isOwnerOrAdmin(Auth::user())) {
        //     return redirect('collection')->with('message', 'Access denied.');
        // }

        $collection_designs = CollectionDesigns::where('collection_id',$id)->pluck('design_id')->toArray();
        $designs = Design::whereIn('id', $collection_designs)
                            ->orderBy('created_at', 'DESC')->get();

        $collection_mockups = CollectionMockups::where('collection_id',$id)->pluck('mockup_id')->toArray();
        $mockups = Mockup::where('is_active', true)
                            ->whereIn('id', $collection_mockups)
                            ->orderBy('created_at', 'DESC')->get();

        return view('collection.edit')
            ->with('data', [
                'collection'=>$collection,
                'designs'=>$designs,
                'mockups'=>$mockups,
            ]);
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
        $request->validate([
            'title' => 'required',
        ]);

        if (!$request->id) return redirect('collection',['message'=>'Missing id collection.']);

        $collection = Collection::find($request->id);
        if (!$collection) {
            return redirect('collection', ['message'=>'Collection {{$request->id}} does not exist.']);
        }

        $collection->title=$request->title;
        $collection->description=$request->description; //htmlentities($request->description),
        $collection->tags=$request->tags;
        $collection->image_url_1=$request->image_url_1;
        $collection->image_url_2=$request->image_url_2;
        $collection->image_url_3=$request->image_url_3;
        $collection->uid=$request->uid;
        $collection->brand_name=$request->brand_name;

        //echo $request->description;
        $collection->save();

        return redirect()->route('collection.edit', ['id'=>$collection->id, 'message'=>'Updated']);


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

    public function delete(Request $request)
    {
        $id = $request->id;
        $collection = Collection::find($id);

        if (!$collection) return back()->withInput();

        if (!$collection->isOwnerOrAdmin(Auth::user())) {
            return back()->withInput();
        }

        CollectionDesigns::where('collection_id', $id)->delete();
        CollectionMockups::where('collection_id', $id)->delete();
        $collection->delete();

        return redirect()->route('collection.index',['message'=>'Collection has been deleted.']);
    }

    public function new_mockup2(Request $request) {
        //json structure
        $success = 1;
        $data = [];

        if (!$request->design_id || !$request->collection_id) return response()->json([
            'success' => 0, //missing input data
            'data' => $data
        ]);

        $design_id = $request->design_id;
        $collection_id = $request->collection_id;
        $etsyshop = EtsyShop::find($request->etsyshop_id);

        $design = Design::find($design_id);
        if (!$design) return response()->json([
            'success' => -1, //resource not found
            'data' => $data
        ]);

        if ($design->filename != null) { //make mockup for artwork
            //select random 1 mockup in collection
            $mockup_ids = CollectionMockups::where('collection_id',$collection_id)->pluck('mockup_id')->toArray();
            if (!$mockup_ids) return response()->json([
                'success' => -1, //resource not found
                'data' => $data
            ]);

            $mockup = Mockup::whereIn('id',$mockup_ids)->where('is_active',true)->where('color','!=',$design->color)->inRandomOrder()->first();

            if (!$mockup) return response()->json([
                'success' => -1, //resource not found
                'data' => $data
            ]);

            $data = $design->makeMockup2($mockup, $etsyshop->shop_name, $design_id);
        } else { //upload thumbnail to become mockup
            $data = $design->makeMockupFromThumbnail2($etsyshop->shop_name, $design_id);
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    public function ajaxSearchDesign(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 mockups by keyword
        $keyword = $request->keyword;
        $collection_id = $request->id;
        $alreadyIds = CollectionDesigns::where('collection_id',$collection_id)->pluck('design_id')->toArray();

        $user_id = $request->user()->id;
        $num = 25;

        if ($request->user()->isAdmin()) {
            $user_condition = ">";
            $user_value = 0;
        } else {
            $user_condition = "=";
            $user_value = $request->user()->id;
        }

        $designs = Design::whereNotIn('id', $alreadyIds)
                            ->where('owner_id', $user_condition, $user_value)
                            ->where('owner_id', Auth::id()) // design theo id
                            ->where(function ($query) use ($keyword) {
                                $query->where('title', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('title80', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('tags', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('color', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('type', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('id', '=', $keyword);
                            })
                            ->orderBy('created_at', 'DESC')->limit($num)->get();

        $data = $designs;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * add designs to a collection
     */
    public function ajaxAddDesigns(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id || !$request->design_ids) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 designss by keyword
        $design_ids = $request->design_ids;
        $collection_id = $request->id;
        $data = array();

        foreach ($design_ids as $mid) {
            CollectionDesigns::create([
                "collection_id" => $collection_id,
                "design_id" => $mid,
            ]);

            $data[] = $mid;
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * add designss to a collection
     */
    public function ajaxRemoveDesigns(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id || !$request->design_ids) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 designss by keyword
        $design_ids = $request->design_ids;
        $collection_id = $request->id;

        CollectionDesigns::where('collection_id',$collection_id)->whereIn('design_id', $design_ids)->delete();
        $data = $design_ids;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }


    public function ajaxSearchMockup(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 mockups by keyword
        $keyword = $request->keyword;
        $collection_id = $request->id;
        $alreadyIds = CollectionMockups::where('collection_id',$collection_id)->pluck('mockup_id')->toArray();

        $user_id = $request->user()->id;
        $numMockups = 100;
        $mockups = Mockup::where('owner_id', $user_id)
                            ->where('is_active', true)
                            ->whereNotIn('id', $alreadyIds)
                            ->where(function ($query) use ($keyword) {
                                $query->where('title', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('color', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('color_name', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('color_map', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('type', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('id', '=', $keyword);
                            })
                            ->orderBy('created_at', 'DESC')->get();
                            // ->orderBy('created_at', 'DESC')->limit($numMockups)->get();

        $data = $mockups;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * add mockups to a collection
     */
    public function ajaxAddMockups(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id || !$request->mockup_ids) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 mockups by keyword
        $mockup_ids = $request->mockup_ids;
        $collection_id = $request->id;
        $data = array();

        foreach ($mockup_ids as $mid) {
            CollectionMockups::create([
                "collection_id" => $collection_id,
                "mockup_id" => $mid,
            ]);

            $data[] = $mid;
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * add mockups to a collection
     */
    public function ajaxRemoveMockups(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id || !$request->mockup_ids) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 mockups by keyword
        $mockup_ids = $request->mockup_ids;
        $collection_id = $request->id;

        CollectionMockups::where('collection_id',$collection_id)->whereIn('mockup_id', $mockup_ids)->delete();
        $data = $mockup_ids;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    public function export(Request $request)
    {
        $id = $request->id;

        $collection = Collection::find($id);

        if (!$collection) return redirect()->route('collection.index',['message'=>'Collection not found!']);

        if (!$collection->isOwnerOrAdmin(Auth::user())) {
            return redirect()->route('collection.index',['message'=>'Not permission!']);
        }

        $target = $request->target;
        if (!$target) $target = "etsycsv";

        $design_ids = CollectionDesigns::where('collection_id',$id)->pluck('design_id')->toArray();

        $designs = Design::whereIn('id', $design_ids)->get();

        $mockup_ids = CollectionMockups::where('collection_id',$id)->pluck('mockup_id')->toArray();

        $mockups = Mockup::whereIn('id', $mockup_ids)->get();

        //$pure_mockups = Mockup::where('owner_id',$request->user()->id)->where('is_pure',true)->get();

        $data = [
            'collection'=>$collection,
            'designs'=>$designs,
            'mockups'=>$mockups
        ];

        return view('collection/exportcsv')
            ->with('data', $data);
    }

    /**
     * make new mockup for a design in a collection
     */
    public function new_mockup(Request $request) {
        //json structure
        $success = 1;
        $data = [];

        if (!$request->design_id || !$request->collection_id) return response()->json([
            'success' => 0, //missing input data
            'data' => $data
        ]);

        $design_id = $request->design_id;
        $collection_id = $request->collection_id;

        $design = Design::find($design_id);
        if (!$design) return response()->json([
            'success' => -1, //resource not found
            'data' => $data
        ]);

        if ($design->filename != null) { //make mockup for artwork
            //select random 1 mockup in collection
            $mockup_ids = CollectionMockups::where('collection_id',$collection_id)->pluck('mockup_id')->toArray();
            if (!$mockup_ids) return response()->json([
                'success' => -1, //resource not found
                'data' => $data
            ]);

            $mockup = Mockup::whereIn('id',$mockup_ids)->where('is_active',true)->where('color','!=',$design->color)->inRandomOrder()->first();

            if (!$mockup) return response()->json([
                'success' => -1, //resource not found
                'data' => $data
            ]);

            $data = $design->makeMockup($mockup);
        } else { //upload thumbnail to become mockup
            $data = $design->makeMockupFromThumbnail();
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * upload csv/xls after collection export
     */
    public function addCollectionExport(Request $request)
    {
        $success = 0;
        $data = [];

        $this->validate($request, [
            'fileToUpload' => 'required',
        ]);

        if ($request->hasFile('fileToUpload')) {
            $owner_id = $request->user()->id;
            $collection_id = $request->collection_id;
            $type = $request->type;

            $file = $request->file('fileToUpload');
            $size = $file->getSize();
            $extension = $file->getClientOriginalExtension();
            $name = basename($file->getClientOriginalName(), '.' . $extension);

            $dirname = 'exports/u' . $owner_id . '/' . Carbon::now()->format('ymd');
            $filename = $file->storeAs($dirname, $file->getClientOriginalName());

            $data = CollectionExport::create(
                [
                    'owner_id'      => $owner_id,
                    'collection_id'   => $collection_id,
                    'name'         => $name,
                    'type'       => $type,
                    'extension' => $extension,
                    'size'      => $size,
                    'filename'     => $filename,
                ]
            );

            $success = 1;
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);

    }
}
