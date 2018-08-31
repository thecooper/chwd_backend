<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;

class ConsolidatedElection extends Model
{
    public function load($inputs)
    {
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("input \$inputs is expected to be an array");
        }

        $this->name = $inputs['name'];
        $this->state_abbreviation = $inputs['state_abbreviation'];
        $this->election_date = $inputs['election_date'];
        $this->is_special = $inputs['is_special'];
        $this->is_runoff = $inputs['is_runoff'];
        $this->election_type = $inputs['election_type'];
        // $this->election_type = $inputs['election_type']; this can be derived from is_special and is_runoff
    }
}
