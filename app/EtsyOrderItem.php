<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtsyOrderItem extends Model
{
    //
    use SoftDeletes;

    protected $table = 'etsy_orders_items';

    protected $fillable = ['transaction_id','title','seller_user_id','buyer_user_id','quantity','receipt_id','is_digital','listing_id','variation_1','variation_2','variation_3','creation_tsz'];

    protected $dates = ['created_at', 'updated_at'];

    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
    } 
}
