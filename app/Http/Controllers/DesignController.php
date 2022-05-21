<?php

namespace App\Http\Controllers;

use App\Services\AwsS3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Design;
use App\Mockup;
use Carbon\Carbon;
use PHPUnit\Framework\Constraint\Exception;
use App\Collection;
use App\CollectionDesigns;
use App\User;

class DesignController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->middleware('auth.user');

        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    public function index(Request $request) {
        if (!$request->has('keyword')) {
            $keyword = "";
        } else {
            $keyword = $request->keyword;
        }

        if (!$request->has('designer_id')) {
            if ($request->user()->isAdmin() || $request->user()->isSeller()) { //select all designer
                $designer_condition = ">";
                $designer_id = 0;
            } else { //only this designer
                $designer_condition = "=";
                $designer_id = $request->user()->id;
            }
        } elseif ($request->designer_id == 0) {
            $designer_condition = ">";
            $designer_id = 0;
        } else {
            $designer_condition = "=";
            $designer_id = $request->designer_id;
        }

        if (!$request->has('owner_id')) {
            if ($request->user()->isAdmin() || $request->user()->isDesigner()) { //select all seller
                $owner_condition = ">";
                $owner_id = 0;
            } else { //only this seller
                $owner_condition = "=";
                $owner_id = $request->user()->id;
            }
        } elseif ($request->owner_id == 0) {
            $owner_condition = ">";
            $owner_id = 0;
        } else {
            $owner_condition = "=";
            $owner_id = $request->owner_id;
        }

        if (!$request->has('collection_id')) {
            $collection_condition = ">";
            $collection_id = 0;
        } elseif ($request->collection_id == 0) {
            $collection_condition = ">";
            $collection_id = 0;
        } else {
            $collection_condition = "=";
            $collection_id = $request->collection_id;
        }

        //shared = 0: no share (only mockup), 1: share design
        if (!$request->has('shared')) {
            $shared_condition = "=";
            $shared_id = 1;             // filter artwork
        } else {                        // filter mockup
            $shared_condition = "=";
            $shared_id = $request->shared;

            //only this seller
            if ($request->shared == 0) {
                if ($request->user()->id != 1) { //except super admin
                    $owner_condition = "=";
                    $owner_id = $request->user()->id;
                }
            }
        }

        if ($collection_id != 0) { //1 collection
            $design_ids_in_collection = CollectionDesigns::where('collection_id',$collection_condition,$collection_id)->pluck('design_id')->toArray();

            $data = Design::where('owner_id', $owner_condition, $owner_id)->where('designer_id', $designer_condition, $designer_id)
                    ->where('is_shared', $shared_condition, $shared_id)
                    ->whereIn('id', $design_ids_in_collection)
                    ->where(function ($query) use ($keyword) {
                        $query->where('title', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('title80', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('tags', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('type', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('id', '=', $keyword);
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        } else { // all collections
            $data = Design::where('owner_id', $owner_condition, $owner_id)->where('designer_id', $designer_condition, $designer_id)
                    ->where('is_shared', $shared_condition, $shared_id)
                    ->where(function ($query) use ($keyword) {
                        $query->where('title', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('title80', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('tags', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('type', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('id', '=', $keyword);
                    })
                    ->orderBy('created_at','desc')->paginate($this->pagesize);
        }

        $collections = Collection::where('owner_id',$request->user()->id)->get()->keyBy('id');

        $users = User::withTrashed()->orderBy('name','ASC')->get()->keyBy('id');

        $filters = [
            'designer_id' => $designer_id,
            'owner_id'=> $owner_id,
            'collection_id' => $collection_id,
            'shared' => $shared_id,
            'keyword' => $keyword,
        ];
        
        $blackWordList = app(BlacklistWordController::class)->getBlackWordList();
        
        return view('design.index')
            ->with('data', $data)
            ->with('designer_id', $designer_id)->with('owner_id', $owner_id)
            ->with('collection_id', $collection_id)->with('keyword', $keyword)
            ->with('filters', $filters)
            ->with('collections', $collections)
            ->with('users', $users)
            ->with('black_word_list', $blackWordList);
    }

    public function trashed(Request $request) {
        if ($request->user()->isAdmin()) {
            $user_condition = ">";
            $user_value = 0;
        } else {
            $user_condition = "=";
            $user_value = $request->user()->id;
        }

        $data = Design::onlyTrashed()->where('owner_id', $user_condition, $user_value)->orderBy('deleted_at','desc')->paginate($this->pagesize);

        return view('design.trashed')
            ->with('data', $data);
    }

    public function upload()
    {
        return view('design.ajaxupload');
    }

    public function ajaxUpload(Request $request)
    {
        $success = 1;
        $data = [];

        $this->validate($request, [
            'filesToUpload' => 'required',
            //'filesToUpload.*' => 'image|mimes:jpg,jpeg,png,svg'
        ]);

        if ($request->hasFile('filesToUpload')) {
            $user_id = $request->user()->id;
            //$allowedfileExtension = ['psd', 'jpg', 'png', 'svg'];

            $files = $request->file('filesToUpload');

            $color = $request->color;
            $artwork_or_mockup = $request->artwork_or_mockup;

            $dirname = 'images/d/' . Carbon::now()->format('ymd');

            foreach ($files as $file) {
                //size, w, h
                $size = $file->getSize();
                $imagesize = getimagesize($file);
                $width = $imagesize[0];
                $height = $imagesize[1];

                $extension = $file->getClientOriginalExtension();
                $title = basename($file->getClientOriginalName(), '.' . $extension);

                $design = Design::create(
                    [
                        'owner_id'      => $user_id,
                        'designer_id'   => $user_id,
                        'title'         => $title,
                        'title80'       => $title,
                        'description'   => $title,
                        'tags'          => $title,
                        'color'         => $color,

                        'extension' => $extension,
                        'size'      => $size,
                        'width'     => $width,
                        'height'    => $height,
                    ]
                );

                if ($artwork_or_mockup == "artwork") {
                    //move artword file
                    $newfolder = 'd' . $design->id;
                    $newfilename = $newfolder . '/d-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

                    // $filename = $file->storeAs($dirname, $newfilename);
                    // 2021-07: move to S3
                    $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

                    $design->filename = $filename;
                    $design->is_shared = 1;
                    $design->save();

                    //make thumnail
                    $design->makeThumbnail();
                } else {
                    //move mockup file
                    $newfolder = 'm' . $design->id;
                    $newfilename = $newfolder . '/m-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

                    // $filename = $file->storeAs($dirname, $newfilename);
                    // 2021-07: move to S3
                    $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

                    $design->thumbnail = $filename;
                    $design->is_shared = 0;
                    $design->save();
                }

                $data[] = $design;
            }
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);

    }
    
    public function replace($id)
    {
        return view('design.replace')
            ->with('id',$id);
    }

    public function ajaxReplace(Request $request)
    {
        $success = 1;
        $data = [];

        $this->validate($request, [
            'filesToUpload' => 'required',
            //'filesToUpload.*' => 'image|mimes:jpg,jpeg,png,svg'
        ]);       
        
        if ($request->hasFile('filesToUpload')) {
            // $user_id = $request->user()->id;
            //$allowedfileExtension = ['psd', 'jpg', 'png', 'svg'];

            $files = $request->file('filesToUpload');
            
            $color = $request->color;
            $artwork_or_mockup = $request->artwork_or_mockup;

            $dirname = 'images/d/' . Carbon::now()->format('ymd');
            
            foreach ($files as $file) {
                //size, w, h
                $size = $file->getSize();
                $imagesize = getimagesize($file);
                $width = $imagesize[0];
                $height = $imagesize[1];

                $extension = $file->getClientOriginalExtension();
                $title = basename($file->getClientOriginalName(), '.' . $extension);
                $design = Design::find($request->designId);
                
                if (!$design->isOwnedOrDesignedOrAdmin($request->user()))
                    return response()->json([
                        'success' => -2, //not permission
                        'data' => null
                    ]);
                
                $design ->extension = $extension;
                $design ->size = $size;
                $design ->width = $width;
                $design ->height = $height;
                
                if ($artwork_or_mockup == "artwork") {
                    //move artword file
                    $newfolder = 'd' . $design->id;
                    $newfilename = $newfolder . '/d-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

                    // $filename = $file->storeAs($dirname, $newfilename);
                    // 2021-07: move to S3
                    $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

                    $design->filename = $filename;
                    $design->is_shared = 1;
                    $design->save();

                    //make thumnail
                    $design->makeThumbnail();
                } else {
                    //move mockup file
                    $newfolder = 'm' . $design->id;
                    $newfilename = $newfolder . '/m-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

                    // $filename = $file->storeAs($dirname, $newfilename);
                    // 2021-07: move to S3
                    $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

                    $design->thumbnail = $filename;
                    $design->is_shared = 0;
                    $design->save();
                }

                $data[] = $design;
            }
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);

    }
    
    public function uploadSubmit(Request $request)
    {
        $this->validate($request, [
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,svg'
        ]);

        if ($request->hasFile('images')) {
            $user_id = $request->user()->id;
            //$allowedfileExtension = ['psd', 'jpg', 'png', 'svg'];

            $files = $request->file('images');

            $dirname = 'images/d/' . Carbon::now()->format('ymd');

            foreach ($files as $file) {
                //size, w, h
                $size = $file->getSize();
                $imagesize = getimagesize($file);
                $width = $imagesize[0];
                $height = $imagesize[1];

                $extension = $file->getClientOriginalExtension();
                $title = basename($file->getClientOriginalName(), '.' . $extension);

                $design = Design::create(
                    [
                        'owner_id'      => $user_id,
                        'designer_id'   => $user_id,
                        'title'         => $title,
                        'title80'       => $title,
                        'description'   => $title,
                        'tags'          => $title,

                        'extension' => $extension,
                        'size'      => $size,
                        'width'     => $width,
                        'height'    => $height,
                    ]
                );

                //move file
                $newfolder = 'd' . $design->id;
                $newfilename = $newfolder . '/d-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

                //$filename = $file->storeAs($dirname, $newfilename);
                // 2021-07: move to S3
                $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

                $design->filename = $filename;
                $design->save();

                $design->makeThumbnail();

            }
        }

        return redirect('design');

    }

    /**
     * create all mockups for a design
     */
    public function generate_mockup(Request $request) {

        if (!$request->id) return redirect('design/index');

        $design_id = $request->id;
        $user_id = $request->user()->id;

        $design = Design::find($design_id);
        if (!$design) return redirect('design/index');

        $mockups = Mockup::where('owner_id', $user_id)->where('is_active', true)->orderBy('created_at','desc')->get();
        $data = array();

        foreach ($mockups as $m) {
            $data[] = $design->makeMockup($m);
        }

        return view('design.generate_mockup')
            ->with('data', $data);
    }

    /**
     * create 4 random mockups for a design
     *
     * return JSON
     */
    public function create_mockup(Request $request) {
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        $design_id = $request->id;
        $design = Design::find($design_id);
        if (!$design) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        // if ((Auth::user()->isAdmin()) || ($design->isCreatedByUser(Auth::user()))) {
        //     return response()->json([
        //         'success' => -1, //not permission
        //         'data' => $data
        //     ]);
        // }

        //get random 3 mockups
        $user_id = $request->user()->id;
        $numMockups = 4;
        //$mockups = Mockup::where('owner_id', $user_id)->where('is_active', true)->inRandomOrder()->limit($numMockups)->get();

        $mockups = Mockup::where('owner_id', $user_id)->where('is_active', true)->where('color','!=',$design->color)->inRandomOrder()->limit($numMockups)->get();

        $data = array();

        $data['design_id'] = $design->id;
        $data['mockups'] = $mockups;

        // foreach ($mockups as $m) {
        //     $data[] = $design->makeMockup($m);
        // }

        // return response()->json([
        //     'success' => $success,
        //     'data' => $data
        // ]);

        return view('design.create_mockup')
            ->with('data', $data);
    }

    /**
     * make new mockup for a design
     */
    public function new_mockup(Request $request) {
        //json structure
        $success = 1;
        $data = [];

        if (!$request->design_id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        $design_id = $request->design_id;
        $mockup_id = $request->mockup_id;

        $design = Design::find($design_id);
        $mockup = Mockup::find($mockup_id);
        if (!$design || !$mockup) return response()->json([
            'success' => 0,
            'data' => $data
        ]);


        //$data = $design->makeMockup($mockup);
        if ($design->filename != null) { //make mockup for artwork
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

        Collection::create([
            'owner_id' => $request->user()->id,
            'title' => $request->title,
            'title80' => $request->title,
        ]);

        return redirect('collection');
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

    public function delete(Request $request)
    {
        if ($request->ajax()) {
        // if (!Auth::user()->isAdmin()) {
        //     return view('error/message')->with('message', 'Access denied!');
        // }
            $design = Design::find($request->id);

            if (!$design) return 0;

            if (!$design->isOwnedOrDesignedOrAdmin(Auth::user())) {
                return -1;
            }

            CollectionDesigns::where('design_id', $request->id)->delete();

            // Storage::delete($design->filename);
            // Storage::delete($design->thumbnail);

            // $path = dirname($design->filename);
            // Storage::deleteDirectory($path);

            // 2021-07: move to S3
            $design->removeFiles();
            $design->delete();

            return 1;
        }
    }

    /**
     * Delete from trash
     */
    public function forceDelete(Request $request)
    {
        // if (!Auth::user()->isAdmin()) {
        //     return view('error/message')->with('message', 'Access denied!');
        // }

        if ($request->ajax()) {
            $design = Design::withTrashed()->find($request->id);

            if (!$design) return 0;

            if (!$design->isOwnedOrDesignedOrAdmin(Auth::user())) {
                return -1;
            }

            if ((Auth::user()->isAdmin()) || ($design->isCreatedByUser(Auth::user()))) {

                try {

                    //CollectionDesigns::withTrashed()->where('design_id', $request->id)->delete();

                    /*
                    Storage::delete($design->filename);
                    Storage::delete($design->thumbnail);

                    $path = dirname($design->filename);
                    //return $path;
                    if ($path != '.') Storage::deleteDirectory($path);
                    */

                    // 2021-07: move to S3
                    $design->removeFiles();
                } catch (\Exception $e) {

                }

                $design->forceDelete();

                return 1;
            } else {
                return -1;
            }
        }
    }

    /**
     * Restore from trash
     */
    public function restore(Request $request)
    {
        if ($request->ajax()) {
            $design = Design::withTrashed()->find($request->id);

            if (!$design) return 0;

            if (!$design->isOwnerOrAdmin(Auth::user())) {
                return -1;
            }

            if ((Auth::user()->isAdmin()) || ($design->isCreatedByUser(Auth::user()))) {

                $design->restore();

                return 1;
            } else {
                return -1;
            }
        }
    }

    public function ajaxSearchCollection(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        //get random 10 mockups by keyword
        $keyword = $request->keyword;
        $design_id = $request->id;
        $alreadyIds = CollectionDesigns::where('design_id',$design_id)->pluck('collection_id')->toArray();

        $user_id = $request->user()->id;
        $num = 20;
        $collections = Collection::where('owner_id', $user_id)
                            ->whereNotIn('id', $alreadyIds)
                            ->where(function ($query) use ($keyword) {
                                $query->where('title', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('tags', 'LIKE', '%'.$keyword.'%')
                                      ->orWhere('id', '=', $keyword);
                            })
                            ->orderBy('created_at', 'DESC')->limit($num)->get();

        $data = $collections;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * add designs to collections
     * input: 2 array design_ids and collection_ids
     */
    public function ajaxAddCollections(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->design_ids || !$request->collection_ids) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        $collection_ids = $request->collection_ids;
        $data = array();
        foreach ($request->design_ids as $did) {
            foreach ($collection_ids as $cid) {
                CollectionDesigns::updateOrCreate(
                    ["collection_id" => $cid, "design_id" => $did],
                    ["collection_id" => $cid, "design_id" => $did]
                );
                // CollectionDesigns::create([
                //     "collection_id" => $cid,
                //     "design_id" => $did,
                // ]);

                $data[] = [ 'design_id'=> $did, 'collection_id' => $cid ];
            }
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * update title of a design
     */
    public function ajaxUpdateDesign(Request $request){

        //json structure
        $success = 1;
        $data = [];

        // if (!$request->design_id || !$request->design_title || !$request->design_title80)
        if (!$request->design_id || !$request->design_title80)
            return response()->json([
                'success' => 0,
                'data' => $data
            ]);

        $id = $request->design_id;
        $data = array();

        $design = Design::find($id);
        if (!$design)
            return response()->json([
                'success' => 0,
                'data' => $data
            ]);

        if (!$design->isOwnedOrDesignedOrAdmin($request->user()))
            return response()->json([
                'success' => -1, //not permission
                'data' => $data
            ]);

        // $design->title = ucwords($request->design_title);
        $design->title = ucwords($request->design_title80);
        $design->title80 = ucwords($request->design_title80);
        $design->tags = strtolower($request->design_tags);
        $design->color = $request->design_color;
        $design->type = $request->design_type;

        if ($request->has('design_owner')) {
            $design->owner_id = $request->design_owner;
        }
        if ($request->has('design_designer')) {
            $design->designer_id = $request->design_designer;
        }

        $design -> credit = $request->credit;

        $design->save();

        $data = $design;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * update title of a design
     */
    public function ajaxGetDesign(Request $request){
        //json structure
        $success = 1;
        $data = [];

        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        $id = $request->id;

        $design = Design::find($id);
        if (!$design) return response()->json([
            'success' => 0,
            'data' => $data
        ]);

        $data = $design;

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
    }

    /**
     * Copy & Paste a design information (title and tags) using session by ajax
     */
    public function copy(Request $request) {
        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => null
        ]);

        $request->session()->put('design_id', $request->id);

        return response()->json([
            'success' => 1,
            'data' => $request->id
        ]);
    }

    public function paste(Request $request){
        if (!$request->id) return response()->json([
            'success' => 0,
            'data' => null
        ]);

        //destination
        $id = $request->id;

        $design = Design::find($id);
        if (!$design) return response()->json([
            'success' => -1, //not found design
            'data' => null
        ]);

        //source
        if (!$request->session()->has('design_id')) {
            return response()->json([
                'success' => -3, //not found in clipboard
                'data' => null
            ]);
        }

        $ssid = $request->session()->get('design_id');
        $ssdesign = Design::find($ssid);
        if (!$ssdesign) return response()->json([
            'success' => -1, //not found design
            'data' => null
        ]);

        if (!$ssdesign->isOwnedOrDesignedOrAdmin($request->user()))
            return response()->json([
                'success' => -2, //not permission
                'data' => null
            ]);

        //copy
        $design->title = $ssdesign->title;
        $design->title80 = $ssdesign->title80;
        $design->tags = $ssdesign->tags;
        $design->save();

        return response()->json([
            'success' => 1,
            'data' => $design
        ]);
    }
    
    public function getAmazonDesignImage(Request $request) {
        $design = Design::find($request->design_id);
        if (!$design) {
            return response()->json([
                'message' => 'Design image not found'
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'thumbnail' => $design->thumbnail,
            'filename' => $design->filename
        ]);
    }
}
