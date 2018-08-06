<?php

namespace App\Models;

use App\Models\DataConsolidator;
use App\Models\DataSourcePriority;
use App\Models\EloquentModelTransferManager;

abstract class DataConsolidator
{

    private $transfer_manager;

    public function __construct(EloquentModelTransferManager $transfer_manager)
    {
        $this->transfer_manager = $transfer_manager;
    }

    public function consolidate($id)
    {
        $bundle = $this->getModelsForConsolidation($id);

        if (count($bundle->from_models) == 0) {
            return array("error" => "no elections found to consolidate");
        }

        $data_source_priorities = DataSourcePriority::where('destination_table', $bundle->consolidation_table)->get()->sortByDesc('priority');

        foreach ($data_source_priorities as $data_priority) {
            $from_model = $bundle->from_models->firstWhere('data_source_id', $data_priority->data_source_id);

            if ($from_model != null) {
                $this->transfer_manager->mapProperties($from_model, $bundle->to_model);
            }

        }

        $bundle->to_model->save();

        return $bundle->to_model;
    }

    abstract protected function getModelsForConsolidation($id);
}
