<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    /**
     * Scope a query to only include popular users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeVehicle($query)
    {
        return $query->where('type', 'VEHICLE');
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDriver($query)
    {
        return $query->where('type', 'DRIVER');
    }

    public function providerdocuments()
    {
        return $this->hasOne('App\ProviderDocument', 'document_id')->select(array('url', 'status', 'document_id'));
    }
}