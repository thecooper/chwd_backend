<?php

namespace App\DataLayer\Ballot;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

use App\DataLayer\User;

class Ballot extends Model
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

    /**
     * The name of the table that backs this Eloquent model
     *
     * @var string
     */
    protected $table = 'user_ballots';

    public function user()
    {
        return $this->belongsTo('App\DataLayer\User');
    }

    public function candidates()
    {
        return $this->belongsToMany('App\DataLayer\Candidate\ConsolidatedCandidate', 'user_ballot_candidates', 'user_ballot_id', 'candidate_id');
    }

    public function verify_belongs_to_user(User $user)
    {
        return $this->user_id == $user->id;
    }
}
