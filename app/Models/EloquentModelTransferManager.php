<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EloquentModelTransferManager
{
    protected $column_mappings = null;

    public function setMappings($column_mappings)
    {
        $this->column_mappings = $column_mappings;
    }

    public function mapProperties(Model $from_model, Model $to_model)
    {
        if ($this->column_mappings == null) {
            $this->generateColumnMappingPassthrough($from_model, $to_model);
        }

        foreach ($this->column_mappings as $column) {
            if ($from_model[$column] != null && $from_model[$column] != '') {
                $to_model[$column] = $from_model[$column];
            }
        }

        return $to_model;

    }

    private function generateColumnMappingPassthrough(Model $from_model, Model $to_model)
    {
        $this->column_mappings = array();

        foreach (Schema::getColumnListing($to_model->getTable()) as $key => $value) {
            // print_r($key . " => " . $value . "<br/>");
            if (Schema::hasColumn($from_model->getTable(), $value)) {
                $this->column_mappings[$value] = $value;
            }
        }
    }
}
