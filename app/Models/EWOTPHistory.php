<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWOTPHistory extends Model
{
	protected $casts = [

    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_otp_histories';
	protected $fillable = ['mobile_no','otp','otp_used','smsgw_response','channel','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by','code_lifetime','type','ref_id'];

	public function creator(){
		return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
	}


	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this

		});
	}
}
