<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWSMSHistory extends Model
{
    use HasFactory;
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_sms_histories';
    protected $fillable = ['mobile_no','sms_provider','request_data','response_data','status','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];

    public function creator(){
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }


    public static function boot() {
        parent::boot();
        static::deleting(function($parent) { // before delete() method call this

        });
    }
}
