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


class UploadController extends Controller
{
    public function upload(Request $request)
    {
        return view('upload.form');
    }

    public function uploadSubmit(Request $request)
    {
        $this->validate($request, [
            'filesToUpload' => 'required',
            'filesToUpload.*' => 'image|mimes:jpg,jpeg,png,svg'
        ]);

        if ($request->hasFile('filesToUpload')) {
            $user_id = $request->user()->id;
            $allowedfileExtension = ['psd', 'jpg', 'png', 'svg'];

            $files = $request->file('filesToUpload');

            $dirname = 'images/u' . $user_id;

            // 2021-07: move to S3
            $awsS3Service = AwsS3Service::instance();

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $title = basename($file->getClientOriginalName(), '.' . $extension);
                $filename = 'd' . Carbon::now()->format('ymdHis-') . Str::slug($title,'-') . '.' . $extension;

                $size = $file->getSize();
                //$check = in_array($extension, $allowedfileExtension);

                // 2021-07: move to S3
                // $pathname = $file->storeAs($dirname, $filename);
                // $fullpath = Storage::url($pathname);
                $pathname = $awsS3Service->storeUploadFile($file, $dirname, $filename);
                $fullpath = $awsS3Service->getUrl($pathname);

                // 2021-07: move to S3
                // $imagesize = getimagesize(asset($fullpath));
                $imagesize = getimagesize($fullpath);
                $width = $imagesize[0];
                $height = $imagesize[1];

                $design = Design::create(
                    [
                        'owner_id'      => $user_id,
                        'designer_id'   => $user_id,
                        'title'         => $title,
                        'description'   => $title,
                        'tags'          => $title,

                        'dirname'   => $dirname,
                        'filename'  => $filename,
                        'pathname'  => $pathname,
                        'fullpath'  => $fullpath,
                        'extension' => $extension,
                        'size'      => $size,
                        'width'     => $width,
                        'height'    => $height,
                    ]
                );

                $design->makeThumbnail();

                //echo "<br/>Upload Successfully: " . $pathname;
            }
        }

        return redirect('design/index');

    }
}
