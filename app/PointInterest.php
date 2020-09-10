<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointInterest extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type','poi_id', 'status'
    ];
}
