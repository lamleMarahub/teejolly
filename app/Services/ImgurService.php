<?php

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class ImgurService
{
    const END_POINT = 'https://api.imgur.com/3/image';

    public static function uploadImageFromUrl($imagePath)
    {
        $client = new GuzzleClient();
        $request = $client->request(
            'POST',
            ImgurService::END_POINT,
            [
                'headers' => [
                    'Authorization' => "Client-ID ".env('IMGUR_CLIENT_ID', '99ced24e3e55f1f'), // post as anonymous (ex: 169d680c32fec15)
                ],
                'form_params' => [
                    'image' => file_get_contents($imagePath)
                ]
            ]
        );
        $response = (string) $request->getBody();
        $jsonResponse = json_decode($response);
        return $jsonResponse->data->link; // return url of image
    }

    public static function uploadImageFromBinary($image)
    {
        $client = new GuzzleClient();
        $request = $client->request(
            'POST',
            ImgurService::END_POINT,
            [
                'headers' => [
                    'Authorization' => "Client-ID ".env('IMGUR_CLIENT_ID', '99ced24e3e55f1f'), // post as anonymous (ex: 169d680c32fec15)
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'image' => base64_encode($image->encode()->encoded)
                ]
            ]
        );
        $response = (string) $request->getBody();
        $jsonResponse = json_decode($response);
        return $jsonResponse->data->link; // return url of image
    }
}