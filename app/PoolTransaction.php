<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoolTransaction extends Model
{

	protected $fillable = ['request_id','pool_type','fleet_id','from_id','commission', 'ride_type'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
