<?php

namespace App\Models\Election;

use Illuminate\Database\Eloquent\Model;
use App\Models\EloquentModelTransferManager;

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
        ElectionLoader::load($this, $inputs);
        $this->consolidated_election_id = $inputs['consolidated_election_id'];
        $this->data_source_id = $inputs['data_source_id'];
    }

    public static function createOrUpdate($inputs)
    {
        $consolidated_election = ConsolidatedElection::where('name', $inputs['name'])->first();

        if($consolidated_election == null) {
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

            $model_transfer_manager = new EloquentModelTransferManager();
            $consolidator = new ElectionConsolidator($model_transfer_manager);
            $consolidator->consolidate($election->id);
            
            return $election;
        }
    }
}
