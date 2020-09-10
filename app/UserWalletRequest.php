<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWalletRequest extends Model
{

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/
    protected $fillable = [
        'user_id',        
        'alias_id',        
        'amount',
        'status', 
        'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
