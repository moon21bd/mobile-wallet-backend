<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EWUserConfig extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_user_config';
    protected $fillable = [
        'config_id',
        'user_id',
        'name',
        'value'
    ];

    public function creator(){
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    public function userinfo()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

}
