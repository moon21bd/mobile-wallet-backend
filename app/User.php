<?php

namespace App;

use Encore\Admin\Traits\ModelTree;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    //use Notifiable;
    use HasApiTokens, Notifiable;
    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    protected $table = 'admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'pin', 'name', 'avatar'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * set username column for login
     *
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }
}
