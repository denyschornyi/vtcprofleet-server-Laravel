<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'provider_name',
        'image',
        'marker',
        'price',
        'fixed',
        'description',
        'status',
        'minute',
        'hour',
        'distance',
        'calculator',
        'capacity',
        'waiting_free_mins',
        'waiting_min_charge',
        'luggage_capacity',
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

    public function fleet_service_type()
    {
        return $this->hasOne('App\FleetServiceType','service_type_id','id');
    }
}
