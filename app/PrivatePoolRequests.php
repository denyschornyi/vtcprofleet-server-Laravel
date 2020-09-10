<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PrivatePoolRequests extends Model {
	protected $fillable = [
		'private_id',
		'request_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at'
	];

	public function privatePool()
	{
		return $this->hasOne('App\PrivatePools','id');
	}
}
