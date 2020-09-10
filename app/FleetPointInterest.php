<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FleetPointInterest extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rule_name','status','fleet_id'
    ];
}
