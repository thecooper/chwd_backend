<?php

namespace App;

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
        'password', 
        'address_line_1', 
        'address_line_2', 
        'city', 
        'state', 
        'zip', 
        'state_abbreviation',
        'congressional_district',
        'state_legislative_district',
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
        $this->hasMany("App\Userballot");
    }
}
