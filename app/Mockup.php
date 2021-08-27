<?php

namespace App;

use App\Traits\MockupTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mockup extends Model
{
    use SoftDeletes;
    // 2021-07: move to S3
    use MockupTrait;

    protected $fillable = [
        'owner_id','title',
        'design_x','design_y','design_width','design_height','design_angle','design_opacity',
        'color','type','size','width','height','color_name','color_map','color_code','is_pure',
        'filename','extension','is_shared','is_active'
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $appends = ['file_url'];

    public function isOwnerOrAdmin(User $user) {
        if (!$user) return false;
        if ($user->isAdmin()) return true;
		return ($this->owner_id == $user->id);
	}

    public function isCreatedByUser(User $user) {
		if (!$user) return false;
		return ($this->owner_id == $user->id);
	}

	public function isCreatedByUserId($userId) {
		$user = User::find($userId);
		return $this->isCreatedByUser($user);
	}
}
