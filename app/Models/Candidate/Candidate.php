<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;
use App\Models\EloquentModelTransferManager;

class Candidate extends Model
{
    public $timestamps = false;

    //
    public function IsValid()
    {
        return true;
    }

    public function Election() {
        return $this->belongsTo('App\Election');
    }

    public function data_source()
    {
        return $this->hasOne('App\DataSource');
    }

    public static function findByCompositeKey($id, $data_source_id)
    {
        return Candidate::where('consolidated_candidate_id', $id)->where('data_source_id', $data_source_id);
    }

    public static function createOrUpdate($inputs)
    {
        $consolidated_candidate = ConsolidatedCandidate::where('name', $inputs['name'])
            ->where('district', $inputs['district'])->first();

        if($consolidated_candidate == null) {
            $consolidated_candidate = new ConsolidatedCandidate();
            $consolidated_candidate->load($inputs);
            $consolidated_candidate->save();

            $new_candidate = new Candidate();
            $new_candidate->load($inputs);
            $new_candidate->consolidated_candidate_id = $consolidated_candidate->id;
            var_dump($new_candidate);
            $new_candidate->save();

            return $new_candidate;
        } else {
            $id = $consolidated_candidate->id;
            $candidate = Candidate::findByCompositeKey($id, $inputs["data_source_id"])->first();
    
            if ($candidate == null) {
                $candidate = new Candidate();
            }
    
            $inputs['consolidated_candidate_id'] = $consolidated_candidate->id;
            
            $candidate->load($inputs);
    
            $candidate->save();

            $model_transfer_manager = new EloquentModelTransferManager();
            $consolidator = new CandidateConsolidator($model_transfer_manager);
            $consolidator->consolidate($candidate->id);
            
            return $candidate;
        }
    }

    public function load($inputs)
    {
        CandidateLoader::load($this, $inputs);
        
        if(array_key_exists('consolidated_candidate_id', $inputs)) {
            $this->consolidated_candidate_id = $inputs['consolidated_candidate_id'];
        }
    }
}
