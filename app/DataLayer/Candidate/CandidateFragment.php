<?php

namespace App\DataLayer\Candidate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\DataLayer\EloquentModelTransferManager;

class CandidateFragment extends Model
{
    public function data_source()
    {
        return $this->hasOne('App\DataSource');
    }
}
