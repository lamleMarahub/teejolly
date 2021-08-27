<?php


namespace App\Traits;


use App\Services\AwsS3Service;
use Illuminate\Support\Facades\Storage;

trait MockupTrait
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

}
