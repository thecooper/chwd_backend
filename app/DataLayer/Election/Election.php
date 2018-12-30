<?php

namespace App\DataLayer\Election;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\DataLayer\EloquentModelTransferManager;

class Election extends Model
{
    public $incrementing = false;
    protected $primaryKey = "id";
    
    /**
     * Override for Model->setKeysForSaveQuery that allows the use of composite keys. In this case the key is based on
     * consolidated_election_id and data_source_id
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('consolidated_election_id', '=', $this->getAttribute('consolidated_election_id'))
            ->where('data_source_id', '=', $this->getAttribute('data_source_id'));
        return $query;
    }

    /**
     * Method for taking an array of field values and mapping them to the Election object
     * @return void
     */
    public function load_fields($inputs)
    {
        ElectionLoader::load($this, $inputs);
        $this->consolidated_election_id = $inputs['consolidated_election_id'];
        $this->data_source_id = $inputs['data_source_id'];
    }

    /**
     * Creates a new or updates an existing entry in the database for the election model and returns the model
     * @return Election the newly created election object or the modified election object from the database
     */
    public static function createOrUpdate($inputs)
    {
        $consolidated_election = ConsolidatedElection::where('state_abbreviation', $inputs['state_abbreviation'])
            ->where('primary_election_date', $inputs['primary_election_date'])
            ->where('general_election_date', $inputs['general_election_date'])
            ->where('runoff_election_date', $inputs['runoff_election_date'])
            ->first();

        if($consolidated_election == null) {
            $consolidated_election = new ConsolidatedElection();
            $consolidated_election->load_fields($inputs);
            $consolidated_election->save();

            $new_election = new Election();
            $new_election->load_fields($inputs);
            $new_election->consolidated_election_id = $consolidated_election->id;
            $new_election->save();

            return $new_election;
        } else {
            $election = Election::findByCompositeKey($consolidated_election->id, $inputs["data_source_id"])->first();
    
            if ($election == null) {
                $election = new Election();
            }
    
            $inputs['consolidated_election_id'] = $consolidated_election->id;
            
            $election->load_fields($inputs);
    
            $election->save();

            $model_transfer_manager = new EloquentModelTransferManager();
            $consolidator = new ElectionConsolidator($model_transfer_manager);
            $consolidator->consolidate($election->id);
            
            return $election;
        }
    }

    /**
     * Creates builder whose base starts with qualifying a query based on composite keys
     * @return Builder|null Election builder instance that does a where qualifier for both composite keys
     */
    public static function findByCompositeKey($id, $data_source_id)
    {
        return Election::where('consolidated_election_id', $id)->where('data_source_id', $data_source_id);
    }
}
