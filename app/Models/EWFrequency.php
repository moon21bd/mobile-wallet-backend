<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EWFrequency extends Model
{
	protected $casts = [
        
    ];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'ew_frequencies';
	protected $fillable = ['title','duration','unit','created_by','updated_by'];
	
	public function creator(){
		return $this->belongsTo(\Platform\Admin\Auth\Database\Administrator::class, 'created_by', 'id');
	}


	public static function boot() {
		parent::boot();
		static::deleting(function($parent) { // before delete() method call this
			 
		});
	}
}