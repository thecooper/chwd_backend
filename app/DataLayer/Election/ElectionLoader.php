<?php 

namespace App\DataLayer\Election;

class ElectionLoader {
    public static function load($model, $inputs) {
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("input \$inputs is expected to be an array");
        }

        $model->name = $inputs['name'];
        $model->state_abbreviation = $inputs['state_abbreviation'];
        $model->primary_election_date = $inputs['primary_election_date'];
        $model->general_election_date = $inputs['general_election_date'];
        $model->runoff_election_date = $inputs['runoff_election_date'];
    }
}