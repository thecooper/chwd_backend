<?php

namespace App\Models\Election;

use App\Models\ConsolidationBundle;
use App\Models\DataConsolidator;
use App\Models\Election\Election;
use App\Models\EloquentModelTransferManager;

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
