<?php

namespace App\Services;


use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AwsS3Service
{
    public static function instance()
    {
        return new AwsS3Service();
    }

    /**
     * @param UploadedFile $file
     * @param string $dirname
     * @param string $newfilename
     * @return string
     */
    public function storeUploadFile($file, $dirname, $newfilename)
    {
        Log::info("S3-upload: " . $dirname . $newfilename);
        $filename = $file->storeAs($dirname, $newfilename, [
            'disk' => 's3',
            'visibility' => 'public'
        ]);

        return $filename;
    }

    /**
     * @param string $filekey
     * @return string|null
     */
    public function getObjectUrl($filekey)
    {
        if (!Storage::disk('s3')->exists($filekey)) {
            return null;
        }

        $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

        return $client->getObjectUrl(config('filesystems.disks.s3.bucket'), $filekey);
    }

    /**
     * @param string $filepath
     * @return string
     */
    public function getUrl($filepath)
    {
        $url = config('filesystems.disks.s3.url') . '/' . config('filesystems.disks.s3.bucket') . '/';
        return $url . $filepath;
    }

    /**
     * @param string $filepath
     * @param string $expiry
     * @return string
     */
    public function getSharedUrl($filepath, $expiry = "+1 hours")
    {
        if (!Storage::disk('s3')->exists($filepath)) {
            return null;
        }

        $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

        $command = $client->getCommand('GetObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $filepath
        ]);

        $request = $client->createPresignedRequest($command, $expiry);

        return (string) $request->getUri();
    }

    /**
     * @param string $folder
     * @param string $filename
     * @param bool $removeLocal
     * @throws \Exception
     */
    public function upload($folder, $filename, $removeLocal = true)
    {
        // Upload file to S3
        $result = Storage::disk('s3')->putFileAs(
            $folder,
            new File(Storage::path(($folder ? $folder : '') . $filename)),
            $filename,
            ['visibility' => 'public']
        );

        Log::info("S3-upload: " . Storage::path(($folder ? $folder : '') . $filename));
        // Forces collection of any existing garbage cycles
        // If we don't add this, in some cases the file remains locked
        gc_collect_cycles();

        if ($result == false) {
            throw new \Exception("Couldn't upload file to S3");
        }

        // delete file from local filesystem
        if ($removeLocal && Storage::exists(($folder ? $folder : '') . $filename)) {
            Storage::delete(($folder ? $folder : '') . $filename);
        }
    }

    /**
     * @param string $filepath
     * @return \Illuminate\Filesystem\Filesystem|string
     */
    public function getFile($filepath)
    {
        // Basic validation to check if the file exists and is in the user directory
        if (!Storage::disk('s3')->exists($filepath)) {
            return null;
        }

        return Storage::disk('s3')->get($filepath);
    }

    /*
     * @param string $filepath
     * @return bool
     */
    public function delete($filepath)
    {
        // Basic validation to check if the file exists and is in the user directory
        if (Storage::disk('s3')->exists($filepath)) {
            return Storage::disk('s3')->delete($filepath);
        }
        return false;
    }

    /**
     * @param string $filepath
     * @return bool
     */
    public function exists($filepath)
    {
        return Storage::disk('s3')->exists($filepath);
    }
}
