<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DownLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url_id',
    ];

    public function url()
    {
        return $this->belongsTo('App\Url', 'url_id', 'id');
    }
}
