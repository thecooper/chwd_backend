<?php

namespace App\DataLayer\Election;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Election extends Model
{   
    /**
     * @return Builder of candidates that belong to this election
     */
    public function candidates()
    {
        return $this->hasMany('App\DataLayer\Candidate\ConsolidatedCandidate', 'election_id');
    }
}
