<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HttpLog extends Model
{
    use HasFactory;

    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'http_logs';
    protected $fillable = [
        'log_name', 'requester_ref_id', 'receiver_ref_id', 'from_url', 'to_url', 'method', 'request', 'response', 'status_code', 'direction','response_time', 'created_by', 'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($parent) { // before delete() method call this

        });
    }
}
