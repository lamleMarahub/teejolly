<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id','title','description','tags','uid','brand_name','image_url_1','image_url_2','image_url_3'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

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
