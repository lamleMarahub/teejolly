<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CollectionExport extends Model
{
    protected $fillable = [
        'name','type','collection_id','owner_id','filename','extension','size'
    ];
}
