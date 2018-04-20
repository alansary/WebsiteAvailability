<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url', 'user_id', 'is_active',
    ];

    public function user()
    {
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function downLog()
    {
    	return $this->hasOne('App\DownLog', 'url_id', 'id');
    }
}
