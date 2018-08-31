<?php

namespace App\Models\Candidate;

use App\Models\ConsolidationBundle;
use App\Models\DataConsolidator;
use App\Models\EloquentModelTransferManager;
use App\Models\Candidate\Candidate;

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
