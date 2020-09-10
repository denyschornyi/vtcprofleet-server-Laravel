<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FleetPolygonShape extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'coordinate', 'shape','fleet_id'
    ];


}
