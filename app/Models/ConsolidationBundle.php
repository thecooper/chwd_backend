<?php

namespace App\Models;

class ConsolidationBundle
{
    public $consolidation_table;
    public $from_models;
    public $to_model;

    public function __construct(string $consolidation_table, $from_models, $to_model)
    {
        $this->consolidation_table = $consolidation_table;
        $this->from_models = $from_models;
        $this->to_model = $to_model;
    }
}
