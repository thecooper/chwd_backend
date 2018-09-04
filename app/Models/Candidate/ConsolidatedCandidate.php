<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;

class ConsolidatedCandidate extends Model
{
    protected $primaryKey = 'id';

    public function load($inputs)
    {
        CandidateLoader::load($this, $inputs);
        
        if(array_key_exists('id', $inputs)) {
            $this->id = $inputs['id'];
        }
    }

    public function election() {
        return $this->belongsTo('App\Models\Election\ConsolidatedElection');
    }
}
