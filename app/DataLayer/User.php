<?php

namespace App\DataLayer;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function ballots()
    {
        return $this->hasMany("App\DataLayer\Ballot\Ballot");
    }

    public function news()
    {
        return $this->belongsToMany('App\DataLayer\News', 'user_news');
    }
}
