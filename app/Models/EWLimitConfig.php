<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWLimitConfig extends Model
{
	protected $casts = [

    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_limit_configs';
	protected $fillable = ['trx_activity_type_id','frequency_id','max_trx_count','max_trx_amount','status','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];

	public function creator(){
		return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
	}
    public function limitConfigLogs(){
        return $this->hasMany(EWLimitConfigLog::class, 'config_id', 'id');
    }

    public function ewtrxactivitytype_trx_activity_type_id(){
    		return $this->belongsTo(EWTrxActivityType::class, "trx_activity_type_id","id");
    }
    public function frequencies(){
    		return $this->belongsTo(EWFrequency::class, "frequency_id","id");
    }
	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this

		});
	}
}
