<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWTrxActivityType extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_trx_activity_types';
    protected $fillable = ['activity_type', 'slug', 'receiver_type_id', 'per_trx_min_amount', 'per_trx_max_amount', 'status', 'is_synchronized', 'organization_ref_id', 'user_ref_id', 'role_ref_id', 'created_by', 'updated_by'];

    public function creator()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    public function activity_type_with_receiver_type_id()
    {
        return $this->belongsTo(EWTrxActivityType::class, 'receiver_type_id', 'id')->select('id', 'activity_type');
    }


    public static function boot()
    {
        parent::boot();
        static::deleting(function ($parent) { // before delete() method call this

        });
    }
}
