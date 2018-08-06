<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class UserBallot
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
        $this->belongsTo('App\User');
    }
}
