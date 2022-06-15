<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWUserActivityLimit extends Model
{
	protected $casts = [

    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_user_activity_limits';
	protected $fillable = ['activity_type_id','user_id','per_trx_min_amount','per_trx_max_amount','status','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];


	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this

		});
	}

    public function ewtrxactivitytype_activity_type_id()
    {
        return $this->belongsTo(EWTrxActivityType::class, "activity_type_id", "id");
    }

    public function creator(){
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    public function userinfo(){
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

}
