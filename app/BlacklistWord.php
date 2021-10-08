<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlacklistWord extends Model
{
    use SoftDeletes;

    protected $table = 'blacklist_words';

    protected $fillable = ['keyword','type'];

    protected $dates = ['created_at', 'updated_at'];
    
}
