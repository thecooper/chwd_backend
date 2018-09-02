<?php

namespace App\DataSources;

class FieldMapper {
    private $column_mapping;
    private $field_set = null;

    function __construct($column_mapping) {
        $this->column_mapping = $column_mapping;
    }

    public function load_fields($fields) {
        $this->field_set = $fields;
    }

    public function get_value($field_name) {
        if(!$this->has_value($field_name)) {
            return null;
        }
        
        return $this->field_set[$this->column_mapping[$field_name]];
    }

    public function get_fields() {
        $translated_fields = array();

        foreach($this->column_mapping as $field_name => $translated_field_index) {
            $translated_fields[$field_name] = $this->field_set[$translated_field_index];
        }

        return $translated_fields;
    }

    public function has_value($field_name) {
        if($this->field_set == null) {
            throw new \Exception('FieldMapper::get_value() - fields have not yet been set');
        }
        
        if(!array_key_exists($field_name, $this->column_mapping)) {
            return false;
        }

        $field_index = $this->column_mapping[$field_name];
        
        if(!array_key_exists($field_index, $this->field_set)) {
            return false;
        }

        return true;
    }
}