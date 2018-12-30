<?php

namespace App\DataLayer\Candidate;

use App\DataLayer\ConsolidationBundle;
use App\DataLayer\DataConsolidator;
use App\DataLayer\EloquentModelTransferManager;

class CandidateConsolidator extends DataConsolidator
{
    public function __construct(EloquentModelTransferManager $transfer_manager)
    {
        parent::__construct($transfer_manager);
    }

    protected function getModelsForConsolidation($id)
    {
        return new ConsolidationBundle(
            "candidates",
            Candidate::where("consolidated_candidate_id", $id)->get(),
            ConsolidatedCandidate::where("id", $id)->first() ?? new ConsolidatedCandidate()
        );
    }
}
