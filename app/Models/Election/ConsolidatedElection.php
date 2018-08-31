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
}
