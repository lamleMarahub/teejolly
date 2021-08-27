<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\EtsyShop;
use App\User;

class EtsyOrder extends Model
{
    //
    use SoftDeletes;

    protected $table = 'etsy_orders';

    protected $fillable = ['receipt_id','receipt_type','order_id','seller_user_id','buyer_user_id','creation_tsz','can_refund','last_modified_tsz','name','first_line','name','first_line','second_line','city','state','zip','formatted_address',
    'payment_method','payment_email','message_from_seller','message_from_buyer','was_paid','total_price','total_shipping_cost','currency_code','message_from_payment','was_shipped','buyer_email','seller_email','discount_amt',
    'subtotal','grandtotal','adjusted_grandtotal','buyer_adjusted_grandtotal','shipped_date','is_update','fulfillment_cost','tracking_code','fulfillment_carrier','owner_id','country_code','revenue','status'];

    protected $dates = ['created_at', 'updated_at'];

    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
    }
    
    public function getOwner($owner_id) {
        $user = User::find($owner_id);
        if(!$user) return null;
        return $user;
    }
}
