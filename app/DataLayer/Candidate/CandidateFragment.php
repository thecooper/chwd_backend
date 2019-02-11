<?php

namespace App\DataLayer\Candidate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\DataLayer\EloquentModelTransferManager;

class CandidateFragment extends Model
{
    public $incrementing = false;

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('candidate_id', '=', $this->getAttribute('candidate_id'))
            ->where('data_source_id', '=', $this->getAttribute('data_source_id'));
        return $query;
    }

    public function data_source()
    {
        return $this->hasOne('App\DataSource');
    }
}
