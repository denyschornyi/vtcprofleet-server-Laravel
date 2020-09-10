<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FleetServiceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price',
        'fixed',
        'description',
        'status',
        'minute',
        'hour',
        'distance',
        'calculator',
        // 'capacity',
        'waiting_free_mins',
        'waiting_min_charge',
        // 'luggage_capacity',
	    'service_type_id',
        'fleet_id',
        'min_price',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    public function service_type()
    {
    	return $this->belongsTo('App\ServiceType');
    }

    public function fleet_service_request()
    {
    	return $this->hasMany('App\UserRequests');
    }

}
