<?php

namespace App\Models\Candidate;

use Illuminate\Database\Eloquent\Model;
use App\Models\EloquentModelTransferManager;
use Illuminate\Database\Eloquent\Builder;

class Candidate extends Model
{
    public $incrementing = false;
    // protected $primaryKey = "name";

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('consolidated_candidate_id', '=', $this->getAttribute('consolidated_candidate_id'))
            ->where('data_source_id', '=', $this->getAttribute('data_source_id'));
        return $query;
    }

    //
    public function IsValid()
    {
        return true;
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
            $consolidated_candidate->load_fields($inputs);
            $consolidated_candidate->save();

            $new_candidate = new Candidate();
            $new_candidate->load_fields($inputs);
            $new_candidate->consolidated_candidate_id = $consolidated_candidate->id;
            $new_candidate->save();

            return $new_candidate;
        } else {
            $candidate = Candidate::findByCompositeKey($consolidated_candidate->id, $inputs["data_source_id"])->first();
    
            if ($candidate == null) {
                $candidate = new Candidate();
            }
    
            $inputs['consolidated_candidate_id'] = $consolidated_candidate->id;
            
            $candidate->load_fields($inputs);
            
            try {
                $candidate->save();
            } catch (Exception $ex) {
                throw $ex;
            }

            $model_transfer_manager = new EloquentModelTransferManager();
            $consolidator = new CandidateConsolidator($model_transfer_manager);
            $consolidator->consolidate($candidate->id);
            
            return $candidate;
        }
    }

    public function load_fields($inputs)
    {
        CandidateLoader::load($this, $inputs);
        
        $this->data_source_id = $inputs['data_source_id'];
        
        if(array_key_exists('consolidated_candidate_id', $inputs)) {
            $this->consolidated_candidate_id = $inputs['consolidated_candidate_id'];
        }
    }
}
