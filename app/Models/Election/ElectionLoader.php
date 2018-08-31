<?php 

namespace App\Models\Election;

class ElectionLoader {
    public static function load($model, $inputs) {
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("input \$inputs is expected to be an array");
        }

        $model->name = $inputs['name'];
        $model->state_abbreviation = $inputs['state_abbreviation'];
        $model->election_date = $inputs['election_date'];
        $model->is_special = $inputs['is_special'];
        $model->is_runoff = $inputs['is_runoff'];
    }
}