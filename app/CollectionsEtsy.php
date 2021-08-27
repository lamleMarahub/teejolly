<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Design;

class CollectionsEtsy extends Model
{
    //
    protected $table = 'collections_etsy';

    protected $fillable = [
        'collection_id','design_id','shop_id','listing_id','main_image_url','owner_id', 'is_digital', 'state'
    ];

    public function getDetail($id){
        $design = Design::find($id);
        if(!$design) return false;
        return $design;
    }
}
