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

        $this->name = $inputs['name'];
        $this->state_abbreviation = $inputs['state_abbreviation'];
        $this->county = $inputs['county'];
        $this->district = $inputs['district'];
        $this->primary_date = $inputs['primary_date'];
        $this->general_date = $inputs['general_date'];
        $this->is_special = $inputs['is_special'];
        $this->is_runoff = $inputs['is_runoff'];
        $this->data_source_id = $inputs['data_source_id'];
    }

    public static function createOrUpdate($inputs)
    {
        $election = Election::findByCompositeKey($inputs["name"], $inputs["data_source_id"])->first();

        if ($election == null) {
            $election = new Election();
        }

        $election->load($inputs);

        $election->save();

        return $election;
    }
}
