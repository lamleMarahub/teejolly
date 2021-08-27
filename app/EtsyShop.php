<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EtsyShop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'shop_url','shop_name','shop_sale','archived',
        'is_active'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
    }

    public function getOwner() {
        if (!$this->owner_id) return null;

        $user = User::find($this->owner_id);
        return $user;
    }
}
