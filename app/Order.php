<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class Order extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'brand','fulfillment_id', 'is_update', 'tracking_number', 'is_cancel', 'fulfillment_cost','fulfillment_by', 'carrier', 'owner_id',
        'amz_order_id','amz_order_date','full_name',       
        'address_1','address_2','city','state','zip_code','country', 'status'
    ];

    protected $dates = ['created_at', 'updated_at'];
    
    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
    }
    
    // public function getOwner($owner_id){
    //     $user = User::find($owner_id);
    //     if(!$user) return false;
    //     return $user;
    // }
    
    public function getOwner() {
        if (!$this->owner_id) return null;
        $user = User::find($this->owner_id);
        return $user;
    }
}
