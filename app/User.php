<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'is_admin', 'is_seller','is_designer', 'is_partner', 'api_token', 'last_login_at', 'last_login_ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function isActive() {
        return $this->is_active == true;
    }

    function isAdmin() {
        return $this->is_admin == true;
    }
    
    function isSeller() {
        return $this->is_seller == true;
    } 

    function isDesigner() {
        return $this->is_designer == true;
    } 

    function isPartner() {
        return $this->is_partner == true;
    } 

    function isDeleted() {
        return $this->deleted_at != null;
    }
    
}
