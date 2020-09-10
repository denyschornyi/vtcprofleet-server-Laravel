<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PrivatePools extends Model {
	protected $fillable = [
		'pool_id',
		'pool_name',
		'from_fleet_id',
		'status'
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

	/**
	 * Request Model Linked
	 */
	public function PrivatePoolID() {
		return $this->hasOne( 'App\PrivatePoolRequests', 'private_id' );
	}
}
