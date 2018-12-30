<?php

namespace App\DataLayer\Election;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConsolidatedElection extends Model
{
    public function load_fields($inputs)
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
        return $this->hasMany('App\DataLayer\Candidate\ConsolidatedCandidate', 'election_id');
    }

    // /**
    //  * @return string[] string array of races that are being run in a particular election
    //  */
    // public function races()
    // {
    //     return [];
    //     // return $this->hasMany('App\Candidate')->
    // }
}
