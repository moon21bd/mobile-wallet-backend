<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWUserActivityLimitLog extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_user_activity_limit_logs';
    protected $fillable = ['trx_activity_type_id', 'user_id', 'previous_data', 'current_data', 'is_synchronized', 'organization_ref_id', 'user_ref_id', 'role_ref_id', 'created_by', 'updated_by'];

    public function ewtrxactivitytype_activity_type_id()
    {
        return $this->belongsTo(EWTrxActivityType::class, "trx_activity_type_id", "id");
    }

    public function creator()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    public function userinfo()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($parent) { // before delete() method call this

        });
    }
}
