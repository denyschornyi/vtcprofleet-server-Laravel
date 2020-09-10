<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Pool extends Model {
	protected $fillable = [
		'request_id',
		'pool_type',
		'from',
		'commission_rate',
		'manual_assigned_at',
		'timeout',
		'expire_date',
		'fleet_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'manual_assigned_at',
	];

	/**
	 * Request Model Linked
	 */
	public function request() {
		return $this->belongsTo( 'App\UserRequests', 'request_id' );
	}
}
