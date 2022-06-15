<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWTrxActivityTypeLog extends Model
{
	protected $casts = [
        
    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_trx_activity_type_logs';
	protected $fillable = ['trx_activity_type_id','previous_data','current_data','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];
	
	public function creator(){
		return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
	}


	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this
			 
		});
	}
}