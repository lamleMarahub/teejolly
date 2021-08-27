<?php

namespace App\Http\Controllers;

use App\Services\AwsS3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use App\Design;
use App\Mockup;
use Carbon\Carbon;

class MockupController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->middleware('auth.user');
        $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    }

    public function index(Request $request) {
        $user_id = $request->user()->id;
        $data = Mockup::where('owner_id', $user_id)->orderBy('created_at','desc')->paginate($this->pagesize);

        return view('mockup.index')
            ->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('mockup.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $success = 1;
        $data = [];

        $this->validate($request, [
            // 'filesToUpload' => 'required',
            // 'filesToUpload' => 'image|mimes:jpg,jpeg,png,svg'
        ]);

        if ($request->hasFile('filesToUpload')) {
            $user_id = $request->user()->id;
            //$allowedfileExtension = ['psd', 'jpg', 'png', 'svg'];

            $file = $request->file('filesToUpload');

            $dirname = 'images/m/' . Carbon::now()->format('ymd');

            //size, w, h
            $size = $file->getSize();
            $imagesize = getimagesize($file);
            $width = $imagesize[0];
            $height = $imagesize[1];

            $extension = $file->getClientOriginalExtension();
            $title     = Input::get('title');

            $mockup = Mockup::create(
                [
                    'owner_id'      => $user_id,
                    'title'         => $title,
                    'design_x'      => Input::get('design_x'),
                    'design_y'      => Input::get('design_y'),
                    'design_width'  => Input::get('design_width'),
                    'design_height' => Input::get('design_height'),
                    'design_angle'  => Input::get('design_angle'),
                    'design_opacity'=> Input::get('design_opacity'),
                    'color'         => Input::get('color'),
                    'type'          => Input::get('type'),

                    'color_name'    => Input::get('color_name'),
                    'color_map'     => Input::get('color_map'),
                    'color_code'    => Input::get('color_code'),

                    'extension' => $extension,
                    'size'      => $size,
                    'width'     => $width,
                    'height'    => $height,
                ]
            );

            //move file
            $newfolder = 'm' . $mockup->id;
            $newfilename = $newfolder . '/m-' . Str::random(5) .'-'. Str::slug($title,'-') . '.' . $extension;

            // $filename = $file->storeAs($dirname, $newfilename);
            // 2021-07: move to S3
            $filename = AwsS3Service::instance()->storeUploadFile($file, $dirname, $newfilename);

            $mockup->filename = $filename;
            $mockup->save();

            $data[] = $mockup;
        }

        return response()->json([
            'success' => $success,
            'data' => $data
        ]);
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
            $mockup = Mockup::find($request->id);

            if (!$mockup) return 0;

            if (!$mockup->isOwnerOrAdmin(Auth::user())) {
                return -1;
            }

            if ((Auth::user()->isAdmin()) || ($mockup->isCreatedByUser(Auth::user()))) {
                $mockup->delete();

                return 1;
            } else {
                return -1;
            }
        }
    }
}
