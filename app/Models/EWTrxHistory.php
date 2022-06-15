<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWTrxHistory extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_trx_histories';
    protected $fillable = ['sender_user_id', 'receiver_user_id', 'trx_activity_type_id', 'trx_amount', 'trx_type', 'trx_status', 'trx_note', 'owner_id', 'trx_uuid', 'balance', 'is_charge', 'trx_reference', 'is_synchronized', 'organization_ref_id', 'user_ref_id', 'role_ref_id', 'created_by', 'updated_by'];

    public function creator()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    public function trx_activity_type()
    {
        return $this->belongsTo(EWTrxActivityType::class, 'trx_activity_type_id', 'id');
    }

    public function sender_user()
    {
        return $this->belongsTo('App\Models\AdminUser', 'sender_user_id', 'id');
    }

    public function receiver_user()
    {
        return $this->belongsTo('App\Models\AdminUser', 'receiver_user_id', 'id');
    }

    public function owner_user()
    {
        return $this->belongsTo('App\Models\AdminUser', 'owner_id', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($parent) { // before delete() method call this

        });
    }
}
