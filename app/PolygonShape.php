<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PolygonShape extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'coordinate', 'shape'
    ];


}
