<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;

class ConsolidatedCandidate extends Model
{
    public function load($inputs)
    {
        CandidateLoader::load($this, $inputs);
        
        if(array_key_exists('id', $inputs)) {
            $this->id = $inputs['id'];
        }
    }
}
