<?php


namespace App\Traits;


use App\Services\AwsS3Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait DesignTrait
{

    /**
     * 2021-27: move to S3
     * @return string
     */
    public function getFileUrlAttribute()
    {
        // Local storage
        if (Storage::exists($this->filename)) {
            return asset('storage/'.$this->filename);
        }
        // S3 storage
        return AwsS3Service::instance()->getUrl($this->filename);
    }

    /**
     * 2021-27: move to S3
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        // Local storage
        if (Storage::exists($this->thumbnail)) {
            return asset('storage/'.$this->thumbnail);
        }
        // S3 storage
        return AwsS3Service::instance()->getUrl($this->thumbnail);
    }

    public function getDirnameAndCreateFolderPath()
    {
        $folderPath = Storage::path(dirname($this->filename));
        if (!file_exists($folderPath)) {
	    $oldmask = umask(0);
            mkdir($folderPath, 0755, true);
            umask($oldmask);
            //mkdir($folderPath, 0755, true);
        }
        return dirname($this->filename);
    }

    public function getThumbnailFileName($new_width, $new_height)
    {
        // Force create folder for save image later
        $dirname = $this->getDirnameAndCreateFolderPath();
        return $dirname . '/t-' . Str::random(5) . '-' . Str::slug($this->title,'-') . "-$new_width"."x$new_height." . $this->extension;
    }

    public function removeFiles()
    {
        try {
            if (Storage::exists($this->filename)) {
                Storage::delete($this->filename);
                Storage::delete($this->thumbnail);
                $path = dirname($this->filename);
                //return $path;
                if ($path != '.') Storage::deleteDirectory($path);
            }
            $awsS3Service = AwsS3Service::instance();
            if ($awsS3Service->exists($this->filename)) {
                $awsS3Service->delete($this->filename);
                Log::info("DELETE S3: $this->filename ");
                $awsS3Service->delete($this->thumbnail);
                Log::info("DELETE S3: $this->thumbnail ");
            }
        } catch (\Exception $e) {
            Log::info("DELETE PROBLEM: {$e->getMessage()}");
        }
    }
}
