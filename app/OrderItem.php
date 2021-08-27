<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'order_id', 'asin','thumbnail','sku','style','size','color','design_id','quantity','amz_order_date'
    ];

    protected $dates = ['created_at', 'updated_at'];
}
