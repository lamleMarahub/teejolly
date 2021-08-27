<?php

namespace App;

use App\Services\AwsS3Service;
use App\Traits\DesignTrait;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\ImgurService;
use Illuminate\Database\Eloquent\SoftDeletes;
class Design extends Model
{
    use SoftDeletes;
    // 2021-07: move to S3
    use DesignTrait;

    protected $fillable = [
        'owner_id','designer_id',
        'title','title80','description','tags','color',
        'dirname','filename','pathname','fullpath','size','extension','width','height','thumbnail',
        'is_shared'
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $appends = ['file_url', 'thumbnail_url'];

    public function isOwnedOrDesignedOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
        // if ($user->id==7) return true;
		return ($this->owner_id == $user->id) || ($this->designer_id == $user->id);
	}

    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
	}

    public function isCreatedByUser(User $user) {
		if (!$user) return false;
		return ($this->owner_id == $user->id);
	}

	public function isCreatedByUserId($userId) {
		$user = User::find($userId);
		return $this->isCreatedByUser($user);
	}

	public function isDesignedByUser(User $user) {
		if (!$user) return false;
		return ($this->designer_id == $user->id);
	}

	public function isDesignedByUserId($userId) {
		$user = User::find($userId);
		return $this->isDesignedByUser($user);
	}

    public function makeThumbnail($new_width = 300, $new_height = 300, $color = '#C2B28F')
    {
        // $original_file = asset('storage/'.$this->filename);
        // $new_thumbnail_file = dirname($this->filename) . '/t-' . Str::random(5) . '-' . Str::slug($this->title,'-') . "-$new_width"."x$new_height." . $this->extension;
        // 2021-07: move to S3
        $original_file = $this->file_url;
        $new_thumbnail_file = $this->getThumbnailFileName($new_width, $new_height);

        if ($this->witdh > $this->height) {
            $thumbnail_image = Image::make($original_file)->resize($new_width-10, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $thumbnail_image = Image::make($original_file)->resize(null, $new_height-10, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $img = Image::canvas($new_width, $new_height);
        $img->fill($color);
        $img->insert($thumbnail_image, 'center');

        //$img->save($realpath);
        $img->save('storage/'.$new_thumbnail_file);

        // 2021-07: move to S3
        AwsS3Service::instance()->upload('', $new_thumbnail_file);

        $this->thumbnail = $new_thumbnail_file;
        $this->save();

        return $this->thumbnail;
    }

    /**
     * make mockup from artwork
     * $storageService = ['local','imgur']
     */
    public function makeMockup($mockup, $storageService = 'local')
    {
        //process artwork
        $new_width = $mockup->design_width;
        $new_height = $mockup->design_height;

        // $original_artwork_file = asset('storage/'.$this->filename);
        // 2021-07: move to S3
        // $original_artwork_file = Image::make($this->file_url);
        $original_artwork_file = $this->file_url;
       
        if ($this->witdh > $this->height) {
            $artwork_file = Image::make($original_artwork_file)->resize($new_width, null, function ($constraint) {
                $constraint->aspectRatio();
                //$constraint->upsize();
            });
        } else {
            $artwork_file = Image::make($original_artwork_file)->resize(null, $new_height, function ($constraint) {
                $constraint->aspectRatio();
                //$constraint->upsize();
            });
        }

        $degree = -$mockup->design_angle;
        $artwork_file->rotate($degree);

        //load mockup file
        // $mockup_file = Image::make(asset('storage/'.$mockup->filename));
        // 2021-07: move to S3
        // $mockup_file = Image::make($this->file_url);
        $mockup_file = Image::make($mockup->file_url);
       
        //insert artwork
        $mockup_file->insert($artwork_file, 'top-left', $mockup->design_x, $mockup->design_y);

        //save new mockup file
        $new_mockup_file = "";

        switch ($storageService) {
            case 'imgur':
                $new_mockup_file = ImgurService::uploadImageFromBinary($mockup_file);
                break;

            default: //local
                $dir = 'images/tmp/' . Carbon::now()->format('ymd');
                Storage::makeDirectory($dir);
                $new_mockup_file = $dir . '/m' . Str::random(5) . '-' . Str::slug($this->title,'-') . "-$new_width"."x$new_height." . "jpg";//$this->extension;
                $mockup_file->save('storage/'.$new_mockup_file);
                // $new_mockup_file = asset('storage/'.$new_mockup_file);

                // 2021-07: move to S3
                $awsS3Service = AwsS3Service::instance();
                $awsS3Service->upload('', $new_mockup_file);
                $new_mockup_file = $awsS3Service->getUrl($new_mockup_file);

                break;
        }


        //
        //return asset('storage/'.$new_mockup_file);
        return $new_mockup_file;
    }

    /**
     * make mockup from this thumbnail (no need mockup resource)
     */
    public function makeMockupFromThumbnail($storageService = 'local')
    {
        // $thumbnail_file = asset('storage/'.$this->thumbnail);
        // 2021-07: move to S3
        $thumbnail_file = Image::make($this->thumbnail_url);
        $mockup_file = Image::make($thumbnail_file);

        //add some pixels
        $t = rand(0,10);
        for ($i=0; $i<$t; $i++) {
            $x = rand(0,$mockup_file->width()-1);
            $y = rand(0,$mockup_file->height()-1);

            $mockup_file->pixel("#000", $x, $y);
        }

        //save new mockup file
        $new_mockup_file = "";

        switch ($storageService) {
            case 'imgur':
                $new_mockup_file = ImgurService::uploadImageFromBinary($mockup_file);
                break;

            default: //local
                $dir = 'images/tmp/' . Carbon::now()->format('ymd');
                Storage::makeDirectory($dir);
                //$new_mockup_file = $dir . '/m' . Str::random(5) . '-' . Str::slug($this->title,'-') . "." . "jpg";//$this->extension;
                $new_mockup_file = $dir . '/m' . Str::random(15) . ".jpg";//$this->extension;
                $mockup_file->save('storage/'.$new_mockup_file);
                // $new_mockup_file = asset('storage/'.$new_mockup_file);
                // 2021-07: move to S3
                $awsS3Service = AwsS3Service::instance();
                $awsS3Service->upload('', $new_mockup_file);
                $new_mockup_file = $awsS3Service->getUrl($new_mockup_file);
                break;
        }

        //$new_mockup_file = ImgurService::uploadImageFromUrl(asset('storage/'.$this->thumbnail));

        return $new_mockup_file;
    }

    public function makeMockup2($mockup, $watermark, $design_id, $storageService = 'local')
    {
        //process artwork
        $new_width = $mockup->design_width;
        $new_height = $mockup->design_height;

        // $original_artwork_file = asset('storage/'.$this->filename);
        // 2021-07: move to S3
        // $original_artwork_file = Image::make($this->file_url);
        
        $original_artwork_file = $this->file_url;
        
        if ($this->witdh > $this->height) {
            $artwork_file = Image::make($original_artwork_file)->resize($new_width, null, function ($constraint) {
                $constraint->aspectRatio();
                //$constraint->upsize();
            });
        } else {
            $artwork_file = Image::make($original_artwork_file)->resize(null, $new_height, function ($constraint) {
                $constraint->aspectRatio();
                //$constraint->upsize();
            });
        }

        $degree = -$mockup->design_angle;
        $artwork_file->rotate($degree);

        //load mockup file
        // $mockup_file = Image::make(asset('storage/'.$mockup->filename));
        // 2021-07: move to S3
        // $mockup_file = Image::make($this->file_url);
        $mockup_file = Image::make($mockup->file_url);

        //insert artwork
        $mockup_file->insert($artwork_file, 'top-left', $mockup->design_x, $mockup->design_y);

        $mockup_file->text($watermark.'-'.$design_id, 150, $mockup_file->height()-250, function($font) {
            $font->file(base_path('public/fonts/2.ttf'));
            $font->size(110);
            $font->color('#909090');
            // $font->align('center');
            // $font->valign('top');
            // $font->angle(45);
        });

        //save new mockup file
        $new_mockup_file = "";

        switch ($storageService) {
            case 'imgur':
                $new_mockup_file = ImgurService::uploadImageFromBinary($mockup_file);
                break;

            default: //local
                $dir = 'images/tmp/' . Carbon::now()->format('ymd');
                Storage::makeDirectory($dir);
                $new_mockup_file = $dir . '/m' . Str::random(5) . '-' . Str::slug($this->title,'-') . "-$new_width"."x$new_height." . "jpg";//$this->extension;
                $mockup_file->save('storage/'.$new_mockup_file);
                // $new_mockup_file = asset('storage/'.$new_mockup_file);
                // 2021-07: move to S3
                $awsS3Service = AwsS3Service::instance();
                $awsS3Service->upload('', $new_mockup_file);
                $new_mockup_file = $awsS3Service->getUrl($new_mockup_file);
                break;
        }

        //return asset('storage/'.$new_mockup_file);
        return $new_mockup_file;
    }

    public function makeMockupFromThumbnail2($watermark, $design_id,$storageService = 'local')
    {
        // $thumbnail_file = asset('storage/'.$this->thumbnail);
        // 2021-07: move to S3
        $thumbnail_file = Image::make($this->thumbnail_url);
        $mockup_file = Image::make($thumbnail_file);

        //add some pixels
        $t = rand(0,10);
        for ($i=0; $i<$t; $i++) {
            $x = rand(0,$mockup_file->width()-1);
            $y = rand(0,$mockup_file->height()-1);

            $mockup_file->pixel("#000", $x, $y);
        }

        $mockup_file->text($watermark.'-'.$design_id, 150, $mockup_file->height()-250, function($font) {
            $font->file(base_path('public/fonts/2.ttf'));
            $font->size(110);
            $font->color('#909090');
        });

        //save new mockup file
        $new_mockup_file = "";

        switch ($storageService) {
            case 'imgur':
                $new_mockup_file = ImgurService::uploadImageFromBinary($mockup_file);
                break;

            default: //local
                $dir = 'images/tmp/' . Carbon::now()->format('ymd');
                Storage::makeDirectory($dir);
                //$new_mockup_file = $dir . '/m' . Str::random(5) . '-' . Str::slug($this->title,'-') . "." . "jpg";//$this->extension;
                $new_mockup_file = $dir . '/m' . Str::random(15) . ".jpg";//$this->extension;
                $mockup_file->save('storage/'.$new_mockup_file);
                // $new_mockup_file = asset('storage/'.$new_mockup_file);
                // 2021-07: move to S3
                $awsS3Service = AwsS3Service::instance();
                $awsS3Service->upload('', $new_mockup_file);
                $new_mockup_file = $awsS3Service->getUrl($new_mockup_file);
                break;
        }

        //$new_mockup_file = ImgurService::uploadImageFromUrl(asset('storage/'.$this->thumbnail));

        return $new_mockup_file;
    }

}
