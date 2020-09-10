<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PrivatePoolPartners extends Model
{
	protected $fillable = [
		'pool_id',
		'fleet_id',
		'status',
		'action_id'
	];

	public $timestamps = false;


}
