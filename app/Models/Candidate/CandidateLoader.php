<?php 

namespace App\Models\Candidate;

class CandidateLoader {
    public static function load($model, $inputs) {
        if (!is_array($inputs)) {
            throw new \InvalidArgumentException("input \$inputs is expected to be an array");
        }
        
        $model->name = $inputs['name'];
        $model->election_id = $inputs['election_id'];
        $model->party_affiliation = $inputs['party_affiliation'];
        $model->election_status = $inputs['election_status'];
        $model->office = $inputs['office'];
        $model->office_level = $inputs['office_level'];
        $model->is_incumbent = $inputs['is_incumbent'];
        $model->district_type = $inputs['district_type'];
        $model->district = $inputs['district'];
        $model->district_identifier = $inputs['district_identifier'];
        $model->ballotpedia_url = $inputs['ballotpedia_url'];
        $model->website_url = $inputs['website_url'];
        $model->donate_url = $inputs['donate_url'];
        $model->facebook_profile = $inputs['facebook_profile'];
        $model->twitter_handle = $inputs['twitter_handle'];
    }
}