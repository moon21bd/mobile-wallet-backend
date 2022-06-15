<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EWConfigLog extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_config_logs';
    protected $fillable = [
        'config_id',
        'previous_data',
        'current_data',
        'is_synchronized',
        'organization_ref_id',
        'user_ref_id',
        'role_ref_id',
        'created_by',
        'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }


}
