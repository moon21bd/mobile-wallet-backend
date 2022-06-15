<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWUserDetail extends Model
{
    protected $casts = [

    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'ew_user_details';
    protected $fillable = ['user_id', 'gender', 'date_of_birth', 'nid_number', 'id_card_image', 'selfie_image', 'id_card_image_2', 'status', 'step', 'comment', 'city_id', 'country_id','address', 'is_synchronized', 'organization_ref_id', 'user_ref_id', 'role_ref_id', 'created_by', 'updated_by'];

    public function creator()
    {
        return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
    }

    /**
     * Get the user that owns the Profile.
     */
    public function userinfo()
    {
        return $this->belongsTo('App\Models\AdminUser', 'user_id', 'id');
    }

    public function ewcountry_country_id()
    {
        return $this->belongsTo('App\Models\EWCountry', 'country_id', 'id');
    }

    public function ewcity_city_id()
    {
        return $this->belongsTo('App\Models\EWCity', 'city_id', 'id');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($parent) { // before delete() method call this

        });
    }
}
