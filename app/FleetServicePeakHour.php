<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class FleetServicePeakHour extends Model
{
    protected $table='fleet_service_peak_hours';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_type_id','peak_hours_id','min_price','fleet_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at'
    ];


}
