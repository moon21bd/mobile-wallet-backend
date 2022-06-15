<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class EWCountry extends Model
{
	protected $casts = [
        
    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_countries';
	protected $fillable = ['name','status','is_synchronized','organization_ref_id','user_ref_id','role_ref_id','created_by','updated_by'];
	
	public function creator(){
		return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
	}

    /**
     * Get primary_image attribute.
     *
     * @param string $flag
     *
     * @return string
     */
    public function getFlagAttribute($flag)
    {
        if (url()->isValidUrl($flag)) {
            return $flag;
        }
        $disk = config('admin.upload.disk');
        if ($flag && array_key_exists($disk, config('filesystems.disks'))) {
            if (Storage::disk(config('admin.upload.disk'))->exists($flag)) {
                return Storage::disk(config('admin.upload.disk'))->url($flag);
            }
        }
        $default = '';
        return admin_asset($default);
    }


	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this
			 
		});
	}
}