<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    public $incrementing = false;
    protected $primaryKey = "name";

    public function Candidates()
    {
        return $this->hasMany('App\Candidate');
    }

    public static function generate($inputs)
    {
        // TODO: do parameter checking

        $election = new Election();

        $election->load($inputs);

        return $election;
    }

    public static function findByCompositeKey($name, $data_source_id)
    {
        return Election::where('name', $name)->where('data_source_id', $data_source_id);
    }

    public function load($inputs)
    {
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("input \$inputs is expected to be an array");
        }

        $this->consolidated_election_id = $inputs['consolidated_election_id'];
        $this->name = $inputs['name'];
        $this->state_abbreviation = $inputs['state_abbreviation'];
        $this->election_date = $inputs['election_date'];
        $this->is_special = $inputs['is_special'];
        $this->is_runoff = $inputs['is_runoff'];
        $this->election_type = $inputs['election_type'];
        $this->data_source_id = $inputs['data_source_id'];
        // $this->election_type = $inputs['election_type']; this can be derived from is_special and is_runoff
    }

    public static function createOrUpdate($inputs)
    {
        $consolidated_election = ConsolidatedElection::where('name', $inputs['name'])->first();
        // $new_consolidated_election = false;

        if($consolidated_election == null) {
            $new_consolidated_election = true;
            $consolidated_election = new ConsolidatedElection();
            $consolidated_election->load($inputs);
            $consolidated_election->save();

            $new_election = new Election();
            $new_election->load($inputs);
            $new_election->consolidated_election_id = $consolidated_election->id;
            $new_election->save();

            return $new_election;
        } else {
            $election = Election::findByCompositeKey($inputs["name"], $inputs["data_source_id"])->first();
    
            if ($election == null) {
                $election = new Election();
            }
    
            $inputs['consolidated_election_id'] = $consolidated_election->id;
            
            $election->load($inputs);
    
            $election->save();
            
            return $election;
        }
    }
}
