<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;

class ConsolidatedElection extends Model
{
    public function load($inputs)
    {
        ElectionLoader::load($this, $inputs);
        
        if(array_key_exists('id', $inputs)) {
            $this->id = $inputs['id'];
        }
    }
    
    /**
     * @return Builder of candidates that belong to this election
     */
    public function candidates()
    {
        return $this->hasMany('App\Models\Candidate\ConsolidatedCandidate', 'election_id');
    }

    /**
     * @return string[] string array of races that are being run in a particular election
     */
    public function races()
    {
        return [];
        // return $this->hasMany('App\Candidate')->
    }
}
