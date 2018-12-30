<?php

namespace App\DataLayer\Election;

use App\DataLayer\ConsolidationBundle;
use App\DataLayer\DataConsolidator;
use App\DataLayer\EloquentModelTransferManager;

class ElectionConsolidator extends DataConsolidator
{
    public function __construct(EloquentModelTransferManager $transfer_manager)
    {
        parent::__construct($transfer_manager);
    }

    protected function getModelsForConsolidation($name)
    {
        return new ConsolidationBundle(
            "elections",
            Election::where("name", $name)->get(),
            ConsolidatedElection::where("name", $name)->first() ?? new ConsolidatedElection()
        );
    }
}
