<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\User;

class UserBallot extends Model
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
        return $this->belongsTo('App\User');
    }

    public function candidates()
    {
        return $this->belongsToMany('App\Models\Candidate\ConsolidatedCandidate', 'user_ballot_candidates', 'user_ballot_id', 'candidate_id');
    }

    public function verify_belongs_to_user(User $user)
    {
        return $this->user_id == $user->id;
    }
}
