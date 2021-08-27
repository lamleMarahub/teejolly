<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // protected $pagesize; // = env('PAGINATION_PAGESIZE', 40);

    // public function __construct()
    // {
    //     $this->pagesize = env('PAGINATION_PAGESIZE', 40);
    // }
}
